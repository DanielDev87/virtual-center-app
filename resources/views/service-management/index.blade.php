@extends(Auth::user()->role->role_name == 'Requester' ? 'layouts.requester' : 'layouts.contributor')

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

@if($completedRequestsToRate->count() > 0)
<div class="alert alert-warning d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2 mb-4" role="alert">
    <div>
        <i class="fas fa-star-half-alt me-2"></i>
        <strong>Tienes {{ $completedRequestsToRate->count() }} {{ $completedRequestsToRate->count() === 1 ? 'solicitud pendiente' : 'solicitudes pendientes' }} de calificar.</strong>
        <span class="ms-1">Tu evaluación ayuda a mejorar el servicio.</span>
    </div>
    <a href="#pending-ratings-card" class="btn btn-sm btn-warning">
        <i class="fas fa-star me-1"></i>Ir a Calificar
    </a>
</div>
@endif

<!-- Solicitudes completadas pendientes de calificar -->
@if($completedRequestsToRate->count() > 0)
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow border-warning" id="pending-ratings-card">
            <div class="card-header bg-warning text-dark">
                <h5 class="card-title mb-0"><i class="fas fa-star-half-alt me-2"></i>Solicitudes Completadas - Pendientes de Calificar</h5>
            </div>
            <div class="card-body">
                <p class="text-muted">Califica la calidad del servicio recibido en las siguientes solicitudes completadas:</p>
                <div class="row">
                    @foreach($completedRequestsToRate as $request)
                    <div class="col-md-6 mb-3">
                        <div class="card h-100">
                            <div class="card-body">
                                <h6 class="card-title">#{{ $request->ticket_number }} - {{ $request->title }}</h6>
                                <p class="card-text small text-muted">{{ Str::limit(strip_tags($request->requester_info), 100) }}</p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">Completado: {{ $request->updated_at->format('d/m/Y') }}</small>
                                    <a href="{{ route('service-management.show', $request->ticket_id) }}" class="btn btn-sm btn-warning">
                                        <i class="fas fa-star me-1"></i>Calificar
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @if($completedRequestsToRate->count() >= 5)
                <div class="text-center mt-3">
                    <a href="{{ route('service-management.index') }}" class="btn btn-outline-primary">
                        <i class="fas fa-list me-1"></i>Ver Todas Mis Solicitudes
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endif

<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between flex-wrap align-items-center pb-2 mb-3 border-bottom gap-2">
        <h1 class="h2 mb-0">Mis Solicitudes</h1>
        <div class="btn-toolbar mb-0 w-100 w-md-auto justify-content-end">
            <a href="{{ route('service-management.create') }}" class="btn btn-gradient w-100 w-md-auto">
                <i class="fas fa-plus me-1"></i>Nueva Solicitud
            </a>
        </div>
    </div>

    <!-- Desktop table -->
    <div class="card shadow d-none d-md-block">
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
                                <small class="text-muted">{{ Str::limit(strip_tags($ticket->requester_info), 50) }}</small>
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
                {{ $tickets->links('pagination::bootstrap-5') }}
            </div>
            @endif
        </div>
    </div>

    <!-- Mobile cards -->
    <div class="d-md-none">
        @forelse($tickets as $ticket)
            @php
                $statusColors = [
                    1 => 'secondary',
                    2 => 'warning',
                    3 => 'success',
                    4 => 'danger'
                ];
                $statusNames = [
                    1 => 'Pendiente',
                    2 => 'En Progreso',
                    3 => 'Completado',
                    4 => 'Cancelado'
                ];
            @endphp
            <div class="card shadow-sm mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2 gap-2">
                        <strong class="text-break">#{{ $ticket->ticket_number }}</strong>
                        <span class="badge bg-{{ $statusColors[$ticket->status] ?? 'secondary' }}">
                            {{ $statusNames[$ticket->status] ?? 'Desconocido' }}
                        </span>
                    </div>
                    <h6 class="mb-1 text-break">{{ $ticket->title }}</h6>
                    <p class="small text-muted mb-2">{{ Str::limit(strip_tags($ticket->requester_info), 90) }}</p>
                    <p class="small text-muted mb-3">{{ $ticket->created_at->format('d/m/Y H:i') }}</p>
                    <a href="{{ route('service-management.show', $ticket->ticket_id) }}" class="btn btn-outline-primary btn-sm w-100">
                        <i class="fas fa-eye me-1"></i>Ver Detalles
                    </a>
                </div>
            </div>
        @empty
            <div class="card shadow-sm">
                <div class="card-body text-center py-4 text-muted">
                    <i class="fas fa-inbox fa-2x mb-3"></i>
                    <p class="mb-3">No tienes solicitudes registradas</p>
                    <a href="{{ route('service-management.create') }}" class="btn btn-primary w-100">
                        <i class="fas fa-plus me-2"></i>Crear Primera Solicitud
                    </a>
                </div>
            </div>
        @endforelse

        @if($tickets->hasPages())
            <div class="d-flex justify-content-center mt-3">
                {{ $tickets->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</div>

<!-- Modal para mostrar ticket creado -->
@if(session('new_ticket'))
<div class="modal fade" id="ticketCreatedModal" tabindex="-1" aria-labelledby="ticketCreatedModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="ticketCreatedModalLabel">
                    <i class="fas fa-check-circle me-2"></i>Solicitud Creada Exitosamente
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <i class="fas fa-ticket-alt fa-3x text-success mb-3"></i>
                    <h4>Número de Ticket: <strong>#{{ session('new_ticket')->ticket_number }}</strong></h4>
                </div>
                <div class="row">
                    <div class="col-sm-6">
                        <strong>Título:</strong><br>
                        {{ session('new_ticket')->title }}
                    </div>
                    <div class="col-sm-6">
                        <strong>Tipo de Solicitud:</strong><br>
                        {{ session('new_ticket')->requestType ? session('new_ticket')->requestType->type_name : 'N/A' }}
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-sm-6">
                        <strong>Fecha de Creación:</strong><br>
                        {{ session('new_ticket')->created_at->format('d/m/Y H:i') }}
                    </div>
                    <div class="col-sm-6">
                        <strong>Estado:</strong><br>
                        <span class="badge bg-warning">Pendiente</span>
                    </div>
                </div>
                <div class="alert alert-info mt-3">
                    <i class="fas fa-info-circle me-2"></i>
                    Se ha enviado un correo electrónico con los detalles de tu solicitud. Puedes hacer seguimiento de tu ticket en cualquier momento.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <a href="{{ route('service-management.track') }}?ticket_number={{ session('new_ticket')->ticket_number }}" class="btn btn-primary">
                    <i class="fas fa-search me-1"></i>Ver Estado del Ticket
                </a>
            </div>
        </div>
    </div>
</div>
@endif

@endsection

@push('styles')
<style>
@media (max-width: 767.98px) {
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
</style>
@endpush

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

    // Mostrar modal si hay un ticket recién creado
    @if(session('new_ticket'))
    $('#ticketCreatedModal').modal('show');
    @endif
});
</script>
@endpush

