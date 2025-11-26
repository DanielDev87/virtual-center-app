@extends('layouts.admin')

@section('title', 'Dashboard - Admin')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
        <h1 class="h2"><i class="fas fa-chart-line me-2"></i>Dashboard</h1>
    </div>

    <!-- Statistics Cards -->
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

    <div class="row">
        <!-- Recent Tickets -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="fas fa-list me-2"></i>Tickets Recientes</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th># Ticket</th>
                                    <th>Título</th>
                                    <th>Tipo</th>
                                    <th>Estado</th>
                                    <th>Fecha</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentTickets as $ticket)
                                <tr>
                                    <td>#{{ $ticket->ticket_number }}</td>
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
                                    <td>{{ $ticket->created_at->format('d/m/Y') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">No hay tickets</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts -->
        <div class="col-lg-4 mb-4">
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

            <div class="card shadow">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="fas fa-chart-bar me-2"></i>Por Tipo</h5>
                </div>
                <div class="card-body">
                    @forelse($ticketsByType as $type => $count)
                    <div class="mb-2">
                        <div class="d-flex justify-content-between">
                            <span>{{ $type }}</span>
                            <strong>{{ $count }}</strong>
                        </div>
                    </div>
                    @empty
                    <p class="text-muted text-center">No hay datos</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
