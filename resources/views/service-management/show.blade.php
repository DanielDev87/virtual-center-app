@extends(Auth::user()->role->role_name == 'Requester' ? 'layouts.requester' : 'layouts.contributor')

@section('title', 'Detalles de la Solicitud - Virtual Center')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between flex-wrap align-items-center pt-3 pb-2 mb-3 border-bottom gap-2">
        <h1 class="h2 mb-0">Detalles de la Solicitud #{{ $ticket->ticket_number }}</h1>
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
                    <div class="text-muted">{!! $ticket->requester_info !!}</div>

                    @php $visibleEvidences = $ticket->evidences->where('storage_disk', '!=', 'richtext_filesystem'); @endphp

                    @if($ticket->requester_url || $visibleEvidences->count())
                    <div class="mt-3">
                        <h6 class="fw-bold mb-2"><i class="fas fa-paperclip me-1"></i>Evidencias Adjuntas</h6>
                        @if($ticket->requester_url)
                        <a href="{{ $ticket->requester_url }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary me-2 mb-2">
                            <i class="fab fa-google-drive me-1"></i>Carpeta de Google Drive
                        </a>
                        @endif

                        @foreach($visibleEvidences as $evidence)
                            @if($evidence->public_url)
                            <a href="{{ $evidence->public_url }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary me-2 mb-2">
                                <i class="fas fa-file-alt me-1"></i>{{ $evidence->file_name }}
                            </a>
                            @endif
                        @endforeach
                    </div>
                    @endif
                    
                    <hr>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <small class="text-muted d-block">Solicitado el:</small>
                            <strong>{{ $ticket->created_at->format('d/m/Y H:i') }}</strong>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted d-block">Última actualización:</small>
                            <strong>{{ $ticket->updated_at->format('d/m/Y H:i') }}</strong>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted d-block">Prioridad:</small>
                            <strong>
                                @php
                                    $priorities = [1 => 'Baja', 2 => 'Media', 3 => 'Alta (Afecta operación)', 4 => 'Urgente (Suspende operación)'];
                                    $priorityColors = [1 => 'success', 2 => 'info', 3 => 'warning text-dark', 4 => 'danger'];
                                @endphp
                                <span class="badge bg-{{ $priorityColors[$ticket->priority] ?? 'secondary' }}">
                                    {{ $priorities[$ticket->priority] ?? 'No asignada' }}
                                </span>
                            </strong>
                        </div>
                    </div>

                    @if($ticket->priority_sla_hours)
                    <hr>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <small class="text-muted d-block">Tiempo objetivo (SLA)</small>
                            <strong>{{ $ticket->priority_sla_hours }} horas</strong>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted d-block">Fecha límite de respuesta</small>
                            <strong>{{ $ticket->response_deadline->format('d/m/Y H:i') }}</strong>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted d-block">Estado del tiempo</small>
                            @if($ticket->is_response_overdue)
                                <span class="badge bg-danger">Vencido</span>
                            @else
                                <span class="badge bg-success">Dentro del tiempo</span>
                            @endif
                        </div>
                    </div>
                    @endif
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

            @php
                $finalResponse = $ticket->progress
                    ->whereIn('status_update', ['service_closed', 'service_closed_admin', 'service_closed_area_admin', 'operario_completion_approved'])
                    ->sortByDesc('created_at')
                    ->first();
                $finalResponseText = $finalResponse?->progress_description;
                foreach ([
                    'Cierre del servicio: ',
                    'Cierre administrativo del servicio: ',
                    'Cierre por admin de área: ',
                    'Auditoría aprobada por admin de área: ',
                ] as $prefix) {
                    $finalResponseText = $finalResponseText !== null
                        ? preg_replace('/^' . preg_quote($prefix, '/') . '/', '', $finalResponseText)
                        : null;
                }
            @endphp
            @if($ticket->status == 3 && $finalResponseText)
            <div class="card shadow mb-4 border-success">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0"><i class="fas fa-comment-check me-2"></i>Respuesta final del servicio</h5>
                </div>
                <div class="card-body">
                    <p class="mb-0">{{ $finalResponseText }}</p>
                </div>
            </div>
            @endif

            @php
                $cancellationLog = $ticket->progress
                    ->whereIn('status_update', ['service_cancelled_admin', 'service_cancelled_area_admin'])
                    ->sortByDesc('created_at')
                    ->first();
                $cancellationReason = $cancellationLog?->progress_description;
                $cancellationReason = $cancellationReason !== null
                    ? preg_replace('/^Cancelación (administrativa del ticket|por admin de área)\. Motivo: /', '', $cancellationReason)
                    : null;
            @endphp
            @if($ticket->status == 4 && $cancellationReason)
            <div class="card shadow mb-4 border-danger">
                <div class="card-header bg-danger text-white">
                    <h5 class="card-title mb-0"><i class="fas fa-times-circle me-2"></i>Motivo de cancelación</h5>
                </div>
                <div class="card-body">
                    <p class="mb-0">{{ $cancellationReason }}</p>
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
                        <form id="service-management-rate-form" action="{{ route('service-management.rate', $ticket->ticket_id) }}" method="POST">
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
                                <label for="ratingComment" class="form-label">Observación <span id="commentHint" class="text-muted">(Opcional)</span></label>
                                <textarea class="form-control @error('comment') is-invalid @enderror" id="ratingComment" name="comment" rows="3" placeholder="Cuéntanos qué se puede mejorar..." minlength="15">{{ old('comment') }}</textarea>
                                @error('comment')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                <small class="text-muted d-block mt-1">Si calificas con 1 a 4 estrellas, la observación es obligatoria.</small>
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
                    <h5 class="card-title mb-0"><i class="fas fa-sitemap me-2"></i>Seguimiento del Caso</h5>
                </div>
                <div class="card-body">
                    @if($ticket->requestType)
                        <div class="mb-3">
                            <small class="text-muted d-block">Tópico actual</small>
                            <strong>{{ $ticket->requestType->type_name }}</strong>
                        </div>

                        @if($ticket->requestType->area)
                        <div class="mb-3">
                            <small class="text-muted d-block">Área responsable</small>
                            <strong>{{ $ticket->requestType->area->area_name }}</strong>
                        </div>
                        @endif

                        <p class="text-muted mb-0">
                            Tu solicitud está siendo atendida internamente por el área responsable según el tópico asignado.
                        </p>
                    @else
                        <p class="text-muted mb-0">Aún no se ha definido el área responsable para este caso.</p>
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
        updateCommentRequirement(rating);
    });

    function updateCommentRequirement(rating) {
        const comment = $('#ratingComment');
        const hint = $('#commentHint');
        const numericRating = parseInt(rating || 0, 10);
        const required = numericRating > 0 && numericRating < 5;

        comment.prop('required', required);
        hint.text(required ? '(Obligatoria. Mínimo 15 caracteres)' : '(Opcional)');
    }

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

    updateCommentRequirement($('#ratingValue').val());

    $('#service-management-rate-form').on('submit', function(e) {
        const rating = parseInt($('#ratingValue').val() || '0', 10);
        const comment = ($('#ratingComment').val() || '').trim();
        const meaningful = (comment.match(/[\p{L}\p{N}]/gu) || []).length;

        if (rating > 0 && rating < 5 && (comment.length < 15 || meaningful < 10)) {
            e.preventDefault();
            VirtualCenter.showAlert('La observación es obligatoria y debe tener mínimo 15 caracteres con contenido descriptivo.', 'warning');
        }
    });
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

@media (max-width: 767.98px) {
    .card-header .badge.fs-6 {
        font-size: 0.8rem !important;
    }

    .card-body .btn.btn-sm {
        width: 100%;
        margin-right: 0 !important;
    }

    .rating-input.display-4 {
        font-size: 2rem !important;
    }
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


