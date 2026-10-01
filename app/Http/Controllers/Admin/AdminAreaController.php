<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Faculty;
use Illuminate\Http\Request;

class AdminAreaController extends Controller
{
    public function index(Request $request)
    {
        $query = Area::with('faculty');

        if ($request->filled('search')) {
            $query->where('area_name', 'like', "%{$request->search}%");
        }

        if ($request->filled('area_id')) {
            $query->where('area_id', $request->area_id);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $areas = $query->orderBy('area_name')->paginate(15)->withQueryString();
        $areaOptions = Area::orderBy('area_name')->get(['area_id', 'area_name']);
        return view('admin.academic.areas.index', compact('areas', 'areaOptions'));
    }

    public function create()
    {
        $faculties = Faculty::where('is_active', true)->get();
        return view('admin.academic.areas.create', compact('faculties'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'faculty_id' => 'nullable|exists:faculties,faculty_id',
            'area_name' => 'required|string|max:255',
            'area_description' => 'nullable|string',
        ]);

        $data = $request->all();
        $data['is_active'] = $request->has('is_active');

        Area::create($data);

        return redirect()->route('admin.academic.areas.index')
            ->with('success', 'Área creada exitosamente.');
    }

    public function edit($id)
    {
        $area = Area::findOrFail($id);
        $faculties = Faculty::where('is_active', true)->get();
        return view('admin.academic.areas.edit', compact('area', 'faculties'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'faculty_id' => 'nullable|exists:faculties,faculty_id',
            'area_name' => 'required|string|max:255',
            'area_description' => 'nullable|string',
        ]);

        $area = Area::findOrFail($id);
        
        $data = $request->all();
        $data['is_active'] = $request->has('is_active');
        
        $area->update($data);

        return redirect()->route('admin.academic.areas.index')
            ->with('success', 'Área actualizada exitosamente.');
    }

    public function destroy($id)
    {
        $area = Area::findOrFail($id);
        $area->update(['is_active' => false]);

        return redirect()->route('admin.academic.areas.index')
            ->with('success', 'Área desactivada exitosamente.');
    }
}
