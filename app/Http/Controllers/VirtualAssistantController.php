<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Models\KnowledgeArticle;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class VirtualAssistantController extends Controller
{
    private const MIN_SCORE = 3;
    private const MAX_RESULTS = 3;

    private const STOPWORDS = [
        'como', 'que', 'cual', 'cuales', 'donde', 'cuando', 'para', 'por', 'con', 'sin', 'una', 'uno', 'unos',
        'unas', 'los', 'las', 'del', 'quiero', 'puedo', 'debo', 'necesito', 'hacer', 'hago', 'esta', 'este',
        'esto', 'ese', 'eso', 'mis', 'pasos', 'ayuda', 'favor', 'hola', 'buenas', 'sobre', 'desde', 'hay',
        'ser', 'son', 'fue', 'muy', 'mas', 'menos', 'pero', 'porque', 'entre', 'ante', 'cada', 'todo', 'toda',
        'ticket', 'tickets',
    ];

    /**
     * Palabras que indican a cual de las tareas de la base de conocimiento se refiere la pregunta.
     */
    private const TASK_HINTS = [
        'tomar_ticket' => ['tomar', 'tomo', 'asignar', 'asignarme', 'autoasignar', 'cola', 'disponible'],
        'unirse_equipo' => ['unir', 'unirme', 'unirse', 'equipo', 'sumar', 'sumarme', 'colaborar'],
        'actualizar_progreso' => ['progreso', 'avance', 'avances', 'actualizar', 'nota', 'porcentaje', 'tareas', 'sprint'],
        'transferir_ticket' => ['transferir', 'transferencia', 'trasladar', 'reasignar', 'pasar'],
        'flujo_addie' => ['addie', 'fase', 'fases', 'analisis', 'diseno', 'desarrollo', 'implementacion', 'evaluacion', 'cerrar', 'cierre'],
    ];

    /**
     * Buscador guiado (HU-054): responde solo con articulos aprobados y vigentes.
     * No ejecuta acciones ni consulta tickets, usuarios, notas internas o evidencias.
     */
    public function ask(Request $request)
    {
        $data = $request->validate([
            'q' => 'required|string|min:2|max:200',
            'screen' => 'nullable|string|max:120',
        ]);

        $stems = array_values(array_unique(array_map(fn ($token) => $this->stem($token), $this->tokens($data['q']))));
        $screenKey = $this->screenKeyFor($data['screen'] ?? null);

        if (empty($stems)) {
            return $this->notFound('Escribe tu consulta con más detalle, por ejemplo: «cómo tomar un ticket».');
        }

        $bestTask = $this->detectTask($stems);
        $scored = [];

        foreach (KnowledgeArticle::current()->get() as $article) {
            $score = $this->score($article, $stems, $bestTask, $screenKey);

            if ($score >= self::MIN_SCORE) {
                $scored[] = ['score' => $score, 'article' => $article];
            }
        }

        if (empty($scored)) {
            return $this->notFound('No encontré información confiable sobre eso en los artículos aprobados y vigentes.');
        }

        usort($scored, fn ($a, $b) => $b['score'] <=> $a['score']);

        $results = [];
        foreach (array_slice($scored, 0, self::MAX_RESULTS) as $item) {
            $article = $item['article'];

            $results[] = [
                'title' => $article->title,
                'summary' => $article->summary,
                'steps' => $article->stepsList(),
                'url' => route('kb.show', $article->article_id),
                'screen_label' => $article->screen_key ? (KnowledgeArticle::SCREENS[$article->screen_key]['label'] ?? null) : null,
                'screen_url' => $article->screenUrl(),
                'reviewed' => $article->last_reviewed_at ? $article->last_reviewed_at->format('d/m/Y') : null,
            ];
        }

        return response()->json([
            'found' => true,
            'message' => 'Esto es lo que encontré en la base de conocimiento aprobada:',
            'results' => $results,
        ]);
    }

    private function notFound(string $message)
    {
        return response()->json([
            'found' => false,
            'message' => $message,
            'contact' => AppSetting::getValue(
                'assistant_support_contact',
                'Comunícate con el administrador de la plataforma o con el equipo de soporte técnico para recibir ayuda.'
            ),
            'kb_url' => route('kb.index'),
        ]);
    }

    private function score(KnowledgeArticle $article, array $stems, ?string $bestTask, ?string $screenKey): int
    {
        $title = $this->normalize((string) $article->title);
        $keywords = $this->normalize((string) $article->keywords);
        $summary = $this->normalize((string) $article->summary);
        $text = $this->normalize($article->steps . ' ' . $article->body);

        $score = 0;

        foreach ($stems as $stem) {
            $pattern = '/\b' . preg_quote($stem, '/') . '/';

            if (preg_match($pattern, $title)) {
                $score += 3;
            }
            if (preg_match($pattern, $keywords)) {
                $score += 3;
            }
            if (preg_match($pattern, $summary)) {
                $score += 2;
            }
            if (preg_match($pattern, $text)) {
                $score += 1;
            }
        }

        if ($bestTask !== null && $article->task === $bestTask) {
            $score += 4;
        }

        if ($score > 0 && $screenKey !== null && $article->screen_key === $screenKey) {
            $score += 1;
        }

        return $score;
    }

    private function detectTask(array $stems): ?string
    {
        $best = null;
        $bestCount = 0;

        foreach (self::TASK_HINTS as $task => $hints) {
            $hintStems = array_map(fn ($hint) => $this->stem($hint), $hints);
            $count = count(array_intersect($hintStems, $stems));

            if ($count > $bestCount) {
                $best = $task;
                $bestCount = $count;
            }
        }

        return $best;
    }

    /**
     * Traduce la ruta de la pantalla actual a la "pantalla relacionada" de los articulos.
     */
    private function screenKeyFor(?string $routeName): ?string
    {
        if (!$routeName) {
            return null;
        }

        return match (true) {
            $routeName === 'contributors.dashboard' => 'dashboard',
            str_starts_with($routeName, 'contributors.topic-tickets') => 'topic_queue',
            str_starts_with($routeName, 'service-management.create') => 'new_request',
            str_starts_with($routeName, 'profile.') => 'profile',
            default => null,
        };
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

    /**
     * Raiz simple para que "transferir" y "transferencia" coincidan.
     */
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