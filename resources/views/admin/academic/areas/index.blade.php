@extends('layouts.admin')

@section('title', 'Áreas - Admin')

@section('content')
@php
    $isMonitorUser = (auth()->user()->role->role_name ?? null) === 'Monitor';
@endphp
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
        @if(!$isMonitorUser)
        <a href="{{ route('admin.academic.areas.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Nueva Área
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
            <form method="GET" action="{{ route('admin.academic.areas.index') }}" class="row g-2 align-items-end mb-3">
                <div class="col-md-5">
                    <label class="form-label form-label-sm mb-1">Buscar</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Nombre del área..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label form-label-sm mb-1">Área</label>
                    <select name="area_id" class="form-select form-select-sm">
                        <option value="">Todas</option>
                        @foreach($areaOptions as $areaOption)
                        <option value="{{ $areaOption->area_id }}" {{ request('area_id') == $areaOption->area_id ? 'selected' : '' }}>{{ $areaOption->area_name }}</option>
                        @endforeach
                    </select>
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
                    @if(request()->hasAny(['search','area_id','status']))
                    <a href="{{ route('admin.academic.areas.index') }}" class="btn btn-sm btn-outline-secondary" title="Limpiar"><i class="fas fa-times"></i></a>
                    @endif
                </div>
            </form>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Facultad (Académica)</th>
                            <th>Descripción</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($areas as $area)
                        <tr>
                            <td>{{ $area->area_name }}</td>
                            <td>
                                @if($area->faculty)
                                    <span class="badge bg-info"><i class="fas fa-graduation-cap me-1"></i> {{ $area->faculty->faculty_name }}</span>
                                @else
                                    <span class="badge bg-secondary"><i class="fas fa-building me-1"></i> Administrativa</span>
                                @endif
                            </td>
                            <td>{{ Str::limit($area->area_description, 50) }}</td>
                            <td>
                                <span class="badge bg-{{ $area->is_active ? 'success' : 'secondary' }}">
                                    {{ $area->is_active ? 'Activa' : 'Inactiva' }}
                                </span>
                            </td>
                            <td>
                                @if(!$isMonitorUser)
                                <a href="{{ route('admin.academic.areas.edit', $area->area_id) }}" 
                                   class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @if($area->is_active)
                                <form action="{{ route('admin.academic.areas.destroy', $area->area_id) }}" 
                                      method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" 
                                            onclick="return confirm('¿Desactivar esta área?')">
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
                                @if(request()->hasAny(['search','area_id','status']))
                                    No se encontraron áreas con los filtros aplicados.
                                    <div class="mt-2"><a href="{{ route('admin.academic.areas.index') }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-times me-1"></i>Limpiar filtros</a></div>
                                @else
                                    No hay áreas registradas
                                @endif
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $areas->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>
@endsection
