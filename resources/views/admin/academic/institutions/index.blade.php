@extends('layouts.admin')

@section('title', 'Instituciones - Admin')

@section('content')
@php
    $isMonitorUser = (auth()->user()->role->role_name ?? null) === 'Monitor';
@endphp
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
        @if(!$isMonitorUser)
        <a href="{{ route('admin.academic.institutions.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Nueva Institución
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
            <form method="GET" action="{{ route('admin.academic.institutions.index') }}" class="row g-2 align-items-end mb-3">
                <div class="col-md-8">
                    <label class="form-label form-label-sm mb-1">Buscar</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Nombre de la institución..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label form-label-sm mb-1">Estado</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Todas</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Activa</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactiva</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-primary w-100"><i class="fas fa-filter me-1"></i>Filtrar</button>
                    @if(request()->hasAny(['search','status']))
                    <a href="{{ route('admin.academic.institutions.index') }}" class="btn btn-sm btn-outline-secondary" title="Limpiar"><i class="fas fa-times"></i></a>
                    @endif
                </div>
            </form>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($institutions as $institution)
                        <tr>
                            <td>{{ $institution->institution_name }}</td>
                            <td>{{ Str::limit($institution->institution_description, 50) }}</td>
                            <td>
                                <span class="badge bg-{{ $institution->is_active ? 'success' : 'secondary' }}">
                                    {{ $institution->is_active ? 'Activa' : 'Inactiva' }}
                                </span>
                            </td>
                            <td>
                                @if(!$isMonitorUser)
                                <a href="{{ route('admin.academic.institutions.edit', $institution->institution_id) }}" 
                                   class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @if($institution->is_active)
                                <form action="{{ route('admin.academic.institutions.destroy', $institution->institution_id) }}" 
                                      method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" 
                                            onclick="return confirm('¿Desactivar esta institución?')">
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
                            <td colspan="4" class="text-center text-muted">
                                @if(request()->hasAny(['search','status']))
                                    No se encontraron instituciones con los filtros aplicados.
                                    <div class="mt-2"><a href="{{ route('admin.academic.institutions.index') }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-times me-1"></i>Limpiar filtros</a></div>
                                @else
                                    No hay instituciones registradas
                                @endif
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $institutions->links() }}
        </div>
    </div>
</div>
@endsection
