<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JobPosition;
use Illuminate\Http\Request;

class AdminJobPositionController extends Controller
{
    public function index()
    {
        $jobPositions = JobPosition::latest()->paginate(15);
        return view('admin.job-positions.index', compact('jobPositions'));
    }

    public function create()
    {
        return view('admin.job-positions.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'position_name' => 'required|string|max:255',
            'position_description' => 'nullable|string',
            'position_color' => 'nullable|string|max:7',
        ]);

        JobPosition::create($request->all());

        return redirect()->route('admin.job-positions.index')
            ->with('success', 'Puesto de trabajo creado exitosamente.');
    }

    public function edit($id)
    {
        $jobPosition = JobPosition::findOrFail($id);
        return view('admin.job-positions.edit', compact('jobPosition'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'position_name' => 'required|string|max:255',
            'position_description' => 'nullable|string',
            'position_color' => 'nullable|string|max:7',
            'is_active' => 'boolean',
        ]);

        $jobPosition = JobPosition::findOrFail($id);
        $jobPosition->update($request->all());

        return redirect()->route('admin.job-positions.index')
            ->with('success', 'Puesto de trabajo actualizado exitosamente.');
    }

    public function destroy($id)
    {
        $jobPosition = JobPosition::findOrFail($id);
        $jobPosition->update(['is_active' => false]);

        return redirect()->route('admin.job-positions.index')
            ->with('success', 'Puesto de trabajo desactivado exitosamente.');
    }
}
