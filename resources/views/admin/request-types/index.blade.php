@extends('layouts.admin')

@section('title', 'Tópicos de Servicio - Admin')

@section('content')
@php
    $isMonitorUser = (auth()->user()->role->role_name ?? null) === 'Monitor';
@endphp
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        @if(!$isMonitorUser)
        <div class="d-flex gap-2">
            <a href="{{ route('admin.bulk-import.index') }}" class="btn btn-outline-secondary shadow-sm">
                <i class="fas fa-file-import fa-sm me-1"></i> Carga Masiva
            </a>
            <a href="{{ route('admin.request-types.create') }}" class="btn btn-primary shadow-sm">
                <i class="fas fa-plus fa-sm text-white-50 me-1"></i> Crear Nuevo Tópico
            </a>
        </div>
        @endif
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-check-circle me-1"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-exclamation-circle me-1"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Gestión de Tópicos</h6>
        </div>
        <div class="card-body pb-2">
            <form method="GET" action="{{ route('admin.request-types.index') }}" class="row g-2 align-items-end mb-3">
                <div class="col-md-7">
                    <label class="form-label form-label-sm mb-1">Buscar</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Nombre o descripción..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3">
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
                    <a href="{{ route('admin.request-types.index') }}" class="btn btn-sm btn-outline-secondary" title="Limpiar"><i class="fas fa-times"></i></a>
                    @endif
                </div>
            </form>
            <div class="table-responsive">
                <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                    <thead class="request-types-thead">
                        <tr>
                            <th>Tópico</th>
                            <th>Icono / Color</th>
                            <th>Colaboradores Asignados</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requestTypes as $type)
                        <tr>
                            <td>
                                <strong>{{ $type->type_name }}</strong>
                                @if($type->type_description)
                                    <br><small class="text-muted">{{ Str::limit($type->type_description, 50) }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge" style="background-color: {{ $type->type_color ?? '#6c757d' }}">
                                    <i class="fas {{ $type->type_icon ?? 'fa-tag' }}"></i> {{ $type->type_icon }}
                                </span>
                            </td>
                            <td>
                                @if(($supportsCollaboratorAssignments ?? false) && $type->collaborators->isNotEmpty())
                                    @foreach($type->collaborators as $collaborator)
                                        <span class="badge bg-light text-dark border me-1 mb-1">
                                            <i class="fas fa-user me-1 text-primary"></i>{{ $collaborator->user_name }}
                                        </span>
                                    @endforeach
                                @elseif($type->gestor)
                                    <span class="badge bg-light text-dark border">
                                        <i class="fas fa-user-tie me-1 text-primary"></i>{{ $type->gestor->user_name }}
                                    </span>
                                @else
                                    <span class="text-warning"><i class="fas fa-exclamation-triangle"></i> Sin Asignar</span>
                                @endif
                            </td>
                            <td>
                                @if($type->is_active)
                                    <span class="badge bg-success">Activo</span>
                                @else
                                    <span class="badge bg-danger">Inactivo</span>
                                @endif
                            </td>
                            <td>
                                @if(!$isMonitorUser)
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.request-types.edit', $type->type_id) }}" class="btn btn-sm btn-info text-white" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.request-types.destroy', $type->type_id) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Está seguro de eliminar este tópico?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                                @else
                                <span class="badge bg-light text-secondary border">Solo observación</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">
                                <i class="fas fa-folder-open fa-3x mb-3 d-block text-gray-300"></i>
                                @if(request()->hasAny(['search','status']))
                                    No se encontraron tópicos con los filtros aplicados.
                                    <div class="mt-2"><a href="{{ route('admin.request-types.index') }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-times me-1"></i>Limpiar filtros</a></div>
                                @else
                                    No hay tópicos registrados en el sistema.
                                @endif
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-center mt-3">
                {{ $requestTypes->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.request-types-thead {
    background-color: var(--bs-tertiary-bg);
}

.request-types-thead th {
    color: var(--bs-body-color);
}

[data-bs-theme="dark"] .card .input-group-text {
    background-color: var(--bs-secondary-bg);
    color: var(--bs-body-color);
    border-color: var(--bs-border-color);
}

[data-bs-theme="dark"] .card .form-control,
[data-bs-theme="dark"] .card .form-select {
    background-color: var(--bs-body-bg);
    color: var(--bs-body-color);
    border-color: var(--bs-border-color);
}
</style>
@endpush
