<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserRole;
use App\Models\JobPosition;
use App\Models\Area;
use Illuminate\Support\Facades\Hash;

class AdminUserController extends Controller
{
    /**
     * Mostrar el listado de recursos.
     */
    public function index(Request $request)
    {
        $query = User::with('role');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('user_name', 'like', "%{$search}%")
                  ->orWhere('user_email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role_id')) {
            $query->where('role_id', $request->role_id);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $users = $query->orderBy('user_name')->paginate(15)->withQueryString();
        $roles = UserRole::where('is_active', true)->orderBy('role_name')->get();

        return view('admin.users.index', compact('users', 'roles'));
    }

    /**
     * Mostrar el formulario para crear un nuevo recurso.
     */
    public function create()
    {
        $roles = UserRole::where('is_active', true)->get();
        $jobPositions = JobPosition::where('is_active', true)->get();
        $areas = Area::where('is_active', true)->get();
        return view('admin.users.create', compact('roles', 'jobPositions', 'areas'));
    }

    /**
     * Almacenar un nuevo recurso recién creado.
     */
    public function store(Request $request)
    {
        $selectedRole = UserRole::find($request->role_id);
        $requiresArea = $this->roleRequiresArea($selectedRole);

        $request->validate([
            'user_name' => 'required|string|max:255',
            'user_email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role_id' => 'required|exists:user_roles,role_id',
            'area_id' => ($requiresArea ? 'required' : 'nullable') . '|exists:areas,area_id',
            'job_positions' => 'nullable|array',
            'job_positions.*' => 'exists:job_positions,job_position_id',
        ]);

        $user = User::create([
            'user_name' => $request->user_name,
            'user_email' => $request->user_email,
            'password' => Hash::make($request->password),
            'role_id' => $request->role_id,
            'area_id' => $request->area_id,
            'is_active' => true,
        ]);

        if ($request->has('job_positions')) {
            $user->jobPositions()->sync($request->job_positions);
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuario creado exitosamente.');
    }

    /**
     * Mostrar el formulario para editar el recurso especificado.
     */
    public function edit($id)
    {
        $user = User::with('jobPositions')->findOrFail($id);
        $roles = UserRole::where('is_active', true)->get();
        $jobPositions = JobPosition::where('is_active', true)->get();
        $areas = Area::where('is_active', true)->get();
        return view('admin.users.edit', compact('user', 'roles', 'jobPositions', 'areas'));
    }

    /**
     * Actualizar el recurso especificado.
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $selectedRole = UserRole::find($request->role_id);
        $requiresArea = $this->roleRequiresArea($selectedRole);

        $request->validate([
            'user_name' => 'required|string|max:255',
            'user_email' => 'required|string|email|max:255|unique:users,user_email,' . $id . ',user_id',
            'role_id' => 'required|exists:user_roles,role_id',
            'area_id' => ($requiresArea ? 'required' : 'nullable') . '|exists:areas,area_id',
            'password' => 'nullable|string|min:8|confirmed',
            'job_positions' => 'nullable|array',
            'job_positions.*' => 'exists:job_positions,job_position_id',
        ]);

        $data = [
            'user_name' => $request->user_name,
            'user_email' => $request->user_email,
            'role_id' => $request->role_id,
            'area_id' => $request->area_id,
            'is_active' => $request->has('is_active'),
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        if ($request->has('job_positions')) {
            $user->jobPositions()->sync($request->job_positions);
        } else {
            $user->jobPositions()->detach();
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuario actualizado exitosamente.');
    }

    /**
     * Eliminar el recurso especificado.
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        // Lógica de eliminación suave o definitiva según los requerimientos
        // Por ahora, simplemente desactivamos el usuario
        $user->update(['is_active' => false]);

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuario desactivado exitosamente.');
    }

    /**
     * Determina si el rol seleccionado requiere área obligatoria.
     */
    private function roleRequiresArea(?UserRole $role): bool
    {
        if (!$role) {
            return false;
        }

        $roleName = mb_strtolower($role->role_name, 'UTF-8');

        return $roleName === 'admin área' || $roleName === 'admin area';
    }
}
