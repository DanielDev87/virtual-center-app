@extends('layouts.admin')

@section('title', 'Cursos - Admin')

@section('content')
@php
    $isMonitorUser = (auth()->user()->role->role_name ?? null) === 'Monitor';
@endphp
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
        @if(!$isMonitorUser)
        <a href="{{ route('admin.academic.courses.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Nuevo Curso
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
            <form method="GET" action="{{ route('admin.academic.courses.index') }}" class="row g-2 align-items-end mb-3">
                <div class="col-md-5">
                    <label class="form-label form-label-sm mb-1">Buscar</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Nombre o código..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label form-label-sm mb-1">Programa</label>
                    <select name="program_id" class="form-select form-select-sm">
                        <option value="">Todos</option>
                        @foreach($programs as $program)
                        <option value="{{ $program->program_id }}" {{ request('program_id') == $program->program_id ? 'selected' : '' }}>{{ $program->program_name }}</option>
                        @endforeach
                    </select>
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
                    @if(request()->hasAny(['search','program_id','status']))
                    <a href="{{ route('admin.academic.courses.index') }}" class="btn btn-sm btn-outline-secondary" title="Limpiar"><i class="fas fa-times"></i></a>
                    @endif
                </div>
            </form>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Nombre</th>
                            <th>Programa</th>
                            <th>Créditos</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($courses as $course)
                        @php
                            $linkedPrograms = $course->relationLoaded('programs')
                                ? $course->programs
                                : collect();

                            if ($linkedPrograms->isEmpty() && $course->program) {
                                $linkedPrograms = collect([$course->program]);
                            }
                        @endphp
                        <tr>
                            <td>{{ $course->course_code }}</td>
                            <td>{{ $course->course_name }}</td>
                            <td>
                                @if($linkedPrograms->isNotEmpty())
                                    @foreach($linkedPrograms as $program)
                                        <span class="badge bg-light text-dark border me-1 mb-1">{{ $program->program_name }}</span>
                                    @endforeach
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>{{ $course->credits }}</td>
                            <td>
                                <span class="badge bg-{{ $course->is_active ? 'success' : 'secondary' }}">
                                    {{ $course->is_active ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td>
                                @if(!$isMonitorUser)
                                <a href="{{ route('admin.academic.courses.edit', $course->course_id) }}" 
                                   class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @if($course->is_active)
                                <form action="{{ route('admin.academic.courses.destroy', $course->course_id) }}" 
                                      method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" 
                                            onclick="return confirm('¿Desactivar este curso?')">
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
                            <td colspan="6" class="text-center text-muted">
                                @if(request()->hasAny(['search','program_id','status']))
                                    No se encontraron cursos con los filtros aplicados.
                                    <div class="mt-2"><a href="{{ route('admin.academic.courses.index') }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-times me-1"></i>Limpiar filtros</a></div>
                                @else
                                    No hay cursos registrados
                                @endif
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $courses->links() }}
        </div>
    </div>
</div>
@endsection
