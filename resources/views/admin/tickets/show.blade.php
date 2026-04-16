@extends('layouts.admin')

@section('title', 'Detalle del Ticket - Admin')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Ticket #{{ $ticket->ticket_number }}</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="{{ route('admin.projects.dashboard', $ticket->ticket_id) }}" class="btn btn-sm btn-primary me-2">
                <i class="fas fa-tasks me-1"></i>Ver Tablero de Proyecto
            </a>
            <a href="{{ route('admin.tickets.index') }}" class="btn btn-sm btn-outline-secondary">
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

    <div class="row">
        <!-- Ticket Information -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Información del Ticket</h5>
                    @php
                        $statusColors = [1 => 'secondary', 2 => 'warning', 3 => 'success', 4 => 'danger'];
                        $statusNames = [1 => 'Pendiente', 2 => 'En Progreso', 3 => 'Completado', 4 => 'Cancelado'];
                        $priorityColors = [1 => 'success', 2 => 'info', 3 => 'warning', 4 => 'danger'];
                        $priorityNames = [1 => '🟢 Baja', 2 => '🟡 Media', 3 => '🟠 Alta', 4 => '🔴 Urgente'];
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
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-bold text-muted small">Progreso General</span>
                            <span class="fw-bold {{ $ticket->progress_percentage == 100 ? 'text-success' : 'text-primary' }}">
                                {{ $ticket->progress_percentage }}%
                            </span>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar {{ $ticket->progress_percentage == 100 ? 'bg-success' : 'bg-primary' }}" 
                                 role="progressbar" 
                                 style="width: {{ $ticket->progress_percentage }}%" 
                                 aria-valuenow="{{ $ticket->progress_percentage }}" 
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

                    
                    <p class="text-muted">{{ $ticket->requester_info }}</p>
                    
                    <hr>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <small class="text-muted d-block">Solicitante:</small>
                            <strong>{{ $ticket->requester->user_name }}</strong>
                            <br>
                            <small>{{ $ticket->requester->user_email }}</small>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block">Mediador Asignado:</small>
                            @if($ticket->mediator)
                                <strong>{{ $ticket->mediator->user_name }}</strong>
                                <br>
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
                    </div>
                    @endif
                </div>
            </div>

            <!-- Team Management Section -->
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
                                        <span class="badge" style="background-color: {{ $assignment->jobPosition->position_color }}">
                                            {{ $assignment->jobPosition->position_name }}
                                        </span>
                                    </td>
                                    <td>
                                        <small>{{ $assignment->assigned_at->format('d/m/Y') }}</small>
                                    </td>
                                    <td>
                                        <form action="{{ route('admin.tickets.remove-assignment', [$ticket->ticket_id, $assignment->assignment_id]) }}" 
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

                    <!-- Add Team Member Form -->
                    <div class="border-top pt-3">
                        <h6 class="mb-3">Agregar Miembro al Equipo</h6>
                        <form action="{{ route('admin.tickets.assign-mediator', $ticket->ticket_id) }}" method="POST">
                            @csrf
                            <div class="row g-2">
                                <div class="col-md-5">
                                    <select name="user_id" class="form-select form-select-sm" required>
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
                                    <select name="job_position_id" class="form-select form-select-sm" required>
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
        </div>

        <!-- Progress History -->
        <div class="col-12 mb-4">
            <div class="card shadow">
                <div class="card-header bg-white py-3">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-history me-2"></i>Historial de Avances</h6>
                </div>
                <div class="card-body">
                    @if($ticket->is_reopened)
                        <h6 class="text-info border-bottom pb-2 mb-3">Avances de Reapertura (Desde {{ $ticket->reopened_at ? $ticket->reopened_at->format('d/m/Y H:i') : '' }})</h6>
                        @php
                            $reopenedProgress = $ticket->progress->where('created_at', '>=', $ticket->reopened_at)->sortByDesc('created_at');
                            $originalProgress = $ticket->progress->where('created_at', '<', $ticket->reopened_at)->sortByDesc('created_at');
                        @endphp

                        @if($reopenedProgress->count() > 0)
                            <div class="timeline mb-4">
                                @foreach($reopenedProgress as $progress)
                                    <div class="timeline-item ps-3 border-start border-info mb-3">
                                        <p class="text-muted small mb-1">
                                            {{ $progress->created_at->format('d/m/Y H:i') }} - 
                                            <span class="fw-bold">{{ $progress->user->user_name ?? 'Usuario' }}</span>
                                            <span class="badge bg-info ms-2">{{ $progress->progress_percentage }}%</span>
                                        </p>
                                        <p class="mb-0">{{ $progress->progress_description }}</p>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted fst-italic mb-4">No hay avances registrados desde la reapertura.</p>
                        @endif

                        <h6 class="text-secondary border-bottom pb-2 mb-3">Avances Anteriores</h6>
                        @if($originalProgress->count() > 0)
                            <div class="timeline">
                                @foreach($originalProgress as $progress)
                                    <div class="timeline-item ps-3 border-start border-secondary mb-3 opacity-75">
                                        <p class="text-muted small mb-1">
                                            {{ $progress->created_at->format('d/m/Y H:i') }} - 
                                            <span class="fw-bold">{{ $progress->user->user_name ?? 'Usuario' }}</span>
                                            <span class="badge bg-secondary ms-2">{{ $progress->progress_percentage }}%</span>
                                        </p>
                                        <p class="mb-0">{{ $progress->progress_description }}</p>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted fst-italic">No hay avances anteriores.</p>
                        @endif

                    @else
                        <!-- Standard Progress View -->
                        @if($ticket->progress->count() > 0)
                            <div class="timeline">
                                @foreach($ticket->progress->sortByDesc('created_at') as $progress)
                                    <div class="timeline-item ps-3 border-start border-primary mb-3">
                                        <p class="text-muted small mb-1">
                                            {{ $progress->created_at->format('d/m/Y H:i') }} - 
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
                    @endif
                </div>
            </div>
        </div>

        <!-- Admin Actions -->
        <div class="col-lg-4">
            <!-- Assign Mediator -->
            @if($ticket->status == 1 || !$ticket->mediator)
            <div class="card shadow mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0"><i class="fas fa-user-plus me-2"></i>Asignar Mediador</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.tickets.assign', $ticket->ticket_id) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="mediator_id" class="form-label">Seleccionar Mediador</label>
                            <select class="form-select" id="mediator_id" name="mediator_id" required>
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

            <!-- Set Priority -->
            <div class="card shadow mb-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="card-title mb-0"><i class="fas fa-flag me-2"></i>Establecer Prioridad</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.tickets.priority', $ticket->ticket_id) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="priority" class="form-label">Nivel de Prioridad</label>
                            <select class="form-select" id="priority" name="priority" required>
                                <option value="1" {{ $ticket->priority == 1 ? 'selected' : '' }}>🟢 Baja</option>
                                <option value="2" {{ $ticket->priority == 2 ? 'selected' : '' }}>🟡 Media</option>
                                <option value="3" {{ $ticket->priority == 3 ? 'selected' : '' }}>🟠 Alta</option>
                                <option value="4" {{ $ticket->priority == 4 ? 'selected' : '' }}>🔴 Urgente</option>
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

            <!-- Close Ticket -->
            @if($ticket->status != 3 && $ticket->status != 4)
            <div class="card shadow mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0"><i class="fas fa-check-circle me-2"></i>Cerrar Ticket</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.tickets.close', $ticket->ticket_id) }}" method="POST" id="closeTicketForm">
                        @csrf
                        <div class="mb-3">
                            <label for="status" class="form-label">Estado Final</label>
                            <select class="form-select" id="status" name="status" required onchange="toggleResourceLink()">
                                <option value="3">Completado</option>
                                <option value="4">Cancelado</option>
                            </select>
                        </div>
                        
                        <div id="resourceLinkField" class="mb-3">
                            <label for="resource_link" class="form-label">Enlace al Recurso Generado <span class="text-danger">*</span></label>
                            <input type="url" class="form-control" id="resource_link" name="resource_link" placeholder="https://...">
                            <div class="form-text text-muted">Obligatorio para marcar como completado.</div>
                        </div>

                        <div class="mb-3">
                            <label for="admin_notes" class="form-label">Notas (Opcional)</label>
                            <textarea class="form-control" id="admin_notes" name="admin_notes" rows="3"></textarea>
                        </div>
                        
                        @if($ticket->progress_percentage < 100)
                        <div class="alert alert-warning small mb-3" id="progressWarning">
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            El progreso es del {{ $ticket->progress_percentage }}%. Debe estar al 100% para completar.
                        </div>
                        @endif

                        <div class="d-grid">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-flag-checkered me-2"></i>Cerrar Ticket
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            @endif

            <!-- Reopen Ticket -->
            @if($ticket->status == 3 || $ticket->status == 4)
            <div class="card shadow mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0"><i class="fas fa-redo me-2"></i>Reabrir Ticket</h5>
                </div>
                <div class="card-body">
                    <p class="small text-muted">Si el solicitante requiere cambios adicionales, puede reabrir el ticket. Esto habilitará una nueva sección de seguimiento.</p>
                    <form action="{{ route('admin.tickets.reopen', $ticket->ticket_id) }}" method="POST">
                        @csrf
                        <div class="d-grid">
                            <button type="submit" class="btn btn-info text-white" onclick="return confirm('¿Está seguro de reabrir este ticket?')">
                                <i class="fas fa-redo me-2"></i>Reabrir Ticket
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            @endif

            <!-- Rate Service (ADDIE Evaluation) -->
            @if($ticket->status == 3)
            <div class="card shadow mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0"><i class="fas fa-star me-2"></i>Evaluación ADDIE</h5>
                </div>
                <div class="card-body">
                    @if($ticket->rating)
                        <div class="text-center mb-3">
                            <div class="display-6 text-warning mb-2">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="fas fa-star {{ $i <= $ticket->rating ? '' : 'text-muted opacity-25' }}"></i>
                                @endfor
                            </div>
                            <p class="fw-bold mb-1">Calificación: {{ $ticket->rating }}/5</p>
                            @if($ticket->feedback)
                                <p class="text-muted small fst-italic">"{{ $ticket->feedback }}"</p>
                            @endif
                        </div>
                    @else
                        <form action="{{ route('admin.tickets.rate', $ticket->ticket_id) }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Calificación</label>
                                <div class="rating-input d-flex justify-content-center gap-2 fs-3 text-warning cursor-pointer">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="far fa-star" data-rating="{{ $i }}" onclick="setRating({{ $i }})"></i>
                                    @endfor
                                </div>
                                <input type="hidden" name="rating" id="ratingInput" required>
                            </div>
                            <div class="mb-3">
                                <label for="feedback" class="form-label">Retroalimentación</label>
                                <textarea class="form-control" id="feedback" name="feedback" rows="3" placeholder="Comentarios sobre el servicio..."></textarea>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Guardar Evaluación
                                </button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
            @endif

            <script>
            function toggleResourceLink() {
                const status = document.getElementById('status').value;
                const field = document.getElementById('resourceLinkField');
                const warning = document.getElementById('progressWarning');
                
                if (status == '3') {
                    field.style.display = 'block';
                    if(warning) warning.style.display = 'block';
                } else {
                    field.style.display = 'none';
                    if(warning) warning.style.display = 'none';
                }
            }
            
            function setRating(rating) {
                document.getElementById('ratingInput').value = rating;
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

            // Init
            document.addEventListener('DOMContentLoaded', function() {
                toggleResourceLink();
            });
            </script>
        </div>
    </div>
</div>
@endsection
