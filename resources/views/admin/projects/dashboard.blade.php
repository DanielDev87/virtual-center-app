@extends('layouts.admin')

@section('title', 'Gestión de Proyecto - ' . $ticket->title)

@section('content')
<div class="container-fluid">
    <!-- Header with ADDIE Phases -->
    <div class="d-flex justify-content-end flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
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
                        <div class="card mb-2 border-left-secondary task-card-readonly">
                            <div class="card-body p-2">
                                <div class="d-flex justify-content-between align-items-start gap-2">
                                    <div class="flex-grow-1">
                                        <small class="fw-bold d-block">{{ $task->title }}</small>
                                        <small class="text-muted d-block">{{ Str::limit($task->description ?? '', 50) }}</small>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-primary assign-to-sprint-btn" data-bs-toggle="modal" data-bs-target="#assignSprintModal" data-task-id="{{ $task->task_id }}" data-task-title="{{ $task->title }}" style="white-space: nowrap;">
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
        <div class="col-md-9">
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
                            <form action="{{ route('admin.projects.update-sprint-status', $activeSprint->sprint_id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="active">
                                <button type="submit" class="btn btn-success btn-sm">
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
                            <form action="{{ route('admin.projects.update-sprint-status', $activeSprint->sprint_id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="completed">
                                <button id="completeSprintButton" type="submit" class="btn btn-secondary btn-sm" {{ $canCompleteSprint ? '' : 'disabled' }} title="{{ $canCompleteSprint ? 'Completar sprint' : 'Todas las tareas deben estar en Hecho para completar el sprint' }}">
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
                        $canDragTasks = $activeSprint->status === 'active';
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
                                        <div class="card mb-2 shadow-sm task-card" {{ $canDragTasks ? 'draggable="true"' : '' }} data-task-id="{{ $task->task_id }}" style="{{ $canDragTasks ? '' : 'opacity: 0.7;' }}">
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
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i> Selecciona un sprint de la lista para ver sus detalles o crea uno nuevo.
                </div>
            @endif
        </div>
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

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const sprintIsActive = {{ ($activeSprint && $activeSprint->status === 'active') ? 'true' : 'false' }};
        const containers = document.querySelectorAll('.kanban-column');
        const completeSprintButton = document.getElementById('completeSprintButton');
        const assignSprintForm = document.getElementById('assignSprintForm');
        const assignSprintTaskTitle = document.getElementById('assignSprintTaskTitle');
        const assignSprintSelect = document.getElementById('assignSprintSelect');
        const assignButtons = document.querySelectorAll('.assign-to-sprint-btn');

        function attachDragListeners() {
            const draggables = document.querySelectorAll('.task-card[draggable="true"]');
            
            draggables.forEach(draggable => {
                draggable.removeEventListener('dragstart', handleDragStart);
                draggable.removeEventListener('dragend', handleDragEnd);
                draggable.addEventListener('dragstart', handleDragStart);
                draggable.addEventListener('dragend', handleDragEnd);
            });
        }

        function syncDraggableState() {
            const cards = document.querySelectorAll('.kanban-column .task-card');
            cards.forEach(card => {
                if (sprintIsActive) {
                    card.setAttribute('draggable', 'true');
                    card.style.opacity = '';
                } else {
                    card.removeAttribute('draggable');
                    card.style.opacity = '0.7';
                }
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
            
            fetch(`/admin/projects/tasks/${taskId}/update-status`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ status: newStatus })
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    alert(data.message || 'No se pudo mover la tarea.');
                    window.location.reload();
                }
            })
            .catch(() => {
                alert('Error al actualizar la tarea.');
                window.location.reload();
            });

            updateEmptyStates();
            updateCompleteSprintButton();
        }

        assignButtons.forEach(button => {
            button.addEventListener('click', () => {
                const taskId = button.dataset.taskId;
                const taskTitle = button.dataset.taskTitle;
                assignSprintTaskTitle.value = taskTitle;
                assignSprintSelect.value = '';
                if (assignSprintForm) {
                    assignSprintForm.action = `/admin/projects/tasks/${taskId}/assign-sprint`;
                }
            });
        });

        containers.forEach(container => {
            container.addEventListener('dragover', e => {
                e.preventDefault();
                const afterElement = getDragAfterElement(container, e.clientY);
                const draggable = document.querySelector('.dragging');
                if (!draggable) return;
                if (afterElement == null) {
                    container.appendChild(draggable);
                } else {
                    container.insertBefore(draggable, afterElement);
                }
            });
        });

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

        syncDraggableState();
        attachDragListeners();
        updateEmptyStates();
        updateCompleteSprintButton();
    });
</script>
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

    .task-card[draggable="true"] { cursor: move; }
    .task-card:not([draggable="true"]) { cursor: default; }
    .task-card.dragging { opacity: 0.5; }
</style>
@endpush
@endsection
