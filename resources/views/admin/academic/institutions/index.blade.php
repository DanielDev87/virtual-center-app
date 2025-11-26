@extends('layouts.admin')

@section('title', 'Instituciones - Admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center pb-2 mb-3 border-bottom">
        <h1 class="h2"><i class="fas fa-university me-2"></i>Instituciones</h1>
        <a href="{{ route('admin.academic.institutions.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Nueva Institución
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
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($institutions as $institution)
                        <tr>
                            <td>{{ $institution->institution_id }}</td>
                            <td>{{ $institution->institution_name }}</td>
                            <td>{{ Str::limit($institution->institution_description, 50) }}</td>
                            <td>
                                <span class="badge bg-{{ $institution->is_active ? 'success' : 'secondary' }}">
                                    {{ $institution->is_active ? 'Activa' : 'Inactiva' }}
                                </span>
                            </td>
                            <td>
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
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">No hay instituciones registradas</td>
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
