@extends('layouts.contributor')

@section('title', 'Mis Tickets - Colaborador')

@section('content')
<div class="d-flex justify-content-end mb-3">
    <a href="{{ route('contributors.reports.requesters') }}" class="btn btn-outline-primary btn-sm">
        <i class="fas fa-user-clock me-1"></i>Solicitantes frecuentes
    </a>
</div>
<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card stat-card border-primary">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Total Asignados</h6>
                        <h2 class="mb-0">{{ $stats['total'] }}</h2>
                    </div>
                    <div class="stat-icon text-primary">
                        <i class="fas fa-tasks"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card stat-card border-secondary">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Pendientes</h6>
                        <h2 class="mb-0">{{ $stats['pending'] }}</h2>
                    </div>
                    <div class="stat-icon text-secondary">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card stat-card border-warning">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">En Progreso</h6>
                        <h2 class="mb-0">{{ $stats['in_progress'] }}</h2>
                    </div>
                    <div class="stat-icon text-warning">
                        <i class="fas fa-spinner"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card stat-card border-success">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Completados</h6>
                        <h2 class="mb-0">{{ $stats['completed'] }}</h2>
                    </div>
                    <div class="stat-icon text-success">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Ratings Cards -->
<div class="row mb-4">
    <!-- Average Rating -->
    <div class="col-md-4 mb-3">
        <div class="card shadow h-100">
            <div class="card-body text-center d-flex flex-column justify-content-center">
                <h6 class="text-muted text-uppercase mb-2">Promedio de Calificación</h6>
                <div class="display-3 fw-bold text-warning mb-2">{{ number_format($averageRating, 1) }}</div>
                <div class="mb-1" style="font-size: 1.5rem;">
                    @for($i = 1; $i <= 5; $i++)
                        @if($i <= round($averageRating))
                            <i class="fas fa-star text-warning"></i>
                        @else
                            <i class="far fa-star text-warning"></i>
                        @endif
                    @endfor
                </div>
                <small class="text-muted">Basado en los servicios completados</small>
            </div>
        </div>
    </div>
    
    <!-- Rating Distribution -->
    <div class="col-md-8 mb-3">
        <div class="card shadow h-100">
            <div class="card-body">
                <h6 class="text-muted text-uppercase mb-3">Distribución de Calificaciones</h6>
                @php
                    $totalRatings = $ratingDistribution->sum();
                @endphp
                
                @for($star = 5; $star >= 1; $star--)
                    @php
                        $count = $ratingDistribution->get($star, 0);
                        $percentage = $totalRatings > 0 ? ($count / $totalRatings) * 100 : 0;
                    @endphp
                    <div class="d-flex align-items-center mb-2">
                        <div class="text-nowrap me-3" style="width: 80px;">
                            {{ $star }} <i class="fas fa-star text-warning"></i>
                        </div>
                        <div class="progress flex-grow-1" style="height: 10px;">
                            <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $percentage }}%" aria-valuenow="{{ $percentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <div class="ms-3 text-end" style="width: 40px; font-size: 0.9rem;">
                            {{ $count }}
                        </div>
                    </div>
                @endfor
                
                @if($totalRatings == 0)
                    <div class="text-center mt-3 text-muted">
                        <small>Aún no has recibido calificaciones.</small>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Header -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Tickets Asignados</h1>
</div>

<!-- Filtro por estado -->
<div class="row mb-3">
    <div class="col-md-5 col-lg-4">
        <form method="GET" action="{{ route('contributors.dashboard') }}" class="border rounded p-2">
            <label class="form-label form-label-sm fw-semibold mb-1">
                <i class="fas fa-filter me-1"></i>Filtrar por estado
            </label>
            <div class="d-flex gap-2">
                <select class="form-select form-select-sm" name="status" onchange="this.form.submit()">
                    <option value="">Todos</option>
                    <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Pendiente</option>
                    <option value="2" {{ request('status') == '2' ? 'selected' : '' }}>En Progreso</option>
                    <option value="3" {{ request('status') == '3' ? 'selected' : '' }}>Completado</option>
                    <option value="4" {{ request('status') == '4' ? 'selected' : '' }}>Cancelado</option>
                </select>
                <a href="{{ route('contributors.dashboard') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-eraser"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Tickets Table (desktop) -->
<div class="card shadow d-none d-md-block">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-dark">
                    <tr>
                        <th># Ticket</th>
                        <th>Título</th>
                        <th>Tipo</th>
                        <th>Rol</th>
                        <th>Solicitante</th>
                        <th>Estado</th>
                        <th>Calificación</th>
                        <th>Fecha</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tickets as $ticket)
                    <tr class="{{ (int) $ticket->priority === 4 ? 'table-danger' : ((int) $ticket->priority === 3 ? 'table-warning' : '') }}">
                        <td>#{{ $ticket->ticket_number }}</td>
                        <td>
                            <div class="fw-bold">
                                @if((int) $ticket->priority === 4)
                                    <span class="badge bg-danger me-1"><i class="fas fa-bolt"></i> Urgente</span>
                                @elseif((int) $ticket->priority === 3)
                                    <span class="badge bg-warning text-dark me-1"><i class="fas fa-exclamation-triangle"></i> Alta</span>
                                @endif
                                {{ $ticket->title }}
                            </div>
                            <small class="text-muted">{{ Str::limit(strip_tags($ticket->requester_info), 50) }}</small>
                        </td>
                        <td>
                            @if($ticket->requestType)
                            <span class="badge" style="background-color: {{ $ticket->requestType->type_color }}">
                                <i class="fas {{ $ticket->requestType->type_icon }} me-1"></i>
                                {{ $ticket->requestType->type_name }}
                            </span>
                            @endif
                        </td>
                        <td>
                            @php
                                $assignment = $ticket->assignments->first();
                            @endphp
                            @if($assignment)
                                <span class="badge" style="background-color: {{ $assignment->jobPosition->position_color ?? '#6c757d' }}">
                                    {{ $assignment->jobPosition->position_name ?? 'General' }}
                                </span>
                            @elseif($ticket->mediator_id == Auth::id())
                                <span class="badge bg-primary">Mediador Principal</span>
                            @else
                                <span class="badge bg-secondary">Colaborador</span>
                            @endif
                        </td>
                        <td>{{ $ticket->requester->user_name }}</td>
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
                            @if(!is_null($ticket->rating))
                                <span class="text-warning small fw-semibold">
                                    {{ number_format($ticket->rating, 1) }} <i class="fas fa-star"></i>
                                </span>
                            @elseif((int)$ticket->status === 3)
                                <span class="badge bg-warning text-dark">Pendiente</span>
                            @else
                                <span class="text-muted small">N/A</span>
                            @endif
                        </td>
                        <td>{{ $ticket->created_at->format('d/m/Y') }}</td>
                        <td>
                            <a href="{{ route('contributors.tickets.show', $ticket->ticket_id) }}" 
                               class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye"></i> Ver
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">
                            <i class="fas fa-inbox fa-3x mb-3"></i>
                            <p>No tienes tickets asignados</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($tickets->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $tickets->withQueryString()->links('pagination::bootstrap-5') }}
        </div>
        @endif
    </div>
