@extends('layouts.admin')

@section('title', 'Cursos - Admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center pb-2 mb-3 border-bottom">
        <h1 class="h2"><i class="fas fa-book me-2"></i>Cursos</h1>
        <a href="{{ route('admin.academic.courses.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Nuevo Curso
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
                        <tr>
                            <td>{{ $course->course_code }}</td>
                            <td>{{ $course->course_name }}</td>
                            <td>{{ $course->program->program_name ?? 'N/A' }}</td>
                            <td>{{ $course->credits }}</td>
                            <td>
                                <span class="badge bg-{{ $course->is_active ? 'success' : 'secondary' }}">
                                    {{ $course->is_active ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td>
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
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">No hay cursos registrados</td>
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
