@extends('layouts.admin')

@section('title', 'Puestos de Trabajo - Admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center pb-2 mb-3 border-bottom">
        <h1 class="h2"><i class="fas fa-briefcase me-2"></i>Puestos de Trabajo</h1>
        <a href="{{ route('admin.job-positions.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Nuevo Puesto
        </a>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="card shadow">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
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
                        @forelse($jobPositions as $position)
                        <tr>
                            <td>{{ $position->job_position_id }}</td>
                            <td>
                                <span class="badge" style="background-color: {{ $position->position_color }}">
                                    {{ $position->position_name }}
                                </span>
                            </td>
                            <td>{{ Str::limit($position->position_description, 50) }}</td>
                            <td>
                                <span class="badge" style="background-color: {{ $position->position_color }}">
                                    {{ $position->position_color }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $position->is_active ? 'success' : 'secondary' }}">
                                    {{ $position->is_active ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('admin.job-positions.edit', $position->job_position_id) }}" 
                                   class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @if($position->is_active)
                                <form action="{{ route('admin.job-positions.destroy', $position->job_position_id) }}" 
                                      method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" 
                                            onclick="return confirm('¿Desactivar este puesto?')">
                                        <i class="fas fa-ban"></i>
                                    </button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">No hay puestos de trabajo registrados</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $jobPositions->links() }}
        </div>
    </div>
</div>
@endsection
