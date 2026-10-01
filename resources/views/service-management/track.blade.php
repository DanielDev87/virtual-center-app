@extends('layouts.requester')

@section('title', 'Consultar Ticket - Virtual Center')

@section('content')
<div class="container">
    <div class="row justify-content-center mt-4">
        <div class="col-md-10 col-lg-8">
            
            <!-- Header & Form -->
            <div class="text-center mb-5">
                <h2 class="fw-bold text-success"><i class="fas fa-search me-2"></i>Consultar Estado de Solicitud</h2>
                <p class="text-muted">Ingresa tu número de documento y el número de ticket para conocer en qué etapa se encuentra actualmente.</p>
            </div>

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body p-4">
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    <form action="{{ route('service-management.searchTrack') }}" method="POST" class="row g-2">
                        @csrf
                        <div class="col-12 col-md-5">
                            <input type="text" name="document_number" class="form-control form-control-lg" placeholder="Número de documento" required value="{{ old('document_number', request('document_number')) }}">
                        </div>
                        <div class="col-12 col-md-5">
                            <input type="text" name="ticket_number" class="form-control form-control-lg" placeholder="Número de ticket (Ej: 20251126150037)" required value="{{ old('ticket_number', request('ticket_number')) }}">
                        </div>
                        <div class="col-12 col-md-2 d-grid">
                            <button type="submit" class="btn btn-success btn-lg"><i class="fas fa-search me-2"></i>Buscar</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Result Card -->
            @if(isset($ticket))
            <div class="card shadow border-0 track-result-card" style="border-top: 5px solid {{ $ticket->requestType->type_color ?? '#17a2b8' }} !important; border-radius: 0.5rem; overflow: hidden;">
                <div class="card-body p-5">
                    <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
                        <h4 class="fw-bold mb-0 text-body">Ticket #{{ $ticket->ticket_number }}</h4>
                        <span class="badge track-date-badge py-2 px-3">
                            <i class="fas fa-calendar-alt me-2 text-muted"></i>{{ $ticket->created_at->format('d/m/Y h:i A') }}
                        </span>
                    </div>
                    
                    <h5 class="fw-bold text-body mb-2">{{ $ticket->title }}</h5>
                    <p class="text-muted mb-4">{{ \Illuminate\Support\Str::limit(strip_tags($ticket->requester_info), 150) }}</p>
                    
                    <div class="row g-4 mt-2 p-3 rounded track-mobile-stack track-summary-panel">
                        <div class="col-md-4 text-center border-end">
                            <span class="track-metric-label text-uppercase d-block mb-2">Estado Actual</span>
                            @switch($ticket->status)
                                @case(1)
                                    <span class="badge bg-warning text-dark fs-6 py-2 px-3 shadow-sm"><i class="fas fa-clock me-2"></i>Pendiente / En Revisión</span>
                                    @break
                                @case(2)
                                    <span class="badge bg-primary fs-6 py-2 px-3 shadow-sm"><i class="fas fa-spinner fa-spin me-2"></i>En Proceso / Asignado</span>
                                    @break
                                @case(3)
                                    <span class="badge bg-success fs-6 py-2 px-3 shadow-sm"><i class="fas fa-check-circle me-2"></i>Completado</span>
                                    @break
                                @case(4)
                                    <span class="badge bg-danger fs-6 py-2 px-3 shadow-sm"><i class="fas fa-times-circle me-2"></i>Cancelado</span>
                                    @break
                                @default
                                    <span class="badge bg-secondary fs-6 py-2 px-3"><i class="fas fa-question-circle me-2"></i>Desconocido</span>
                            @endswitch
                        </div>
                        <div class="col-md-4 text-center border-end">
                            <span class="track-metric-label text-uppercase d-block mb-2">Tipo de Servicio</span>
                            <div class="d-inline-flex align-items-center p-2 rounded track-type-pill">
                                @if($ticket->requestType)
                                    <i class="fas {{ $ticket->requestType->type_icon }} fa-lg me-2" style="color: {{ $ticket->requestType->type_color }}"></i>
                                    <span class="fw-bold text-body-emphasis">{{ $ticket->requestType->type_name }}</span>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-4 text-center">
                            <span class="track-metric-label text-uppercase d-block mb-2">Prioridad Inicial</span>
                            @php
                                $priorities = [1 => 'Baja', 2 => 'Media', 3 => 'Alta (Afecta operación)', 4 => 'Urgente (Suspende operación)'];
                                $priorityColors = [1 => 'success', 2 => 'info', 3 => 'warning text-dark', 4 => 'danger'];
                            @endphp
                            <span class="badge bg-{{ $priorityColors[$ticket->priority] ?? 'secondary' }} fs-6 py-2 px-3 shadow-sm">
                                {{ $priorities[$ticket->priority] ?? 'No asignada' }}
                            </span>
                        </div>
                    </div>

                    @if($ticket->priority_sla_hours)
                    <div class="row g-3 mt-3">
                        <div class="col-md-4">
                            <div class="border rounded p-3 h-100 text-center track-sla-box">
                                <small class="text-muted d-block">Tiempo objetivo (SLA)</small>
                                <strong>{{ $ticket->priority_sla_hours }} horas</strong>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded p-3 h-100 text-center track-sla-box">
                                <small class="text-muted d-block">Fecha límite de respuesta</small>
                                <strong>{{ $ticket->response_deadline->format('d/m/Y H:i') }}</strong>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded p-3 h-100 text-center track-sla-box">
                                <small class="text-muted d-block">Estado del tiempo</small>
                                @if($ticket->is_response_overdue)
                                    <span class="badge bg-danger">Vencido</span>
                                @else
                                    <span class="badge bg-success">Dentro del tiempo</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif

                    @php
                        $finalResponse = $ticket->progress
                            ->whereIn('status_update', ['service_closed', 'service_closed_admin', 'service_closed_area_admin', 'operario_completion_approved'])
                            ->sortByDesc('created_at')
                            ->first();
                        $finalResponseText = $finalResponse?->progress_description;
                        $finalAttachmentIds = collect();
                        if ($finalResponseText !== null && preg_match('/\[attachments:([0-9,]+)\]$/', $finalResponseText, $matches)) {
                            $finalAttachmentIds = collect(explode(',', $matches[1]))
                                ->map(fn ($id) => (int) trim($id))
                                ->filter(fn ($id) => $id > 0)
                                ->values();
                            $finalResponseText = trim(preg_replace('/\n?\n?\[attachments:[0-9,]+\]$/', '', $finalResponseText));
                        }
                        $finalAttachmentItems = $finalAttachmentIds->isNotEmpty()
                            ? $ticket->evidences->whereIn('evidence_id', $finalAttachmentIds)->keyBy('evidence_id')
                            : collect();
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
                    <div class="alert alert-success mt-4 mb-0">
                        <h5 class="alert-heading"><i class="fas fa-comment-check me-2"></i>Respuesta final del servicio</h5>
                        <p class="mb-0">{{ $finalResponseText }}</p>
                        @if($finalAttachmentItems->isNotEmpty())
                        <div class="mt-3">
                            <strong><i class="fas fa-paperclip me-1"></i>Evidencias del cierre:</strong>
                            <ul class="mb-0 ps-3">
                                @foreach($finalAttachmentIds as $finalAttachmentId)
                                    @if($finalAttachmentItems->has($finalAttachmentId))
                                        @php $finalEvidence = $finalAttachmentItems->get($finalAttachmentId); @endphp
                                        <li>
                                            <a href="{{ route('service-management.trackEvidence', ['evidence' => $finalAttachmentId, 'ticket_number' => $ticket->ticket_number, 'document_number' => request('document_number', old('document_number'))]) }}" target="_blank" rel="noopener">
                                                {{ $finalEvidence->file_name }}
                                            </a>
                                        </li>
                                    @endif
                                @endforeach
                            </ul>
                        </div>
                        @endif
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
                    <div class="alert alert-danger mt-4 mb-0">
                        <h5 class="alert-heading"><i class="fas fa-times-circle me-2"></i>Motivo de cancelación</h5>
                        <p class="mb-0">{{ $cancellationReason }}</p>
                    </div>
                    @endif

                    @php
                        $communicationLogs = $ticket->progress
                            ->whereIn('status_update', ['collaborator_message', 'requester_message'])
                            ->sortBy('created_at')
                            ->values();
                        $canCommunicate = !in_array((int) $ticket->status, [3, 4], true);
                    @endphp

                    <div class="card shadow-sm mt-4 border-info">
                        <div class="card-header bg-info text-white">
                            <h5 class="card-title mb-0"><i class="fas fa-comments me-2"></i>Comunicación con el Colaborador</h5>
                        </div>
                        <div class="card-body">
                            @if($communicationLogs->isEmpty())
                                <p class="text-muted mb-3">Aún no hay mensajes en este ticket.</p>
                            @else
                                <div class="mb-3" style="max-height: 280px; overflow-y: auto;">
                                    @foreach($communicationLogs as $log)
                                        @php
                                            $isRequesterMessage = $log->status_update === 'requester_message';
                                            $senderName = $isRequesterMessage
                                                ? 'Solicitante'
                                                : (optional($log->user)->user_name ?? 'Colaborador');
                                            $rawDescription = (string) $log->progress_description;
                                            $attachmentIds = collect();
                                            if (preg_match('/\[attachments:([0-9,]+)\]$/', $rawDescription, $matches)) {
                                                $attachmentIds = collect(explode(',', $matches[1]))
                                                    ->map(fn ($id) => (int) trim($id))
                                                    ->filter(fn ($id) => $id > 0)
                                                    ->values();
                                                $rawDescription = trim(preg_replace('/\n?\n?\[attachments:[0-9,]+\]$/', '', $rawDescription));
                                            }
                                            $attachmentItems = $attachmentIds->isNotEmpty()
                                                ? \App\Models\TicketEvidence::whereIn('evidence_id', $attachmentIds)->get()->keyBy('evidence_id')
                                                : collect();
                                        @endphp
                                        <div class="border rounded p-2 mb-2 {{ $isRequesterMessage ? 'bg-light' : 'bg-body-tertiary' }}">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <strong class="small">{{ $senderName }}</strong>
                                                <small class="text-muted">{{ optional($log->created_at)->format('d/m/Y H:i') }}</small>
                                            </div>
                                            <div class="small">{{ $rawDescription }}</div>
                                            @if($attachmentItems->isNotEmpty())
                                                <div class="mt-2 small">
                                                    <strong>Adjuntos:</strong>
                                                    <ul class="mb-0 ps-3">
                                                        @foreach($attachmentIds as $attachmentId)
                                                            @if($attachmentItems->has($attachmentId))
                                                                @php $evidence = $attachmentItems->get($attachmentId); @endphp
                                                                <li>
                                                                    <a href="{{ route('service-management.trackEvidence', ['evidence' => $attachmentId, 'ticket_number' => $ticket->ticket_number, 'document_number' => request('document_number', old('document_number'))]) }}" target="_blank" rel="noopener">
                                                                        {{ $evidence->file_name }}
                                                                    </a>
                                                                </li>
                                                            @endif
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            @if($canCommunicate)
                                <form action="{{ route('service-management.trackMessage') }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <input type="hidden" name="document_number" value="{{ request('document_number', old('document_number')) }}">
                                    <input type="hidden" name="ticket_number" value="{{ $ticket->ticket_number }}">
                                    <div class="mb-2">
                                        <label for="message" class="form-label">Responder al colaborador</label>
                                        <textarea name="message" id="message" rows="3" class="form-control @error('message') is-invalid @enderror" placeholder="Escribe tu mensaje..." required>{{ old('message') }}</textarea>
                                        @error('message')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="mb-2">
                                        <label for="attachments" class="form-label">Adjuntar evidencias (opcional)</label>
                                        <input type="file" name="attachments[]" id="attachments" class="form-control @error('attachments') is-invalid @enderror @error('attachments.*') is-invalid @enderror" multiple>
                                        <small class="text-muted">Hasta 5 archivos. Max 2MB c/u. Formatos: pdf, imagenes y ofimática comunes.</small>
                                        @error('attachments')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        @error('attachments.*')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="d-grid d-md-flex justify-content-md-end">
                                        <button type="submit" class="btn btn-info text-white">
                                            <i class="fas fa-paper-plane me-1"></i>Enviar mensaje
                                        </button>
                                    </div>
                                </form>
                            @else
                                <div class="alert alert-secondary mb-0">
                                    <i class="fas fa-lock me-1"></i>La comunicación está cerrada porque el ticket fue finalizado.
                                </div>
                            @endif
                        </div>
                    </div>

                    @if($ticket->status == 3)
                    <div class="card shadow-sm mt-4 border-success">
                        <div class="card-header bg-success text-white">
                            <h5 class="card-title mb-0"><i class="fas fa-star me-2"></i>Calificar Servicio</h5>
                        </div>
                        <div class="card-body">
                            @if($ticket->rating)
                                <div class="text-center">
                                    <h5 class="mb-3">¡Gracias por tu calificación!</h5>
                                    <div class="fs-2 text-warning mb-2">
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="{{ $i <= $ticket->rating ? 'fas' : 'far' }} fa-star"></i>
                                        @endfor
                                    </div>
                                    <p class="text-muted mb-0">Calificaste este servicio con {{ $ticket->rating }} estrellas.</p>
                                </div>
                            @else
                                <form id="service-management-rate-public-form" action="{{ route('service-management.ratePublic') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="ticket_number" value="{{ $ticket->ticket_number }}">
                                    <div class="text-center mb-4">
                                        <p class="lead mb-2">¿Qué tan satisfecho estás con el resultado?</p>
                                        <div class="rating-input fs-1 text-warning mb-3" style="cursor: pointer;">
                                            <i class="far fa-star" data-rating="1" onclick="setRating(1)"></i>
                                            <i class="far fa-star" data-rating="2" onclick="setRating(2)"></i>
                                            <i class="far fa-star" data-rating="3" onclick="setRating(3)"></i>
                                            <i class="far fa-star" data-rating="4" onclick="setRating(4)"></i>
                                            <i class="far fa-star" data-rating="5" onclick="setRating(5)"></i>
                                        </div>
                                        <input type="hidden" name="rating" id="ratingValue" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="publicRatingComment" class="form-label text-muted">Observación <span id="publicCommentHint">(Opcional)</span></label>
                                        <textarea class="form-control @error('comment') is-invalid @enderror" id="publicRatingComment" name="comment" rows="2" placeholder="Cuéntanos qué se puede mejorar..." minlength="15">{{ old('comment') }}</textarea>
                                        @error('comment')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                        <small class="text-muted d-block mt-1">Si calificas con 1 a 4 estrellas, la observación es obligatoria.</small>
                                    </div>
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-success">Guardar Calificación</button>
                                    </div>
                                </form>
                                <script>
                                    function setRating(rating) {
                                        document.getElementById('ratingValue').value = rating;
                                        const commentField = document.getElementById('publicRatingComment');
                                        const commentHint = document.getElementById('publicCommentHint');
                                        const requiresComment = parseInt(rating, 10) < 5;
                                        if (commentField) {
                                            commentField.required = requiresComment;
                                        }
                                        if (commentHint) {
                                            commentHint.textContent = requiresComment
                                                ? '(Obligatoria. Mínimo 15 caracteres)'
                                                : '(Opcional)';
                                        }

                                        const stars = document.querySelectorAll('.rating-input i');
                                        stars.forEach((star, index) => {
                                            if (index < rating) {
                                                star.classList.remove('far');
                                                star.classList.add('fas');
                                            } else {
                                                star.classList.remove('fas');
                                                star.classList.add('far');
                                            }
                                        });
                                    }

                                    document.getElementById('service-management-rate-public-form')?.addEventListener('submit', function(event) {
                                        const rating = parseInt(document.getElementById('ratingValue')?.value || '0', 10);
                                        const comment = (document.getElementById('publicRatingComment')?.value || '').trim();
                                        const meaningful = (comment.match(/[\p{L}\p{N}]/gu) || []).length;

                                        if (rating > 0 && rating < 5 && (comment.length < 15 || meaningful < 10)) {
                                            event.preventDefault();
                                            alert('La observación es obligatoria y debe tener mínimo 15 caracteres con contenido descriptivo.');
                                        }
                                    });
                                </script>
                            @endif
                        </div>
                    </div>
                    @endif

                    <div class="mt-4 text-center">
                        <a href="{{ route('home') }}" class="btn btn-outline-secondary mt-3"><i class="fas fa-home me-2"></i>Volver al Inicio</a>
                    </div>
                </div>
            </div>
            @endif

        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.track-result-card {
    background-color: var(--bs-body-bg);
    color: var(--bs-body-color);
}

