<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Models\TicketProgress;
use App\Models\ProjectTracking;
use App\Models\User;
use App\Models\RequestType;
use App\Models\UserRole;
use Illuminate\Support\Facades\DB;

class MonitorController extends Controller
{
    /**
     * Mostrar panel de monitoreo
     */
    public function index()
    {
        $totalTickets = Ticket::count();
        $openTickets = Ticket::whereIn('status', [1, 2])->count();
        $completedTickets = Ticket::where('status', 3)->count();
        $cancelledTickets = Ticket::where('status', 4)->count();
        $overdueTickets = Ticket::all()->filter(fn ($ticket) => $ticket->is_response_overdue)->count();

        $activeUsers = User::where('is_active', true)->count();
        $totalUsers = User::count();
        $averageRating = (float) (Ticket::whereNotNull('rating')->avg('rating') ?? 0);

        $stats = [
            'total_tickets' => $totalTickets,
            'open_tickets' => $openTickets,
            'completed_tickets' => $completedTickets,
            'cancelled_tickets' => $cancelledTickets,
            'overdue_tickets' => $overdueTickets,
            'active_users' => $activeUsers,
            'total_users' => $totalUsers,
            'request_types' => RequestType::count(),
            'average_rating' => round($averageRating, 1),
        ];

        $recentTickets = Ticket::with(['requester', 'mediator', 'requestType'])
            ->latest()
            ->take(10)
            ->get();

        $recentActivities = TicketProgress::with(['user', 'ticket'])
            ->latest('created_at')
            ->take(10)
            ->get();

        $systemAlerts = collect();
        if ($overdueTickets > 0) {
            $systemAlerts->push([
                'alert_level' => 'critical',
                'alert_title' => 'Tickets fuera de SLA',
                'alert_message' => "Hay {$overdueTickets} ticket(s) que superan el tiempo objetivo de respuesta.",
                'created_at' => now(),
            ]);
        }

        $inactiveUsers = User::where('is_active', false)->count();
        if ($inactiveUsers > 0) {
            $systemAlerts->push([
                'alert_level' => 'info',
                'alert_title' => 'Usuarios inactivos registrados',
                'alert_message' => "Hay {$inactiveUsers} usuario(s) inactivos en el sistema.",
                'created_at' => now(),
            ]);
        }

        $timelineRange = collect(range(6, 0))->map(fn ($i) => now()->subDays($i));
        $timelineData = [
            'labels' => $timelineRange->map(fn ($d) => $d->format('d/m'))->values(),
            'created' => $timelineRange->map(function ($d) {
                return Ticket::whereDate('created_at', $d->toDateString())->count();
            })->values(),
            'completed' => $timelineRange->map(function ($d) {
                return Ticket::where('status', 3)->whereDate('updated_at', $d->toDateString())->count();
            })->values(),
        ];

        $statusMap = [1 => 'Pendiente', 2 => 'En Progreso', 3 => 'Completado', 4 => 'Cancelado'];
        $ticketsByStatus = Ticket::select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $statusDistribution = [
            'labels' => collect($statusMap)->values(),
            'data' => collect($statusMap)->keys()->map(fn ($s) => (int) ($ticketsByStatus[$s] ?? 0))->values(),
        ];

        $priorityMap = [1 => 'Baja', 2 => 'Media', 3 => 'Alta (Afecta operación)', 4 => 'Urgente (Suspende operación)'];
        $ticketsByPriority = Ticket::select('priority', DB::raw('COUNT(*) as total'))
            ->whereNotNull('priority')
            ->groupBy('priority')
            ->pluck('total', 'priority');

        $priorityDistribution = [
            'labels' => collect($priorityMap)->values(),
            'data' => collect($priorityMap)->keys()->map(fn ($p) => (int) ($ticketsByPriority[$p] ?? 0))->values(),
        ];

        $usersByRole = UserRole::withCount('users')
            ->orderBy('role_name')
            ->get();

        return view('monitor.index', compact(
            'stats',
            'recentActivities',
            'systemAlerts',
            'timelineData',
            'statusDistribution',
            'priorityDistribution',
            'usersByRole',
            'recentTickets'
        ));
    }

    /**
     * Mostrar reportes
     */
    public function reports()
    {
        // Generar reportes
        $reports = [
            'daily_activity' => $this->getDailyActivityReport(),
            'user_engagement' => $this->getUserEngagementReport(),
            'project_performance' => $this->getProjectPerformanceReport()
        ];

        return view('monitor.reports', compact('reports'));
    }

    /**
     * Mostrar analytics
     */
    public function analytics()
    {
        // Datos para analytics
        $analytics = [
            'page_views' => $this->getPageViewsData(),
            'user_growth' => $this->getUserGrowthData(),
            'project_trends' => $this->getProjectTrendsData()
        ];

        return view('monitor.analytics', compact('analytics'));
    }

    /**
     * Obtener reporte de actividad diaria
     */
    private function getDailyActivityReport()
    {
        return ProjectTracking::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as count')
        )
        ->where('created_at', '>=', now()->subDays(30))
        ->groupBy('date')
        ->orderBy('date')
        ->get();
    }

    /**
     * Obtener reporte de engagement de usuarios
     */
    private function getUserEngagementReport()
    {
        return User::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as count')
        )
        ->where('created_at', '>=', now()->subDays(30))
        ->groupBy('date')
        ->orderBy('date')
        ->get();
    }

    /**
     * Obtener reporte de rendimiento de proyectos
     */
    private function getProjectPerformanceReport()
    {
        return ProjectTracking::select('project_status', DB::raw('COUNT(*) as count'))
            ->groupBy('project_status')
            ->get();
    }

    /**
     * Obtener datos de visualizaciones de página
     */
    private function getPageViewsData()
    {
        // Datos simulados - en una implementación real, esto vendría de analytics
        return collect(range(0, 29))->map(function($day) {
            return [
                'date' => now()->subDays(29 - $day)->format('Y-m-d'),
                'views' => rand(100, 1000)
            ];
        });
    }

    /**
     * Obtener datos de crecimiento de usuarios
     */
    private function getUserGrowthData()
    {
        return collect(range(0, 11))->map(function($month) {
            return [
                'month' => now()->subMonths(11 - $month)->format('M Y'),
                'users' => rand(10, 100)
            ];
        });
    }

    /**
     * Obtener datos de tendencias de proyectos
     */
    private function getProjectTrendsData()
    {
        return collect(range(0, 6))->map(function($day) {
            return [
                'date' => now()->subDays(6 - $day)->format('Y-m-d'),
                'projects' => rand(5, 25)
            ];
        });
    }
}



