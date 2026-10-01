@extends('layouts.area-admin')

@section('title', 'Dashboard - ' . ($area->area_name ?? 'Área'))

@section('content')
<div class="container-fluid">
    @if($returnedTickets->isNotEmpty())
    <div class="alert alert-warning shadow-sm">
        <h5 class="alert-heading"><i class="fas fa-undo me-2"></i>Tickets devueltos por Operarios</h5>
        <p class="mb-2">Un Operario devolvió tickets que asignaste:</p>
        <ul class="mb-0">
            @foreach($returnedTickets as $assignment)
                <li>
                    <form method="POST" action="{{ route('returned-alerts.read', $assignment->assignment_id) }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-link p-0 align-baseline">#{{ $assignment->ticket->ticket_number }} - {{ $assignment->ticket->title }}</button>
                    </form>
                    <span class="text-muted">({{ $assignment->notes }})</span>
                </li>
            @endforeach
        </ul>
    </div>
    @endif
    @if($associationRequests->isNotEmpty())
    <div class="alert alert-primary shadow-sm">
        <h5 class="alert-heading"><i class="fas fa-link me-2"></i>Solicitudes de asociación pendientes</h5>
        <p class="mb-2">Contributors solicitaron asociar tickets relacionados:</p>
        <ul class="mb-0">
            @foreach($associationRequests as $associationRequest)
                <li>
                    <a href="{{ route('area-admin.tickets.show', $associationRequest->parent_ticket_id) }}">
                        Ticket principal #{{ $associationRequest->parentTicket->ticket_number }}
                    </a>
                    <span class="text-muted">
                        · {{ $associationRequest->requester->user_name ?? 'Contributor' }}
                        @if($associationRequest->request_group)
                            · solicitud múltiple
                        @endif
                    </span>
                </li>
            @endforeach
        </ul>
    </div>
    @endif
    <div class="d-flex justify-content-between flex-wrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <div>
            <h1 class="h2 mb-0">Dashboard</h1>
            <p class="text-muted mb-0 small">
                <i class="fas fa-sitemap me-1"></i>{{ $area->area_name ?? 'Área sin asignar' }}
                @if($area && $area->faculty)
                    &mdash; {{ $area->faculty->faculty_name }}
                @endif
            </p>
        </div>
    </div>

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(!empty($regionalScope['isRegionalScoped']) && $regionalScope['isRegionalScoped'])
    <div class="alert alert-info d-flex flex-wrap align-items-center gap-2">
        <i class="fas fa-map-marker-alt me-1"></i>
        <span class="fw-semibold">Vista filtrada por regional:</span>
        @foreach(($regionalScope['regionalScopeNames'] ?? collect()) as $regionalName)
            <span class="badge bg-primary-subtle text-primary border">{{ $regionalName }}</span>
        @endforeach
    </div>
    @endif

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-warning-subtle">
            <h5 class="mb-0"><i class="fas fa-triangle-exclamation me-2"></i>Incidencias por tópico</h5>
        </div>
        <div class="card-body">
            <p class="text-muted small">Activa una alerta para bloquear temporalmente nuevas solicitudes de un tópico mientras se resuelve una incidencia general.</p>
            @forelse($incidentTopics as $topic)
                <form method="POST" action="{{ route('area-admin.topics.incident', $topic->type_id) }}" class="border rounded p-3 mb-3">
                    @csrf
                    @method('PATCH')
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <strong>{{ $topic->type_name }}</strong>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="incident_active" value="1" id="incident_{{ $topic->type_id }}" {{ $topic->incident_active ? 'checked' : '' }}>
                            <label class="form-check-label" for="incident_{{ $topic->type_id }}">Bloquear tópico</label>
                        </div>
                    </div>
                    <div class="row g-2">
                        <div class="col-md-4">
                            <input type="text" name="incident_title" class="form-control" value="{{ $topic->incident_title }}" placeholder="Título de la alerta">
                        </div>
                        <div class="col-md-6">
                            <input type="text" name="incident_message" class="form-control" value="{{ $topic->incident_message }}" placeholder="Mensaje para los solicitantes">
                        </div>
                        <div class="col-md-2 d-grid">
                            <button type="submit" class="btn btn-outline-warning">Guardar</button>
                        </div>
                    </div>
                </form>
            @empty
                <p class="text-muted mb-0">No hay tópicos visibles para gestionar.</p>
            @endforelse
        </div>
    </div>

    <!-- Stats cards -->
    <div class="row g-3 mb-4">
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card shadow-sm h-100">
                <div class="card-body text-center py-3">
                    <div class="text-primary fw-bold fs-3">{{ $stats['total_tickets'] }}</div>
                    <div class="small text-muted">Total Tickets</div>
                    <i class="fas fa-ticket-alt text-primary opacity-25 fa-2x mt-1"></i>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card shadow-sm h-100">
                <div class="card-body text-center py-3">
                    <div class="text-warning fw-bold fs-3">{{ $stats['pending_tickets'] }}</div>
                    <div class="small text-muted">Pendientes</div>
                    <i class="fas fa-clock text-warning opacity-25 fa-2x mt-1"></i>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card shadow-sm h-100">
                <div class="card-body text-center py-3">
                    <div class="text-info fw-bold fs-3">{{ $stats['in_progress_tickets'] }}</div>
                    <div class="small text-muted">En Progreso</div>
                    <i class="fas fa-spinner text-info opacity-25 fa-2x mt-1"></i>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card shadow-sm h-100">
                <div class="card-body text-center py-3">
                    <div class="text-success fw-bold fs-3">{{ $stats['completed_tickets'] }}</div>
                    <div class="small text-muted">Completados</div>
                    <i class="fas fa-check-circle text-success opacity-25 fa-2x mt-1"></i>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card shadow-sm h-100">
                <div class="card-body text-center py-3">
                    <div class="text-danger fw-bold fs-3">{{ $stats['high_priority'] }}</div>
                    <div class="small text-muted">Alta Prioridad</div>
                    <i class="fas fa-exclamation-triangle text-danger opacity-25 fa-2x mt-1"></i>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card shadow-sm h-100">
                <div class="card-body text-center py-3">
                    <div class="text-warning fw-bold fs-3">
                        @if($stats['avg_rating'])
                            {{ number_format($stats['avg_rating'], 1) }} <i class="fas fa-star"></i>
                        @else
                            &mdash;
                        @endif
                    </div>
                    <div class="small text-muted">Calif. Promedio</div>
                    <i class="fas fa-star text-warning opacity-25 fa-2x mt-1"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <!-- Tickets por estado -->
        <div class="col-lg-4">
            <div class="card shadow-sm h-100">
                <div class="card-header fw-semibold"><i class="fas fa-chart-pie me-1"></i>Tickets por Estado</div>
                <div class="card-body">
                    @php
                        $statusColors = ['Pendiente' => 'warning', 'En Progreso' => 'info', 'Realizado por Operario' => 'primary', 'Completado' => 'success', 'Cancelado' => 'danger'];
                        $total = array_sum($ticketsByStatus);
                    @endphp
                    @foreach($ticketsByStatus as $label => $count)
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="badge bg-{{ $statusColors[$label] ?? 'secondary' }}">{{ $label }}</span>
                        <div class="d-flex align-items-center gap-2" style="flex: 1; margin-left: 0.75rem;">
                            <div class="progress flex-grow-1" style="height: 8px;">
                                <div class="progress-bar bg-{{ $statusColors[$label] ?? 'secondary' }}"
                                     style="width: {{ $total > 0 ? round($count / $total * 100) : 0 }}%"></div>
                            </div>
                            <span class="fw-bold small" style="min-width: 28px; text-align: right;">{{ $count }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Tickets por tópico -->
        <div class="col-lg-4">
            <div class="card shadow-sm h-100">
                <div class="card-header fw-semibold"><i class="fas fa-tags me-1"></i>Tickets por Tópico</div>
                <div class="card-body">
                    @forelse($ticketsByTopic as $topic => $count)
                    <div class="d-flex justify-content-between mb-2">
                        <span class="small text-truncate" style="max-width: 68%;">{{ $topic }}</span>
                        <span class="badge bg-primary rounded-pill">{{ $count }}</span>
                    </div>
                    @empty
                    <p class="text-muted small">Sin tópicos registrados para esta área.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Calificaciones recientes -->
        <div class="col-lg-4">
            <div class="card shadow-sm h-100">
                <div class="card-header fw-semibold"><i class="fas fa-star me-1 text-warning"></i>Calificaciones Recientes</div>
                <div class="card-body p-0">
                    @forelse($recentRatings as $rated)
                    <div class="px-3 py-2 border-bottom">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="small fw-semibold text-truncate" style="max-width: 65%;">
                                #{{ $rated->ticket_number }} {{ $rated->title }}
                            </span>
                            <span class="text-warning small">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="{{ $i <= $rated->rating ? 'fas' : 'far' }} fa-star"></i>
                                @endfor
                            </span>
                        </div>
                        <div class="text-muted" style="font-size: 0.75rem;">{{ $rated->requester->user_name ?? '' }}</div>
                    </div>
                    @empty
                    <p class="text-muted small p-3">Sin calificaciones aún.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos: Distribución de calificaciones + Tickets más rápidos -->
    <div class="row g-3 mb-4">
        <!-- Distribución de calificaciones -->
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header fw-semibold">
                    <i class="fas fa-chart-pie me-1 text-warning"></i>Distribución de Calificaciones
                </div>
                <div class="card-body d-flex align-items-center justify-content-center">
                    @if($ratingDistribution->isNotEmpty())
                        <canvas id="areaRatingChart" height="220"></canvas>
                    @else
                        <p class="text-muted text-center mb-0">Sin calificaciones registradas en el área aún.</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Tickets más rápidos del área -->
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header fw-semibold">
                    <i class="fas fa-tachometer-alt me-1 text-success"></i>Tickets Más Rápidos del Área
                </div>
                <div class="card-body p-0">
                    @if($fastestTickets->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Ticket</th>
                                    <th>Tópico</th>
                                    <th>Solicitante</th>
                                    <th class="text-end pe-3">Tiempo</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($fastestTickets as $ticket)
                                <tr>
                                    <td class="ps-3">
                                        <a href="{{ route('area-admin.tickets.show', $ticket->ticket_id) }}" class="text-decoration-none fw-semibold">
                                            #{{ $ticket->ticket_number }}
                                        </a>
                                    </td>
                                    <td>
                                        @if($ticket->requestType)
                                            <span class="badge" style="background-color: {{ $ticket->requestType->type_color ?? '#6c757d' }}; font-size: 0.72rem;">
                                                {{ $ticket->requestType->type_name }}
                                            </span>
                                        @else
                                            <span class="text-muted small">—</span>
                                        @endif
                                    </td>
                                    <td class="small text-muted">{{ $ticket->requester->user_name ?? 'N/A' }}</td>
                                    <td class="text-end pe-3">
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
                        <p class="text-muted small p-3 mb-0">No hay tickets completados aún en el área.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Evolución mensual por tópico del área -->
    <div class="row g-3 mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2 fw-semibold">
                    <span><i class="fas fa-chart-line me-1"></i>Evolución Mensual por Tópico (Top 5 del Área)</span>
                    <form action="{{ route('area-admin.dashboard') }}" method="GET" class="d-flex align-items-center gap-2">
                        <label for="topic_trend_months" class="small text-muted mb-0">Rango:</label>
                        <select id="topic_trend_months" name="topic_trend_months" class="form-select form-select-sm" style="width: auto;" onchange="this.form.submit()">
                            @foreach($allowedTopicTrendMonths as $monthOption)
                                <option value="{{ $monthOption }}" {{ (int)$topicTrendMonths === (int)$monthOption ? 'selected' : '' }}>
                                    Últimos {{ $monthOption }} meses
                                </option>
                            @endforeach
                        </select>
                    </form>
                </div>
                <div class="card-body">
                    @if($topicTrendDatasets->count() > 0)
                        <canvas id="areaTopicsChart" height="120"></canvas>
                    @else
                        <p class="text-muted text-center mb-0">No hay datos suficientes para mostrar tendencia por tópico en el área.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Tickets recientes -->
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center fw-semibold">
            <span><i class="fas fa-list me-1"></i>Tickets Recientes del Área</span>
            <a href="{{ route('area-admin.tickets.index') }}" class="btn btn-sm btn-outline-primary">
                Ver todos <i class="fas fa-arrow-right ms-1"></i>
            </a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3"># Ticket</th>
                            <th>Título</th>
                            <th>Tópico</th>
                            <th>Estado</th>
                            <th>Progreso</th>
                            <th>Fecha</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $statusColors = [1 => 'secondary', 2 => 'warning', 3 => 'success', 4 => 'danger'];
                            $statusNames  = [1 => 'Pendiente', 2 => 'En Progreso', 3 => 'Completado', 4 => 'Cancelado'];
                        @endphp
                        @forelse($recentTickets as $ticket)
                        <tr>
                            <td class="ps-3">#{{ $ticket->ticket_number }}</td>
                            <td class="text-truncate" style="max-width: 220px;">{{ $ticket->title }}</td>
                            <td>
                                @if($ticket->requestType)
                                    <span class="badge" style="background-color: {{ $ticket->requestType->type_color ?? '#6c757d' }}">
                                        {{ $ticket->requestType->type_name }}
                                    </span>
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $statusColors[$ticket->status] ?? 'secondary' }}">
                                    {{ $statusNames[$ticket->status] ?? '—' }}
                                </span>
                            </td>
                            <td style="min-width: 100px;">
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar" style="width: {{ $ticket->auto_progress }}%"></div>
                                </div>
                                <small class="text-muted">{{ $ticket->auto_progress }}%</small>
                            </td>
                            <td class="small text-muted">{{ $ticket->created_at->format('d/m/Y') }}</td>
                            <td>
                                <a href="{{ route('area-admin.tickets.show', $ticket->ticket_id) }}"
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No hay tickets registrados para esta área.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($recentTickets->hasPages())
        <div class="card-footer">
            {{ $recentTickets->links() }}
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
// Distribución de calificaciones del área
const areaRatingCtx = document.getElementById('areaRatingChart');
if (areaRatingCtx) {
    new Chart(areaRatingCtx, {
        type: 'pie',
        data: {
            labels: {!! json_encode($ratingDistribution->keys()) !!},
            datasets: [{
                data: {!! json_encode($ratingDistribution->values()) !!},
                backgroundColor: ['#dc3545','#fd7e14','#ffc107','#20c997','#28a745'],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' },
                tooltip: {
                    callbacks: {
                        label: function(ctx) {
                            return (ctx.label || '') + ': ' + ctx.parsed + ' tickets';
                        }
                    }
                }
            }
        }
    });
}

// Evolución mensual por tópico del área
const areaTopicsCtx = document.getElementById('areaTopicsChart');
if (areaTopicsCtx) {
    const trendLabels = {!! json_encode($topicTrendLabels) !!};
    const trendDatasetsRaw = {!! json_encode($topicTrendDatasets) !!};
    const palette = [
        'rgba(13,110,253,0.85)',
        'rgba(220,53,69,0.85)',
        'rgba(25,135,84,0.85)',
        'rgba(255,193,7,0.85)',
        'rgba(111,66,193,0.85)'
    ];

    const trendDatasets = trendDatasetsRaw.map(function(ds, idx) {
        const color = palette[idx % palette.length];
        return {
            label: ds.label,
            data: ds.data,
            borderColor: color,
            backgroundColor: color.replace('0.85', '0.15'),
            borderWidth: 2,
            pointRadius: 4,
            fill: true,
            tension: 0.3
        };
    });

    new Chart(areaTopicsCtx, {
        type: 'line',
        data: { labels: trendLabels, datasets: trendDatasets },
        options: {
            responsive: true,
            interaction: { mode: 'index', intersect: false },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 } }
            },
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });
}
</script>
@endpush
@endsection
