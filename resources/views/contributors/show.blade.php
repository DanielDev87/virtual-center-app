@extends('layouts.contributor')

@section('title', 'Detalle del Ticket - Colaborador')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Ticket #{{ $ticket->ticket_number }}</h1>
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

<div class="row">
    <!-- Ticket Information -->
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Información del Ticket</h5>
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
                
                @if($ticket->requestType)
                <div class="mb-3">
                    <span class="badge" style="background-color: {{ $ticket->requestType->type_color }}; font-size: 1rem;">
                        <i class="fas {{ $ticket->requestType->type_icon }} me-1"></i>
                        {{ $ticket->requestType->type_name }}
                    </span>
                </div>
                @endif
                
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
                        <small class="text-muted d-block">Creado:</small>
                        <strong>{{ $ticket->created_at->format('d/m/Y H:i') }}</strong>
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
                                    <span class="badge" style="background-color: {{ $assignment->jobPosition->position_color }}">
                                        {{ $assignment->jobPosition->position_name }}
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
            </div>
        </div>

        <!-- Progress History -->
        <div class="card shadow mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="fas fa-history me-2"></i>Historial de Avances</h5>
            </div>
            <div class="card-body">
                @forelse($ticket->progress()->latest()->get() as $progress)
                <div class="border-start border-3 border-primary ps-3 mb-3">
                    <div class="d-flex justify-content-between">
                        <strong>{{ $progress->user->user_name }}</strong>
                        <small class="text-muted">{{ $progress->created_at->format('d/m/Y H:i') }}</small>
                    </div>
                    <p class="mb-1">{{ $progress->progress_description }}</p>
                    <div class="progress" style="height: 20px;">
                        <div class="progress-bar bg-success" role="progressbar" 
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

    <!-- Register Progress -->
    <div class="col-lg-4">
        @if($ticket->status != 3 && $ticket->status != 4)
        <div class="card shadow mb-4 border-success">
            <div class="card-header bg-success text-white">
                <h5 class="card-title mb-0"><i class="fas fa-plus-circle me-2"></i>Registrar Avance</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('contributors.tickets.progress', $ticket->ticket_id) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="progress_description" class="form-label">Descripción del Avance</label>
                        <textarea class="form-control @error('progress_description') is-invalid @enderror" 
                                  id="progress_description" name="progress_description" rows="4" 
                                  required>{{ old('progress_description') }}</textarea>
                        @error('progress_description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="progress_percentage" class="form-label">Porcentaje de Avance</label>
                        <input type="range" class="form-range" id="progress_percentage" 
                               name="progress_percentage" min="0" max="100" value="{{ old('progress_percentage', 0) }}" 
                               oninput="this.nextElementSibling.value = this.value + '%'">
                        <output class="badge bg-primary">0%</output>
                        @error('progress_percentage')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save me-2"></i>Guardar Avance
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @else
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            Este ticket ya está {{ $ticket->status == 3 ? 'completado' : 'cancelado' }}.
        </div>
        @endif
    </div>
</div>

<h3 class="h4 mt-5 mb-3"><i class="fas fa-tasks me-2"></i>Sprints y Tareas del Proyecto</h3>
<div class="row mb-5">
    <!-- Sidebar: Sprints & Backlog -->
    <div class="col-md-3">
        <div class="card shadow mb-4">
            <div class="card-header border-bottom-0 pb-0">
                <h6 class="m-0 font-weight-bold text-primary">Sprints</h6>
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
            <div class="card-header pb-0 border-bottom-0">
                <h6 class="m-0 font-weight-bold text-secondary">Backlog</h6>
            </div>
            <div class="card-body mt-2">
                @forelse($backlogTasks as $task)
                    <div class="card mb-2 border-left-secondary task-card-readonly">
                        <div class="card-body p-2">
                            <small class="fw-bold">{{ $task->title }}</small>
                            <div class="d-flex justify-content-between align-items-center mt-1">
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
    <div class="col-md-9">
        @if($activeSprint)
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex align-items-center">
                    <h4 class="mb-0 me-3">{{ $activeSprint->name }}</h4>
                    <span class="badge bg-{{ $activeSprint->status == 'active' ? 'success' : ($activeSprint->status == 'completed' ? 'secondary' : 'warning') }}">
                        {{ ucfirst($activeSprint->status) }}
                    </span>
                </div>
            </div>
            
            <div class="row">
                @php
                    $columns = [
                        'todo' => ['title' => 'Por Hacer', 'bg' => 'bg-light'],
                        'in_progress' => ['title' => 'En Progreso', 'bg' => 'bg-info bg-opacity-10'],
                        'review' => ['title' => 'Revisión', 'bg' => 'bg-warning bg-opacity-10'],
                        'done' => ['title' => 'Hecho', 'bg' => 'bg-success bg-opacity-10']
                    ];
                @endphp

                @foreach($columns as $status => $col)
                    <div class="col-md-3">
                        <div class="card h-100 {{ $col['bg'] }}">
                            <div class="card-header py-2">
                                <h6 class="m-0 fw-bold text-uppercase small">{{ $col['title'] }}</h6>
                            </div>
                            <div class="card-body p-2 kanban-column" data-status="{{ $status }}" style="min-height: 400px;">
                                @foreach($activeSprint->tasks->where('status', $status) as $task)
                                    <div class="card mb-2 shadow-sm task-card" draggable="true" data-task-id="{{ $task->task_id }}">
                                        <div class="card-body p-2">
                                            <h6 class="card-title small fw-bold mb-1">{{ $task->title }}</h6>
                                            <p class="card-text small text-muted mb-2">{{ Str::limit($task->description, 50) }}</p>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="badge bg-{{ $task->priority == 'high' ? 'danger' : ($task->priority == 'medium' ? 'warning' : 'info') }}">{{ $task->priority }}</span>
                                                @if($task->assignee)
                                                    <img src="https://ui-avatars.com/api/?name={{ urlencode($task->assignee->user_name) }}&size=24" class="rounded-circle" title="{{ $task->assignee->user_name }}">
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="alert alert-info shadow-sm">
                <i class="fas fa-info-circle me-2"></i> Selecciona un sprint de la lista para ver sus tareas o espera a que se asignen.
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const draggables = document.querySelectorAll('.task-card');
        const containers = document.querySelectorAll('.kanban-column');

        draggables.forEach(draggable => {
            draggable.addEventListener('dragstart', () => {
                draggable.classList.add('dragging');
            });

            draggable.addEventListener('dragend', () => {
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
            });
        });

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
    });
</script>
<style>
    .task-card { cursor: move; }
    .task-card.dragging { opacity: 0.5; }
</style>
@endpush
@endsection
