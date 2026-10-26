<?php

namespace App\Services;

use App\Models\Ticket;

class SlaRiskService
{
    public const ON_TIME = 'on_time';
    public const AT_RISK = 'at_risk';
    public const OVERDUE = 'overdue';

    public const STATES = [self::ON_TIME, self::AT_RISK, self::OVERDUE];

    private const LABELS = [
        self::ON_TIME => 'Dentro del plazo',
        self::AT_RISK => 'Próximo a vencer',
        self::OVERDUE => 'Vencido',
    ];

    public function __construct(private BusinessHoursService $businessHours)
    {
    }

    /**
     * Describe el estado de SLA de un ticket abierto. Devuelve null si no aplica
     * (ticket completado, cancelado o sin prioridad/SLA configurado).
     * No modifica datos ni envía avisos.
     */
    public function describe(Ticket $ticket): ?array
    {
        if (in_array((int) $ticket->status, [3, 4], true)) {
            return null;
        }

        $totalHours = $ticket->priority_sla_hours;
        if (!$ticket->created_at || !$totalHours) {
            return null;
        }

        $deadline = $this->businessHours->addMinutes($ticket->created_at->copy(), $totalHours * 60);
        $now = now();
        $deadlineText = $deadline->format('d/m/Y H:i');

        if ($now->greaterThan($deadline)) {
            $overdueMinutes = abs($this->businessHours->businessMinutesBetween($deadline, $now));

            return [
                'state' => self::OVERDUE,
                'label' => self::LABELS[self::OVERDUE],
                'remaining_minutes' => -$overdueMinutes,
                'remaining_text' => 'Venció hace ' . $this->formatMinutes($overdueMinutes) . ' de jornada laboral (límite: ' . $deadlineText . ')',
                'next_milestone' => 'Atender de inmediato: el plazo de respuesta venció el ' . $deadlineText . '.',
                'deadline' => $deadline,
            ];
        }

        $remaining = max(0, $this->businessHours->businessMinutesBetween($now, $deadline));
        $ratio = (float) config('sla.warning_ratio', 0.25);
        $atRisk = $remaining <= (int) ceil($totalHours * 60 * $ratio);

        $milestone = ((int) $ticket->status === 1)
            ? 'Asignar el ticket y responder al solicitante antes del ' . $deadlineText . '.'
            : 'Registrar una respuesta o avance antes del ' . $deadlineText . '.';

        return [
            'state' => $atRisk ? self::AT_RISK : self::ON_TIME,
            'label' => self::LABELS[$atRisk ? self::AT_RISK : self::ON_TIME],
            'remaining_minutes' => $remaining,
            'remaining_text' => 'Quedan ' . $this->formatMinutes($remaining) . ' de jornada laboral (límite: ' . $deadlineText . ')',
            'next_milestone' => $milestone,
            'deadline' => $deadline,
        ];
    }

    /**
     * Conteo de tickets abiertos por estado de SLA sobre una consulta ya restringida por rol/área.
     */
    public function summarize($query): array
    {
        $summary = [self::ON_TIME => 0, self::AT_RISK => 0, self::OVERDUE => 0];

        foreach ($this->openTickets($query) as $ticket) {
            $state = $this->describe($ticket)['state'] ?? null;
            if ($state !== null) {
                $summary[$state]++;
            }
        }

        return $summary;
    }

    /**
     * Restringe una consulta a los tickets que están en un estado de SLA dado.
     * Parte de la misma consulta ya limitada por rol/área, así que solo puede reducir resultados.
     */
    public function restrictQuery($query, string $state): void
    {
        $ids = [];

        foreach ($this->openTickets($query) as $ticket) {
            if (($this->describe($ticket)['state'] ?? null) === $state) {
                $ids[] = $ticket->ticket_id;
            }
        }

        $query->whereIn('tickets.ticket_id', $ids);
    }

    private function openTickets($query)
    {
        return (clone $query)
            ->setEagerLoads([])
            ->whereNotIn('tickets.status', [3, 4])
            ->whereNotNull('tickets.priority')
            ->get(['tickets.*']);
    }

    private function formatMinutes(int $minutes): string
    {
        $hours = intdiv($minutes, 60);
        $mins = $minutes % 60;

        if ($hours === 0) {
            return $mins . ' min';
        }

        return $mins === 0 ? $hours . ' h' : $hours . ' h ' . $mins . ' min';
    }
}