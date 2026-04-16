<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProjectTracking;
use App\Models\User;
use App\Models\TaskList;
use Illuminate\Support\Facades\DB;

class MonitorController extends Controller
{
    /**
     * Mostrar panel de monitoreo
     */
    public function index()
    {
        // Estadísticas de monitoreo
        $monitoringStats = [
            'active_sessions' => rand(15, 50), // Simulado
            'system_uptime' => '99.9%',
            'response_time' => rand(100, 500) . 'ms',
            'error_rate' => '0.1%'
        ];

        // Actividad reciente
        $recentActivity = ProjectTracking::with(['institution', 'materialType'])
            ->where('updated_at', '>=', now()->subDays(7))
            ->orderBy('updated_at', 'desc')
            ->take(20)
            ->get();

        // Usuarios activos
        $activeUsers = User::where('is_active', true)
            ->orderBy('updated_at', 'desc')
            ->take(10)
            ->get();

        return view('monitor.index', compact('monitoringStats', 'recentActivity', 'activeUsers'));
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



