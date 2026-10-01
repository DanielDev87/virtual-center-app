@extends('layouts.admin')

@section('title', 'Puestos de Trabajo - Admin')

@section('content')
@php
    $isMonitorUser = (auth()->user()->role->role_name ?? null) === 'Monitor';
@endphp
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
        @if(!$isMonitorUser)
        <a href="{{ route('admin.job-positions.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Nuevo Puesto
        </a>
        @endif
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="card shadow">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.job-positions.index') }}" class="row g-2 align-items-end mb-3">
                <div class="col-md-8">
                    <label class="form-label form-label-sm mb-1">Buscar</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Nombre del puesto..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label form-label-sm mb-1">Estado</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Todos</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Activo</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactivo</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-primary w-100"><i class="fas fa-filter me-1"></i>Filtrar</button>
                    @if(request()->hasAny(['search','status']))
                    <a href="{{ route('admin.job-positions.index') }}" class="btn btn-sm btn-outline-secondary" title="Limpiar"><i class="fas fa-times"></i></a>
                    @endif
                </div>
            </form>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
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
                                @if(!$isMonitorUser)
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
                                @else
                                <span class="badge bg-light text-secondary border">Solo observación</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">
                                @if(request()->hasAny(['search','status']))
                                    No se encontraron puestos con los filtros aplicados.
                                    <div class="mt-2"><a href="{{ route('admin.job-positions.index') }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-times me-1"></i>Limpiar filtros</a></div>
                                @else
                                    No hay puestos de trabajo registrados
                                @endif
                            </td>
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
