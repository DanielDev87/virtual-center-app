<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserRole;
use App\Models\JobPosition;
use Illuminate\Support\Facades\Hash;

class AdminUserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::with('role')->paginate(15);
        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $roles = UserRole::where('is_active', true)->get();
        $jobPositions = JobPosition::where('is_active', true)->get();
        return view('admin.users.create', compact('roles', 'jobPositions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_name' => 'required|string|max:255',
            'user_email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role_id' => 'required|exists:user_roles,role_id',
            'job_positions' => 'nullable|array',
            'job_positions.*' => 'exists:job_positions,job_position_id',
        ]);

        $user = User::create([
            'user_name' => $request->user_name,
            'user_email' => $request->user_email,
            'password' => Hash::make($request->password),
            'role_id' => $request->role_id,
            'is_active' => true,
        ]);

        if ($request->has('job_positions')) {
            $user->jobPositions()->sync($request->job_positions);
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuario creado exitosamente.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $user = User::with('jobPositions')->findOrFail($id);
        $roles = UserRole::where('is_active', true)->get();
        $jobPositions = JobPosition::where('is_active', true)->get();
        return view('admin.users.edit', compact('user', 'roles', 'jobPositions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'user_name' => 'required|string|max:255',
            'user_email' => 'required|string|email|max:255|unique:users,user_email,' . $id . ',user_id',
            'role_id' => 'required|exists:user_roles,role_id',
            'password' => 'nullable|string|min:8|confirmed',
            'job_positions' => 'nullable|array',
            'job_positions.*' => 'exists:job_positions,job_position_id',
        ]);

        $data = [
            'user_name' => $request->user_name,
            'user_email' => $request->user_email,
            'role_id' => $request->role_id,
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
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        // Soft delete logic or hard delete depending on requirements
        // For now, we'll just deactivate
        $user->update(['is_active' => false]);

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuario desactivado exitosamente.');
    }
}
