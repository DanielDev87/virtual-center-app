@extends('layouts.app')

@section('title', 'Dashboard - Virtual Center')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Dashboard</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <button type="button" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-download me-1"></i>Exportar
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-calendar me-1"></i>Calendario
                </button>
            </div>
            <button type="button" class="btn btn-sm btn-primary">
                <i class="fas fa-plus me-1"></i>Nuevo Proyecto
            </button>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Proyectos
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_projects'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-project-diagram fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Proyectos Activos
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['active_projects'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Usuarios Activos
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_users'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Tareas Pendientes
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['pending_tasks'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-tasks fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row">
        <!-- Projects by Status Chart -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Proyectos por Estado</h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie pt-4 pb-2">
                        <canvas id="projectsStatusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Projects by Institution Chart -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Proyectos por Institución</h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie pt-4 pb-2">
                        <canvas id="institutionChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Projects and Tasks -->
    <div class="row">
        <!-- Recent Projects -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Proyectos Recientes</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Proyecto</th>
                                    <th>Institución</th>
                                    <th>Estado</th>
                                    <th>Fecha</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentProjects as $project)
                                <tr>
                                    <td>{{ $project->project_name }}</td>
                                    <td>{{ $project->institution->institution_name }}</td>
                                    <td>
                                        <span class="badge bg-{{ $project->project_status === 'completed' ? 'success' : ($project->project_status === 'in_progress' ? 'warning' : 'secondary') }}">
                                            {{ ucfirst($project->project_status) }}
                                        </span>
                                    </td>
                                    <td>{{ $project->created_at->format('d/m/Y') }}</td>
                                    <td>
                                        <a href="{{ route('service-management.show', $project->tracking_id) }}" class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">No hay proyectos recientes</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Tasks -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Tareas Pendientes</h6>
                </div>
                <div class="card-body">
                    @forelse($pendingTasks as $task)
                    <div class="d-flex align-items-center mb-3">
                        <div class="flex-shrink-0">
                            <i class="fas fa-circle text-warning"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="fw-bold">{{ $task->task_name }}</div>
                            <div class="text-muted small">
                                Asignado a: {{ $task->assignedTo->user_name }}
                            </div>
                            <div class="text-muted small">
                                Vence: {{ $task->due_date ? $task->due_date->format('d/m/Y') : 'Sin fecha' }}
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center text-muted">
                        <i class="fas fa-check-circle fa-2x mb-2"></i>
                        <p>No hay tareas pendientes</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Projects Status Chart
const projectsStatusCtx = document.getElementById('projectsStatusChart').getContext('2d');
new Chart(projectsStatusCtx, {
    type: 'doughnut',
    data: {
        labels: ['Completados', 'En Progreso', 'Pendientes', 'Cancelados'],
        datasets: [{
            data: [
                {{ $projectsByStatus['completed'] ?? 0 }},
                {{ $projectsByStatus['in_progress'] ?? 0 }},
                {{ $projectsByStatus['pending'] ?? 0 }},
                {{ $projectsByStatus['cancelled'] ?? 0 }}
            ],
            backgroundColor: ['#28a745', '#ffc107', '#6c757d', '#dc3545'],
            hoverBackgroundColor: ['#218838', '#e0a800', '#5a6268', '#c82333']
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

// Institution Chart
const institutionCtx = document.getElementById('institutionChart').getContext('2d');
new Chart(institutionCtx, {
    type: 'pie',
    data: {
        labels: {!! json_encode(array_keys($projectsByInstitution->toArray())) !!},
        datasets: [{
            data: {!! json_encode(array_values($projectsByInstitution->toArray())) !!},
            backgroundColor: [
                '#007bff', '#28a745', '#ffc107', '#dc3545', '#6f42c1',
                '#20c997', '#fd7e14', '#e83e8c', '#6c757d', '#17a2b8'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});
</script>
@endpush
