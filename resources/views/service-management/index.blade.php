@extends('layouts.app')

@section('title', 'Gestión de Servicios - Virtual Center')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Gestión de Servicios</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#filterModal">
                    <i class="fas fa-filter me-1"></i>Filtrar
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-download me-1"></i>Exportar
                </button>
            </div>
            <a href="{{ route('service-management.create') }}" class="btn btn-sm btn-primary">
                <i class="fas fa-plus me-1"></i>Nuevo Servicio
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-md-3">
            <select class="form-select" id="statusFilter">
                <option value="">Todos los estados</option>
                <option value="pending">Pendiente</option>
                <option value="in_progress">En Progreso</option>
                <option value="completed">Completado</option>
                <option value="cancelled">Cancelado</option>
            </select>
        </div>
        <div class="col-md-3">
            <select class="form-select" id="institutionFilter">
                <option value="">Todas las instituciones</option>
                @foreach($institutions as $institution)
                <option value="{{ $institution->institution_id }}">{{ $institution->institution_name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <select class="form-select" id="materialFilter">
                <option value="">Todos los tipos</option>
                @foreach($materialTypes as $type)
                <option value="{{ $type->material_type_id }}">{{ $type->material_type_name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <input type="text" class="form-control" id="searchInput" placeholder="Buscar proyectos...">
        </div>
    </div>

    <!-- Projects Table -->
    <div class="card shadow">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="projectsTable">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Nombre del Proyecto</th>
                            <th>Institución</th>
                            <th>Tipo de Material</th>
                            <th>Estado</th>
                            <th>Fecha Inicio</th>
                            <th>Fecha Fin</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($projects as $project)
                        <tr>
                            <td>{{ $project->tracking_id }}</td>
                            <td>
                                <div class="fw-bold">{{ $project->project_name }}</div>
                                <small class="text-muted">{{ Str::limit($project->project_description, 50) }}</small>
                            </td>
                            <td>{{ $project->institution->institution_name }}</td>
                            <td>{{ $project->materialType->material_type_name }}</td>
                            <td>
                                <span class="badge bg-{{ $project->project_status === 'completed' ? 'success' : ($project->project_status === 'in_progress' ? 'warning' : ($project->project_status === 'cancelled' ? 'danger' : 'secondary')) }}">
                                    {{ ucfirst($project->project_status) }}
                                </span>
                            </td>
                            <td>{{ $project->start_date ? $project->start_date->format('d/m/Y') : '-' }}</td>
                            <td>{{ $project->end_date ? $project->end_date->format('d/m/Y') : '-' }}</td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('service-management.show', $project->tracking_id) }}" 
                                       class="btn btn-outline-primary" title="Ver">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('service-management.edit', $project->tracking_id) }}" 
                                       class="btn btn-outline-warning" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-outline-danger" 
                                            onclick="deleteProject({{ $project->tracking_id }})" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="fas fa-inbox fa-3x mb-3"></i>
                                <p>No hay proyectos registrados</p>
                                <a href="{{ route('service-management.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>Crear Primer Proyecto
                                </a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            @if($projects->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $projects->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Filter Modal -->
<div class="modal fade" id="filterModal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="filterModalLabel">Filtrar Proyectos</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="filterForm">
                    <div class="mb-3">
                        <label for="filterStatus" class="form-label">Estado</label>
                        <select class="form-select" id="filterStatus">
                            <option value="">Todos</option>
                            <option value="pending">Pendiente</option>
                            <option value="in_progress">En Progreso</option>
                            <option value="completed">Completado</option>
                            <option value="cancelled">Cancelado</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="filterInstitution" class="form-label">Institución</label>
                        <select class="form-select" id="filterInstitution">
                            <option value="">Todas</option>
                            @foreach($institutions as $institution)
                            <option value="{{ $institution->institution_id }}">{{ $institution->institution_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="filterDateFrom" class="form-label">Fecha Desde</label>
                        <input type="date" class="form-control" id="filterDateFrom">
                    </div>
                    <div class="mb-3">
                        <label for="filterDateTo" class="form-label">Fecha Hasta</label>
                        <input type="date" class="form-control" id="filterDateTo">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" onclick="applyFilters()">Aplicar Filtros</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirmar Eliminación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de que deseas eliminar este proyecto?</p>
                <p class="text-muted">Esta acción no se puede deshacer.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Eliminar</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Search functionality
    $('#searchInput').on('keyup', function() {
        const value = $(this).val().toLowerCase();
        $('#projectsTable tbody tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });

    // Filter functionality
    $('#statusFilter, #institutionFilter, #materialFilter').on('change', function() {
        applyFilters();
    });
});

function applyFilters() {
    const status = $('#statusFilter').val();
    const institution = $('#institutionFilter').val();
    const material = $('#materialFilter').val();
    
    // Reload page with filters
    const url = new URL(window.location);
    if (status) url.searchParams.set('status', status);
    if (institution) url.searchParams.set('institution', institution);
    if (material) url.searchParams.set('material', material);
    
    window.location.href = url.toString();
}

function deleteProject(projectId) {
    $('#deleteForm').attr('action', `/service-management/${projectId}`);
    $('#deleteModal').modal('show');
}
</script>
@endpush
