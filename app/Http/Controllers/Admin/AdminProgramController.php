<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Program;
use App\Models\Faculty;
use Illuminate\Http\Request;

class AdminProgramController extends Controller
{
    public function index(Request $request)
    {
        $query = Program::with('faculty');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('program_name', 'like', "%{$search}%")
                  ->orWhere('program_code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('faculty_id')) {
            $query->where('faculty_id', $request->faculty_id);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $programs = $query->orderBy('program_name')->paginate(15)->withQueryString();
        $faculties = Faculty::where('is_active', true)->orderBy('faculty_name')->get();
        return view('admin.academic.programs.index', compact('programs', 'faculties'));
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
