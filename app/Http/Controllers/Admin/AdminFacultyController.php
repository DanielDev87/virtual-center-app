<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faculty;
use App\Models\Institution;
use Illuminate\Http\Request;

class AdminFacultyController extends Controller
{
    public function index(Request $request)
    {
        $query = Faculty::with('institution');

        if ($request->filled('search')) {
            $query->where('faculty_name', 'like', "%{$request->search}%");
        }

        if ($request->filled('institution_id')) {
            $query->where('institution_id', $request->institution_id);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $faculties = $query->orderBy('faculty_name')->paginate(15)->withQueryString();
        $institutions = Institution::where('is_active', true)->orderBy('institution_name')->get();
        return view('admin.academic.faculties.index', compact('faculties', 'institutions'));
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
