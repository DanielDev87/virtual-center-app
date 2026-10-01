@extends('layouts.area-admin')

@section('title', 'Ticket #' . $ticket->ticket_number)

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Ticket #{{ $ticket->ticket_number }}</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="{{ route('area-admin.tickets.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>Volver
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif
    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show">
        <strong>No se pudo completar la acción:</strong>
        <ul class="mb-0 mt-2">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row">
        <!-- Información del Ticket -->
        <div class="col-lg-8 order-1">
            <div class="card shadow mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Información del Ticket</h5>
                    @php
                        $statusColors   = [1 => 'secondary', 2 => 'warning', 3 => 'success', 4 => 'danger', 5 => 'info'];
                        $statusNames    = [1 => 'Pendiente', 2 => 'En Progreso', 3 => 'Completado', 4 => 'Cancelado', 5 => 'Realizado por Operario'];
                        $priorityColors = [1 => 'success', 2 => 'info', 3 => 'warning', 4 => 'danger'];
                        $priorityNames  = [1 => '🟢 Baja', 2 => '🟡 Media', 3 => '🟠 Alta (Afecta operación)', 4 => '🔴 Urgente (Suspende operación)'];
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

                    <!-- Progress Bar -->
                    <div class="mb-4">
                        @php $displayProgress = $autoProgress ?? $ticket->progress_percentage; @endphp
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-bold text-muted small">Progreso General</span>
                            <span class="fw-bold {{ $displayProgress == 100 ? 'text-success' : 'text-primary' }}">
                                {{ $displayProgress }}%
                            </span>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar {{ $displayProgress == 100 ? 'bg-success' : 'bg-primary' }}"
                                 role="progressbar"
                                 style="width: {{ $displayProgress }}%"
                                 aria-valuenow="{{ $displayProgress }}"
                                 aria-valuemin="0"
                                 aria-valuemax="100"></div>
                        </div>
                        @if($ticket->is_reopened)
                        <div class="mt-1 text-end">
                            <span class="badge bg-info text-white small">
                                <i class="fas fa-redo me-1"></i>Ticket Reabierto
                            </span>
                        </div>
                        @endif
                    </div>

                    <div class="text-muted">{!! $ticket->requester_info !!}</div>

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
                            <strong>{{ $ticket->requester->user_name }}</strong><br>
                            <small>{{ $ticket->requester->user_email }}</small>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block">Mediador Asignado:</small>
                            @if($ticket->mediator)
                                <strong>{{ $ticket->mediator->user_name }}</strong><br>
                                <small>{{ $ticket->mediator->user_email }}</small>
                            @else
                                <span class="text-muted">Sin asignar</span>
                            @endif
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <small class="text-muted d-block">Creado:</small>
                            <strong>{{ $ticket->created_at->format('d/m/Y H:i') }}</strong>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block">Última actualización:</small>
                            <strong>{{ $ticket->updated_at->format('d/m/Y H:i') }}</strong>
                        </div>
                    </div>

                    @if($ticket->rating)
                    <hr>
                    <div>
                        <small class="text-muted d-block">Calificación del Solicitante:</small>
                        <div class="text-warning">
                            @for($i = 1; $i <= 5; $i++)
                                <i class="{{ $i <= $ticket->rating ? 'fas' : 'far' }} fa-star"></i>
                            @endfor
                            <span class="text-dark ms-2">({{ $ticket->rating }}/5)</span>
                        </div>
                        @if($ticket->feedback)
                        <p class="text-muted small mt-1">{{ $ticket->feedback }}</p>
                        @endif
                    </div>
                    @endif
                </div>
            </div>

            <!-- Equipo de Trabajo -->
            <div class="card shadow mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0"><i class="fas fa-users me-2"></i>Equipo de Trabajo</h5>
                </div>
                <div class="card-body">
                    @if($ticket->assignments->where('status', 'active')->count() > 0)
                    <div class="table-responsive mb-3">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Mediador</th>
                                    <th>Puesto</th>
                                    <th>Asignado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($ticket->assignments->where('status', 'active') as $assignment)
                                <tr>
                                    <td>
                                        <strong>{{ $assignment->mediator->user_name }}</strong><br>
                                        <small class="text-muted">{{ $assignment->mediator->user_email }}</small>
                                    </td>
                                    <td>
                                        <span class="badge" style="background-color: {{ $assignment->jobPosition->position_color ?? '#6c757d' }}">
                                            {{ $assignment->jobPosition->position_name ?? 'General' }}
                                        </span>
                                    </td>
                                    <td><small>{{ $assignment->assigned_at->format('d/m/Y') }}</small></td>
                                    <td>
                                        <form action="{{ route('area-admin.tickets.remove-assignment', [$ticket->ticket_id, $assignment->assignment_id]) }}"
                                              method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger"
                                                    onclick="return confirm('¿Remover a este mediador del equipo?')">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p class="text-muted mb-3">No hay mediadores asignados al equipo de trabajo.</p>
                    @endif

                    @if($ticket->status != 3 && $ticket->status != 4)
                    <form action="{{ route('area-admin.tickets.associate', $ticket->ticket_id) }}" method="POST" class="border-top pt-3 mt-3">
                        @csrf
                        <label class="form-label fw-semibold">Asociar ticket relacionado</label>
                        <div class="input-group">
                            <select name="child_ticket_id" class="form-select" required>
                                <option value="">Seleccionar ticket relacionado...</option>
                                @foreach($availableTickets as $availableTicket)
                                    <option value="{{ $availableTicket->ticket_id }}">#{{ $availableTicket->ticket_number }} - {{ $availableTicket->title }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="btn btn-outline-primary"><i class="fas fa-link me-1"></i>Asociar</button>
                        </div>
                    </form>
                    @endif

                    @php
                        $pendingAssociationGroups = $ticket->associationRequests
                            ->where('status', 'pending')
                            ->groupBy(fn ($item) => $item->request_group ?: 'single-' . $item->association_request_id);
                    @endphp
                    @if($pendingAssociationGroups->isNotEmpty())
                    <div class="border-top pt-3 mt-3">
                        <h6 class="fw-semibold"><i class="fas fa-link me-1"></i>Solicitudes de asociación</h6>
                        @foreach($pendingAssociationGroups as $associationRequests)
                            @php $associationRequest = $associationRequests->first(); @endphp
                            <div class="border rounded p-2 mb-2">
                                <div class="small mb-2">
                                    {{ $associationRequest->requester->user_name ?? 'Contributor' }} solicita asociar {{ $associationRequests->count() }} ticket(s).
                                    @if($associationRequest->request_note)
                                        <span class="text-muted">{{ $associationRequest->request_note }}</span>
                                    @endif
                                </div>
                                <div class="d-flex gap-2">
                                    <form method="POST" action="{{ route('area-admin.association-requests.approve', $associationRequest->association_request_id) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success"><i class="fas fa-check me-1"></i>Aprobar</button>
                                    </form>
                                    <form method="POST" action="{{ route('area-admin.association-requests.reject', $associationRequest->association_request_id) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-times me-1"></i>Rechazar</button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @endif

                    <div class="border-top pt-3">
                        <h6 class="mb-3">Agregar Miembro al Equipo</h6>
                        <form action="{{ route('area-admin.tickets.assign-mediator', $ticket->ticket_id) }}" method="POST">
                            @csrf
                            <input type="hidden" name="mediator_id" id="mediator_id_hidden">
                            <div class="row g-2">
                                <div class="col-md-5">
                                    <select name="mediator_id" class="form-select form-select-sm" required>
                                        <option value="">Seleccionar Mediador</option>
                                        @foreach($mediators as $mediator)
                                        <option value="{{ $mediator->user_id }}">
                                            {{ $mediator->user_name }}
                                            @if($mediator->jobPositions->count() > 0)
                                                [{{ $mediator->jobPositions->pluck('position_name')->join(', ') }}]
                                            @else
                                                ({{ $mediator->role->role_name }})
                                            @endif
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-5">
                                    <select name="job_position_id" class="form-select form-select-sm">
                                        <option value="">Seleccionar Puesto</option>
                                        @foreach($jobPositions as $position)
                                        <option value="{{ $position->job_position_id }}">
                                            {{ $position->position_name }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary btn-sm w-100">
                                        <i class="fas fa-plus"></i> Asignar
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Historial de Avances -->
            <div class="card shadow mb-4">
                <div class="card-header">
                    <h6 class="m-0 fw-bold text-primary"><i class="fas fa-history me-2"></i>Historial de Avances</h6>
                </div>
                <div class="card-body">
                    @if($ticket->progress->count() > 0)
                        <div class="timeline">
                            @foreach($ticket->progress->sortByDesc('created_at') as $progress)
                            <div class="timeline-item ps-3 border-start border-primary mb-3">
                                <p class="text-muted small mb-1">
                                    {{ $progress->created_at->format('d/m/Y H:i') }} —
                                    <span class="fw-bold">{{ $progress->user->user_name ?? 'Usuario' }}</span>
                                    <span class="badge bg-primary ms-2">{{ $progress->progress_percentage }}%</span>
                                </p>
                                <p class="mb-0">{{ $progress->progress_description }}</p>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted text-center py-3">No hay avances registrados para este ticket.</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Panel de Acciones -->
        <div class="col-lg-4 order-2 mb-4">

            <!-- Asignar Mediador Principal -->
            @if($ticket->status == 1 || !$ticket->mediator_id)
            <div class="card shadow mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0"><i class="fas fa-user-plus me-2"></i>Asignar Mediador</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('area-admin.tickets.assign', $ticket->ticket_id) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="mediator_id_main" class="form-label">Seleccionar Mediador</label>
                            <select class="form-select" id="mediator_id_main" name="mediator_id" required>
                                <option value="">Seleccionar...</option>
                                @foreach($mediators as $mediator)
                                <option value="{{ $mediator->user_id }}"
                                        {{ $ticket->mediator_id == $mediator->user_id ? 'selected' : '' }}>
                                    {{ $mediator->user_name }} ({{ $mediator->role->role_name }})
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-check me-2"></i>Asignar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            @endif

            <!-- Prioridad -->
            <div class="card shadow mb-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="card-title mb-0"><i class="fas fa-flag me-2"></i>Prioridad</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('area-admin.tickets.priority', $ticket->ticket_id) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <select class="form-select" name="priority" required>
                                <option value="1" {{ $ticket->priority == 1 ? 'selected' : '' }}>🟢 Baja</option>
                                <option value="2" {{ $ticket->priority == 2 ? 'selected' : '' }}>🟡 Media</option>
                                <option value="3" {{ $ticket->priority == 3 ? 'selected' : '' }}>🟠 Alta (Afecta operación)</option>
                                <option value="4" {{ $ticket->priority == 4 ? 'selected' : '' }}>🔴 Urgente (Suspende operación)</option>
                            </select>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-flag me-2"></i>Actualizar Prioridad
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            @if($ticket->status == 5)
            <!-- Auditoría del resultado del Operario -->
            <div class="card shadow mb-4 border-info">
                <div class="card-header bg-info text-dark">
                    <h5 class="card-title mb-0"><i class="fas fa-clipboard-check me-2"></i>Auditar resultado del Operario</h5>
                </div>
                <div class="card-body">
                    <p class="small text-muted">Revisa la descripción y las evidencias antes de cerrar oficialmente el ticket.</p>
                    <form action="{{ route('area-admin.tickets.audit.approve', $ticket->ticket_id) }}" method="POST" class="mb-3" enctype="multipart/form-data">
                        @csrf
                        <label class="form-label">Detalle corregido de la solución <span class="text-danger">*</span></label>
                        <textarea class="form-control mb-2" name="solution_detail" rows="3" minlength="10" maxlength="2000" required placeholder="Confirma y corrige la solución realizada..."></textarea>
                        <input type="file" class="form-control mb-2" name="final_evidence_files[]" multiple accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.zip,.rar,.webp">
                        <small class="text-muted d-block mb-2">Evidencias finales opcionales: máximo 5 archivos de 2 MB.</small>
                        <button type="submit" class="btn btn-success w-100" onclick="return confirm('¿Aprobar y marcar este ticket como completado?')">
                            <i class="fas fa-check me-2"></i>Aprobar y completar
                        </button>
                    </form>
                    <form action="{{ route('area-admin.tickets.audit.reject', $ticket->ticket_id) }}" method="POST">
                        @csrf
                        <label class="form-label">Motivo de devolución al Operario <span class="text-danger">*</span></label>
                        <textarea class="form-control mb-2" name="audit_reason" rows="2" minlength="10" maxlength="1000" required placeholder="Indica qué debe corregirse..."></textarea>
                        <button type="submit" class="btn btn-outline-warning w-100" onclick="return confirm('¿Devolver este ticket a En Proceso?')">
                            <i class="fas fa-undo me-2"></i>Devolver para corrección
                        </button>
                    </form>
                </div>
            </div>
            @elseif($ticket->status != 3 && $ticket->status != 4)
            <!-- Cerrar / Cancelar Ticket -->
            <div class="card shadow mb-4">
                <div class="card-header bg-danger text-white">
                    <h5 class="card-title mb-0"><i class="fas fa-times-circle me-2"></i>Cerrar Ticket</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('area-admin.tickets.close', $ticket->ticket_id) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Acción</label>
                            <select class="form-select" name="status" id="closeStatusSelect" required>
                                <option value="3">✅ Marcar como Completado</option>
                                <option value="4">❌ Cancelar Ticket</option>
                            </select>
                        </div>
                        <div class="mb-3" id="solution_detail_group">
                            <label class="form-label">Detalle de la Solución <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="solution_detail" rows="3"
                                      placeholder="Describe la solución implementada..."></textarea>
                        </div>
                        <div class="mb-3" id="cancel_reason_group" style="display:none;">
                            <label class="form-label">Motivo de Cancelación <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="cancellation_reason" rows="3"
                                      placeholder="Describe el motivo de la cancelación..."></textarea>
                        </div>
                        <div class="mb-3" id="resource_link_group">
                            <label class="form-label">Enlace de Recurso (opcional)</label>
                            <input type="url" class="form-control" name="resource_link"
                                   placeholder="https://...">
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-danger"
                                    onclick="return confirm('¿Estás seguro de esta acción?')">
                                <i class="fas fa-check me-2"></i>Confirmar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            @endif

            <!-- Reabrir Ticket -->
            @if($ticket->status == 3 || $ticket->status == 4)
            <div class="card shadow mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0"><i class="fas fa-redo me-2"></i>Reabrir Ticket</h5>
                </div>
                <div class="card-body">
                    <p class="small text-muted">Reabre el ticket para continuar con el proceso.</p>
                    <form action="{{ route('area-admin.tickets.reopen', $ticket->ticket_id) }}" method="POST">
                        @csrf
                        <div class="d-grid">
                            <button type="submit" class="btn btn-info text-white"
                                    onclick="return confirm('¿Reabrir este ticket?')">
                                <i class="fas fa-redo me-2"></i>Reabrir
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            @endif

            <!-- Calificar Ticket -->
            @if($ticket->status == 3)
            <div class="card shadow mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0"><i class="fas fa-star me-2"></i>Calificación</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('area-admin.tickets.rate', $ticket->ticket_id) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Calificación (1-5)</label>
                            <select class="form-select" name="rating" required>
                                @for($i = 1; $i <= 5; $i++)
                                <option value="{{ $i }}" {{ $ticket->rating == $i ? 'selected' : '' }}>
                                    {{ str_repeat('★', $i) }} ({{ $i }})
                                </option>
                                @endfor
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Comentario (opcional)</label>
                            <textarea class="form-control" name="feedback" rows="2">{{ $ticket->feedback }}</textarea>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save me-2"></i>Guardar Evaluación
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            @endif

        </div><!-- /col actions -->
    </div><!-- /row -->
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const sel = document.getElementById('closeStatusSelect');
    if (!sel) return;

    function toggleCloseFields() {
        const isCancel = sel.value === '4';
        document.getElementById('solution_detail_group').style.display  = isCancel ? 'none' : '';
        document.getElementById('resource_link_group').style.display    = isCancel ? 'none' : '';
        document.getElementById('cancel_reason_group').style.display    = isCancel ? ''     : 'none';
    }

    sel.addEventListener('change', toggleCloseFields);
    toggleCloseFields();
});
</script>
@endpush
