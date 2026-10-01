@extends('layouts.app')

@section('title', 'Monitor del Sistema - Virtual Center')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap align-items-center pt-3 pb-2 mb-3 border-bottom gap-2">
        <h1 class="h2 mb-0">Panel de Auditoria y Monitoreo</h1>
        <div class="btn-toolbar mb-0 w-100 justify-content-end monitor-toolbar">
            <button type="button" class="btn btn-sm btn-outline-secondary w-100 monitor-toolbar-btn" onclick="window.location.reload()">
                <i class="fas fa-sync-alt me-1"></i>Actualizar
            </button>
        </div>
    </div>

    <div class="alert alert-info monitor-audit-banner">
        <i class="fas fa-eye me-1"></i>
        Esta vista es de solo lectura para auditoria. Puedes revisar toda la informacion operativa sin modificar datos.
    </div>

    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2 monitor-stat-card">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Tickets Totales
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-body-emphasis">{{ $stats['total_tickets'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-ticket-alt fa-2x text-body-tertiary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2 monitor-stat-card">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Tickets Completados
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-body-emphasis">{{ $stats['completed_tickets'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-body-tertiary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2 monitor-stat-card">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Tickets en Atencion
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-body-emphasis">{{ $stats['open_tickets'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-spinner fa-2x text-body-tertiary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2 monitor-stat-card">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Usuarios Activos
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-body-emphasis">{{ $stats['active_users'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-body-tertiary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-danger shadow h-100 py-2 monitor-stat-card">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Tickets Vencidos SLA</div>
                    <div class="h5 mb-0 font-weight-bold text-body-emphasis">{{ $stats['overdue_tickets'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-secondary shadow h-100 py-2 monitor-stat-card">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">Tickets Cancelados</div>
                    <div class="h5 mb-0 font-weight-bold text-body-emphasis">{{ $stats['cancelled_tickets'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-primary shadow h-100 py-2 monitor-stat-card">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Topicos Registrados</div>
                    <div class="h5 mb-0 font-weight-bold text-body-emphasis">{{ $stats['request_types'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-success shadow h-100 py-2 monitor-stat-card">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Calificacion Promedio</div>
                    <div class="h5 mb-0 font-weight-bold text-body-emphasis">{{ number_format($stats['average_rating'], 1) }}/5</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Acceso a Recursos (Solo Lectura)</h6>
                </div>
                <div class="card-body d-flex flex-wrap gap-2 monitor-links-grid">
                    <a href="{{ route('admin.tickets.index') }}" class="btn btn-outline-primary btn-sm"><i class="fas fa-ticket-alt me-1"></i>Tickets</a>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-primary btn-sm"><i class="fas fa-users me-1"></i>Usuarios</a>
                    <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-primary btn-sm"><i class="fas fa-user-tag me-1"></i>Roles</a>
                    <a href="{{ route('admin.request-types.index') }}" class="btn btn-outline-primary btn-sm"><i class="fas fa-tags me-1"></i>Topicos</a>
                    <a href="{{ route('admin.job-positions.index') }}" class="btn btn-outline-primary btn-sm"><i class="fas fa-briefcase me-1"></i>Puestos</a>
                    <a href="{{ route('admin.academic.institutions.index') }}" class="btn btn-outline-primary btn-sm"><i class="fas fa-university me-1"></i>Instituciones</a>
                    <a href="{{ route('admin.academic.faculties.index') }}" class="btn btn-outline-primary btn-sm"><i class="fas fa-building me-1"></i>Facultades</a>
                    <a href="{{ route('admin.academic.areas.index') }}" class="btn btn-outline-primary btn-sm"><i class="fas fa-sitemap me-1"></i>Areas</a>
                    <a href="{{ route('admin.academic.programs.index') }}" class="btn btn-outline-primary btn-sm"><i class="fas fa-graduation-cap me-1"></i>Programas</a>
                    <a href="{{ route('admin.academic.courses.index') }}" class="btn btn-outline-primary btn-sm"><i class="fas fa-book me-1"></i>Cursos</a>
                    <a href="{{ route('admin.reports.index') }}" class="btn btn-outline-primary btn-sm"><i class="fas fa-file-export me-1"></i>Reportes</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Timeline de Tickets (Ultimos 7 dias)</h6>
                </div>
                <div class="card-body">
                    <div class="chart-area monitor-chart-box">
                        <canvas id="projectsTimelineChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Tickets por Estado</h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie pt-4 pb-2 monitor-chart-box">
                        <canvas id="statusDistributionChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Tickets Recientes</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive d-none d-md-block">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th># Ticket</th>
                                    <th>Titulo</th>
                                    <th>Solicitante</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody id="ticketsTable">
                                @forelse($recentTickets as $ticket)
                                <tr>
                                    <td>#{{ $ticket->ticket_number }}</td>
                                    <td>{{ \Illuminate\Support\Str::limit($ticket->title, 40) }}</td>
                                    <td>{{ $ticket->requester->user_name ?? 'N/A' }}</td>
                                    <td>
                                        @php
                                            $statusMap = [1 => 'Pendiente', 2 => 'En Progreso', 3 => 'Completado', 4 => 'Cancelado'];
                                        @endphp
                                        <span class="badge bg-secondary">{{ $statusMap[$ticket->status] ?? 'N/A' }}</span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">No hay actividades recientes</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="d-md-none">
                        @forelse($recentTickets as $ticket)
                        @php
                            $statusMap = [1 => 'Pendiente', 2 => 'En Progreso', 3 => 'Completado', 4 => 'Cancelado'];
                        @endphp
                        <div class="border rounded p-2 mb-2">
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
                                <strong>#{{ $ticket->ticket_number }}</strong>
                                <span class="badge bg-secondary">{{ $statusMap[$ticket->status] ?? 'N/A' }}</span>
                            </div>
                            <div class="small text-body text-break">{{ \Illuminate\Support\Str::limit($ticket->title, 65) }}</div>
                            <div class="small text-muted">{{ $ticket->requester->user_name ?? 'N/A' }}</div>
                        </div>
                        @empty
                        <div class="text-center text-muted py-3">No hay actividades recientes</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Actividad Reciente del Proceso</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive d-none d-md-block">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Usuario</th>
                                    <th>Ticket</th>
                                    <th>Estado</th>
                                    <th>Fecha</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentActivities as $activity)
                                <tr>
                                    <td>{{ $activity->user->user_name ?? 'N/A' }}</td>
                                    <td>#{{ $activity->ticket->ticket_number ?? 'N/A' }}</td>
                                    <td>{{ $activity->status_update ?? 'N/A' }}</td>
                                    <td>{{ $activity->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">No hay actividad registrada</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="d-md-none">
                        @forelse($recentActivities as $activity)
                        <div class="border rounded p-2 mb-2">
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
                                <strong class="text-break">{{ $activity->user->user_name ?? 'N/A' }}</strong>
                                <small class="text-muted">{{ $activity->created_at->format('d/m/Y H:i') }}</small>
                            </div>
                            <div class="small">Ticket #{{ $activity->ticket->ticket_number ?? 'N/A' }}</div>
                            <div class="small text-muted text-break">{{ $activity->status_update ?? 'N/A' }}</div>
                        </div>
                        @empty
                        <div class="text-center text-muted py-3">No hay actividad registrada</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Usuarios por Rol</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive d-none d-md-block">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Rol</th>
                                    <th>Usuarios</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($usersByRole as $role)
                                <tr>
                                    <td>{{ $role->role_name }}</td>
                                    <td>{{ $role->users_count }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="2" class="text-center text-muted">No hay datos de roles</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="d-md-none">
                        @forelse($usersByRole as $role)
                        <div class="d-flex justify-content-between border rounded p-2 mb-2">
                            <strong>{{ $role->role_name }}</strong>
                            <span class="badge bg-primary">{{ $role->users_count }}</span>
                        </div>
                        @empty
                        <div class="text-center text-muted py-3">No hay datos de roles</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h6 class="m-0 font-weight-bold text-primary">Alertas de Auditoria</h6>
                    <button class="btn btn-sm btn-outline-secondary w-100 monitor-export-btn" type="button" onclick="exportTicketsCsv()">
                        <i class="fas fa-download me-1"></i>Exportar Tickets
                    </button>
                </div>
                <div class="card-body">
                    @forelse($systemAlerts as $alert)
                    <div class="alert alert-{{ $alert['alert_level'] === 'critical' ? 'danger' : 'info' }}" role="alert">
                        <strong>{{ $alert['alert_title'] }}</strong>
                        <p class="mb-0">{{ $alert['alert_message'] }}</p>
                    </div>
                        @empty
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-shield-alt fa-3x mb-3"></i>
                        <p>No hay alertas activas.</p>
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
$(document).ready(function() {
    initializeCharts();
});

function initializeCharts() {
    const isSmallMobile = window.matchMedia('(max-width: 399.98px)').matches;
    const isMobile = window.matchMedia('(max-width: 767.98px)').matches;
    const rootStyles = getComputedStyle(document.documentElement);

    const bodyColor = (rootStyles.getPropertyValue('--bs-body-color') || '#212529').trim();
    const secondaryColor = (rootStyles.getPropertyValue('--bs-secondary-color') || '#6c757d').trim();
    const borderColor = (rootStyles.getPropertyValue('--bs-border-color') || 'rgba(0,0,0,0.12)').trim();

    const legendFontSize = isSmallMobile ? 10 : (isMobile ? 11 : 12);
    const tickFontSize = isSmallMobile ? 9 : (isMobile ? 10 : 11);

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
                pointRadius: isMobile ? 2 : 3,
                pointHoverRadius: isMobile ? 3 : 4,
                borderWidth: isMobile ? 2 : 2.5,
                tension: 0.2
            }, {
                label: 'Proyectos Completados',
                data: {!! json_encode($timelineData['completed']) !!},
                borderColor: 'rgb(54, 162, 235)',
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                pointRadius: isMobile ? 2 : 3,
                pointHoverRadius: isMobile ? 3 : 4,
                borderWidth: isMobile ? 2 : 2.5,
                tension: 0.2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            layout: {
                padding: {
                    top: isMobile ? 4 : 8,
                    right: isMobile ? 6 : 10,
                    bottom: isMobile ? 2 : 6,
                    left: isMobile ? 2 : 6
                }
            },
            scales: {
                x: {
                    ticks: {
                        color: secondaryColor,
                        autoSkip: true,
                        maxTicksLimit: isSmallMobile ? 4 : (isMobile ? 5 : 7),
                        maxRotation: 0,
                        font: {
                            size: tickFontSize
                        }
                    },
                    grid: {
                        color: borderColor,
                        drawBorder: false
                    }
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        color: secondaryColor,
                        precision: 0,
                        font: {
                            size: tickFontSize
                        }
                    },
                    grid: {
                        color: borderColor,
                        drawBorder: false
                    }
                }
            },
            plugins: {
                legend: {
                    position: 'top',
                    align: isMobile ? 'start' : 'center',
                    labels: {
                        color: bodyColor,
                        boxWidth: isSmallMobile ? 10 : 14,
                        boxHeight: isSmallMobile ? 10 : 12,
                        padding: isSmallMobile ? 10 : 14,
                        font: {
                            size: legendFontSize
                        }
                    }
                },
                tooltip: {
                    bodyFont: {
                        size: isSmallMobile ? 10 : 12
                    },
                    titleFont: {
                        size: isSmallMobile ? 11 : 12
                    }
                }
            }
        }
    });

    const statusCtx = document.getElementById('statusDistributionChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($statusDistribution['labels']) !!},
            datasets: [{
                data: {!! json_encode($statusDistribution['data']) !!},
                backgroundColor: ['#6c757d', '#ffc107', '#198754', '#dc3545'],
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: isSmallMobile ? '64%' : (isMobile ? '60%' : '54%'),
            layout: {
                padding: {
                    top: isMobile ? 0 : 6,
                    right: isMobile ? 0 : 6,
                    bottom: isMobile ? 0 : 6,
                    left: isMobile ? 0 : 6
                }
            },
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        color: bodyColor,
                        boxWidth: isSmallMobile ? 10 : 12,
                        boxHeight: isSmallMobile ? 10 : 12,
                        padding: isSmallMobile ? 8 : 12,
                        font: {
                            size: legendFontSize
                        }
                    }
                },
                tooltip: {
                    bodyFont: {
                        size: isSmallMobile ? 10 : 12
                    },
                    titleFont: {
                        size: isSmallMobile ? 11 : 12
                    }
                }
            }
        }
    });
}

function exportTicketsCsv() {
    const headers = ['Ticket','Titulo','Solicitante','Estado'];
    const rows = [];

    document.querySelectorAll('#ticketsTable tr').forEach(row => {
        const cells = Array.from(row.querySelectorAll('td')).map(td => td.innerText.trim());
        if (cells.length === 4) {
            rows.push(cells.join(','));
        }
    });

    const csvContent = [headers.join(','), ...rows].join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'auditoria-tickets-' + new Date().toISOString().split('T')[0] + '.csv';
    a.click();
    window.URL.revokeObjectURL(url);
}
</script>
@endpush

@push('styles')
<style>
.monitor-stat-card .card-body {
    padding: 0.85rem;
}

.monitor-chart-box {
    min-height: 240px;
}

.monitor-links-grid .btn {
    border-radius: 0.5rem;
}

@media (min-width: 768px) {
    .monitor-toolbar,
    .monitor-toolbar-btn,
    .monitor-export-btn {
        width: auto !important;
    }
}

@media (max-width: 767.98px) {
    .monitor-audit-banner {
        font-size: 0.92rem;
        line-height: 1.4;
    }

    .monitor-stat-card .text-xs {
        font-size: 0.68rem;
        letter-spacing: 0.04em;
    }

    .monitor-stat-card .h5 {
        font-size: 1.2rem;
    }

    .monitor-links-grid {
        display: grid !important;
        grid-template-columns: 1fr;
        gap: 0.5rem !important;
    }

    .monitor-links-grid .btn {
        width: 100%;
        justify-content: flex-start;
        text-align: left;
        padding-top: 0.45rem;
        padding-bottom: 0.45rem;
    }

    .monitor-chart-box {
        min-height: 220px;
    }
}

@media (max-width: 399.98px) {
    .monitor-audit-banner {
        font-size: 0.84rem;
        padding: 0.65rem 0.7rem;
    }

    .monitor-stat-card .card-body {
        padding: 0.7rem;
    }

    .monitor-stat-card .h5 {
        font-size: 1.05rem;
    }

    .monitor-chart-box {
        min-height: 190px;
    }

    .monitor-links-grid .btn,
    .monitor-export-btn,
    .monitor-toolbar-btn {
        font-size: 0.8rem;
    }
}
</style>
@endpush


