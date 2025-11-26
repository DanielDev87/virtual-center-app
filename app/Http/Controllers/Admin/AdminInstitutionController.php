<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Institution;
use Illuminate\Http\Request;

class AdminInstitutionController extends Controller
{
    /**
     * Display a listing of institutions
     */
    public function index()
    {
        $institutions = Institution::latest()->paginate(15);
        return view('admin.academic.institutions.index', compact('institutions'));
    }

    /**
     * Show the form for creating a new institution
     */
    public function create()
    {
        return view('admin.academic.institutions.create');
    }

    /**
     * Store a newly created institution
     */
    public function store(Request $request)
    {
        $request->validate([
            'institution_name' => 'required|string|max:255',
            'institution_description' => 'nullable|string',
            'institution_logo' => 'nullable|string|max:255',
        ]);

        Institution::create($request->all());

        return redirect()->route('admin.academic.institutions.index')
            ->with('success', 'Institución creada exitosamente.');
    }

    /**
     * Show the form for editing the specified institution
     */
    public function edit($id)
    {
        $institution = Institution::findOrFail($id);
        return view('admin.academic.institutions.edit', compact('institution'));
    }

    /**
     * Update the specified institution
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'institution_name' => 'required|string|max:255',
            'institution_description' => 'nullable|string',
            'institution_logo' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $institution = Institution::findOrFail($id);
        $institution->update($request->all());

        return redirect()->route('admin.academic.institutions.index')
            ->with('success', 'Institución actualizada exitosamente.');
    }

    /**
     * Remove the specified institution (soft delete by setting is_active = false)
     */
    public function destroy($id)
    {
        $institution = Institution::findOrFail($id);
        $institution->update(['is_active' => false]);

        return redirect()->route('admin.academic.institutions.index')
            ->with('success', 'Institución desactivada exitosamente.');
    }
}
