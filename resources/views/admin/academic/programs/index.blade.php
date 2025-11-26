@extends('layouts.admin')

@section('title', 'Programas - Admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center pb-2 mb-3 border-bottom">
        <h1 class="h2"><i class="fas fa-graduation-cap me-2"></i>Programas</h1>
        <a href="{{ route('admin.academic.programs.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Nuevo Programa
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
                            <th>Código</th>
                            <th>Nombre</th>
                            <th>Facultad</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($programs as $program)
                        <tr>
                            <td>{{ $program->program_id }}</td>
                            <td>{{ $program->program_code }}</td>
                            <td>{{ $program->program_name }}</td>
                            <td>{{ $program->faculty->faculty_name ?? 'N/A' }}</td>
                            <td>
                                <span class="badge bg-{{ $program->is_active ? 'success' : 'secondary' }}">
                                    {{ $program->is_active ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('admin.academic.programs.edit', $program->program_id) }}" 
                                   class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @if($program->is_active)
                                <form action="{{ route('admin.academic.programs.destroy', $program->program_id) }}" 
                                      method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" 
                                            onclick="return confirm('¿Desactivar este programa?')">
                                        <i class="fas fa-ban"></i>
                                    </button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">No hay programas registrados</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $programs->links() }}
        </div>
    </div>
</div>
@endsection
