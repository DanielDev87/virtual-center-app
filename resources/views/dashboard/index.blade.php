@extends('layouts.admin')

@section('title', 'Dashboard - Admin')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
        <h1 class="h2"><i class="fas fa-chart-line me-2"></i>Dashboard</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="{{ route('admin.reports.index') }}" class="btn btn-sm btn-primary">
                <i class="fas fa-file-export me-1"></i>Reportes
            </a>
        </div>
    </div>

    <!-- Statistics Cards Row 1 -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-primary shadow h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small text-uppercase mb-1">Total Tickets</div>
                            <div class="h3 mb-0 font-weight-bold">{{ $stats['total_tickets'] }}</div>
                        </div>
                        <div>
                            <i class="fas fa-ticket-alt fa-2x text-primary opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-warning shadow h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small text-uppercase mb-1">Pendientes</div>
                            <div class="h3 mb-0 font-weight-bold">{{ $stats['pending_tickets'] }}</div>
                        </div>
                        <div>
                            <i class="fas fa-clock fa-2x text-warning opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-info shadow h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small text-uppercase mb-1">En Progreso</div>
                            <div class="h3 mb-0 font-weight-bold">{{ $stats['in_progress_tickets'] }}</div>
                        </div>
                        <div>
                            <i class="fas fa-spinner fa-2x text-info opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-success shadow h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small text-uppercase mb-1">Completados</div>
                            <div class="h3 mb-0 font-weight-bold">{{ $stats['completed_tickets'] }}</div>
                        </div>
                        <div>
                            <i class="fas fa-check-circle fa-2x text-success opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards Row 2 -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-secondary shadow h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small text-uppercase mb-1">Progreso Promedio</div>
                            <div class="h3 mb-0 font-weight-bold">{{ number_format($stats['avg_progress'], 1) }}%</div>
                        </div>
                        <div>
                            <i class="fas fa-chart-line fa-2x text-secondary opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-danger shadow h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small text-uppercase mb-1">Alta Prioridad</div>
                            <div class="h3 mb-0 font-weight-bold">{{ $stats['high_priority'] }}</div>
                        </div>
                        <div>
                            <i class="fas fa-exclamation-triangle fa-2x text-danger opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-primary shadow h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small text-uppercase mb-1">Usuarios Activos</div>
                            <div class="h3 mb-0 font-weight-bold">{{ $stats['total_users'] }}</div>
                        </div>
                        <div>
                            <i class="fas fa-users fa-2x text-primary opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-info shadow h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small text-uppercase mb-1">Roles Activos</div>
                            <div class="h3 mb-0 font-weight-bold">{{ $stats['total_roles'] }}</div>
                        </div>
                        <div>
                            <i class="fas fa-user-tag fa-2x text-info opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Recent Tickets -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="fas fa-list me-2"></i>Tickets Recientes</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th># Ticket</th>
                                    <th>Título</th>
                                    <th>Tipo</th>
                                    <th>Estado</th>
                                    <th>Progreso</th>
                                    <th>Fecha</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentTickets as $ticket)
                                <tr>
                                    <td><a href="{{ route('admin.tickets.show', $ticket->ticket_id) }}">#{{ $ticket->ticket_number }}</a></td>
                                    <td>{{ Str::limit($ticket->title, 30) }}</td>
                                    <td>
                                        @if($ticket->requestType)
                                        <span class="badge" style="background-color: {{ $ticket->requestType->type_color }}">
                                            {{ $ticket->requestType->type_name }}
                                        </span>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $statusColors = [1 => 'secondary', 2 => 'warning', 3 => 'success', 4 => 'danger'];
                                            $statusNames = [1 => 'Pendiente', 2 => 'En Progreso', 3 => 'Completado', 4 => 'Cancelado'];
                                        @endphp
                                        <span class="badge bg-{{ $statusColors[$ticket->status] ?? 'secondary' }}">
                                            {{ $statusNames[$ticket->status] ?? 'Desconocido' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="progress" style="height: 20px; width: 80px;">
                                            <div class="progress-bar" role="progressbar" style="width: {{ $ticket->progress_percentage }}%">
                                                {{ $ticket->progress_percentage }}%
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $ticket->created_at->format('d/m/Y') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">No hay tickets</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Urgent Tickets -->
            @if($urgentTickets->count() > 0)
            <div class="card shadow border-danger">
                <div class="card-header bg-danger text-white">
                    <h5 class="card-title mb-0"><i class="fas fa-exclamation-circle me-2"></i>Tickets Urgentes</h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        @foreach($urgentTickets as $ticket)
                        <a href="{{ route('admin.tickets.show', $ticket->ticket_id) }}" class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">#{{ $ticket->ticket_number }} - {{ $ticket->title }}</h6>
                                <small>{{ $ticket->created_at->diffForHumans() }}</small>
                            </div>
                            <p class="mb-1 small text-muted">Solicitante: {{ $ticket->requester->user_name }}</p>
                        </a>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Charts and Stats -->
        <div class="col-lg-4 mb-4">
            <!-- By Status -->
            <div class="card shadow mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="fas fa-chart-pie me-2"></i>Por Estado</h5>
                </div>
                <div class="card-body">
                    @foreach($ticketsByStatus as $status => $count)
                    <div class="mb-2">
                        <div class="d-flex justify-content-between">
                            <span>{{ $status }}</span>
                            <strong>{{ $count }}</strong>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar" style="width: {{ $stats['total_tickets'] > 0 ? ($count / $stats['total_tickets'] * 100) : 0 }}%"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- By ADDIE Phase -->
            <div class="card shadow mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="fas fa-layer-group me-2"></i>Por Fase ADDIE</h5>
                </div>
                <div class="card-body">
                    @forelse($ticketsByPhase as $phase => $count)
                    <div class="mb-2">
                        <div class="d-flex justify-content-between">
                            <span>{{ $phase }}</span>
                            <strong>{{ $count }}</strong>
                        </div>
                    </div>
                    @empty
                    <p class="text-muted text-center">No hay datos</p>
                    @endforelse
                </div>
            </div>

            <!-- Top Collaborators -->
            <div class="card shadow">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="fas fa-trophy me-2"></i>Top Colaboradores</h5>
                </div>
                <div class="card-body">
                    @forelse($topCollaborators as $collaborator)
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="d-flex align-items-center">
                            <div class="me-2">
                                <i class="fas fa-user-circle fa-2x text-primary"></i>
                            </div>
                            <div>
                                <div class="fw-bold">{{ $collaborator->user_name }}</div>
                                <small class="text-muted">{{ $collaborator->completed_count }} completados</small>
                            </div>
                        </div>
                        <span class="badge bg-success">{{ $collaborator->completed_count }}</span>
                    </div>
                    @empty
                    <p class="text-muted text-center">No hay datos</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- New Metrics Row -->
    <div class="row mb-4">
        <!-- Average Rating Card -->
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-warning shadow h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small text-uppercase mb-1">Calificación Promedio</div>
                            <div class="h3 mb-0 font-weight-bold">
                                {{ number_format($averageRating, 2) }} 
                                <span class="h5 text-warning">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="fas fa-star {{ $i <= round($averageRating) ? '' : 'text-muted opacity-25' }}"></i>
                                    @endfor
                                </span>
                            </div>
                        </div>
                        <div>
                            <i class="fas fa-star fa-2x text-warning opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Average Completion Time Card -->
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-info shadow h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small text-uppercase mb-1">Tiempo Promedio</div>
                            <div class="h3 mb-0 font-weight-bold">
                                {{ number_format($avgCompletionTime, 1) }} hrs
                            </div>
                            <small class="text-muted">De resolución</small>
                        </div>
                        <div>
                            <i class="fas fa-hourglass-half fa-2x text-info opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Fastest Resolution Card -->
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-success shadow h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small text-uppercase mb-1">Mejor Tiempo</div>
                            <div class="h3 mb-0 font-weight-bold">
                                @if($fastestTickets->count() > 0)
                                    {{ number_format($fastestTickets->first()->completion_hours, 1) }} hrs
                                @else
                                    N/A
                                @endif
                            </div>
                            <small class="text-muted">Más rápido</small>
                        </div>
                        <div>
                            <i class="fas fa-bolt fa-2x text-success opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <!-- Rating Distribution Chart -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="fas fa-chart-pie me-2"></i>Distribución de Calificaciones</h5>
                </div>
                <div class="card-body">
                    <canvas id="ratingChart" height="200"></canvas>
                </div>
            </div>
        </div>

        <!-- Fastest Tickets Table -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="fas fa-tachometer-alt me-2"></i>Tickets Más Rápidos</h5>
                </div>
                <div class="card-body">
                    @if($fastestTickets->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>Ticket</th>
                                        <th>Solicitante</th>
                                        <th class="text-end">Tiempo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($fastestTickets as $ticket)
                                    <tr>
                                        <td>
                                            <a href="{{ route('admin.tickets.show', $ticket->ticket_id) }}">
                                                #{{ $ticket->ticket_number }}
                                            </a>
                                        </td>
                                        <td>{{ $ticket->requester->user_name ?? 'N/A' }}</td>
                                        <td class="text-end">
                                            <span class="badge bg-success">
                                                {{ number_format($ticket->completion_hours, 1) }} hrs
                                            </span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted text-center mb-0">No hay tickets completados aún</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
// Rating Distribution Chart
const ratingCtx = document.getElementById('ratingChart');
if (ratingCtx) {
    new Chart(ratingCtx, {
        type: 'pie',
        data: {
            labels: {!! json_encode($ratingDistribution->keys()) !!},
            datasets: [{
                data: {!! json_encode($ratingDistribution->values()) !!},
                backgroundColor: [
                    '#dc3545', // 1 star - red
                    '#fd7e14', // 2 stars - orange
                    '#ffc107', // 3 stars - yellow
                    '#20c997', // 4 stars - teal
                    '#28a745', // 5 stars - green
                ],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.label || '';
                            if (label) {
                                label += ': ';
                            }
                            label += context.parsed + ' tickets';
                            return label;
                        }
                    }
                }
            }
        }
    });
}
</script>
@endsection

