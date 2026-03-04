<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserRole;
use App\Models\TaskList;
use App\Models\ProjectTracking;

class CollaboratorsController extends Controller
{
    /**
     * Mostrar página pública de colaboradores
     */
    public function index()
    {
        $collaborators = User::with('role')
            ->where('is_active', true)
            ->whereHas('role', function($query) {
                $query->where('role_name', 'like', '%colaborador%')
                      ->orWhere('role_name', 'like', '%contributor%');
            })
            ->get();

        return view('collaborators.public', compact('collaborators'));
    }

    /**
     * Dashboard de colaboradores (área protegida)
     */
    public function dashboard()
    {
        $user = auth()->user();
        
        $assignedTasks = TaskList::with(['assignedBy', 'assignedTo'])
            ->where('assigned_to', $user->user_id)
            ->where('task_status', '!=', 'completed')
            ->latest()
            ->get();

        $recentProjects = ProjectTracking::with(['institution', 'materialType'])
            ->where('is_active', true)
            ->latest()
            ->take(5)
            ->get();

        return view('collaborators.dashboard', compact('assignedTasks', 'recentProjects'));
    }

    /**
     * Perfil del colaborador
     */
    public function profile()
    {
        $user = auth()->user();
        $user->load('role');
        
        return view('collaborators.profile', compact('user'));
    }

    /**
     * Tareas del colaborador
     */
    public function tasks()
    {
        $user = auth()->user();
        
        $tasks = TaskList::with(['assignedBy', 'assignedTo'])
            ->where('assigned_to', $user->user_id)
            ->orderBy('due_date', 'asc')
            ->get();

        return view('collaborators.tasks', compact('tasks'));
    }

    /**
     * Proyectos del colaborador
     */
    public function projects()
    {
        $user = auth()->user();
        
        // Obtener proyectos donde el usuario ha comentado o está involucrado
        $projects = ProjectTracking::with(['institution', 'materialType', 'comments'])
            ->whereHas('comments', function($query) use ($user) {
                $query->where('user_id', $user->user_id);
            })
            ->orWhere('is_active', true)
            ->get();

        return view('collaborators.projects', compact('projects'));
    }
}



