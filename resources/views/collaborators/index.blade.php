@extends('layouts.app')

@section('title', 'Colaboradores - Virtual Center')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Colaboradores</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#filterModal">
                    <i class="fas fa-filter me-1"></i>Filtrar
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="exportCollaborators()">
                    <i class="fas fa-download me-1"></i>Exportar
                </button>
            </div>
            <a href="{{ route('collaborators.create') }}" class="btn btn-sm btn-primary">
                <i class="fas fa-plus me-1"></i>Nuevo Colaborador
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-md-3">
            <select class="form-select" id="roleFilter">
                <option value="">Todos los roles</option>
                @foreach($userRoles as $role)
                <option value="{{ $role->role_id }}">{{ $role->role_name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <select class="form-select" id="statusFilter">
                <option value="">Todos los estados</option>
                <option value="active">Activo</option>
                <option value="inactive">Inactivo</option>
                <option value="pending">Pendiente</option>
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
            <input type="text" class="form-control" id="searchInput" placeholder="Buscar colaboradores...">
        </div>
    </div>

    <!-- Collaborators Grid -->
    <div class="row" id="collaboratorsGrid">
        @forelse($collaborators as $collaborator)
        <div class="col-lg-4 col-md-6 mb-4 collaborator-card" 
             data-role="{{ $collaborator->user_role_id }}" 
             data-status="{{ $collaborator->user_status }}" 
             data-institution="{{ $collaborator->institution_id }}">
            <div class="card shadow h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="flex-shrink-0">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                <i class="fas fa-user fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="card-title mb-1">{{ $collaborator->user_name }}</h5>
                            <p class="card-text text-muted mb-0">{{ $collaborator->userRole->role_name }}</p>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('collaborators.show', $collaborator->user_id) }}">
                                    <i class="fas fa-eye me-2"></i>Ver Detalles
                                </a></li>
                                <li><a class="dropdown-item" href="{{ route('collaborators.edit', $collaborator->user_id) }}">
                                    <i class="fas fa-edit me-2"></i>Editar
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="#" onclick="deleteCollaborator({{ $collaborator->user_id }})">
                                    <i class="fas fa-trash me-2"></i>Eliminar
                                </a></li>
                            </ul>
                        </div>
                    </div>

                    <div class="row text-center mb-3">
                        <div class="col-4">
                            <div class="border-end">
                                <div class="h6 text-primary">{{ $collaborator->projects->count() }}</div>
                                <small class="text-muted">Proyectos</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border-end">
                                <div class="h6 text-success">{{ $collaborator->tasks->count() }}</div>
                                <small class="text-muted">Tareas</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="h6 text-info">{{ $collaborator->comments->count() }}</div>
                            <small class="text-muted">Comentarios</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <span class="badge bg-{{ $collaborator->user_status === 'active' ? 'success' : ($collaborator->user_status === 'inactive' ? 'secondary' : 'warning') }}">
                            {{ ucfirst($collaborator->user_status) }}
                        </span>
                        @if($collaborator->institution)
                        <span class="badge bg-info">{{ $collaborator->institution->institution_name }}</span>
                        @endif
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">
                            Última actividad: {{ $collaborator->updated_at->diffForHumans() }}
                        </small>
                        <a href="{{ route('collaborators.show', $collaborator->user_id) }}" class="btn btn-sm btn-outline-primary">
                            Ver Perfil
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="text-center text-muted py-5">
                <i class="fas fa-users fa-5x mb-4"></i>
                <h3>No hay colaboradores registrados</h3>
                <p>Comienza agregando el primer colaborador al sistema</p>
                <a href="{{ route('collaborators.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Agregar Primer Colaborador
                </a>
            </div>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($collaborators->hasPages())
    <div class="d-flex justify-content-center mt-4">
        {{ $collaborators->links() }}
    </div>
    @endif
</div>

<!-- Filter Modal -->
<div class="modal fade" id="filterModal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="filterModalLabel">Filtrar Colaboradores</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="filterForm">
                    <div class="mb-3">
                        <label for="filterRole" class="form-label">Rol</label>
                        <select class="form-select" id="filterRole">
                            <option value="">Todos</option>
                            @foreach($userRoles as $role)
                            <option value="{{ $role->role_id }}">{{ $role->role_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="filterStatus" class="form-label">Estado</label>
                        <select class="form-select" id="filterStatus">
                            <option value="">Todos</option>
                            <option value="active">Activo</option>
                            <option value="inactive">Inactivo</option>
                            <option value="pending">Pendiente</option>
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
                <p>¿Estás seguro de que deseas eliminar este colaborador?</p>
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
        $('.collaborator-card').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });

    // Filter functionality
    $('#roleFilter, #statusFilter, #institutionFilter').on('change', function() {
        applyFilters();
    });
});

function applyFilters() {
    const role = $('#roleFilter').val();
    const status = $('#statusFilter').val();
    const institution = $('#institutionFilter').val();
    
    $('.collaborator-card').each(function() {
        const card = $(this);
        const cardRole = card.data('role');
        const cardStatus = card.data('status');
        const cardInstitution = card.data('institution');
        
        let show = true;
        
        if (role && cardRole != role) show = false;
        if (status && cardStatus != status) show = false;
        if (institution && cardInstitution != institution) show = false;
        
        card.toggle(show);
    });
}

function exportCollaborators() {
    VirtualCenter.showAlert('Exportando colaboradores...', 'info', 2000);
    
    // Create and download CSV
    const csvContent = generateCollaboratorsCSV();
    const blob = new Blob([csvContent], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'colaboradores-' + new Date().toISOString().split('T')[0] + '.csv';
    a.click();
    window.URL.revokeObjectURL(url);
}

function generateCollaboratorsCSV() {
    const headers = ['Nombre', 'Rol', 'Estado', 'Institución', 'Proyectos', 'Tareas', 'Última Actividad'];
    const rows = [];
    
    $('.collaborator-card').each(function() {
        const card = $(this);
        const name = card.find('.card-title').text();
        const role = card.find('.card-text').text();
        const status = card.find('.badge').first().text();
        const institution = card.find('.badge').last().text();
        const projects = card.find('.text-primary').text();
        const tasks = card.find('.text-success').text();
        const lastActivity = card.find('.text-muted').last().text();
        
        rows.push([name, role, status, institution, projects, tasks, lastActivity].join(','));
    });
    
    return [headers.join(','), ...rows].join('\n');
}

function deleteCollaborator(collaboratorId) {
    $('#deleteForm').attr('action', `/collaborators/${collaboratorId}`);
    $('#deleteModal').modal('show');
}
</script>
@endpush


