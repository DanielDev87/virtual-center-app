@extends('layouts.contributor')

@section('title', 'Mis Tickets - Colaborador')

@section('content')
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

<!-- Header -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Tickets Asignados</h1>
</div>

<!-- Tickets Table -->
<div class="card shadow">
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
                        <th>Fecha</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tickets as $ticket)
                    <tr>
                        <td>#{{ $ticket->ticket_number }}</td>
                        <td>
                            <div class="fw-bold">{{ $ticket->title }}</div>
                            <small class="text-muted">{{ Str::limit($ticket->requester_info, 50) }}</small>
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
                                <span class="badge" style="background-color: {{ $assignment->jobPosition->position_color }}">
                                    {{ $assignment->jobPosition->position_name }}
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
                        <td colspan="7" class="text-center text-muted py-4">
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
            {{ $tickets->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
