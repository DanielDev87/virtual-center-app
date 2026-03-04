<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Models\User;
use App\Models\UserRole;
use App\Models\RequestType;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Mostrar dashboard principal para admin
     */
    public function index()
    {
        // Estadísticas generales
        $stats = [
            'total_tickets' => Ticket::count(),
            'pending_tickets' => Ticket::where('status', 1)->count(),
            'in_progress_tickets' => Ticket::where('status', 2)->count(),
            'completed_tickets' => Ticket::where('status', 3)->count(),
            'total_users' => User::where('is_active', true)->count(),
            'total_roles' => UserRole::where('is_active', true)->count(),
            'avg_progress' => Ticket::where('status', '!=', 4)->avg('progress_percentage') ?? 0,
            'high_priority' => Ticket::where('priority', 'high')->whereIn('status', [1, 2])->count(),
        ];

        // Tickets recientes
        $recentTickets = Ticket::with(['requester', 'mediator', 'requestType'])
            ->latest()
            ->paginate(10);

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
        $urgentTickets = Ticket::where('priority', 'high')
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
            'ticketsByStatus', 
            'ticketsByType',
            'ticketsByPhase',
            'topCollaborators',
            'urgentTickets',
            'fastestTickets',
            'averageRating',
            'ratingDistribution',
            'avgCompletionTime'
        ));
    }
}


