@extends('layouts.requester')

@section('title', 'Detalles del Proyecto - Virtual Center')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Detalles de la Solicitud #{{ $ticket->ticket_number }}</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="{{ route('service-management.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>Volver
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Ticket Information -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Información</h5>
                    @php
                        $statusColors = [1 => 'secondary', 2 => 'warning', 3 => 'success', 4 => 'danger'];
                        $statusNames = [1 => 'Pendiente', 2 => 'En Progreso', 3 => 'Completado', 4 => 'Cancelado'];
                    @endphp
                    <span class="badge bg-{{ $statusColors[$ticket->status] ?? 'secondary' }} fs-6">
                        {{ $statusNames[$ticket->status] ?? 'Desconocido' }}
                    </span>
                </div>
                <div class="card-body">
                    <h5 class="fw-bold">{{ $ticket->title }}</h5>
                    <p class="text-muted">{{ $ticket->requester_info }}</p>
                    
                    <hr>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <small class="text-muted d-block">Solicitado el:</small>
                            <strong>{{ $ticket->created_at->format('d/m/Y H:i') }}</strong>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block">Última actualización:</small>
                            <strong>{{ $ticket->updated_at->format('d/m/Y H:i') }}</strong>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Resource Link (Only if Completed and has link) -->
            @if($ticket->status == 3 && $ticket->resource_link)
            <div class="card shadow mb-4 border-primary">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0"><i class="fas fa-check-circle me-2"></i>Resultado del Servicio</h5>
                </div>
                <div class="card-body text-center">
                    <h5 class="card-title">¡Su solicitud ha sido completada!</h5>
                    <p class="card-text">Su recurso ha sido generado y está disponible para descarga o visualización.</p>
                    <a href="{{ $ticket->resource_link }}" target="_blank" class="btn btn-primary btn-lg mt-2">
                        <i class="fas fa-external-link-alt me-2"></i>Acceder al Recurso
                    </a>
                </div>
            </div>
            @endif

            <!-- Rating Section (Only if Completed) -->
            @if($ticket->status == 3)
            <div class="card shadow mb-4 border-success">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0"><i class="fas fa-star me-2"></i>Calificar Servicio</h5>
                </div>
                <div class="card-body">
                    @if($ticket->rating)
                        <div class="text-center">
                            <h4 class="mb-3">¡Gracias por tu calificación!</h4>
                            <div class="display-4 text-warning mb-2">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="{{ $i <= $ticket->rating ? 'fas' : 'far' }} fa-star"></i>
                                @endfor
                            </div>
                            <p class="text-muted">Calificaste este servicio con {{ $ticket->rating }} estrellas.</p>
                        </div>
                    @else
                        <form action="{{ route('service-management.rate', $ticket->ticket_id) }}" method="POST">
                            @csrf
                            <div class="text-center mb-4">
                                <p class="lead">¿Qué tan satisfecho estás con el resultado?</p>
                                <div class="rating-input display-4 text-warning" style="cursor: pointer;">
                                    <i class="far fa-star" data-rating="1"></i>
                                    <i class="far fa-star" data-rating="2"></i>
                                    <i class="far fa-star" data-rating="3"></i>
                                    <i class="far fa-star" data-rating="4"></i>
                                    <i class="far fa-star" data-rating="5"></i>
                                </div>
                                <input type="hidden" name="rating" id="ratingValue" required>
                            </div>
                            <div class="mb-3">
                                <label for="comment" class="form-label">Comentario (Opcional)</label>
                                <textarea class="form-control" name="comment" rows="3" placeholder="Cuéntanos más sobre tu experiencia..."></textarea>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-success btn-lg">Enviar Calificación</button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0"><i class="fas fa-users me-2"></i>Equipo de Trabajo</h5>
                </div>
                <div class="card-body">
                    @if($ticket->assignments->where('status', 'active')->count() > 0)
                        <ul class="list-group list-group-flush">
                            @foreach($ticket->assignments->where('status', 'active') as $assignment)
                            <li class="list-group-item px-0">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <div class="avatar-circle bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                            {{ strtoupper(substr($assignment->mediator->user_name, 0, 1)) }}
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="mb-0">{{ $assignment->mediator->user_name }}</h6>
                                        <span class="badge" style="background-color: {{ $assignment->jobPosition->position_color }}">
                                            {{ $assignment->jobPosition->position_name }}
                                        </span>
                                    </div>
                                </div>
                            </li>
                            @endforeach
                        </ul>
                    @elseif($ticket->mediator)
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="avatar-circle bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    {{ strtoupper(substr($ticket->mediator->user_name, 0, 1)) }}
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-0">{{ $ticket->mediator->user_name }}</h6>
                                <small class="text-muted">Mediador Principal</small>
                            </div>
                        </div>
                    @else
                        <p class="text-muted mb-0">Aún no se ha asignado un equipo de trabajo.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Star rating interaction
    $('.rating-input i').hover(function() {
        const rating = $(this).data('rating');
        updateStars(rating);
    }, function() {
        const currentRating = $('#ratingValue').val();
        updateStars(currentRating);
    });

    $('.rating-input i').click(function() {
        const rating = $(this).data('rating');
        $('#ratingValue').val(rating);
        updateStars(rating);
    });

    function updateStars(rating) {
        $('.rating-input i').each(function() {
            const starRating = $(this).data('rating');
            if (starRating <= rating) {
                $(this).removeClass('far').addClass('fas');
            } else {
                $(this).removeClass('fas').addClass('far');
            }
        });
    }
});
</script>
@endpush

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


