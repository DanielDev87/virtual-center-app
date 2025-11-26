<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserRole;

class AdminRoleController extends Controller
{
    /**
     * Display a listing of roles
     */
    public function index()
    {
        $roles = UserRole::paginate(15);
        return view('admin.roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new role
     */
    public function create()
    {
        return view('admin.roles.create');
    }

    /**
     * Store a newly created role
     */
    public function store(Request $request)
    {
        $request->validate([
            'role_name' => 'required|string|max:100|unique:user_roles',
            'role_description' => 'nullable|string|max:255',
            'role_color' => 'nullable|string|max:7',
        ]);

        UserRole::create([
            'role_name' => $request->role_name,
            'role_description' => $request->role_description,
            'role_color' => $request->role_color ?? '#6c757d',
            'is_active' => true,
        ]);

        return redirect()->route('admin.roles.index')
            ->with('success', 'Rol creado exitosamente.');
    }

    /**
     * Show the form for editing a role
     */
    public function edit($id)
    {
        $role = UserRole::findOrFail($id);
        return view('admin.roles.edit', compact('role'));
    }

    /**
     * Update the specified role
     */
    public function update(Request $request, $id)
    {
        $role = UserRole::findOrFail($id);

        $request->validate([
            'role_name' => 'required|string|max:100|unique:user_roles,role_name,' . $id . ',role_id',
            'role_description' => 'nullable|string|max:255',
            'role_color' => 'nullable|string|max:7',
        ]);

        $role->update([
            'role_name' => $request->role_name,
            'role_description' => $request->role_description,
            'role_color' => $request->role_color,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.roles.index')
            ->with('success', 'Rol actualizado exitosamente.');
    }

    /**
     * Remove the specified role
     */
    public function destroy($id)
    {
        $role = UserRole::findOrFail($id);
        
        // Check if role is in use
        if ($role->users()->count() > 0) {
            return redirect()->route('admin.roles.index')
                ->with('error', 'No se puede eliminar un rol que está asignado a usuarios.');
        }

        $role->update(['is_active' => false]);

        return redirect()->route('admin.roles.index')
            ->with('success', 'Rol desactivado exitosamente.');
    }
}
