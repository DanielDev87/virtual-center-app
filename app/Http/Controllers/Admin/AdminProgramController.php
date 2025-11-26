<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Program;
use App\Models\Faculty;
use Illuminate\Http\Request;

class AdminProgramController extends Controller
{
    public function index()
    {
        $programs = Program::with('faculty')->latest()->paginate(15);
        return view('admin.academic.programs.index', compact('programs'));
    }

    public function create()
    {
        $faculties = Faculty::where('is_active', true)->get();
        return view('admin.academic.programs.create', compact('faculties'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'faculty_id' => 'required|exists:faculties,faculty_id',
            'program_code' => 'nullable|string|max:20',
            'program_name' => 'required|string|max:255',
            'program_description' => 'nullable|string',
        ]);

        Program::create($request->all());

        return redirect()->route('admin.academic.programs.index')
            ->with('success', 'Programa creado exitosamente.');
    }

    public function edit($id)
    {
        $program = Program::findOrFail($id);
        $faculties = Faculty::where('is_active', true)->get();
        return view('admin.academic.programs.edit', compact('program', 'faculties'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'faculty_id' => 'required|exists:faculties,faculty_id',
            'program_code' => 'nullable|string|max:20',
            'program_name' => 'required|string|max:255',
            'program_description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $program = Program::findOrFail($id);
        $program->update($request->all());

        return redirect()->route('admin.academic.programs.index')
            ->with('success', 'Programa actualizado exitosamente.');
    }

    public function destroy($id)
    {
        $program = Program::findOrFail($id);
        $program->update(['is_active' => false]);

        return redirect()->route('admin.academic.programs.index')
            ->with('success', 'Programa desactivado exitosamente.');
    }
}
