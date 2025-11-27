@extends('layouts.admin')

@section('title', 'Gestión de Proyecto - ' . $ticket->title)

@section('content')
<div class="container-fluid">
    <!-- Header with ADDIE Phases -->
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Proyecto: {{ $ticket->title }}</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="{{ route('admin.tickets.show', $ticket->ticket_id) }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>Volver al Ticket
            </a>
        </div>
    </div>

    <!-- ADDIE Phase Stepper -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <h6 class="card-subtitle mb-3 text-muted">Fase Actual (ADDIE)</h6>
            <div class="position-relative m-4">
                <div class="progress" style="height: 2px;">
                    <div class="progress-bar" role="progressbar" style="width: {{ $ticket->current_phase == 'Evaluation' ? '100%' : ($ticket->current_phase == 'Implementation' ? '75%' : ($ticket->current_phase == 'Development' ? '50%' : ($ticket->current_phase == 'Design' ? '25%' : '0%'))) }};" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                @php
                    $phases = ['Analysis' => 'Análisis', 'Design' => 'Diseño', 'Development' => 'Desarrollo', 'Implementation' => 'Implementación', 'Evaluation' => 'Evaluación'];
                    $currentFound = false;
                @endphp
                <div class="d-flex justify-content-between position-absolute top-0 w-100" style="margin-top: -10px;">
                    @foreach($phases as $key => $label)
                        <div class="text-center">
                            <form action="{{ route('admin.projects.update-phase', $ticket->ticket_id) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="phase" value="{{ $key }}">
                                <button type="submit" class="btn btn-sm rounded-circle {{ $ticket->current_phase == $key ? 'btn-primary' : 'btn-secondary' }}" style="width: 30px; height: 30px; padding: 0;">
                                    {{ $loop->iteration }}
                                </button>
                            </form>
                            <small class="d-block mt-1 {{ $ticket->current_phase == $key ? 'fw-bold text-primary' : 'text-muted' }}">{{ $label }}</small>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Sidebar: Sprints & Backlog -->
        <div class="col-md-3">
            <div class="card shadow mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Sprints</h6>
                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#createSprintModal">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($ticket->sprints as $sprint)
                            <a href="{{ route('admin.projects.dashboard', ['ticketId' => $ticket->ticket_id, 'sprint_id' => $sprint->sprint_id]) }}" class="list-group-item list-group-item-action {{ (isset($activeSprint) && $activeSprint->sprint_id == $sprint->sprint_id) ? 'active' : '' }}">
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
                    <button class="btn btn-sm btn-secondary" data-bs-toggle="modal" data-bs-target="#createTaskModal">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
                <div class="card-body">
                    @forelse($backlogTasks as $task)
                        <div class="card mb-2 border-left-secondary">
                            <div class="card-body p-2">
                                <small class="fw-bold">{{ $task->title }}</small>
                                <div class="d-flex justify-content-between align-items-center mt-1">
                                    <span class="badge bg-secondary">{{ $task->priority }}</span>
                                    <small class="text-muted">{{ $task->assignee->user_name ?? 'Unassigned' }}</small>
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
                    <div>
                        @if($activeSprint->status == 'planned')
                            <form action="{{ route('admin.projects.update-sprint-status', $activeSprint->sprint_id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="active">
                                <button type="submit" class="btn btn-success btn-sm">
                                    <i class="fas fa-play me-1"></i>Iniciar Sprint
                                </button>
                            </form>
                        @elseif($activeSprint->status == 'active')
                            <form action="{{ route('admin.projects.update-sprint-status', $activeSprint->sprint_id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="completed">
                                <button type="submit" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-check me-1"></i>Completar Sprint
                                </button>
                            </form>
                        @endif
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
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i> Selecciona un sprint de la lista para ver sus detalles o crea uno nuevo.
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Create Sprint Modal -->
<div class="modal fade" id="createSprintModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('admin.projects.store-sprint', $ticket->ticket_id) }}" method="POST">
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
        <form action="{{ route('admin.projects.store-task', $ticket->ticket_id) }}" method="POST">
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
                                    <option value="{{ $sprint->sprint_id }}">{{ $sprint->name }}</option>
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
                fetch(`/admin/projects/tasks/${taskId}/update-status`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ status: newStatus })
                });
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
