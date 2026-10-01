@extends('layouts.contributor')

@section('title', 'Detalle del Ticket - Colaborador')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <h1 class="h2 mb-0">Ticket #{{ $ticket->ticket_number }}</h1>
        @if(!empty($isTopicPreview))
            <span class="badge bg-warning text-dark fs-6">
                <i class="fas fa-lock me-1"></i>Solo lectura
            </span>
        @endif
    </div>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('contributors.dashboard') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Volver
        </a>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if(!empty($isTopicPreview))
<div class="alert alert-warning d-flex align-items-start" role="alert">
    <i class="fas fa-info-circle me-2 mt-1"></i>
    <div>
        Está viendo un ticket del pool de su tópico. Para poder trabajar en él, debe asignárselo primero desde la cola de tickets de su tópico.
    </div>
</div>
@endif

@php
    $isReadOnlyTopicPreview = !empty($isTopicPreview);
@endphp

<div class="row">
    <!-- Ticket Information -->
        <div class="col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Información del Ticket</h5>
                @php
                    $statusColors = [1 => 'secondary', 2 => 'warning', 3 => 'success', 4 => 'danger'];
                    $statusNames = [1 => 'Pendiente', 2 => 'En Progreso', 3 => 'Completado', 4 => 'Cancelado'];
                    $priorityColors = [1 => 'success', 2 => 'info', 3 => 'warning', 4 => 'danger'];
                    $priorityNames = [1 => 'Baja', 2 => 'Media', 3 => 'Alta (Afecta operación)', 4 => 'Urgente (Suspende operación)'];
                @endphp
                <div>
                    <span class="badge bg-{{ $statusColors[$ticket->status] ?? 'secondary' }} fs-6 me-2">
                        {{ $statusNames[$ticket->status] ?? 'Desconocido' }}
                    </span>
                    @if($ticket->priority)
                    <span class="badge bg-{{ $priorityColors[$ticket->priority] ?? 'secondary' }} fs-6">
                        {{ $priorityNames[$ticket->priority] ?? 'Sin prioridad' }}
                    </span>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <h5 class="fw-bold">{{ $ticket->title }}</h5>
                
                @if($ticket->requestType)
                <div class="mb-3">
                    <span class="badge" style="background-color: {{ $ticket->requestType->type_color }}; font-size: 1rem;">
                        <i class="fas {{ $ticket->requestType->type_icon }} me-1"></i>
                        {{ $ticket->requestType->type_name }}
                    </span>
                </div>
                @endif

                @if(!$isTopicPreview && $ticket->status != 3 && $ticket->status != 4)
                    <form action="{{ route('contributors.tickets.associate', $ticket->ticket_id) }}" method="POST" class="border-top pt-3 mt-3">
                        @csrf
                        <label class="form-label fw-semibold">Asociar ticket relacionado</label>
                        <div class="input-group">
                            <select name="child_ticket_ids[]" class="form-select" multiple size="5" required>
                                @foreach($availableTickets as $availableTicket)
                                    <option value="{{ $availableTicket->ticket_id }}">#{{ $availableTicket->ticket_number }} - {{ $availableTicket->title }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="btn btn-outline-primary"><i class="fas fa-link me-1"></i>Asociar</button>
                        </div>
                    </form>
                @endif
                @php $hasEmbeddedImages = stripos($ticket->requester_info ?? '', '<img') !== false; @endphp
                @if($hasEmbeddedImages)
                <div class="mb-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="toggle-requester-images-contributor" data-expanded="0">
                        <i class="fas fa-image me-1"></i>Mostrar imágenes pegadas
                    </button>
                </div>
                @endif
                <div class="text-muted" id="requester-info-content-contributor">{!! $ticket->requester_info !!}</div>

                    @php $visibleEvidences = $ticket->evidences->where('storage_disk', '!=', 'richtext_filesystem'); @endphp

                    @if($ticket->requester_url || $visibleEvidences->count())
                    <div class="mt-3">
                        <h6 class="fw-bold mb-2"><i class="fas fa-paperclip me-1"></i>Evidencias del Solicitante</h6>
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
                    <div class="col-md-6">
                        <small class="text-muted d-block">Solicitante:</small>
                        <strong>{{ $ticket->requester->user_name }}</strong>
                        <br>
                        <small>{{ $ticket->requester->user_email }}</small>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">Creado:</small>
                        <strong>{{ $ticket->created_at->format('d/m/Y H:i') }}</strong>
                    </div>
                </div>

                <div class="mt-4">
                    <div class="card border-info">
                        <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0">Fase Actual (ADDIE)</h6>
                                <small>{{ $ticket->current_phase ?? 'Sin fase definida' }}</small>
                            </div>
                        </div>
                        <div class="card-body py-3">
                            <div class="position-relative mb-3">
                                <div class="progress" style="height: 2px;">
                                    <div class="progress-bar" role="progressbar" style="width: {{ $ticket->current_phase == 'Evaluation' ? '100%' : ($ticket->current_phase == 'Implementation' ? '75%' : ($ticket->current_phase == 'Development' ? '50%' : ($ticket->current_phase == 'Design' ? '25%' : '0%'))) }};"></div>
                                </div>
                            </div>

                            <div class="d-flex flex-wrap gap-2">
                                @php
                                    $phases = ['Analysis' => 'Análisis', 'Design' => 'Diseño', 'Development' => 'Desarrollo', 'Implementation' => 'Implementación', 'Evaluation' => 'Evaluación'];
                                @endphp
                                @foreach($phases as $key => $label)
                                    <form action="{{ route('contributors.tickets.update-phase', $ticket->ticket_id) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="phase" value="{{ $key }}">
                                        <button type="submit" class="btn btn-sm {{ $ticket->current_phase == $key ? 'btn-primary' : 'btn-outline-primary' }}" {{ $isReadOnlyTopicPreview ? 'disabled' : '' }}>
                                            {{ $label }}
                                        </button>
                                    </form>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Team Section -->
        <div class="card shadow mb-4">
            <div class="card-header bg-info text-white">
                <h5 class="card-title mb-0"><i class="fas fa-users me-2"></i>Equipo de Trabajo</h5>
            </div>
            <div class="card-body">
                @if($ticket->assignments->where('status', 'active')->count() > 0)
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Mediador</th>
                                <th>Puesto</th>
                                <th>Asignado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($ticket->assignments->where('status', 'active') as $assignment)
                            <tr>
                                <td>
                                    <strong>{{ $assignment->mediator->user_name }}</strong>
                                </td>
                                <td>
                                    <span class="badge" style="background-color: {{ $assignment->jobPosition->position_color ?? '#6c757d' }}">
                                        {{ $assignment->jobPosition->position_name ?? 'General' }}
                                    </span>
                                </td>
                                <td>{{ $assignment->assigned_at->format('d/m/Y') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <p class="text-muted mb-0">No hay equipo asignado (solo mediador principal).</p>
                @endif

                @if($canReviewJoinRequests && $pendingJoinRequests->isNotEmpty())
                    <div class="border-top mt-3 pt-3">
                        <h6 class="fw-bold"><i class="fas fa-user-clock me-1"></i>Solicitudes para unirse</h6>
                        @foreach($pendingJoinRequests as $joinRequest)
                            <div class="border rounded p-2 mb-2">
                                <div class="small mb-2">
                                    <strong>{{ $joinRequest->requester->user_name }}</strong>
                                    @if($joinRequest->request_note)
                                        <span class="text-muted">: {{ $joinRequest->request_note }}</span>
                                    @endif
                                </div>
                                <div class="d-flex gap-2">
                                    <form method="POST" action="{{ route('contributors.join-requests.approve', $joinRequest->join_request_id) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success"><i class="fas fa-check me-1"></i>Aprobar</button>
                                    </form>
                                    <form method="POST" action="{{ route('contributors.join-requests.reject', $joinRequest->join_request_id) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-times me-1"></i>Rechazar</button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- Progress History -->
        <div class="card shadow mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="fas fa-history me-2"></i>Historial de Avances</h5>
            </div>
            <div class="card-body">
                @forelse($ticket->progress()->latest()->get() as $progress)
                @php
                    $isTaskCompletedEvent = $progress->status_update === 'task_completed';
                    $isSprintCompletedEvent = $progress->status_update === 'sprint_completed';
                    $isTransferEvent = $progress->status_update === 'ticket_transferred';
                    $eventBadgeClass = $isTaskCompletedEvent
                        ? 'bg-success'
                        : ($isSprintCompletedEvent ? 'bg-info text-dark' : ($isTransferEvent ? 'bg-warning text-dark' : 'bg-secondary'));
                    $eventLabel = $isTaskCompletedEvent
                        ? 'Tarea completada'
                        : ($isSprintCompletedEvent ? 'Sprint completado' : ($isTransferEvent ? 'Transferencia' : 'Nota'));
                @endphp
                <div class="border-start border-3 {{ $isTaskCompletedEvent ? 'border-success' : ($isSprintCompletedEvent ? 'border-info' : ($isTransferEvent ? 'border-warning' : 'border-primary')) }} ps-3 mb-3">
                    <div class="d-flex justify-content-between">
                        <div>
                            <strong>{{ $progress->user->user_name }}</strong>
                            <span class="badge {{ $eventBadgeClass }} ms-2">{{ $eventLabel }}</span>
                        </div>
                        <small class="text-muted">{{ $progress->created_at->format('d/m/Y H:i') }}</small>
                    </div>
                    <p class="mb-1">{{ $progress->progress_description }}</p>
                    <div class="progress" style="height: 20px;">
                        <div class="progress-bar {{ $isTaskCompletedEvent ? 'bg-success' : ($isSprintCompletedEvent ? 'bg-info' : 'bg-primary') }}" role="progressbar"
                             style="width: {{ $progress->progress_percentage }}%">
                            {{ $progress->progress_percentage }}%
                        </div>
                    </div>
                </div>
                @empty
                <p class="text-muted text-center">No hay avances registrados aún</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Progress & Close -->
    <div class="col-lg-6">
        @php
            $sprintTasksAll = $ticket->sprints->flatMap(fn($s) => $s->tasks);
            $totalTasks = $sprintTasksAll->count();
            $doneTasks = $sprintTasksAll->where('status', 'done')->count();
            $autoProgress = $totalTasks > 0 ? (int) round(($doneTasks / $totalTasks) * 100) : 0;
            $totalSprints = $ticket->sprints->count();
            $completedSprints = $ticket->sprints->where('status', 'completed')->count();
            $isFinalAddiePhase = $ticket->current_phase === 'Evaluation';
        @endphp

        <div class="card shadow mb-4 border-warning">
            <div class="card-header bg-warning text-dark">
                <h5 class="card-title mb-0"><i class="fas fa-flag me-2"></i>Establecer Prioridad</h5>
            </div>
            <div class="card-body">
                @if($isReadOnlyTopicPreview)
                <div class="alert alert-light border mb-3">
                    <i class="fas fa-lock me-1"></i>Modo vista previa: para cambiar prioridad, primero debes tomar el ticket.
                </div>
                @endif
                <form action="{{ route('contributors.tickets.priority', $ticket->ticket_id) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="priority" class="form-label">Nivel de Prioridad</label>
                        <select class="form-select" id="priority" name="priority" required {{ $isReadOnlyTopicPreview ? 'disabled' : '' }}>
                            <option value="1" {{ $ticket->priority == 1 ? 'selected' : '' }}>Baja</option>
                            <option value="2" {{ $ticket->priority == 2 ? 'selected' : '' }}>Media</option>
                            <option value="3" {{ $ticket->priority == 3 ? 'selected' : '' }}>Alta (Afecta operación)</option>
                            <option value="4" {{ $ticket->priority == 4 ? 'selected' : '' }}>Urgente (Suspende operación)</option>
                        </select>
                        @if($ticket->priority_sla_hours)
                        <small class="text-muted d-block mt-2">
                            Tiempo objetivo: <strong>{{ $ticket->priority_sla_hours }} horas</strong>.
                            Límite: <strong>{{ $ticket->response_deadline->format('d/m/Y H:i') }}</strong>.
                            @if($ticket->is_response_overdue)
                                <span class="text-danger">(Vencido)</span>
                            @else
                                <span class="text-success">(Dentro del tiempo)</span>
                            @endif
                        </small>
                        @endif
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-warning text-dark" {{ $isReadOnlyTopicPreview ? 'disabled' : '' }}>
                            <i class="fas fa-flag me-2"></i>Actualizar Prioridad
                        </button>
                    </div>
                </form>
            </div>
        </div>

        @if((int) $ticket->mediator_id === (int) auth()->id())
        <div class="card shadow mb-4 border-danger">
            <div class="card-header bg-danger text-white">
                <h5 class="card-title mb-0"><i class="fas fa-random me-2"></i>Transferir Ticket a Otro Gestor</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('contributors.tickets.transfer', $ticket->ticket_id) }}" method="POST" onsubmit="return confirm('¿Confirmas la transferencia del ticket al nuevo tópico y gestor responsable?')">
                    @csrf
                    <div class="mb-3">
                        <label for="new_request_type_id" class="form-label">Nuevo tópico</label>
                        <select class="form-select @error('new_request_type_id') is-invalid @enderror" id="new_request_type_id" name="new_request_type_id" required>
                            <option value="">Selecciona un tópico de destino</option>
                            @foreach($transferRequestTypes as $requestType)
                                @php
                                    $targetMediator = null;
                                    if (($supportsCollaboratorAssignments ?? false) && $requestType->relationLoaded('collaborators')) {
                                        $targetMediator = $requestType->collaborators->firstWhere('user_id', $requestType->resolvePreferredMediatorId((int) $ticket->mediator_id));
                                    }
                                    if (!$targetMediator) {
                                        $targetMediator = $requestType->gestor;
                                    }
                                @endphp
                                <option value="{{ $requestType->type_id }}" {{ old('new_request_type_id') == $requestType->type_id ? 'selected' : '' }}>
                                    {{ $requestType->type_name }} - Colaborador: {{ optional($targetMediator)->user_name ?? 'Sin asignar' }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted d-block mt-2">El ticket cambiará de tópico y el nuevo gestor será el responsable del seguimiento y cierre.</small>
                        @error('new_request_type_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="target_mediator_id" class="form-label">Nuevo colaborador responsable (opcional)</label>
                        <select class="form-select @error('target_mediator_id') is-invalid @enderror" id="target_mediator_id" name="target_mediator_id">
                            <option value="">Usar el colaborador preferido del tópico</option>
                            @foreach($transferCollaborators as $transferCollaborator)
                                <option value="{{ $transferCollaborator->user_id }}" {{ old('target_mediator_id') == $transferCollaborator->user_id ? 'selected' : '' }}>
                                    {{ $transferCollaborator->user_name }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted d-block mt-2">Solo se aceptan colaboradores del tópico destino y del mismo área.</small>
                        @error('target_mediator_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="transfer_note" class="form-label">Nota de transferencia (opcional)</label>
                        <textarea class="form-control @error('transfer_note') is-invalid @enderror" id="transfer_note" name="transfer_note" rows="3" placeholder="Describe por qué se transfiere el ticket...">{{ old('transfer_note') }}</textarea>
                        @error('transfer_note')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-share-square me-2"></i>Transferir Ticket
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @endif

        {{-- Auto Progress Card --}}
        <div class="card shadow mb-4 border-primary">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0"><i class="fas fa-chart-line me-2"></i>Progreso del Ticket</h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <small class="text-muted">Avance general</small>
                    <strong class="fs-5 {{ $autoProgress == 100 ? 'text-success' : 'text-primary' }}">{{ $autoProgress }}%</strong>
                </div>
                <div class="progress mb-3" style="height: 18px;">
                    <div class="progress-bar {{ $autoProgress == 100 ? 'bg-success' : 'bg-primary' }} progress-bar-striped progress-bar-animated"
                         role="progressbar"
                         style="width: {{ $autoProgress }}%"
                         aria-valuenow="{{ $autoProgress }}" aria-valuemin="0" aria-valuemax="100">
                        {{ $autoProgress }}%
                    </div>
                </div>
                <div class="row text-center small text-muted">
                    <div class="col">
                        <div class="fw-bold text-dark">{{ $doneTasks }} / {{ $totalTasks }}</div>
                        Tareas completadas
                    </div>
                    <div class="col">
                        <div class="fw-bold text-dark">{{ $completedSprints }} / {{ $totalSprints }}</div>
                        Sprints completados
                    </div>
                </div>

                @if($autoProgress == 100 && $ticket->status != 3 && $ticket->status != 4)
                    <hr>
                    <form action="{{ route('contributors.tickets.close', $ticket->ticket_id) }}" method="POST" enctype="multipart/form-data" onsubmit="return confirm('¿Confirmas el cierre del servicio?')">
                        @csrf
                        @method('PATCH')
                        @if(!$isFinalAddiePhase)
                        <div class="alert alert-warning small">
                            <i class="fas fa-project-diagram me-1"></i>
                            Para cerrar el servicio, el ticket debe estar en la fase final de ADDIE (Evaluación). Fase actual: <strong>{{ $ticket->current_phase ?? 'Sin fase' }}</strong>.
                        </div>
                        @endif
                        <div class="mb-3">
                            <label for="solution_detail" class="form-label fw-semibold">Detalle de la solución</label>
                            <textarea class="form-control @error('solution_detail') is-invalid @enderror" id="solution_detail" name="solution_detail" rows="3" required placeholder="Describe cómo se resolvió el servicio..." {{ $isReadOnlyTopicPreview ? 'disabled' : '' }}>{{ old('solution_detail') }}</textarea>
                            @error('solution_detail')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="final_evidence_files" class="form-label fw-semibold">Evidencias finales (opcional)</label>
                            <input type="file" class="form-control" id="final_evidence_files" name="final_evidence_files[]" multiple accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.zip,.rar,.webp">
                            <small class="text-muted">Máximo 5 archivos de 2 MB cada uno.</small>
                        </div>
                        <div class="mb-3">
                            <label for="resource_link" class="form-label fw-semibold">URL de recurso (opcional)</label>
                            <input type="url" class="form-control @error('resource_link') is-invalid @enderror" id="resource_link" name="resource_link" value="{{ old('resource_link') }}" placeholder="https://..." {{ $isReadOnlyTopicPreview ? 'disabled' : '' }}>
                            <small class="text-muted">Si aplica, agrega el enlace de evidencia o recurso entregado.</small>
                            @error('resource_link')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-success btn-lg" {{ (!$isFinalAddiePhase || $isReadOnlyTopicPreview) ? 'disabled' : '' }}>
                                <i class="fas fa-check-circle me-2"></i>Cerrar Servicio
                            </button>
                        </div>
                    </form>
                @elseif($ticket->status == 3)
                    <hr>
                    <div class="alert alert-success mb-0 py-2 text-center">
                        <i class="fas fa-check-circle me-1"></i> Servicio cerrado exitosamente
                    </div>
                    @if($ticket->resource_link)
                    <div class="mt-2 text-center">
                        <a href="{{ $ticket->resource_link }}" target="_blank" rel="noopener" class="btn btn-outline-success btn-sm">
                            <i class="fas fa-link me-1"></i>Ver recurso entregado
                        </a>
                    </div>
                    @endif
                @else
                    <hr>
                    <small class="text-muted d-block text-center">
                        <i class="fas fa-info-circle me-1"></i>Completa todas las tareas de los sprints para cerrar el servicio.
                    </small>
                @endif
            </div>
        </div>

        {{-- Manual progress notes (keep for descriptions) --}}
        @if($ticket->status != 3 && $ticket->status != 4)
        <div class="card shadow mb-4 border-secondary">
            <div class="card-header bg-secondary text-white">
                <h5 class="card-title mb-0"><i class="fas fa-sticky-note me-2"></i>Agregar Nota de Avance</h5>
            </div>
            <div class="card-body">
                @if($isReadOnlyTopicPreview)
                <div class="alert alert-light border mb-0">
                    <i class="fas fa-lock me-1"></i>Para agregar avances, primero debes tomar el ticket.
                </div>
                @else
                <form action="{{ route('contributors.tickets.progress', $ticket->ticket_id) }}" method="POST">
                    @csrf
                    <input type="hidden" name="progress_percentage" value="0">
                    <div class="mb-3">
                        <label for="progress_description" class="form-label">Nota / Descripción</label>
                        <textarea class="form-control @error('progress_description') is-invalid @enderror"
                                  id="progress_description" name="progress_description" rows="3"
                                  required placeholder="Describe lo que avanzaste...">{{ old('progress_description') }}</textarea>
                        @error('progress_description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-secondary">
                            <i class="fas fa-save me-2"></i>Guardar Nota
                        </button>
                    </div>
                </form>
                @endif
            </div>
        </div>
        @else
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            Este ticket ya está {{ $ticket->status == 3 ? 'completado' : 'cancelado' }}.
        </div>
        @endif

        @php
            $communicationLogs = $ticket->progress
                ->whereIn('status_update', ['collaborator_message', 'requester_message'])
                ->sortBy('created_at')
                ->values();
            $canCommunicate = !in_array((int) $ticket->status, [3, 4], true) && !$isReadOnlyTopicPreview;
        @endphp
        <div class="card shadow mb-4 border-info">
            <div class="card-header bg-info text-white">
                <h5 class="card-title mb-0"><i class="fas fa-comments me-2"></i>Comunicación con Solicitante</h5>
            </div>
            <div class="card-body">
                @if($communicationLogs->isEmpty())
                    <p class="text-muted mb-3">No hay mensajes todavía.</p>
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
                                                        <a href="{{ route('evidences.view', $attachmentId) }}" target="_blank" rel="noopener">
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
                    <form action="{{ route('contributors.tickets.message', $ticket->ticket_id) }}" method="POST">
                        @csrf
                        <div class="mb-2">
                            <label for="message" class="form-label">Enviar mensaje al solicitante</label>
                            <textarea name="message" id="message" rows="3" class="form-control @error('message') is-invalid @enderror" placeholder="Escribe tu mensaje..." required>{{ old('message') }}</textarea>
                            @error('message')
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
                        <i class="fas fa-lock me-1"></i>{{ $isReadOnlyTopicPreview ? 'Para comunicarte con el solicitante, primero debes tomar el ticket.' : 'La comunicación está cerrada porque el ticket fue finalizado.' }}
                    </div>
                @endif
            </div>
        </div>
    </div>{{-- end col-lg-6 --}}
</div>{{-- end outer row --}}

<h3 class="h4 mt-5 mb-3"><i class="fas fa-tasks me-2"></i>Sprints y Tareas del Proyecto</h3>
<div class="row mb-5 g-3">
    <!-- Sidebar: Sprints & Backlog -->
    <div class="col-lg-3 col-md-4">
        <div class="card shadow mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Sprints</h6>
                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#createSprintModal" {{ $isReadOnlyTopicPreview ? 'disabled' : '' }}>
                    <i class="fas fa-plus"></i>
                </button>
            </div>
            <div class="card-body p-0 mt-2">
                <div class="list-group list-group-flush rounded-bottom">
                    @forelse($ticket->sprints as $sprint)
                        <a href="{{ route('contributors.tickets.show', ['id' => $ticket->ticket_id, 'sprint_id' => $sprint->sprint_id]) }}" class="list-group-item list-group-item-action {{ (isset($activeSprint) && $activeSprint->sprint_id == $sprint->sprint_id) ? 'active' : '' }}">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">{{ $sprint->name }}</h6>
                                <small>{{ $sprint->status }}</small>
                            </div>
                            <small>{{ $sprint->start_date->format('d/m') }} - {{ $sprint->end_date->format('d/m') }}</small>
                        </a>
                    @empty
                        <div class="p-3 text-center text-muted">No hay sprints creados</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="card shadow">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-secondary">Backlog</h6>
                <button class="btn btn-sm btn-secondary" data-bs-toggle="modal" data-bs-target="#createTaskModal" {{ $isReadOnlyTopicPreview ? 'disabled' : '' }}>
                    <i class="fas fa-plus"></i>
                </button>
            </div>
            <div class="card-body mt-2">
                @forelse($backlogTasks as $task)
                    <div class="card mb-2 border-left-secondary task-card-readonly">
                        <div class="card-body p-2">
                            <div class="d-flex justify-content-between align-items-start gap-2">
                                <div class="flex-grow-1">
                                    <small class="fw-bold d-block">{{ $task->title }}</small>
                                    <small class="text-muted d-block">{{ Str::limit($task->description ?? '', 50) }}</small>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary assign-to-sprint-btn" data-bs-toggle="modal" data-bs-target="#assignSprintModal" data-task-id="{{ $task->task_id }}" data-task-title="{{ $task->title }}" style="white-space: nowrap;" {{ $isReadOnlyTopicPreview ? 'disabled' : '' }}>
                                    <i class="fas fa-arrow-right me-1"></i>Asignar
                                </button>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <span class="badge bg-secondary">{{ $task->priority }}</span>
                                <small class="text-muted">{{ $task->assignee->user_name ?? 'Sin asignar' }}</small>
                            </div>
                        </div>
                    </div>
                @empty
                    <small class="text-muted">No hay tareas en backlog</small>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Main: Kanban Board -->
    <div class="col-lg-9 col-md-8">
        @if($activeSprint)
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <div class="d-flex align-items-center flex-wrap gap-2">
                    <h4 class="mb-0">{{ $activeSprint->name }}</h4>
                    <span class="badge bg-{{ $activeSprint->status == 'active' ? 'success' : ($activeSprint->status == 'completed' ? 'secondary' : 'warning') }}">
                        {{ ucfirst($activeSprint->status) }}
                    </span>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    @if($activeSprint->status == 'planned')
                        <form action="{{ route('contributors.tickets.update-sprint-status', $activeSprint->sprint_id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="active">
                            <button type="submit" class="btn btn-success btn-sm" {{ $isReadOnlyTopicPreview ? 'disabled' : '' }}>
                                <i class="fas fa-play me-1"></i>Iniciar Sprint
                            </button>
                        </form>
                    @elseif($activeSprint->status == 'active')
                        @php
                            $totalTasks = $activeSprint->tasks->count();
                            $completedTasks = $activeSprint->tasks->where('status', 'done')->count();
                            $canCompleteSprint = $totalTasks > 0 && $completedTasks === $totalTasks;
                        @endphp
                        <div class="d-flex flex-column flex-sm-row align-items-start gap-2">
                            <form action="{{ route('contributors.tickets.update-sprint-status', $activeSprint->sprint_id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="completed">
                                <button id="completeSprintButton" type="submit" class="btn btn-secondary btn-sm" {{ ($canCompleteSprint && !$isReadOnlyTopicPreview) ? '' : 'disabled' }} title="{{ $canCompleteSprint ? 'Completar sprint' : 'Todas las tareas deben estar en Hecho para completar el sprint' }}">
                                    <i class="fas fa-check me-1"></i>Completar Sprint
                                </button>
                            </form>
                            <small id="completeSprintHint" class="text-muted small mb-0">{{ $canCompleteSprint ? 'Todas las tareas están en Hecho, puedes completar el sprint.' : 'Para completar el sprint, mueve todas las tareas a la columna Hecho.' }}</small>
                        </div>
                    @endif
                </div>
            </div>
            
            <div class="kanban-wrapper">
                <div class="kanban-board">
                    @php
                        $columns = [
                            'todo' => ['title' => 'Por Hacer', 'bg' => 'bg-light'],
                            'in_progress' => ['title' => 'En Progreso', 'bg' => 'bg-info bg-opacity-10'],
                            'review' => ['title' => 'Revisión', 'bg' => 'bg-warning bg-opacity-10'],
                            'done' => ['title' => 'Hecho', 'bg' => 'bg-success bg-opacity-10']
                        ];
                    @endphp

                    @foreach($columns as $status => $col)
                        @php $tasksForColumn = $activeSprint->tasks->where('status', $status); @endphp
                        <div class="kanban-column-wrapper">
                            <div class="card h-100 {{ $col['bg'] }}">
                                <div class="card-header d-flex justify-content-between align-items-center py-2 px-3">
                                    <div>
                                        <h6 class="m-0 fw-bold text-uppercase small">{{ $col['title'] }}</h6>
                                        <small class="text-muted">{{ $tasksForColumn->count() }} tareas</small>
                                    </div>
                                    <span class="badge rounded-pill bg-white text-dark border">{{ $tasksForColumn->count() }}</span>
                                </div>
                                <div class="card-body p-2 kanban-column" data-status="{{ $status }}">
                                    @forelse($tasksForColumn as $task)
                                        @if($activeSprint->status == 'active' && !$isReadOnlyTopicPreview)
                                            <div class="card mb-3 task-card shadow-sm" draggable="true" data-task-id="{{ $task->task_id }}">
                                        @else
                                            <div class="card mb-3 task-card shadow-sm" data-task-id="{{ $task->task_id }}" style="opacity: 0.6;">
                                        @endif
                                            <div class="card-body p-3">
                                                <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                                    <h6 class="card-title small fw-semibold mb-0">{{ $task->title }}</h6>
                                                    @if($task->assignee)
                                                        <img src="https://ui-avatars.com/api/?name={{ urlencode($task->assignee->user_name) }}&size=24" class="rounded-circle" title="{{ $task->assignee->user_name }}">
                                                    @endif
                                                </div>
                                                <p class="card-text small text-muted mb-2">{{ Str::limit($task->description, 55) }}</p>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span class="badge bg-{{ $task->priority == 'high' ? 'danger' : ($task->priority == 'medium' ? 'warning' : 'info') }} text-uppercase small">{{ $task->priority }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="kanban-empty-state text-center text-muted py-4">No hay tareas</div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <div class="alert alert-info shadow-sm">
                <i class="fas fa-info-circle me-2"></i> Selecciona un sprint de la lista para ver sus tareas o espera a que se asignen.
            </div>
        @endif
    </div>
</div>

<!-- Assign Task to Sprint Modal -->
<div class="modal fade" id="assignSprintModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="assignSprintForm" method="POST" action="">
            @csrf
            @method('PATCH')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Asignar tarea a sprint</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Tarea</label>
                        <input type="text" id="assignSprintTaskTitle" class="form-control" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Sprint</label>
                        <select name="sprint_id" id="assignSprintSelect" class="form-select" required>
                            <option value="">Selecciona un sprint</option>
                            @foreach($ticket->sprints->where('status', '!=', 'completed') as $sprint)
                                <option value="{{ $sprint->sprint_id }}">{{ $sprint->name }} ({{ ucfirst($sprint->status) }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar asignación</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Create Sprint Modal -->
<div class="modal fade" id="createSprintModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('contributors.tickets.store-sprint', $ticket->ticket_id) }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Crear Nuevo Sprint</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nombre del Sprint</label>
                        <input type="text" name="name" class="form-control" required placeholder="Ej: Sprint 1 - Diseño">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Inicio</label>
                            <input type="date" name="start_date" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Fin</label>
                            <input type="date" name="end_date" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Objetivo</label>
                        <textarea name="goal" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Crear Sprint</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Create Task Modal -->
<div class="modal fade" id="createTaskModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('contributors.tickets.store-task', $ticket->ticket_id) }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nueva Tarea</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Título</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descripción</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Prioridad</label>
                            <select name="priority" class="form-select">
                                <option value="low">Baja</option>
                                <option value="medium" selected>Media</option>
                                <option value="high">Alta</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Sprint</label>
                            <select name="sprint_id" class="form-select">
                                <option value="">Backlog (Sin Sprint)</option>
                                @foreach($ticket->sprints as $sprint)
                                    <option value="{{ $sprint->sprint_id }}" {{ $sprint->status === 'completed' ? 'disabled' : '' }}>
                                        {{ $sprint->name }}{{ $sprint->status === 'completed' ? ' – completado' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Tarea</button>
                </div>
            </div>
        </form>
    </div>
</div>

<style>
    .kanban-wrapper {
        width: 100%;
        overflow-x: auto;
        overflow-y: visible;
        display: block;
        border-radius: 0.75rem;
        padding-bottom: 0.5rem;
        -webkit-overflow-scrolling: touch;
    }

    .kanban-board {
        display: flex;
        gap: 1rem;
        width: 100%;
        padding: 0 1rem 1rem 0;
        box-sizing: border-box;
        min-width: 100%;
    }

    .kanban-column-wrapper {
        flex: 0 0 250px;
        box-sizing: border-box;
        display: flex;
        flex-direction: column;
        min-height: 0;
    }

    .kanban-column-wrapper .card {
        display: flex;
        flex-direction: column;
        min-height: 0;
        border-radius: 1.1rem;
        background: #f8fafc;
        border: 1px solid rgba(15, 23, 42, 0.08);
    }

    .kanban-column-wrapper .card-header {
        padding: 0.9rem 1rem;
        border-bottom: 1px solid rgba(15, 23, 42, 0.08);
        background-color: #ffffff;
    }

    .kanban-column-wrapper .card-header h6 {
        font-size: 0.85rem;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        margin-bottom: 0.15rem;
    }

    .kanban-column-wrapper .card-header small {
        font-size: 0.75rem;
        color: #6b7280;
    }

    .kanban-column-wrapper .kanban-column {
        flex: 1;
        overflow-y: auto;
        min-height: 220px;
        max-height: 560px;
        padding: 0.85rem;
        background: transparent;
    }

    .task-card {
        max-height: 140px;
        min-height: 88px;
        overflow: hidden;
        border-radius: 1rem;
        border: 1px solid rgba(15, 23, 42, 0.08);
        background: #ffffff;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .task-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 14px 26px rgba(15, 23, 42, 0.08);
    }

    .task-card .card-body {
        padding: 0.85rem 0.95rem;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .task-card .card-title {
        font-size: 0.95rem;
        margin-bottom: 0.35rem;
    }

    .task-card .card-text {
        font-size: 0.82rem;
        color: #6b7280;
        line-height: 1.3;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-overflow: ellipsis;
        margin-bottom: 0.4rem;
    }

    .task-card .badge {
        padding: 0.35rem 0.55rem;
        font-size: 0.72rem;
        text-transform: uppercase;
    }

    .task-card {
        max-height: 145px;
        min-height: 95px;
        overflow: hidden;
        border-radius: 0.85rem;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .task-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 18px 30px rgba(18, 37, 41, 0.08);
    }

    .task-card .card-body {
        padding: 0.7rem 0.85rem;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .task-card .card-title {
        font-size: 0.95rem;
        margin-bottom: 0.35rem;
    }

    .task-card .card-text {
        font-size: 0.84rem;
        color: #5f6c72;
        line-height: 1.3;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-overflow: ellipsis;
        margin-bottom: 0.4rem;
    }

    .task-card .d-flex.justify-content-between {
        gap: 0.4rem;
        align-items: center;
    }

    .task-card .badge {
        padding: 0.35rem 0.55rem;
        font-size: 0.72rem;
    }

    .kanban-wrapper::-webkit-scrollbar {
        height: 8px;
    }

    .kanban-wrapper::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }

    .kanban-wrapper::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 10px;
    }

    .kanban-wrapper::-webkit-scrollbar-thumb:hover {
        background: #555;
    }

    @media (max-width: 991.98px) {
        .h2 {
            font-size: 1.35rem;
        }

        .card-header {
            padding: 0.7rem 0.85rem;
        }

        .card-body {
            padding: 0.9rem;
        }

        .card-header .badge.fs-6 {
            font-size: 0.78rem !important;
        }

        .btn-toolbar {
            width: 100%;
        }

        .btn-toolbar .btn {
            width: 100%;
        }

        .kanban-board {
            gap: 0.75rem;
            padding: 0 0.5rem 0.85rem 0;
        }

        .kanban-column-wrapper {
            flex: 0 0 220px;
        }

        .kanban-column-wrapper .kanban-column {
            max-height: 460px;
        }

        .task-card .card-title {
            font-size: 0.88rem;
        }

        .task-card .card-text {
            font-size: 0.78rem;
        }
    }

    @media (max-width: 767.98px) {
        .card-header.d-flex.justify-content-between.align-items-center {
            flex-direction: column;
            align-items: flex-start !important;
            gap: 0.5rem;
        }

        .card-header.d-flex.justify-content-between.align-items-center > div {
            width: 100%;
        }

        .card-header.d-flex.justify-content-between.align-items-center > div .badge {
            margin-top: 0.25rem;
        }

        .card-body .btn.btn-sm {
            width: 100%;
            margin-right: 0 !important;
        }

        .card-body .d-flex.flex-wrap.gap-2 > form {
            width: calc(50% - 0.25rem);
        }

        .card-body .d-flex.flex-wrap.gap-2 > form .btn {
            width: 100%;
            padding-left: 0.35rem;
            padding-right: 0.35rem;
            font-size: 0.78rem;
        }

        .kanban-column-wrapper {
            flex: 0 0 200px;
        }

        .kanban-column-wrapper .card-header {
            padding: 0.6rem 0.75rem;
        }
    }

    @media (max-width: 575.98px) {
        .h4, h4 {
            font-size: 1.1rem;
        }

        .card-body .d-flex.flex-wrap.gap-2 > form {
            width: 100%;
        }

        .kanban-column-wrapper {
            flex: 0 0 185px;
        }
    }

    @media (max-width: 399.98px) {
        .h2 {
            font-size: 1.15rem;
        }

        .card-header h5,
        .card-header .card-title {
            font-size: 0.98rem;
        }

        .card-header .badge {
            font-size: 0.72rem !important;
        }

        .card-body {
            padding: 0.75rem;
        }

        .card-body .btn {
            font-size: 0.82rem;
            padding-top: 0.35rem;
            padding-bottom: 0.35rem;
        }

        .card-body .btn.btn-sm {
            font-size: 0.76rem;
        }

        .kanban-column-wrapper {
            flex: 0 0 170px;
        }

        .kanban-column-wrapper .card-header h6 {
            font-size: 0.72rem;
        }

        .task-card .card-title {
            font-size: 0.8rem;
        }

        .task-card .card-text {
            font-size: 0.72rem;
            -webkit-line-clamp: 1;
        }
    }
</style>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const isReadOnlyTopicPreview = @json(!empty($isTopicPreview));
        const containers = document.querySelectorAll('.kanban-column');
        const completeSprintButton = document.getElementById('completeSprintButton');
        const assignSprintForm = document.getElementById('assignSprintForm');
        const assignSprintTaskTitle = document.getElementById('assignSprintTaskTitle');
        const assignSprintSelect = document.getElementById('assignSprintSelect');
        const assignButtons = document.querySelectorAll('.assign-to-sprint-btn');

        function attachDragListeners() {
            if (isReadOnlyTopicPreview) {
                return;
            }

            const draggables = document.querySelectorAll('.task-card[draggable="true"]');
            
            draggables.forEach(draggable => {
                draggable.removeEventListener('dragstart', handleDragStart);
                draggable.removeEventListener('dragend', handleDragEnd);
                draggable.addEventListener('dragstart', handleDragStart);
                draggable.addEventListener('dragend', handleDragEnd);
            });
        }

        function handleDragStart(e) {
            e.target.classList.add('dragging');
        }

        function handleDragEnd(e) {
            const draggable = e.target;
            draggable.classList.remove('dragging');
            const taskId = draggable.dataset.taskId;
            const newStatus = draggable.closest('.kanban-column').dataset.status;
            
            // Update status via AJAX
            fetch(`/contributors/tasks/${taskId}/update-status`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ status: newStatus })
            })
            .then(response => response.json())
            .then(data => {
                if(!data.success) {
                    alert(data.message || 'Error al actualizar');
                }
            })
            .catch(err => console.error(err));

            updateEmptyStates();
            updateCompleteSprintButton();
        }

        if (!isReadOnlyTopicPreview) {
            assignButtons.forEach(button => {
                button.addEventListener('click', () => {
                    const taskId = button.dataset.taskId;
                    const taskTitle = button.dataset.taskTitle;
                    assignSprintTaskTitle.value = taskTitle;
                    assignSprintSelect.value = '';
                    if (assignSprintForm) {
                        assignSprintForm.action = `/contributors/tasks/${taskId}/assign-sprint`;
                    }
                });
            });
        }

        if (!isReadOnlyTopicPreview) {
            containers.forEach(container => {
                container.addEventListener('dragover', e => {
                    e.preventDefault();
                    const afterElement = getDragAfterElement(container, e.clientY);
                    const draggable = document.querySelector('.dragging');
                    if (afterElement == null) {
                        container.appendChild(draggable);
                    } else {
                        container.insertBefore(draggable, afterElement);
                    }
                });
            });
        }

        function updateEmptyStates() {
            containers.forEach(container => {
                const emptyState = container.querySelector('.kanban-empty-state');
                const hasTasks = container.querySelectorAll('.task-card').length > 0;
                if (emptyState) {
                    emptyState.style.display = hasTasks ? 'none' : 'block';
                }
            });
        }

        function updateCompleteSprintButton() {
            if (!completeSprintButton) return;

            const totalTasks = document.querySelectorAll('.kanban-column .task-card').length;
            const doneTasks = document.querySelectorAll('.kanban-column[data-status="done"] .task-card').length;
            const canComplete = totalTasks > 0 && doneTasks === totalTasks;
            const hintText = document.getElementById('completeSprintHint');

            completeSprintButton.disabled = !canComplete;
            completeSprintButton.title = canComplete
                ? 'Completar sprint'
                : 'Todas las tareas deben estar en Hecho para completar el sprint';

            if (hintText) {
                hintText.textContent = canComplete
                    ? 'Todas las tareas están en Hecho, puedes completar el sprint.'
                    : 'Para completar el sprint, mueve todas las tareas a la columna Hecho.';
                hintText.classList.toggle('text-danger', !canComplete);
                hintText.classList.toggle('text-muted', canComplete);
            }
        }

        function getDragAfterElement(container, y) {
            const draggableElements = [...container.querySelectorAll('.task-card:not(.dragging)')];

            return draggableElements.reduce((closest, child) => {
                const box = child.getBoundingClientRect();
                const offset = y - box.top - box.height / 2;
                if (offset < 0 && offset > closest.offset) {
                    return { offset: offset, element: child };
                } else {
                    return closest;
                }
            }, { offset: Number.NEGATIVE_INFINITY }).element;
        }

        attachDragListeners();
        updateEmptyStates();
        updateCompleteSprintButton();

        const descriptionContainer = document.getElementById('requester-info-content-contributor');
        const toggleButton = document.getElementById('toggle-requester-images-contributor');

        if (descriptionContainer && toggleButton) {
            const images = Array.from(descriptionContainer.querySelectorAll('img'));
            if (!images.length) {
                toggleButton.style.display = 'none';
            } else {
                const setImagesVisibility = (showImages) => {
                    images.forEach((img) => {
                        img.style.display = showImages ? '' : 'none';
                    });

                    toggleButton.setAttribute('data-expanded', showImages ? '1' : '0');
                    toggleButton.innerHTML = showImages
                        ? '<i class="fas fa-eye-slash me-1"></i>Ocultar imágenes pegadas'
                        : '<i class="fas fa-image me-1"></i>Mostrar imágenes pegadas';
                };

                setImagesVisibility(false);
                toggleButton.addEventListener('click', () => {
                    const isExpanded = toggleButton.getAttribute('data-expanded') === '1';
                    setImagesVisibility(!isExpanded);
                });
            }
        }
    });
</script>
<style>
    .task-card { cursor: default; }
    .task-card[draggable="true"] { cursor: move; }
    .task-card.dragging { opacity: 0.5; }
</style>
@endpush
@endsection
