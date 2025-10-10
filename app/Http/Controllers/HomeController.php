<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProjectTracking;
use App\Models\Institution;
use App\Models\MaterialType;

class HomeController extends Controller
{
    /**
     * Mostrar la página principal
     */
    public function index()
    {
        $projects = ProjectTracking::with(['institution', 'materialType'])
            ->where('is_active', true)
            ->latest()
            ->take(6)
            ->get();

        $institutions = Institution::where('is_active', true)->get();
        $materialTypes = MaterialType::where('is_active', true)->get();

        return view('home.index', compact('projects', 'institutions', 'materialTypes'));
    }

    /**
     * Búsqueda de proyectos
     */
    public function search(Request $request)
    {
        $query = $request->get('q');
        
        $projects = ProjectTracking::with(['institution', 'materialType'])
            ->where('is_active', true)
            ->where(function($q) use ($query) {
                $q->where('project_name', 'like', "%{$query}%")
                  ->orWhere('project_description', 'like', "%{$query}%");
            })
            ->get();

        return response()->json($projects);
    }

    /**
     * Detalles de un proyecto específico
     */
    public function projectDetails($id)
    {
        $project = ProjectTracking::with(['institution', 'materialType', 'comments.user'])
            ->findOrFail($id);

        return response()->json($project);
    }

    /**
     * Enviar estado de proyecto
     */
    public function sendStatus(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:project_tracking,tracking_id',
            'status' => 'required|in:pending,in_progress,completed,cancelled',
            'notes' => 'nullable|string'
        ]);

        $project = ProjectTracking::findOrFail($request->project_id);
        $project->update([
            'project_status' => $request->status,
            'project_notes' => $request->notes
        ]);

        return response()->json(['success' => true, 'message' => 'Estado actualizado correctamente']);
    }
}

