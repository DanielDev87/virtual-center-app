@extends('layouts.app')

@section('title', 'Monitor del Sistema - Virtual Center')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Monitor del Sistema</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="refreshData()">
                    <i class="fas fa-sync-alt me-1"></i>Actualizar
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="exportData()">
                    <i class="fas fa-download me-1"></i>Exportar
                </button>
            </div>
            <div class="btn-group">
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleAutoRefresh()">
                    <i class="fas fa-clock me-1"></i>Auto-refresh
                </button>
            </div>
        </div>
    </div>

    <!-- System Status Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Proyectos Activos
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['active_projects'] }}</div>
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
                                Proyectos Completados
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['completed_projects'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
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

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Usuarios Conectados
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['online_users'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row">
        <!-- Projects Timeline Chart -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Timeline de Proyectos</h6>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="dropdownMenuLink">
                            <div class="dropdown-header">Opciones:</div>
                            <a class="dropdown-item" href="#" onclick="changeChartPeriod('7d')">Últimos 7 días</a>
                            <a class="dropdown-item" href="#" onclick="changeChartPeriod('30d')">Últimos 30 días</a>
                            <a class="dropdown-item" href="#" onclick="changeChartPeriod('90d')">Últimos 90 días</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="projectsTimelineChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Performance Chart -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Rendimiento del Sistema</h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie pt-4 pb-2">
                        <canvas id="systemPerformanceChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Real-time Data Tables -->
    <div class="row">
        <!-- Recent Activities -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Actividades Recientes</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Usuario</th>
                                    <th>Acción</th>
                                    <th>Proyecto</th>
                                    <th>Hora</th>
                                </tr>
                            </thead>
                            <tbody id="activitiesTable">
                                @forelse($recentActivities as $activity)
                                <tr>
                                    <td>{{ $activity->user->user_name }}</td>
                                    <td>
                                        <span class="badge bg-{{ $activity->action_type === 'create' ? 'success' : ($activity->action_type === 'update' ? 'warning' : 'info') }}">
                                            {{ ucfirst($activity->action_type) }}
                                        </span>
                                    </td>
                                    <td>{{ $activity->project->project_name }}</td>
                                    <td>{{ $activity->created_at->format('H:i:s') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">No hay actividades recientes</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Alerts -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Alertas del Sistema</h6>
                </div>
                <div class="card-body">
                    <div id="systemAlerts">
                        @forelse($systemAlerts as $alert)
                        <div class="alert alert-{{ $alert->alert_level === 'critical' ? 'danger' : ($alert->alert_level === 'warning' ? 'warning' : 'info') }} alert-dismissible fade show" role="alert">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-{{ $alert->alert_level === 'critical' ? 'exclamation-triangle' : ($alert->alert_level === 'warning' ? 'exclamation-circle' : 'info-circle') }} me-2"></i>
                                <div>
                                    <strong>{{ $alert->alert_title }}</strong>
                                    <p class="mb-0">{{ $alert->alert_message }}</p>
                                    <small class="text-muted">{{ $alert->created_at->format('d/m/Y H:i:s') }}</small>
                                </div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        @empty
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-shield-alt fa-3x mb-3"></i>
                            <p>No hay alertas del sistema</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Server Status -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Estado del Servidor</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="h4 text-success" id="serverStatus">En Línea</div>
                                <small class="text-muted">Estado del Servidor</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="h4 text-info" id="responseTime">{{ $serverStats['response_time'] }}ms</div>
                                <small class="text-muted">Tiempo de Respuesta</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="h4 text-warning" id="cpuUsage">{{ $serverStats['cpu_usage'] }}%</div>
                                <small class="text-muted">Uso de CPU</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="h4 text-primary" id="memoryUsage">{{ $serverStats['memory_usage'] }}%</div>
                                <small class="text-muted">Uso de Memoria</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let autoRefreshInterval;
let isAutoRefreshEnabled = false;

$(document).ready(function() {
    initializeCharts();
    startAutoRefresh();
});

function initializeCharts() {
    // Projects Timeline Chart
    const timelineCtx = document.getElementById('projectsTimelineChart').getContext('2d');
    new Chart(timelineCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($timelineData['labels']) !!},
            datasets: [{
                label: 'Proyectos Creados',
                data: {!! json_encode($timelineData['created']) !!},
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.1
            }, {
                label: 'Proyectos Completados',
                data: {!! json_encode($timelineData['completed']) !!},
                borderColor: 'rgb(54, 162, 235)',
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            },
            plugins: {
                legend: {
                    position: 'top'
                }
            }
        }
    });

    // System Performance Chart
    const performanceCtx = document.getElementById('systemPerformanceChart').getContext('2d');
    new Chart(performanceCtx, {
        type: 'doughnut',
        data: {
            labels: ['CPU', 'Memoria', 'Disco', 'Red'],
            datasets: [{
                data: [
                    {{ $serverStats['cpu_usage'] }},
                    {{ $serverStats['memory_usage'] }},
                    {{ $serverStats['disk_usage'] }},
                    {{ $serverStats['network_usage'] }}
                ],
                backgroundColor: ['#ff6384', '#36a2eb', '#ffce56', '#4bc0c0'],
                hoverBackgroundColor: ['#ff6384', '#36a2eb', '#ffce56', '#4bc0c0']
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
}

function refreshData() {
    VirtualCenter.showAlert('Actualizando datos...', 'info', 2000);
    
    // Simulate data refresh
    setTimeout(() => {
        location.reload();
    }, 1000);
}

function exportData() {
    VirtualCenter.showAlert('Exportando datos...', 'info', 2000);
    
    // Create and download CSV
    const csvContent = generateCSV();
    const blob = new Blob([csvContent], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'monitor-data-' + new Date().toISOString().split('T')[0] + '.csv';
    a.click();
    window.URL.revokeObjectURL(url);
}

function generateCSV() {
    const headers = ['Usuario', 'Acción', 'Proyecto', 'Hora'];
    const rows = [];
    
    $('#activitiesTable tr').each(function() {
        const cells = [];
        $(this).find('td').each(function() {
            cells.push($(this).text().trim());
        });
        if (cells.length > 0) {
            rows.push(cells.join(','));
        }
    });
    
    return [headers.join(','), ...rows].join('\n');
}

function toggleAutoRefresh() {
    if (isAutoRefreshEnabled) {
        stopAutoRefresh();
    } else {
        startAutoRefresh();
    }
}

function startAutoRefresh() {
    isAutoRefreshEnabled = true;
    autoRefreshInterval = setInterval(() => {
        refreshData();
    }, 30000); // Refresh every 30 seconds
    
    VirtualCenter.showAlert('Auto-refresh activado (30s)', 'success', 2000);
}

function stopAutoRefresh() {
    isAutoRefreshEnabled = false;
    clearInterval(autoRefreshInterval);
    VirtualCenter.showAlert('Auto-refresh desactivado', 'info', 2000);
}

function changeChartPeriod(period) {
    VirtualCenter.showAlert(`Cambiando período a: ${period}`, 'info', 2000);
    // Here you would typically make an AJAX call to update the chart data
    setTimeout(() => {
        location.reload();
    }, 1000);
}
</script>
@endpush
