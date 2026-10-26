<?php

namespace App\Console\Commands;

use App\Mail\TicketSlaEscalation;
use App\Models\SlaEscalation;
use App\Models\SlaEscalationRule;
use App\Models\Ticket;
use App\Models\User;
use App\Services\SlaRiskService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;

class EscalateSlaTickets extends Command
{
    protected $signature = 'tickets:escalate-sla {--dry-run : Muestra los tickets a escalar sin enviar correos ni registrar nada}';
    protected $description = 'Avisa a los responsables de escalamiento cuando un ticket se acerca al vencimiento de su SLA';

    public function handle(SlaRiskService $risk): int
    {
        if (!Schema::hasTable('sla_escalation_rules') || !Schema::hasTable('sla_escalations')) {
            $this->warn('Las tablas de escalamiento SLA no existen. Ejecuta php artisan migrate.');
            return self::SUCCESS;
        }

        $rules = SlaEscalationRule::where('is_active', true)->get();
        if ($rules->isEmpty()) {
            $this->info('No hay reglas de escalamiento activas.');
            return self::SUCCESS;
        }

        $tickets = Ticket::with(['mediator', 'requestType'])
            ->whereIn('request_type_id', $rules->pluck('request_type_id')->unique())
            ->whereNotIn('status', [3, 4])
            ->whereNotNull('priority')
            ->get();

        $processed = 0;

        foreach ($tickets as $ticket) {
            $rule = $this->resolveRule($rules, $ticket);
            if (!$rule) {
                continue;
            }

            $info = $risk->describe($ticket);
            if (!$info || $info['state'] === SlaRiskService::OVERDUE) {
                continue;
            }

            $totalMinutes = (int) $ticket->priority_sla_hours * 60;
            $thresholdMinutes = (int) ceil($totalMinutes * $rule->threshold_percent / 100);
            if ($info['remaining_minutes'] > $thresholdMinutes) {
                continue;
            }

            $alreadySent = SlaEscalation::where('ticket_id', $ticket->ticket_id)
                ->where('threshold_percent', $rule->threshold_percent)
                ->exists();
            if ($alreadySent) {
                continue;
            }

            $recipients = $this->recipients($ticket, $rule);
            if ($recipients->isEmpty()) {
                continue;
            }

            if ($this->option('dry-run')) {
                $this->line("#{$ticket->ticket_number} - {$ticket->title} | {$info['remaining_text']} | " . $recipients->pluck('user_email')->implode(', '));
                $processed++;
                continue;
            }

            $escalation = SlaEscalation::firstOrCreate(
                ['ticket_id' => $ticket->ticket_id, 'threshold_percent' => $rule->threshold_percent],
                [
                    'rule_id' => $rule->rule_id,
                    'remaining_minutes' => $info['remaining_minutes'],
                    'recipients_count' => $recipients->count(),
                    'notified_at' => now(),
                ]
            );

            if (!$escalation->wasRecentlyCreated) {
                continue;
            }

            foreach ($recipients as $recipient) {
                try {
                    Mail::to($recipient->user_email)->send(new TicketSlaEscalation($ticket, $info, (int) $rule->threshold_percent));
                } catch (\Throwable $exception) {
                    Log::error('Error enviando escalamiento SLA: ' . $exception->getMessage(), [
                        'ticket_id' => $ticket->ticket_id,
                        'recipient_id' => $recipient->user_id,
                    ]);
                }
            }

            $processed++;
        }

        $this->info($this->option('dry-run')
            ? "Tickets a escalar detectados: {$processed}"
            : "Escalamientos enviados: {$processed}");

        return self::SUCCESS;
    }

    /**
     * La regla más específica gana: tópico + prioridad, y si no, la del tópico sin prioridad.
     */
    private function resolveRule($rules, Ticket $ticket): ?SlaEscalationRule
    {
        $topicRules = $rules->where('request_type_id', $ticket->request_type_id);

        return $topicRules->first(fn ($rule) => $rule->priority !== null && (int) $rule->priority === (int) $ticket->priority)
            ?? $topicRules->first(fn ($rule) => $rule->priority === null);
    }

    /**
     * Responsable configurado en la regla; si no hay, los Admin de Área del área del tópico.
     * También se avisa al mediador del ticket.
     */
    private function recipients(Ticket $ticket, SlaEscalationRule $rule)
    {
        $users = collect();

        if ($rule->escalate_user_id) {
            $users = User::where('user_id', $rule->escalate_user_id)->where('is_active', true)->get();
        }

        if ($users->isEmpty() && $ticket->requestType?->area_id) {
            $users = User::where('area_id', $ticket->requestType->area_id)
                ->where('is_active', true)
                ->whereHas('role', fn ($query) => $query->where('role_name', 'Admin Área'))
                ->get();
        }

        if ($ticket->mediator && ($ticket->mediator->is_active ?? true)) {
            $users->push($ticket->mediator);
        }

        return $users->filter(fn ($user) => $user && $user->user_email)->unique('user_id')->values();
    }
}