<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Models\User;
use App\Models\TicketProgress;
use App\Models\RequestType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class ReportsController extends Controller
{
    /**
     * Determinar si la petición solicita previsualización en pantalla.
     */
    private function shouldPreview(Request $request): bool
    {
        return $request->boolean('preview');
    }

    /**
     * Renderizar la previsualización de cualquier reporte en la misma vista de reportes.
     */
    private function renderPreview(string $title, array $columns, array $rows, array $filters = [])
    {
        return view('admin.reports.index', [
            'previewReport' => [
                'title' => $title,
                'columns' => $columns,
                'rows' => $rows,
                'filters' => $filters,
                'generated_at' => now()->format('Y-m-d H:i:s'),
            ],
            'topicOptions' => $this->getTopicOptions(),
        ]);
    }

    /**
     * Obtener listado de tópicos activos para filtros del módulo de reportes.
     */
    private function getTopicOptions()
    {
        return RequestType::where('is_active', true)
            ->orderBy('type_name')
            ->get(['type_id', 'type_name']);
    }

    /**
     * Normalizar el formato de exportación desde la petición.
     */
    private function resolveExportFormat(Request $request): string
    {
        $format = strtolower((string) $request->get('format', 'excel'));
        return in_array($format, ['excel', 'csv', 'pdf'], true) ? $format : 'excel';
    }

    /**
     * Asegurar que al menos un filtro esté aplicado antes de generar el reporte.
     */
    private function ensureFiltersApplied(Request $request, array $filterKeys)
    {
        $hasFilter = false;

        foreach ($filterKeys as $key) {
            if ($request->filled($key)) {
                $hasFilter = true;
                break;
            }
        }

        if (!$hasFilter) {
            return redirect()->route('admin.reports.index')
                ->with('error', 'Debes aplicar al menos un filtro antes de generar el reporte.');
        }

        return null;
    }

    /**
     * Mostrar el panel de reportes
     */
    public function index()
    {
        return view('admin.reports.index', [
            'topicOptions' => $this->getTopicOptions(),
        ]);
    }

    /**
     * Generar el reporte de tickets
     */
    public function ticketsReport(Request $request)
    {
        if ($redirect = $this->ensureFiltersApplied($request, ['start_date', 'end_date', 'status', 'priority', 'current_phase'])) {
            return $redirect;
        }

        $query = Ticket::with(['requester', 'mediator', 'requestType', 'faculty', 'program']);

        // Aplicar filtros
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }
        if ($request->filled('current_phase')) {
            $query->where('current_phase', $request->current_phase);
        }

        $tickets = $query->get();

        return $this->exportToExcel($tickets, 'Reporte de Tickets', $request);
    }

    /**
     * Generar el reporte de desempeño de colaboradores
     */
    public function collaboratorsReport(Request $request)
    {
        if ($redirect = $this->ensureFiltersApplied($request, ['is_active'])) {
            return $redirect;
        }

        $collaborators = User::whereHas('role', function($query) {
                $query->where('role_name', 'Contributor');
            })
            ->when($request->filled('is_active'), function($query) use ($request) {
                $query->where('is_active', $request->is_active === '1');
            })
            ->withCount([
                'assignedTickets as total_tickets',
                'assignedTickets as completed_tickets' => function($query) {
                    $query->where('tickets.status', 3);
                },
                'assignedTickets as in_progress_tickets' => function($query) {
                    $query->where('tickets.status', 2);
                }
            ])
            ->get();

        return $this->exportCollaboratorsToExcel($collaborators, $request);
    }

    /**
     * Generar el reporte de progreso
     */
    public function progressReport(Request $request)
    {
        if ($redirect = $this->ensureFiltersApplied($request, ['start_date', 'end_date'])) {
            return $redirect;
        }

        $progress = TicketProgress::with(['ticket', 'user'])
            ->when($request->filled('start_date'), function($query) use ($request) {
                $query->whereDate('created_at', '>=', $request->start_date);
            })
            ->when($request->filled('end_date'), function($query) use ($request) {
                $query->whereDate('created_at', '<=', $request->end_date);
            })
            ->latest()
            ->get();

        return $this->exportProgressToExcel($progress, $request);
    }

    /**
     * Generar el reporte de solicitudes agrupadas por tópico.
     */
    public function topicsReport(Request $request)
    {
        if ($redirect = $this->ensureFiltersApplied($request, ['start_date', 'end_date', 'status', 'request_type_id'])) {
            return $redirect;
        }

        $query = Ticket::query()
            ->leftJoin('request_types', 'tickets.request_type_id', '=', 'request_types.type_id')
            ->selectRaw("COALESCE(request_types.type_name, 'Sin tópico') as topic_name")
            ->selectRaw('COUNT(*) as total_tickets')
            ->selectRaw('SUM(CASE WHEN tickets.status = 1 THEN 1 ELSE 0 END) as pending_tickets')
            ->selectRaw('SUM(CASE WHEN tickets.status = 2 THEN 1 ELSE 0 END) as in_progress_tickets')
            ->selectRaw('SUM(CASE WHEN tickets.status = 3 THEN 1 ELSE 0 END) as completed_tickets')
            ->selectRaw('SUM(CASE WHEN tickets.status = 4 THEN 1 ELSE 0 END) as canceled_tickets');

        if ($request->filled('start_date')) {
            $query->whereDate('tickets.created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('tickets.created_at', '<=', $request->end_date);
        }
        if ($request->filled('status')) {
            $query->where('tickets.status', $request->status);
        }
        if ($request->filled('request_type_id')) {
            $query->where('tickets.request_type_id', $request->request_type_id);
        }

        $topicStats = $query
            ->groupBy('topic_name')
            ->orderByDesc('total_tickets')
            ->get();

        return $this->exportTopicsToExcel($topicStats, $request);
    }

    /**
     * Reporte de solicitantes con mayor frecuencia de solicitudes.
     * Admin ve todo el sistema; Admin Área queda limitado a su alcance.
     */
    public function requestersReport(Request $request)
    {
        if ($redirect = $this->ensureFiltersApplied($request, ['start_date', 'end_date', 'status', 'request_type_id'])) {
            return $redirect;
        }

        $requesterStats = $this->buildRequesterStatsQuery($request)->get();

        return $this->exportRequestersToExcel($requesterStats, $request);
    }

    public function requestersReportPage(Request $request)
    {
        $requesterStats = $this->buildRequesterStatsQuery($request)->get();

        return view('reports.requesters', [
            'requesterStats' => $requesterStats,
            'filters' => $request->only(['start_date', 'end_date', 'status', 'request_type_id']),
            'topicOptions' => $this->getTopicOptions(),
        ]);
    }

    private function buildRequesterStatsQuery(Request $request)
    {
        $user = $request->user();
        $query = Ticket::query()
            ->join('users', 'tickets.requester_id', '=', 'users.user_id')
            ->leftJoin('request_types', 'tickets.request_type_id', '=', 'request_types.type_id')
            ->select('users.user_name', 'users.user_email', 'users.document_number')
            ->selectRaw('COUNT(tickets.ticket_id) as total_tickets')
            ->selectRaw('SUM(CASE WHEN tickets.status = 1 THEN 1 ELSE 0 END) as pending_tickets')
            ->selectRaw('SUM(CASE WHEN tickets.status = 2 THEN 1 ELSE 0 END) as in_progress_tickets')
            ->selectRaw('SUM(CASE WHEN tickets.status = 3 THEN 1 ELSE 0 END) as completed_tickets')
            ->selectRaw('SUM(CASE WHEN tickets.status = 4 THEN 1 ELSE 0 END) as canceled_tickets');

        if ($user?->role?->role_name === 'Admin Área') {
            $query->where('request_types.area_id', $user->area_id);
        }
        if ($user?->role?->role_name === 'Contributor') {
            $query->where(function ($scope) use ($user) {
                $scope->where('tickets.mediator_id', $user->user_id)
                    ->orWhereExists(function ($subQuery) use ($user) {
                        $subQuery->select(DB::raw(1))
                            ->from('ticket_assignments')
                            ->whereColumn('ticket_assignments.ticket_id', 'tickets.ticket_id')
                            ->where('ticket_assignments.user_id', $user->user_id)
                            ->where('ticket_assignments.status', 'active');
                    });
            });
        }

        $query->when($request->filled('start_date'), fn ($q) => $q->whereDate('tickets.created_at', '>=', $request->start_date))
            ->when($request->filled('end_date'), fn ($q) => $q->whereDate('tickets.created_at', '<=', $request->end_date))
            ->when($request->filled('status'), fn ($q) => $q->where('tickets.status', $request->status))
            ->when($request->filled('request_type_id'), fn ($q) => $q->where('tickets.request_type_id', $request->request_type_id));

        return $query
            ->groupBy('users.user_id', 'users.user_name', 'users.user_email', 'users.document_number')
            ->orderByDesc('total_tickets')
            ->orderBy('users.user_name')
            ->limit(100);
    }

    private function exportRequestersToExcel($requesterStats, Request $request)
    {
        $columns = ['Solicitante', 'Documento', 'Correo', 'Total Tickets', 'Pendientes', 'En Progreso', 'Completados', 'Cancelados'];
        $rows = $requesterStats->map(fn ($item) => [
            $item->user_name,
            $item->document_number ?: 'N/A',
            $item->user_email ?: 'N/A',
            (int) $item->total_tickets,
            (int) $item->pending_tickets,
            (int) $item->in_progress_tickets,
            (int) $item->completed_tickets,
            (int) $item->canceled_tickets,
        ])->all();
        $filters = $this->cleanFilters([
            'Fecha inicio' => $request->start_date,
            'Fecha fin' => $request->end_date,
            'Estado' => $request->status,
            'Tópico' => $request->filled('request_type_id') ? optional(RequestType::find($request->request_type_id))->type_name : null,
        ]);

        if ($this->shouldPreview($request)) {
            return $this->renderPreview('Reporte de Solicitantes Frecuentes', $columns, $rows, $filters);
        }

        $format = $this->resolveExportFormat($request);
        if ($format === 'csv') {
            return $this->exportCsv('reporte_solicitantes_' . date('Y-m-d_His') . '.csv', 'Reporte de Solicitantes Frecuentes', $columns, $rows, $filters);
        }
        if ($format === 'pdf') {
            return $this->exportPrintablePdf('Reporte de Solicitantes Frecuentes', $columns, $rows, $filters, '#d63384');
        }

        return $this->exportStyledExcel('reporte_solicitantes_' . date('Y-m-d_His') . '.xls', 'Reporte de Solicitantes Frecuentes', $columns, $rows, $filters, '#d63384');
    }

    /**
     * Exportar tickets a Excel
     */
    private function exportToExcel($tickets, $title, Request $request)
    {
        $filename = 'reporte_tickets_' . date('Y-m-d_His') . '.xls';

        $statusNames = [1 => 'Pendiente', 2 => 'En Progreso', 3 => 'Completado', 4 => 'Cancelado'];
        $phaseNames = [
            'Analysis' => 'Análisis',
            'Design' => 'Diseño',
            'Development' => 'Desarrollo',
            'Implementation' => 'Implementación',
            'Evaluation' => 'Evaluación'
        ];
        $priorityNames = [
            '1' => 'Baja',
            '2' => 'Media',
            '3' => 'Alta (Afecta operación)',
            '4' => 'Urgente (Suspende operación)',
            'low' => 'Baja',
            'medium' => 'Media',
            'high' => 'Alta (Afecta operación)',
            'urgent' => 'Urgente (Suspende operación)',
        ];

        $columns = [
            'Número', 'Título', 'Tipo', 'Estado', 'Prioridad', 'Fase ADDIE', 'Progreso',
            'Solicitante', 'Mediador', 'Facultad', 'Programa', 'Fecha Creación', 'Última Actualización'
        ];

        $rows = [];
        foreach ($tickets as $ticket) {
            $rows[] = [
                $ticket->ticket_number,
                $ticket->title,
                $ticket->requestType->type_name ?? 'N/A',
                $statusNames[$ticket->status] ?? 'Desconocido',
                $priorityNames[(string)($ticket->priority ?? '')] ?? ($ticket->priority ?? 'N/A'),
                $phaseNames[$ticket->current_phase] ?? ($ticket->current_phase ?? 'N/A'),
                ($ticket->progress_percentage ?? 0) . '%',
                $ticket->requester->user_name ?? 'N/A',
                $ticket->mediator->user_name ?? 'Sin asignar',
                $ticket->faculty->faculty_name ?? 'N/A',
                $ticket->program->program_name ?? 'N/A',
                optional($ticket->created_at)->format('Y-m-d H:i:s') ?? 'N/A',
                optional($ticket->updated_at)->format('Y-m-d H:i:s') ?? 'N/A',
            ];
        }

        $filters = $this->cleanFilters([
            'Fecha inicio' => $request->start_date,
            'Fecha fin' => $request->end_date,
            'Estado' => $request->filled('status') ? ($statusNames[$request->status] ?? $request->status) : null,
            'Prioridad' => $request->filled('priority') ? ($priorityNames[(string)$request->priority] ?? $request->priority) : null,
            'Fase ADDIE' => $request->filled('current_phase') ? ($phaseNames[$request->current_phase] ?? $request->current_phase) : null,
        ]);

        if ($this->shouldPreview($request)) {
            return $this->renderPreview($title, $columns, $rows, $filters);
        }

        $format = $this->resolveExportFormat($request);
        if ($format === 'csv') {
            return $this->exportCsv(
                'reporte_tickets_' . date('Y-m-d_His') . '.csv',
                'Reporte de Tickets',
                $columns,
                $rows,
                $filters
            );
        }

        if ($format === 'pdf') {
            return $this->exportPrintablePdf('Reporte de Tickets', $columns, $rows, $filters, '#0b5ed7');
        }

        return $this->exportStyledExcel($filename, $title, $columns, $rows, $filters, '#0b5ed7');
    }

    /**
     * Export collaborators to Excel
     */
    private function exportCollaboratorsToExcel($collaborators, Request $request)
    {
        $filename = 'reporte_colaboradores_' . date('Y-m-d_His') . '.xls';

        $columns = [
            'Nombre', 'Email', 'Total Tickets', 'Completados', 'En Progreso', 'Tasa de Completitud'
        ];

        $rows = [];
        foreach ($collaborators as $collaborator) {
            $completionRate = $collaborator->total_tickets > 0
                ? round(($collaborator->completed_tickets / $collaborator->total_tickets) * 100, 2)
                : 0;

            $rows[] = [
                $collaborator->user_name,
                $collaborator->user_email,
                $collaborator->total_tickets,
                $collaborator->completed_tickets,
                $collaborator->in_progress_tickets,
                $completionRate . '%',
            ];
        }

        $filters = $this->cleanFilters([
            'Estado colaborador' => $request->filled('is_active')
                ? ($request->is_active === '1' ? 'Activo' : 'Inactivo')
                : null,
        ]);

        if ($this->shouldPreview($request)) {
            return $this->renderPreview('Reporte de Colaboradores', $columns, $rows, $filters);
        }

        $format = $this->resolveExportFormat($request);
        if ($format === 'csv') {
            return $this->exportCsv(
                'reporte_colaboradores_' . date('Y-m-d_His') . '.csv',
                'Reporte de Colaboradores',
                $columns,
                $rows,
                $filters
            );
        }

        if ($format === 'pdf') {
            return $this->exportPrintablePdf('Reporte de Colaboradores', $columns, $rows, $filters, '#198754');
        }

        return $this->exportStyledExcel($filename, 'Reporte de Colaboradores', $columns, $rows, $filters, '#198754');
    }

    /**
     * Export progress to Excel
     */
    private function exportProgressToExcel($progress, Request $request)
    {
        $filename = 'reporte_progreso_' . date('Y-m-d_His') . '.xls';

        $columns = ['Ticket', 'Colaborador', 'Descripción', 'Progreso', 'Fecha'];
        $rows = [];
        foreach ($progress as $item) {
            $rows[] = [
                $item->ticket->ticket_number ?? 'N/A',
                $item->user->user_name ?? 'N/A',
                $item->progress_description,
                ($item->progress_percentage ?? 0) . '%',
                optional($item->created_at)->format('Y-m-d H:i:s') ?? 'N/A',
            ];
        }

        $filters = $this->cleanFilters([
            'Fecha inicio' => $request->start_date,
            'Fecha fin' => $request->end_date,
        ]);

        if ($this->shouldPreview($request)) {
            return $this->renderPreview('Reporte de Progreso', $columns, $rows, $filters);
        }

        $format = $this->resolveExportFormat($request);
        if ($format === 'csv') {
            return $this->exportCsv(
                'reporte_progreso_' . date('Y-m-d_His') . '.csv',
                'Reporte de Progreso',
                $columns,
                $rows,
                $filters
            );
        }

        if ($format === 'pdf') {
            return $this->exportPrintablePdf('Reporte de Progreso', $columns, $rows, $filters, '#0f9fb8');
        }

        return $this->exportStyledExcel($filename, 'Reporte de Progreso', $columns, $rows, $filters, '#0f9fb8');
    }

    /**
     * Exportar estadísticas de solicitudes por tópico.
     */
    private function exportTopicsToExcel($topicStats, Request $request)
    {
        $filename = 'reporte_topicos_' . date('Y-m-d_His') . '.xls';

        $statusNames = [1 => 'Pendiente', 2 => 'En Progreso', 3 => 'Completado', 4 => 'Cancelado'];

        $columns = [
            'Tópico',
            'Total Solicitudes',
            'Pendientes',
            'En Progreso',
            'Completadas',
            'Canceladas',
            'Porcentaje del Total',
        ];

        $rows = [];
        $globalTotal = (int) $topicStats->sum('total_tickets');

        foreach ($topicStats as $item) {
            $topicTotal = (int) $item->total_tickets;
            $percentage = $globalTotal > 0 ? round(($topicTotal / $globalTotal) * 100, 2) : 0;

            $rows[] = [
                $item->topic_name,
                $topicTotal,
                (int) $item->pending_tickets,
                (int) $item->in_progress_tickets,
                (int) $item->completed_tickets,
                (int) $item->canceled_tickets,
                $percentage . '%',
            ];
        }

        $filters = $this->cleanFilters([
            'Fecha inicio' => $request->start_date,
            'Fecha fin' => $request->end_date,
            'Estado' => $request->filled('status') ? ($statusNames[$request->status] ?? $request->status) : null,
            'Tópico' => $request->filled('request_type_id')
                ? optional(RequestType::find($request->request_type_id))->type_name
                : null,
        ]);

        if ($this->shouldPreview($request)) {
            return $this->renderPreview('Reporte de Solicitudes por Tópico', $columns, $rows, $filters);
        }

        $format = $this->resolveExportFormat($request);
        if ($format === 'csv') {
            return $this->exportCsv(
                'reporte_topicos_' . date('Y-m-d_His') . '.csv',
                'Reporte de Solicitudes por Tópico',
                $columns,
                $rows,
                $filters
            );
        }

        if ($format === 'pdf') {
            return $this->exportPrintablePdf('Reporte de Solicitudes por Tópico', $columns, $rows, $filters, '#6f42c1');
        }

        return $this->exportStyledExcel(
            $filename,
            'Reporte de Solicitudes por Tópico',
            $columns,
            $rows,
            $filters,
            '#6f42c1'
        );
    }

    /**
     * Remove empty filter values while preserving meaningful values like "0".
     */
    private function cleanFilters(array $filters): array
    {
        return array_filter($filters, function ($value) {
            return $value !== null && $value !== '';
        });
    }

    /**
     * Export data as plain CSV.
     */
    private function exportCsv(string $filename, string $title, array $columns, array $rows, array $filters = [])
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'max-age=0, no-cache, no-store, must-revalidate',
            'Pragma' => 'public',
        ];

        $callback = function () use ($title, $columns, $rows, $filters) {
            $file = fopen('php://output', 'w');

            // UTF-8 BOM for Excel compatibility
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($file, [$title]);
            fputcsv($file, ['Generado', date('Y-m-d H:i:s')]);

            if (!empty($filters)) {
                fputcsv($file, ['Filtros aplicados']);
                foreach ($filters as $label => $value) {
                    fputcsv($file, [$label, $value]);
                }
            }

            fputcsv($file, []);
            fputcsv($file, $columns);

            if (empty($rows)) {
                fputcsv($file, ['Sin resultados para los filtros aplicados.']);
            } else {
                foreach ($rows as $row) {
                    fputcsv($file, $row);
                }
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    /**
     * Export data as a styled Excel-compatible HTML document.
     */
    private function exportStyledExcel(string $filename, string $title, array $columns, array $rows, array $filters = [], string $accentColor = '#0b5ed7')
    {
        $html = $this->buildStyledReportHtml($title, $columns, $rows, $filters, $accentColor, false);

        $headers = [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'max-age=0, no-cache, no-store, must-revalidate',
            'Pragma' => 'public',
        ];

        return Response::make("\xEF\xBB\xBF" . $html, 200, $headers);
    }

    /**
     * Exportar el reporte en versión imprimible para generar PDF desde el navegador.
     */
    private function exportPrintablePdf(string $title, array $columns, array $rows, array $filters = [], string $accentColor = '#0b5ed7')
    {
        $html = $this->buildStyledReportHtml($title, $columns, $rows, $filters, $accentColor, true);

        return Response::make($html, 200, ['Content-Type' => 'text/html; charset=UTF-8']);
    }

    /**
     * Construir documento HTML reutilizable para exportación/imprimible.
     */
    private function buildStyledReportHtml(string $title, array $columns, array $rows, array $filters = [], string $accentColor = '#0b5ed7', bool $printMode = false): string
    {
        $safeAccentColor = htmlspecialchars($accentColor, ENT_QUOTES, 'UTF-8');

        $filtersHtml = '';
        if (!empty($filters)) {
            $filtersHtml .= '<div class="meta"><strong>Filtros aplicados:</strong> ';
            $parts = [];
            foreach ($filters as $label => $value) {
                $parts[] = htmlspecialchars($label . ': ' . $value, ENT_QUOTES, 'UTF-8');
            }
            $filtersHtml .= implode(' | ', $parts) . '</div>';
        }

        $headerCells = '';
        foreach ($columns as $column) {
            $headerCells .= '<th>' . htmlspecialchars($column, ENT_QUOTES, 'UTF-8') . '</th>';
        }

        $bodyRows = '';
        if (empty($rows)) {
            $bodyRows = '<tr><td colspan="' . count($columns) . '" class="empty">Sin resultados para los filtros aplicados.</td></tr>';
        } else {
            foreach ($rows as $row) {
                $bodyRows .= '<tr>';
                foreach ($row as $cell) {
                    $bodyRows .= '<td>' . htmlspecialchars((string) $cell, ENT_QUOTES, 'UTF-8') . '</td>';
                }
                $bodyRows .= '</tr>';
            }
        }

        $generatedAt = date('Y-m-d H:i:s');

        $printBlock = '';
        if ($printMode) {
            $printBlock = '<script>window.addEventListener("load", function(){ window.print(); });</script>';
        }

        return '<html><head><meta charset="UTF-8"><style>
            body { font-family: Arial, Helvetica, sans-serif; color: #1f2937; margin: 20px; }
            .title { font-size: 18px; font-weight: bold; color: ' . $safeAccentColor . '; margin-bottom: 4px; }
            .meta { font-size: 12px; color: #4b5563; margin-bottom: 8px; }
            table { border-collapse: collapse; width: 100%; }
            th { background: ' . $safeAccentColor . '; color: #ffffff; font-weight: bold; border: 1px solid #cbd5e1; padding: 8px; text-align: left; }
            td { border: 1px solid #d1d5db; padding: 7px; vertical-align: top; }
            tr:nth-child(even) td { background: #f8fafc; }
            .empty { text-align: center; color: #6b7280; font-style: italic; padding: 14px; }
            @media print { body { margin: 10px; } }
        </style></head><body>
            <div class="title">' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</div>
            <div class="meta"><strong>Generado:</strong> ' . htmlspecialchars($generatedAt, ENT_QUOTES, 'UTF-8') . '</div>'
            . $filtersHtml .
            '<table><thead><tr>' . $headerCells . '</tr></thead><tbody>' . $bodyRows . '</tbody></table>'
            . $printBlock .
        '</body></html>';
    }
}
