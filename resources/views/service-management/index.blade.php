@extends('layouts.requester')

@section('title', 'Mis Solicitudes - Virtual Center')

@section('content')
<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card stat-card border-primary">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Total</h6>
                        <h2 class="mb-0">{{ $stats['total'] }}</h2>
                    </div>
                    <div class="stat-icon text-primary">
                        <i class="fas fa-ticket-alt"></i>
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

<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
        <h1 class="h2">Mis Solicitudes</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="{{ route('service-management.create') }}" class="btn btn-gradient">
                <i class="fas fa-plus me-1"></i>Nueva Solicitud
            </a>
        </div>
    </div>

    <!-- Projects Table -->
    <div class="card shadow">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="projectsTable">
                    <thead class="table-dark">
                        <tr>
                            <th># Ticket</th>
                            <th>Título</th>
                            <th>Estado</th>
                            <th>Fecha Creación</th>
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
                                @php
                                    $statusColors = [
                                        1 => 'secondary', // Pending
                                        2 => 'warning',   // In Progress
                                        3 => 'success',   // Completed
                                        4 => 'danger'     // Cancelled
                                    ];
                                    $statusNames = [
                                        1 => 'Pendiente',
                                        2 => 'En Progreso',
                                        3 => 'Completado',
                                        4 => 'Cancelado'
                                    ];
                                @endphp
                                <span class="badge bg-{{ $statusColors[$ticket->status] ?? 'secondary' }}">
                                    {{ $statusNames[$ticket->status] ?? 'Desconocido' }}
                                </span>
                            </td>
                            <td>{{ $ticket->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('service-management.show', $ticket->ticket_id) }}" 
                                       class="btn btn-outline-primary" title="Ver Detalles">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                <i class="fas fa-inbox fa-3x mb-3"></i>
                                <p>No tienes solicitudes registradas</p>
                                <a href="{{ route('service-management.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>Crear Primera Solicitud
                                </a>
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
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Search functionality (if needed later)
    $('#searchInput').on('keyup', function() {
        const value = $(this).val().toLowerCase();
        $('#projectsTable tbody tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });
});
</script>
@endpush
