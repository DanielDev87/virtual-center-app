<?php

namespace App\Services;

use App\Models\RequestType;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class TicketSuggestionService
{
    private const OPEN_STATUSES = [1, 2, 5];
    private const MIN_SCORE = 3;
    private const MAX_RESULTS = 5;
    private const MAX_CANDIDATES = 300;

    private const STATUS_LABELS = [
        1 => 'Pendiente',
        2 => 'En progreso',
        5 => 'Realizado por operario',
    ];

    private const STOPWORDS = [
        'como', 'que', 'cual', 'cuales', 'donde', 'cuando', 'para', 'por', 'con', 'sin', 'una', 'uno', 'unos',
        'unas', 'los', 'las', 'del', 'quiero', 'puedo', 'debo', 'necesito', 'hacer', 'esta', 'este', 'esto',
        'ese', 'eso', 'favor', 'hola', 'buenas', 'sobre', 'desde', 'hay', 'tengo', 'tiene', 'ayuda', 'pero',
        'porque', 'entre', 'cada', 'todo', 'toda', 'ticket', 'tickets', 'solicitud', 'solicito', 'problema',
    ];

    /**
     * Sugiere tickets abiertos del mismo topico con texto parecido.
     * Solo devuelve datos identificables de los tickets que la persona puede abrir.
     * No combina ni cierra tickets: es solo informacion.
     */
    public function suggest(?int $topicId, string $title, string $description, ?User $user, ?int $excludeTicketId = null): array
    {
        $stems = array_values(array_unique(array_map(
            fn ($token) => $this->stem($token),
            $this->tokens($title . ' ' . $description)
        )));

        if (!$topicId || empty($stems)) {
            return [];
        }

        $query = Ticket::query()
            ->where('request_type_id', $topicId)
            ->whereIn('status', self::OPEN_STATUSES)
            ->orderByDesc('ticket_id')
            ->limit(self::MAX_CANDIDATES);

        if ($excludeTicketId) {
            $query->where('ticket_id', '!=', $excludeTicketId);
        }

        $candidates = $query->get([
            'ticket_id', 'ticket_number', 'title', 'requester_info', 'status', 'created_at',
            'requester_id', 'request_type_id', 'institution_id', 'mediator_id',
        ]);

        $scored = [];

        foreach ($candidates as $candidate) {
            $candidateTitle = $this->normalize((string) $candidate->title);
            $candidateText = $this->normalize(mb_substr(strip_tags((string) $candidate->requester_info), 0, 2000));
            $score = 0;

            foreach ($stems as $stem) {
                $pattern = '/\b' . preg_quote($stem, '/') . '/';

                if (preg_match($pattern, $candidateTitle)) {
                    $score += 2;
                }

                if (preg_match($pattern, $candidateText)) {
                    $score += 1;
                }
            }

            if ($score >= self::MIN_SCORE) {
                $scored[] = ['score' => $score, 'ticket' => $candidate];
            }
        }

        usort($scored, fn ($a, $b) => $b['score'] <=> $a['score']);

        $results = [];

        foreach (array_slice($scored, 0, self::MAX_RESULTS) as $item) {
            $ticket = $item['ticket'];
            $accessible = $this->canOpen($user, $ticket);

            $results[] = [
                'accessible' => $accessible,
                'ticket_number' => $accessible ? $ticket->ticket_number : null,
                'title' => $accessible ? $ticket->title : null,
                'url' => $accessible ? $this->urlFor($user, $ticket) : null,
                'status' => self::STATUS_LABELS[(int) $ticket->status] ?? 'Abierto',
                'created_at' => $ticket->created_at ? $ticket->created_at->format('d/m/Y') : '',
            ];
        }

        return $results;
    }

    private function canOpen(?User $user, Ticket $ticket): bool
    {
        if (!$user) {
            return false;
        }

        $role = $user->role->role_name ?? '';

        return match ($role) {
            'Admin', 'Monitor' => true,
            'Admin Área' => $this->areaAdminCanOpen($user, $ticket),
            'Contributor' => $this->contributorCanOpen($user, $ticket),
            'Requester' => (int) $ticket->requester_id === (int) $user->user_id,
            default => false,
        };
    }

    private function urlFor(User $user, Ticket $ticket): ?string
    {
        $role = $user->role->role_name ?? '';

        return match ($role) {
            'Admin', 'Monitor' => route('admin.tickets.show', $ticket->ticket_id),
            'Admin Área' => route('area-admin.tickets.show', $ticket->ticket_id),
            'Contributor' => route('contributors.tickets.show', $ticket->ticket_id),
            'Requester' => route('service-management.show', $ticket->ticket_id),
            default => null,
        };
    }

    /**
     * Misma regla de alcance que usa el Admin Area en sus listados: su area y su regional.
     */
    private function areaAdminCanOpen(User $user, Ticket $ticket): bool
    {
        if (empty($user->area_id)) {
            return false;
        }

        $userId = (int) $user->user_id;

        $query = Ticket::whereKey($ticket->ticket_id)
            ->whereHas('requestType', fn ($q) => $q->where('area_id', $user->area_id));

        if (Schema::hasTable('request_type_regional_assignments')) {
            $query->where(function ($scope) use ($userId) {
                $scope->whereHas('requestType', fn ($q) => $q->whereDoesntHave('regionalAssignments'))
                    ->orWhereHas('requestType.regionalAssignments', function ($regional) use ($userId) {
                        $regional->where('user_id', $userId)
                            ->whereColumn('request_type_regional_assignments.institution_id', 'tickets.institution_id');
                    });
            });
        }

        return $query->exists();
    }

    /**
     * Misma regla que usa el Contributor para abrir un ticket: es suyo, esta en su equipo o es de sus topicos.
     */
    private function contributorCanOpen(User $user, Ticket $ticket): bool
    {
        $userId = (int) $user->user_id;
        $topicIds = $this->contributorTopicIds($userId);

        return Ticket::whereKey($ticket->ticket_id)
            ->where(function ($q) use ($userId, $topicIds) {
                $q->where('mediator_id', $userId)
                    ->orWhereHas('assignments', fn ($a) => $a->where('user_id', $userId)->where('status', 'active'))
                    ->orWhereIn('request_type_id', $topicIds);
            })
            ->exists();
    }

    private function contributorTopicIds(int $userId): array
    {
        if (Schema::hasTable('request_type_user')) {
            $ids = RequestType::where(function ($q) use ($userId) {
                $q->whereHas('collaborators', fn ($c) => $c->where('users.user_id', $userId))
                    ->orWhere('gestor_id', $userId);
            })->pluck('type_id');
        } else {
            $ids = RequestType::where('gestor_id', $userId)->pluck('type_id');
        }

        if (Schema::hasTable('request_type_regional_assignments')) {
            $ids = $ids->merge(
                DB::table('request_type_regional_assignments')->where('user_id', $userId)->pluck('request_type_id')
            );
        }

        return $ids->unique()->values()->all();
    }

    private function normalize(string $text): string
    {
        $text = Str::lower(Str::ascii($text));
        $text = preg_replace('/[^a-z0-9\s]/', ' ', $text) ?? $text;

        return trim(preg_replace('/\s+/', ' ', $text) ?? $text);
    }

    private function tokens(string $text): array
    {
        $words = explode(' ', $this->normalize($text));

        return array_values(array_unique(array_filter(
            $words,
            fn ($word) => strlen($word) >= 3 && !in_array($word, self::STOPWORDS, true)
        )));
    }

    private function stem(string $word): string
    {
        $length = strlen($word);

        if ($length >= 6) {
            return substr($word, 0, 5);
        }

        if ($length >= 5) {
            return substr($word, 0, 4);
        }

        return $word;
    }
}