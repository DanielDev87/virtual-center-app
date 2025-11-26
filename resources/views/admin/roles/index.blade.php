@extends('layouts.admin')

@section('title', 'Gestión de Roles - Admin')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Gestión de Roles</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="{{ route('admin.roles.create') }}" class="btn btn-sm btn-primary">
                <i class="fas fa-plus me-1"></i>Nuevo Rol
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Roles Table -->
    <div class="card shadow">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th>Color</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($roles as $role)
                        <tr>
                            <td>{{ $role->role_id }}</td>
                            <td>
                                <span class="badge" style="background-color: {{ $role->role_color }}">
                                    {{ $role->role_name }}
                                </span>
                            </td>
                            <td>{{ $role->role_description ?? '-' }}</td>
                            <td>
                                <input type="color" value="{{ $role->role_color }}" disabled style="width: 50px; height: 30px; border: none;">
                            </td>
                            <td>
                                @if($role->is_active)
                                    <span class="badge bg-success">Activo</span>
                                @else
                                    <span class="badge bg-secondary">Inactivo</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('admin.roles.edit', $role->role_id) }}" 
                                       class="btn btn-outline-warning" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.roles.destroy', $role->role_id) }}" 
                                          method="POST" class="d-inline" 
                                          onsubmit="return confirm('¿Estás seguro de desactivar este rol?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Desactivar">
                                            <i class="fas fa-ban"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="fas fa-user-tag fa-3x mb-3"></i>
                                <p>No hay roles registrados</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            @if($roles->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $roles->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
