<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Models\User;
use App\Models\TicketProgress;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class ReportsController extends Controller
{
    /**
     * Display reports dashboard
     */
    public function index()
    {
        return view('admin.reports.index');
    }

    /**
     * Generate tickets report
     */
    public function ticketsReport(Request $request)
    {
        $query = Ticket::with(['requester', 'mediator', 'requestType', 'faculty', 'program']);

        // Apply filters
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

        return $this->exportToExcel($tickets, 'Reporte de Tickets');
    }

    /**
     * Generate collaborators performance report
     */
    public function collaboratorsReport(Request $request)
    {
        $collaborators = User::whereHas('role', function($query) {
                $query->where('role_name', 'Contributor');
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

        return $this->exportCollaboratorsToExcel($collaborators);
    }

    /**
     * Generate progress report
     */
    public function progressReport(Request $request)
    {
        $progress = TicketProgress::with(['ticket', 'user'])
            ->when($request->filled('start_date'), function($query) use ($request) {
                $query->whereDate('created_at', '>=', $request->start_date);
            })
            ->when($request->filled('end_date'), function($query) use ($request) {
                $query->whereDate('created_at', '<=', $request->end_date);
            })
            ->latest()
            ->get();

        return $this->exportProgressToExcel($progress);
    }

    /**
     * Export tickets to Excel
     */
    private function exportToExcel($tickets, $title)
    {
        $filename = 'reporte_tickets_' . date('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($tickets) {
            $file = fopen('php://output', 'w');
            
            // UTF-8 BOM for Excel
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Headers
            fputcsv($file, [
                'Número',
                'Título',
                'Tipo',
                'Estado',
                'Prioridad',
                'Fase ADDIE',
                'Progreso %',
                'Solicitante',
                'Mediador',
                'Facultad',
                'Programa',
                'Fecha Creación',
                'Última Actualización'
            ]);

            // Data
            foreach ($tickets as $ticket) {
                $statusNames = [1 => 'Pendiente', 2 => 'En Progreso', 3 => 'Completado', 4 => 'Cancelado'];
                $phaseNames = [
                    'Analysis' => 'Análisis',
                    'Design' => 'Diseño',
                    'Development' => 'Desarrollo',
                    'Implementation' => 'Implementación',
                    'Evaluation' => 'Evaluación'
                ];

                fputcsv($file, [
                    $ticket->ticket_number,
                    $ticket->title,
                    $ticket->requestType->type_name ?? 'N/A',
                    $statusNames[$ticket->status] ?? 'Desconocido',
                    ucfirst($ticket->priority ?? 'N/A'),
                    $phaseNames[$ticket->current_phase] ?? $ticket->current_phase ?? 'N/A',
                    $ticket->progress_percentage . '%',
                    $ticket->requester->user_name ?? 'N/A',
                    $ticket->mediator->user_name ?? 'Sin asignar',
                    $ticket->faculty->faculty_name ?? 'N/A',
                    $ticket->program->program_name ?? 'N/A',
                    $ticket->created_at->format('Y-m-d H:i:s'),
                    $ticket->updated_at->format('Y-m-d H:i:s')
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    /**
     * Export collaborators to Excel
     */
    private function exportCollaboratorsToExcel($collaborators)
    {
        $filename = 'reporte_colaboradores_' . date('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($collaborators) {
            $file = fopen('php://output', 'w');
            
            // UTF-8 BOM
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Headers
            fputcsv($file, [
                'Nombre',
                'Email',
                'Total Tickets',
                'Completados',
                'En Progreso',
                'Tasa de Completitud %'
            ]);

            // Data
            foreach ($collaborators as $collaborator) {
                $completionRate = $collaborator->total_tickets > 0 
                    ? round(($collaborator->completed_tickets / $collaborator->total_tickets) * 100, 2)
                    : 0;

                fputcsv($file, [
                    $collaborator->user_name,
                    $collaborator->user_email,
                    $collaborator->total_tickets,
                    $collaborator->completed_tickets,
                    $collaborator->in_progress_tickets,
                    $completionRate . '%'
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    /**
     * Export progress to Excel
     */
    private function exportProgressToExcel($progress)
    {
        $filename = 'reporte_progreso_' . date('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($progress) {
            $file = fopen('php://output', 'w');
            
            // UTF-8 BOM
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Headers
            fputcsv($file, [
                'Ticket',
                'Colaborador',
                'Descripción',
                'Progreso %',
                'Fecha'
            ]);

            // Data
            foreach ($progress as $item) {
                fputcsv($file, [
                    $item->ticket->ticket_number ?? 'N/A',
                    $item->user->user_name ?? 'N/A',
                    $item->progress_description,
                    $item->progress_percentage . '%',
                    $item->created_at->format('Y-m-d H:i:s')
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }
}
