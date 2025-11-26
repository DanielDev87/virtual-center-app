@extends('layouts.admin')

@section('title', 'Facultades - Admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center pb-2 mb-3 border-bottom">
        <h1 class="h2"><i class="fas fa-building me-2"></i>Facultades</h1>
        <a href="{{ route('admin.academic.faculties.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Nueva Facultad
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
                            <th>Institución</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($faculties as $faculty)
                        <tr>
                            <td>{{ $faculty->faculty_id }}</td>
                            <td>{{ $faculty->faculty_name }}</td>
                            <td>{{ $faculty->institution->institution_name ?? 'N/A' }}</td>
                            <td>
                                <span class="badge bg-{{ $faculty->is_active ? 'success' : 'secondary' }}">
                                    {{ $faculty->is_active ? 'Activa' : 'Inactiva' }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('admin.academic.faculties.edit', $faculty->faculty_id) }}" 
                                   class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @if($faculty->is_active)
                                <form action="{{ route('admin.academic.faculties.destroy', $faculty->faculty_id) }}" 
                                      method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" 
                                            onclick="return confirm('¿Desactivar esta facultad?')">
                                        <i class="fas fa-ban"></i>
                                    </button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">No hay facultades registradas</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $faculties->links() }}
        </div>
    </div>
</div>
@endsection