.track-date-badge {
    background-color: var(--bs-tertiary-bg);
    color: var(--bs-body-color);
    border: 1px solid var(--bs-border-color);
}

.track-summary-panel {
    background-color: var(--bs-tertiary-bg);
    border: 1px solid var(--bs-border-color-translucent);
}

.track-metric-label {
    color: var(--bs-secondary-color);
    font-size: 0.8rem;
    letter-spacing: 1px;
}

.track-type-pill {
    background-color: var(--bs-secondary-bg);
}

.track-sla-box {
    background-color: var(--bs-body-bg);
    border-color: var(--bs-border-color) !important;
}

[data-bs-theme="dark"] .track-type-pill {
    background-color: var(--bs-secondary-bg);
}

[data-bs-theme="dark"] .track-date-badge {
    background-color: var(--bs-secondary-bg);
}

@media (max-width: 767.98px) {
    .track-result-card .card-body {
        padding: 1.25rem !important;
    }

    .track-date-badge {
        width: 100%;
        text-align: center;
    }

    .track-result-card .d-flex.justify-content-between.align-items-center {
        flex-direction: column;
        align-items: flex-start !important;
        gap: 0.75rem;
    }

    .track-mobile-stack .border-end {
        border-right: 0 !important;
        border-bottom: 1px solid var(--bs-border-color-translucent);
        padding-bottom: 1rem;
        margin-bottom: 0.5rem;
    }

    .track-mobile-stack .border-end:last-child {
        border-bottom: 0;
        margin-bottom: 0;
        padding-bottom: 0;
    }
}
</style>
@endpush
