<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Models\User;
use App\Models\UserRole;
use App\Models\RequestType;
use App\Models\TicketAssignment;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Calcular el progreso automático desde tareas de sprint (% completadas sobre el total vinculado a sprints).
     */
    private function computeAutoProgress($ticket): int
    {
        $sprintTasks = $ticket->sprints->flatMap(fn($sprint) => $sprint->tasks);
        $total = $sprintTasks->count();

        if ($total === 0) {
            return 0;
        }

        $done = $sprintTasks->where('status', 'done')->count();
        return (int) round(($done / $total) * 100);
    }

    /**
     * Mostrar dashboard principal para admin
     */
    public function index(Request $request)
    {
        // Promedio de progreso automático basado en tareas de sprint para tickets no cancelados.
        $ticketsForProgress = Ticket::with('sprints.tasks')
            ->where('status', '!=', 4)
            ->get();

        $avgProgress = $ticketsForProgress->count() > 0
            ? $ticketsForProgress->map(fn($ticket) => $this->computeAutoProgress($ticket))->avg()
            : 0;

        // Estadísticas generales
        $stats = [
            'total_tickets' => Ticket::count(),
            'pending_tickets' => Ticket::where('status', 1)->count(),
            'in_progress_tickets' => Ticket::where('status', 2)->count(),
            'completed_tickets' => Ticket::where('status', 3)->count(),
            'total_users' => User::where('is_active', true)->count(),
            'total_roles' => UserRole::where('is_active', true)->count(),
            'avg_progress' => $avgProgress,
            'high_priority' => Ticket::whereIn('priority', [3, 4])->whereIn('status', [1, 2])->count(),
        ];

        // Tickets recientes
        $recentTickets = Ticket::with(['requester', 'mediator', 'requestType', 'sprints.tasks'])
            ->orderByDesc('priority')
            ->latest()
            ->paginate(10);

        $recentTickets->getCollection()->transform(function ($ticket) {
            $ticket->auto_progress = $this->computeAutoProgress($ticket);
            return $ticket;
        });

        $returnedTickets = TicketAssignment::with(['ticket.requestType', 'ticket.mediator'])
            ->where('assigned_by', $request->user()->user_id)
            ->where('status', 'removed')
            ->where('notes', 'like', 'Devuelto por operario:%')
            ->whereNull('returned_alert_read_at')
            ->latest('updated_at')
            ->take(5)
            ->get();

        // Tickets por estado
        $ticketsByStatus = [
            'Pendiente' => Ticket::where('status', 1)->count(),
            'En Progreso' => Ticket::where('status', 2)->count(),
            'Completado' => Ticket::where('status', 3)->count(),
            'Cancelado' => Ticket::where('status', 4)->count(),
        ];

        // Tickets por tipo
        $ticketsByType = Ticket::with('requestType')
            ->select('request_type_id', DB::raw('count(*) as count'))
            ->whereNotNull('request_type_id')
            ->groupBy('request_type_id')
            ->get()
            ->mapWithKeys(function($item) {
                return [$item->requestType->type_name ?? 'Sin tipo' => $item->count];
            });

        // Tendencia mensual por tópico (rango configurable: 3, 6 o 12 meses).
        $allowedTopicTrendMonths = [3, 6, 12];
        $topicTrendMonths = (int) $request->query('topic_trend_months', 6);
        if (!in_array($topicTrendMonths, $allowedTopicTrendMonths, true)) {
            $topicTrendMonths = 6;
        }

        $monthKeys = collect(range(0, $topicTrendMonths - 1))
            ->map(fn ($offset) => now()->copy()->subMonths(($topicTrendMonths - 1) - $offset)->startOfMonth()->format('Y-m'));

        $topicTrendLabels = collect(range(0, $topicTrendMonths - 1))
            ->map(fn ($offset) => now()->copy()->subMonths(($topicTrendMonths - 1) - $offset)->startOfMonth()->format('m/Y'));

        $topicMonthlyRaw = Ticket::leftJoin('request_types', 'tickets.request_type_id', '=', 'request_types.type_id')
            ->selectRaw("DATE_FORMAT(tickets.created_at, '%Y-%m') as month_key")
            ->selectRaw("COALESCE(request_types.type_name, 'Sin tópico') as topic_name")
            ->selectRaw('COUNT(*) as total')
            ->whereDate('tickets.created_at', '>=', now()->copy()->subMonths($topicTrendMonths - 1)->startOfMonth())
            ->groupBy('month_key', 'topic_name')
            ->orderBy('month_key')
            ->get();

        $topTopicNames = $topicMonthlyRaw
            ->groupBy('topic_name')
            ->map(fn ($rows) => (int) $rows->sum('total'))
            ->sortDesc()
            ->take(5)
            ->keys()
            ->values();

        $topicTrendDatasets = $topTopicNames->map(function ($topicName) use ($topicMonthlyRaw, $monthKeys) {
            $data = $monthKeys->map(function ($monthKey) use ($topicMonthlyRaw, $topicName) {
                $match = $topicMonthlyRaw->first(function ($row) use ($topicName, $monthKey) {
                    return $row->topic_name === $topicName && $row->month_key === $monthKey;
                });

                return $match ? (int) $match->total : 0;
            })->values();

            return [
                'label' => $topicName,
                'data' => $data,
            ];
        })->values();

        // Tickets por fase ADDIE
        $ticketsByPhase = Ticket::select('current_phase', DB::raw('count(*) as count'))
            ->groupBy('current_phase')
            ->get()
            ->mapWithKeys(function($item) {
                $phaseNames = [
                    'Analysis' => 'Análisis',
                    'Design' => 'Diseño',
                    'Development' => 'Desarrollo',
                    'Implementation' => 'Implementación',
                    'Evaluation' => 'Evaluación'
                ];
                return [$phaseNames[$item->current_phase] ?? $item->current_phase => $item->count];
            });

        // Top colaboradores
        $topCollaborators = User::select('users.user_id', 'users.user_name', 'users.user_email', 'users.user_avatar')
            ->join('ticket_assignments', 'users.user_id', '=', 'ticket_assignments.user_id')
            ->join('tickets', 'ticket_assignments.ticket_id', '=', 'tickets.ticket_id')
            ->where('tickets.status', 3)
            ->whereHas('role', function($query) {
                $query->where('role_name', 'Contributor');
            })
            ->groupBy('users.user_id', 'users.user_name', 'users.user_email', 'users.user_avatar')
            ->selectRaw('COUNT(tickets.ticket_id) as completed_count')
            ->orderBy('completed_count', 'desc')
            ->take(5)
            ->get();


        // Tickets urgentes
        $urgentTickets = Ticket::whereIn('priority', [3, 4])
            ->whereIn('status', [1, 2])
            ->with(['requester', 'requestType'])
            ->take(5)
            ->get();

        // Mejores tiempos de resolución (tickets completados más rápido)
        $fastestTickets = Ticket::where('status', 3)
            ->whereNotNull('updated_at')
            ->with(['requester', 'requestType'])
            ->select('*', DB::raw('TIMESTAMPDIFF(HOUR, created_at, updated_at) as completion_hours'))
            ->orderBy('completion_hours', 'asc')
            ->take(5)
            ->get();

        // Promedio de calificaciones
        $averageRating = Ticket::where('status', 3)
            ->whereNotNull('rating')
            ->avg('rating') ?? 0;

        // Distribución de calificaciones
        $ratingDistribution = Ticket::where('status', 3)
            ->whereNotNull('rating')
            ->select('rating', DB::raw('count(*) as count'))
            ->groupBy('rating')
            ->get()
            ->mapWithKeys(function($item) {
                return [$item->rating . ' Estrellas' => $item->count];
            });

        // Tiempo promedio de resolución por estado
        $avgCompletionTime = Ticket::where('status', 3)
            ->whereNotNull('updated_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, updated_at)) as avg_hours')
            ->first()
            ->avg_hours ?? 0;

        return view('dashboard.index', compact(
            'stats', 
            'recentTickets', 
            'returnedTickets',
            'ticketsByStatus', 
            'ticketsByType',
            'ticketsByPhase',
            'topCollaborators',
            'urgentTickets',
            'fastestTickets',
            'averageRating',
            'ratingDistribution',
            'avgCompletionTime',
            'topicTrendLabels',
            'topicTrendDatasets',
            'topicTrendMonths',
            'allowedTopicTrendMonths'
        ));
    }

    public function markReturnedAlertAsRead(Request $request, $assignmentId)
    {
        $assignment = TicketAssignment::where('assignment_id', $assignmentId)
            ->where('assigned_by', $request->user()->user_id)
            ->where('status', 'removed')
            ->where('notes', 'like', 'Devuelto por operario:%')
            ->firstOrFail();

        $assignment->update(['returned_alert_read_at' => now()]);

        return $request->user()->role?->role_name === 'Admin Área'
            ? redirect()->route('area-admin.tickets.show', $assignment->ticket_id)
            : redirect()->route('admin.tickets.show', $assignment->ticket_id);
    }
}


