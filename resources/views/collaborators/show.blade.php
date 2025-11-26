@extends('layouts.app')

@section('title', 'Perfil del Colaborador - Virtual Center')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Perfil del Colaborador</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <a href="{{ route('collaborators.edit', $collaborator->user_id) }}" class="btn btn-sm btn-warning">
                    <i class="fas fa-edit me-1"></i>Editar
                </a>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="printProfile()">
                    <i class="fas fa-print me-1"></i>Imprimir
                </button>
            </div>
            <a href="{{ route('collaborators.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>Volver
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Profile Information -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Información Personal</h5>
                    <span class="badge bg-{{ $collaborator->user_status === 'active' ? 'success' : ($collaborator->user_status === 'inactive' ? 'secondary' : 'warning') }} fs-6">
                        {{ ucfirst($collaborator->user_status) }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="fw-bold">Nombre:</td>
                                    <td>{{ $collaborator->user_name }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Correo:</td>
                                    <td>{{ $collaborator->user_email }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Teléfono:</td>
                                    <td>{{ $collaborator->user_phone ?? 'No especificado' }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Rol:</td>
                                    <td>{{ $collaborator->userRole->role_name }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="fw-bold">Estado:</td>
                                    <td>
                                        <span class="badge bg-{{ $collaborator->user_status === 'active' ? 'success' : ($collaborator->user_status === 'inactive' ? 'secondary' : 'warning') }}">
                                            {{ ucfirst($collaborator->user_status) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Institución:</td>
                                    <td>{{ $collaborator->institution->institution_name ?? 'No asignada' }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Miembro desde:</td>
                                    <td>{{ $collaborator->created_at->format('d/m/Y') }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Última actualización:</td>
                                    <td>{{ $collaborator->updated_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($collaborator->user_bio)
                    <div class="mt-4">
                        <h6 class="fw-bold">Biografía:</h6>
                        <p class="text-muted">{{ $collaborator->user_bio }}</p>
                    </div>
                    @endif

                    @if($collaborator->user_skills)
                    <div class="mt-4">
                        <h6 class="fw-bold">Habilidades:</h6>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach(explode(',', $collaborator->user_skills) as $skill)
                            <span class="badge bg-primary">{{ trim($skill) }}</span>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Projects Section -->
            <div class="card shadow mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Proyectos Asignados</h5>
                    <span class="badge bg-primary">{{ $collaborator->projects->count() }}</span>
                </div>
                <div class="card-body">
                    @forelse($collaborator->projects as $project)
                    <div class="d-flex align-items-center mb-3 p-3 border rounded">
                        <div class="flex-shrink-0">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                <i class="fas fa-project-diagram"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1">{{ $project->project_name }}</h6>
                                    <p class="text-muted mb-0">{{ $project->institution->institution_name }}</p>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-{{ $project->project_status === 'completed' ? 'success' : ($project->project_status === 'in_progress' ? 'warning' : 'secondary') }}">
                                        {{ ucfirst($project->project_status) }}
                                    </span>
                                    <div class="mt-1">
                                        <a href="{{ route('service-management.show', $project->tracking_id) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-project-diagram fa-3x mb-3"></i>
                        <p>No hay proyectos asignados</p>
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- Tasks Section -->
            <div class="card shadow mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Tareas Asignadas</h5>
                    <span class="badge bg-warning">{{ $collaborator->tasks->count() }}</span>
                </div>
                <div class="card-body">
                    @forelse($collaborator->tasks as $task)
                    <div class="d-flex align-items-center mb-3 p-3 border rounded">
                        <div class="flex-shrink-0">
                            <div class="bg-warning text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                <i class="fas fa-tasks"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1">{{ $task->task_name }}</h6>
                                    <p class="text-muted mb-0">{{ Str::limit($task->task_description, 100) }}</p>
                                    @if($task->due_date)
                                    <small class="text-muted">
                                        Vence: {{ $task->due_date->format('d/m/Y') }}
                                    </small>
                                    @endif
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-{{ $task->task_status === 'completed' ? 'success' : ($task->task_status === 'in_progress' ? 'warning' : 'secondary') }}">
                                        {{ ucfirst($task->task_status) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-tasks fa-3x mb-3"></i>
                        <p>No hay tareas asignadas</p>
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- Comments Section -->
            <div class="card shadow">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Comentarios Recientes</h5>
                    <span class="badge bg-info">{{ $collaborator->comments->count() }}</span>
                </div>
                <div class="card-body">
                    @forelse($collaborator->comments->take(5) as $comment)
                    <div class="d-flex mb-3">
                        <div class="flex-shrink-0">
                            <div class="bg-info text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="fas fa-comment"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1">{{ $comment->project->project_name }}</h6>
                                    <p class="mb-0">{{ $comment->comment_content }}</p>
                                </div>
                                <small class="text-muted">{{ $comment->created_at->format('d/m/Y H:i') }}</small>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-comments fa-3x mb-3"></i>
                        <p>No hay comentarios recientes</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Profile Picture -->
            <div class="card shadow mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Foto de Perfil</h5>
                </div>
                <div class="card-body text-center">
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 120px; height: 120px;">
                        <i class="fas fa-user fa-3x"></i>
                    </div>
                    <h5>{{ $collaborator->user_name }}</h5>
                    <p class="text-muted">{{ $collaborator->userRole->role_name }}</p>
                    <button type="button" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-camera me-2"></i>Cambiar Foto
                    </button>
                </div>
            </div>

            <!-- Statistics -->
            <div class="card shadow mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Estadísticas</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-end">
                                <h4 class="text-primary">{{ $collaborator->projects->count() }}</h4>
                                <small class="text-muted">Proyectos</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <h4 class="text-warning">{{ $collaborator->tasks->count() }}</h4>
                            <small class="text-muted">Tareas</small>
                        </div>
                    </div>
                    <hr>
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-end">
                                <h4 class="text-info">{{ $collaborator->comments->count() }}</h4>
                                <small class="text-muted">Comentarios</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <h4 class="text-success">{{ $collaborator->created_at->diffInDays(now()) }}</h4>
                            <small class="text-muted">Días Activo</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card shadow mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Acciones Rápidas</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('collaborators.edit', $collaborator->user_id) }}" class="btn btn-outline-primary">
                            <i class="fas fa-edit me-2"></i>Editar Perfil
                        </a>
                        <button type="button" class="btn btn-outline-success" onclick="changeStatus('active')">
                            <i class="fas fa-check me-2"></i>Activar Usuario
                        </button>
                        <button type="button" class="btn btn-outline-warning" onclick="changeStatus('inactive')">
                            <i class="fas fa-pause me-2"></i>Desactivar Usuario
                        </button>
                        <button type="button" class="btn btn-outline-info" onclick="sendMessage()">
                            <i class="fas fa-envelope me-2"></i>Enviar Mensaje
                        </button>
                    </div>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="card shadow">
                <div class="card-header">
                    <h5 class="card-title mb-0">Información de Contacto</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <i class="fas fa-envelope text-primary me-3"></i>
                        <div>
                            <div class="fw-bold">Correo Electrónico</div>
                            <small class="text-muted">{{ $collaborator->user_email }}</small>
                        </div>
                    </div>
                    
                    @if($collaborator->user_phone)
                    <div class="d-flex align-items-center mb-3">
                        <i class="fas fa-phone text-success me-3"></i>
                        <div>
                            <div class="fw-bold">Teléfono</div>
                            <small class="text-muted">{{ $collaborator->user_phone }}</small>
                        </div>
                    </div>
                    @endif
                    
                    <div class="d-flex align-items-center">
                        <i class="fas fa-building text-info me-3"></i>
                        <div>
                            <div class="fw-bold">Institución</div>
                            <small class="text-muted">{{ $collaborator->institution->institution_name ?? 'No asignada' }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function printProfile() {
    window.print();
}

function changeStatus(newStatus) {
    const statusNames = {
        'active': 'Activo',
        'inactive': 'Inactivo',
        'pending': 'Pendiente'
    };
    
    if (confirm(`¿Estás seguro de cambiar el estado a "${statusNames[newStatus]}"?`)) {
        // Aquí iría la lógica para cambiar el estado
        VirtualCenter.showAlert(`Estado cambiado a: ${statusNames[newStatus]}`, 'success');
        location.reload();
    }
}

function sendMessage() {
    const email = '{{ $collaborator->user_email }}';
    const subject = 'Mensaje desde Virtual Center';
    const body = `Hola {{ $collaborator->user_name }},\n\n`;
    
    const mailtoLink = `mailto:${email}?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;
    window.open(mailtoLink);
}
</script>
@endpush

