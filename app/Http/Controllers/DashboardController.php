<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProjectTracking;
use App\Models\User;
use App\Models\TaskList;
use App\Models\Institution;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Mostrar dashboard principal
     */
    public function index()
    {
        // Estadísticas generales
        $stats = [
            'total_projects' => ProjectTracking::count(),
            'active_projects' => ProjectTracking::where('is_active', true)->count(),
            'completed_projects' => ProjectTracking::where('project_status', 'completed')->count(),
            'total_users' => User::where('is_active', true)->count(),
            'pending_tasks' => TaskList::where('task_status', 'pending')->count(),
            'total_institutions' => Institution::where('is_active', true)->count()
        ];

        // Proyectos recientes
        $recentProjects = ProjectTracking::with(['institution', 'materialType'])
            ->latest()
            ->take(10)
            ->get();

        // Tareas pendientes
        $pendingTasks = TaskList::with(['assignedTo', 'assignedBy'])
            ->where('task_status', 'pending')
            ->orderBy('due_date', 'asc')
            ->take(10)
            ->get();

        // Gráfico de proyectos por estado
        $projectsByStatus = ProjectTracking::select('project_status', DB::raw('count(*) as count'))
            ->groupBy('project_status')
            ->get()
            ->pluck('count', 'project_status');

        // Gráfico de proyectos por institución
        $projectsByInstitution = ProjectTracking::with('institution')
            ->select('institution_id', DB::raw('count(*) as count'))
            ->groupBy('institution_id')
            ->get()
            ->mapWithKeys(function($item) {
                return [$item->institution->institution_name => $item->count];
            });

        return view('dashboard.index', compact(
            'stats', 
            'recentProjects', 
            'pendingTasks', 
            'projectsByStatus', 
            'projectsByInstitution'
        ));
    }
}

