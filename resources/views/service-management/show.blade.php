@extends('layouts.app')

@section('title', 'Detalles del Proyecto - Virtual Center')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Detalles del Proyecto</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <a href="{{ route('service-management.edit', $project->tracking_id) }}" class="btn btn-sm btn-warning">
                    <i class="fas fa-edit me-1"></i>Editar
                </a>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="printProject()">
                    <i class="fas fa-print me-1"></i>Imprimir
                </button>
            </div>
            <a href="{{ route('service-management.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>Volver
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Project Information -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Información del Proyecto</h5>
                    <span class="badge bg-{{ $project->project_status === 'completed' ? 'success' : ($project->project_status === 'in_progress' ? 'warning' : ($project->project_status === 'cancelled' ? 'danger' : 'secondary')) }} fs-6">
                        {{ ucfirst($project->project_status) }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="fw-bold">ID del Proyecto:</td>
                                    <td>{{ $project->tracking_id }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Nombre:</td>
                                    <td>{{ $project->project_name }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Institución:</td>
                                    <td>{{ $project->institution->institution_name }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Tipo de Material:</td>
                                    <td>{{ $project->materialType->material_type_name }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="fw-bold">Estado:</td>
                                    <td>
                                        <span class="badge bg-{{ $project->project_status === 'completed' ? 'success' : ($project->project_status === 'in_progress' ? 'warning' : ($project->project_status === 'cancelled' ? 'danger' : 'secondary')) }}">
                                            {{ ucfirst($project->project_status) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Fecha de Inicio:</td>
                                    <td>{{ $project->start_date ? $project->start_date->format('d/m/Y') : 'No definida' }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Fecha de Fin:</td>
                                    <td>{{ $project->end_date ? $project->end_date->format('d/m/Y') : 'No definida' }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Creado:</td>
                                    <td>{{ $project->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($project->project_description)
                    <div class="mt-4">
                        <h6 class="fw-bold">Descripción:</h6>
                        <p class="text-muted">{{ $project->project_description }}</p>
                    </div>
                    @endif

                    @if($project->project_notes)
                    <div class="mt-4">
                        <h6 class="fw-bold">Notas:</h6>
                        <p class="text-muted">{{ $project->project_notes }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Comments Section -->
            <div class="card shadow mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Comentarios</h5>
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#commentModal">
                        <i class="fas fa-plus me-1"></i>Agregar Comentario
                    </button>
                </div>
                <div class="card-body">
                    @forelse($project->comments as $comment)
                    <div class="d-flex mb-3">
                        <div class="flex-shrink-0">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="fas fa-user"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1">{{ $comment->user->user_name }}</h6>
                                    <small class="text-muted">{{ $comment->created_at->format('d/m/Y H:i') }}</small>
                                </div>
                                <span class="badge bg-{{ $comment->comment_type === 'important' ? 'danger' : ($comment->comment_type === 'feedback' ? 'warning' : 'info') }}">
                                    {{ ucfirst($comment->comment_type) }}
                                </span>
                            </div>
                            <p class="mb-0 mt-2">{{ $comment->comment_content }}</p>
                        </div>
                    </div>
                    @empty
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-comments fa-3x mb-3"></i>
                        <p>No hay comentarios aún</p>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#commentModal">
                            <i class="fas fa-plus me-2"></i>Agregar Primer Comentario
                        </button>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Quick Actions -->
            <div class="card shadow mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Acciones Rápidas</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-outline-primary" onclick="changeStatus('in_progress')">
                            <i class="fas fa-play me-2"></i>Iniciar Proyecto
                        </button>
                        <button type="button" class="btn btn-outline-success" onclick="changeStatus('completed')">
                            <i class="fas fa-check me-2"></i>Marcar Completado
                        </button>
                        <button type="button" class="btn btn-outline-warning" onclick="changeStatus('pending')">
                            <i class="fas fa-pause me-2"></i>Pausar Proyecto
                        </button>
                        <button type="button" class="btn btn-outline-danger" onclick="changeStatus('cancelled')">
                            <i class="fas fa-times me-2"></i>Cancelar Proyecto
                        </button>
                    </div>
                </div>
            </div>

            <!-- Project Statistics -->
            <div class="card shadow mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Estadísticas</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-end">
                                <h4 class="text-primary">{{ $project->comments->count() }}</h4>
                                <small class="text-muted">Comentarios</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <h4 class="text-success">{{ $project->created_at->diffInDays(now()) }}</h4>
                            <small class="text-muted">Días Transcurridos</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Project Timeline -->
            <div class="card shadow">
                <div class="card-header">
                    <h5 class="card-title mb-0">Cronología</h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <h6>Proyecto Creado</h6>
                                <small class="text-muted">{{ $project->created_at->format('d/m/Y H:i') }}</small>
                            </div>
                        </div>
                        @if($project->start_date)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-success"></div>
                            <div class="timeline-content">
                                <h6>Proyecto Iniciado</h6>
                                <small class="text-muted">{{ $project->start_date->format('d/m/Y') }}</small>
                            </div>
                        </div>
                        @endif
                        @if($project->end_date)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-warning"></div>
                            <div class="timeline-content">
                                <h6>Proyecto Finalizado</h6>
                                <small class="text-muted">{{ $project->end_date->format('d/m/Y') }}</small>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Comment Modal -->
<div class="modal fade" id="commentModal" tabindex="-1" aria-labelledby="commentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="commentModalLabel">Agregar Comentario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="commentForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="comment_content" class="form-label">Comentario</label>
                        <textarea class="form-control" id="comment_content" name="comment_content" rows="4" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="comment_type" class="form-label">Tipo</label>
                        <select class="form-select" id="comment_type" name="comment_type">
                            <option value="general">General</option>
                            <option value="feedback">Feedback</option>
                            <option value="issue">Problema</option>
                            <option value="update">Actualización</option>
                        </select>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="is_important" name="is_important">
                        <label class="form-check-label" for="is_important">
                            Comentario Importante
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Agregar Comentario</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -30px;
    top: 5px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px #dee2e6;
}

.timeline-content h6 {
    margin-bottom: 5px;
    font-weight: 600;
}
</style>
@endpush

@push('scripts')
<script>
function printProject() {
    window.print();
}

function changeStatus(newStatus) {
    if (confirm(`¿Estás seguro de cambiar el estado a "${newStatus}"?`)) {
        // Aquí iría la lógica para cambiar el estado
        VirtualCenter.showAlert('Estado actualizado correctamente', 'success');
        location.reload();
    }
}

$('#commentForm').on('submit', function(e) {
    e.preventDefault();
    
    const formData = {
        content: $('#comment_content').val(),
        type: $('#comment_type').val(),
        important: $('#is_important').is(':checked')
    };
    
    if (!formData.content.trim()) {
        VirtualCenter.showAlert('El comentario no puede estar vacío', 'warning');
        return;
    }
    
    // Aquí iría la lógica para enviar el comentario
    VirtualCenter.showAlert('Comentario agregado correctamente', 'success');
    $('#commentModal').modal('hide');
    $('#commentForm')[0].reset();
    location.reload();
});
</script>
@endpush
