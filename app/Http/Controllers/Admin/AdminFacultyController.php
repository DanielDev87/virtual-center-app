<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faculty;
use App\Models\Institution;
use Illuminate\Http\Request;

class AdminFacultyController extends Controller
{
    public function index()
    {
        $faculties = Faculty::with('institution')->latest()->paginate(15);
        return view('admin.academic.faculties.index', compact('faculties'));
    }

    public function create()
    {
        $institutions = Institution::where('is_active', true)->get();
        return view('admin.academic.faculties.create', compact('institutions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'institution_id' => 'nullable|exists:institutions,institution_id',
            'faculty_name' => 'required|string|max:255',
            'faculty_description' => 'nullable|string',
        ]);

        Faculty::create($request->all());

        return redirect()->route('admin.academic.faculties.index')
            ->with('success', 'Facultad creada exitosamente.');
    }

    public function edit($id)
    {
        $faculty = Faculty::findOrFail($id);
        $institutions = Institution::where('is_active', true)->get();
        return view('admin.academic.faculties.edit', compact('faculty', 'institutions'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'institution_id' => 'nullable|exists:institutions,institution_id',
            'faculty_name' => 'required|string|max:255',
            'faculty_description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $faculty = Faculty::findOrFail($id);
        $faculty->update($request->all());

        return redirect()->route('admin.academic.faculties.index')
            ->with('success', 'Facultad actualizada exitosamente.');
    }

    public function destroy($id)
    {
        $faculty = Faculty::findOrFail($id);
        $faculty->update(['is_active' => false]);

        return redirect()->route('admin.academic.faculties.index')
            ->with('success', 'Facultad desactivada exitosamente.');
    }
}