</div>

<!-- Tickets List (mobile) -->
<div class="d-md-none">
    @forelse($tickets as $ticket)
        @php
            $assignment = $ticket->assignments->first();
            $statusColors = [1 => 'secondary', 2 => 'warning', 3 => 'success', 4 => 'danger'];
            $statusNames = [1 => 'Pendiente', 2 => 'En Progreso', 3 => 'Completado', 4 => 'Cancelado'];
        @endphp
        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                    <strong class="text-break">#{{ $ticket->ticket_number }}</strong>
                    <span class="badge bg-{{ $statusColors[$ticket->status] ?? 'secondary' }}">
                        {{ $statusNames[$ticket->status] ?? 'Desconocido' }}
                    </span>
                </div>

                <h6 class="mb-1 text-break">{{ $ticket->title }}</h6>
                <p class="small text-muted mb-2">{{ Str::limit(strip_tags($ticket->requester_info), 80) }}</p>

                <div class="d-flex flex-wrap gap-2 mb-2">
                    @if($ticket->requestType)
                    <span class="badge" style="background-color: {{ $ticket->requestType->type_color }}">
                        <i class="fas {{ $ticket->requestType->type_icon }} me-1"></i>{{ $ticket->requestType->type_name }}
                    </span>
                    @endif

                    @if($assignment)
                        <span class="badge" style="background-color: {{ $assignment->jobPosition->position_color ?? '#6c757d' }}">
                            {{ $assignment->jobPosition->position_name ?? 'General' }}
                        </span>
                    @elseif($ticket->mediator_id == Auth::id())
                        <span class="badge bg-primary">Mediador Principal</span>
                    @else
                        <span class="badge bg-secondary">Colaborador</span>
                    @endif
                </div>

                <p class="small text-muted mb-3">
                    Solicitante: {{ $ticket->requester->user_name }} · {{ $ticket->created_at->format('d/m/Y') }}
                </p>

                <p class="small mb-3">
                    <span class="text-muted">Calificación:</span>
                    @if(!is_null($ticket->rating))
                        <span class="text-warning fw-semibold">{{ number_format($ticket->rating, 1) }} <i class="fas fa-star"></i></span>
                    @elseif((int)$ticket->status === 3)
                        <span class="badge bg-warning text-dark">Pendiente</span>
                    @else
                        <span class="text-muted">N/A</span>
                    @endif
                </p>

                <a href="{{ route('contributors.tickets.show', $ticket->ticket_id) }}" class="btn btn-outline-primary btn-sm w-100">
                    <i class="fas fa-eye me-1"></i>Ver Ticket
                </a>
            </div>
        </div>
    @empty
        <div class="card shadow-sm">
            <div class="card-body text-center text-muted py-4">
                <i class="fas fa-inbox fa-2x mb-3"></i>
                <p class="mb-0">No tienes tickets asignados</p>
            </div>
        </div>
    @endforelse

    @if($tickets->hasPages())
    <div class="d-flex justify-content-center mt-3">
        {{ $tickets->withQueryString()->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>
@endsection

@push('styles')
<style>
@media (max-width: 991.98px) {
    .stat-card .card-body {
        padding: 1rem;
    }

    .stat-card h2 {
        font-size: 1.5rem;
    }

    .stat-icon {
        font-size: 1.75rem;
    }
}

@media (max-width: 767.98px) {
    .display-3 {
        font-size: 2.1rem;
    }

    .progress[style*="height: 10px"] {
        height: 8px !important;
    }
}

@media (max-width: 399.98px) {
    .h2 {
        font-size: 1.2rem;
    }

    .stat-card .card-body {
        padding: 0.8rem;
    }

    .stat-card h6 {
        font-size: 0.78rem;
    }

    .stat-card h2 {
        font-size: 1.3rem;
    }

    .stat-icon {
        font-size: 1.45rem;
    }

    .display-3 {
        font-size: 1.8rem;
    }

    .card-body .small {
        font-size: 0.76rem !important;
    }

    .btn.btn-sm.w-100 {
        font-size: 0.82rem;
        padding-top: 0.35rem;
        padding-bottom: 0.35rem;
    }
}
</style>
@endpush
