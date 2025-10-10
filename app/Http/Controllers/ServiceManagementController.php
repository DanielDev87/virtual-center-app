<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProjectTracking;
use App\Models\Institution;
use App\Models\MaterialType;
use App\Models\TaskList;

class ServiceManagementController extends Controller
{
    /**
     * Mostrar lista de servicios
     */
    public function index()
    {
        $projects = ProjectTracking::with(['institution', 'materialType'])
            ->latest()
            ->paginate(15);

        $institutions = Institution::where('is_active', true)->get();
        $materialTypes = MaterialType::where('is_active', true)->get();

        return view('service-management.index', compact('projects', 'institutions', 'materialTypes'));
    }

    /**
     * Mostrar formulario de creación
     */
    public function create()
    {
        $institutions = Institution::where('is_active', true)->get();
        $materialTypes = MaterialType::where('is_active', true)->get();

        return view('service-management.create', compact('institutions', 'materialTypes'));
    }

    /**
     * Guardar nuevo servicio
     */
    public function store(Request $request)
    {
        $request->validate([
            'project_name' => 'required|string|max:255',
            'project_description' => 'nullable|string',
            'institution_id' => 'required|exists:institutions,institution_id',
            'material_type_id' => 'required|exists:material_types,material_type_id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date'
        ]);

        ProjectTracking::create($request->all());

        return redirect()->route('service-management.index')
            ->with('success', 'Servicio creado exitosamente.');
    }

    /**
     * Mostrar servicio específico
     */
    public function show($id)
    {
        $project = ProjectTracking::with(['institution', 'materialType', 'comments.user'])
            ->findOrFail($id);

        return view('service-management.show', compact('project'));
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit($id)
    {
        $project = ProjectTracking::findOrFail($id);
        $institutions = Institution::where('is_active', true)->get();
        $materialTypes = MaterialType::where('is_active', true)->get();

        return view('service-management.edit', compact('project', 'institutions', 'materialTypes'));
    }

    /**
     * Actualizar servicio
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'project_name' => 'required|string|max:255',
            'project_description' => 'nullable|string',
            'institution_id' => 'required|exists:institutions,institution_id',
            'material_type_id' => 'required|exists:material_types,material_type_id',
            'project_status' => 'required|in:pending,in_progress,completed,cancelled',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'project_notes' => 'nullable|string'
        ]);

        $project = ProjectTracking::findOrFail($id);
        $project->update($request->all());

        return redirect()->route('service-management.show', $id)
            ->with('success', 'Servicio actualizado exitosamente.');
    }

    /**
     * Eliminar servicio
     */
    public function destroy($id)
    {
        $project = ProjectTracking::findOrFail($id);
        $project->update(['is_active' => false]);

        return redirect()->route('service-management.index')
            ->with('success', 'Servicio desactivado exitosamente.');
    }
}

