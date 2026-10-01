<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserRole;

class AdminRoleController extends Controller
{
    /**
     * Mostrar el listado de roles
     */
    public function index(Request $request)
    {
        $query = UserRole::query();

        if ($request->filled('search')) {
            $query->where('role_name', 'like', "%{$request->search}%");
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $roles = $query->orderBy('role_name')->paginate(15)->withQueryString();
        return view('admin.roles.index', compact('roles'));
    }

    /**
     * Mostrar el formulario para crear un nuevo rol
     */
    public function create()
    {
        return view('admin.roles.create');
    }

    /**
     * Almacenar un nuevo rol recién creado
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
     * Mostrar el formulario para editar un rol
     */
    public function edit($id)
    {
        $role = UserRole::findOrFail($id);
        return view('admin.roles.edit', compact('role'));
    }

    /**
     * Actualizar el rol especificado
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
     * Eliminar el rol especificado
     */
    public function destroy($id)
    {
        $role = UserRole::findOrFail($id);
        
        // Verificar si el rol está en uso
        if ($role->users()->count() > 0) {
            return redirect()->route('admin.roles.index')
                ->with('error', 'No se puede eliminar un rol que está asignado a usuarios.');
        }

        $role->update(['is_active' => false]);

        return redirect()->route('admin.roles.index')
            ->with('success', 'Rol desactivado exitosamente.');
    }
}
