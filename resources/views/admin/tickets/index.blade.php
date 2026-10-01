@extends('layouts.admin')

@section('title', 'Gestión de Tickets - Admin')

@section('content')
<div class="container-fluid">
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Filter -->
    <div class="row pt-3 mb-4 align-items-center g-2">
        <div class="col-md-6 col-lg-5">
            <form method="GET" action="{{ route('admin.tickets.index') }}" class="border rounded p-2 tickets-filter-box">
                <label for="ticketStatusFilter" class="form-label form-label-sm fw-semibold mb-1">
                    <i class="fas fa-filter me-1"></i>Filtrar por estado
                </label>
                <select id="ticketStatusFilter" class="form-select mb-2" name="status" onchange="this.form.submit()">
                    <option value="">Todos los estados</option>
                    <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Pendiente</option>
                    <option value="2" {{ request('status') == '2' ? 'selected' : '' }}>En Progreso</option>
                    <option value="3" {{ request('status') == '3' ? 'selected' : '' }}>Completado</option>
                    <option value="4" {{ request('status') == '4' ? 'selected' : '' }}>Cancelado</option>
                </select>

                <label for="ticketTopicFilter" class="form-label form-label-sm fw-semibold mb-1">
                    <i class="fas fa-tags me-1"></i>Filtrar por tópico
                </label>
                <select id="ticketTopicFilter" class="form-select" name="request_type_id" onchange="this.form.submit()">
                    <option value="">Todos los tópicos</option>
                    @foreach($requestTypes as $requestType)
                    <option value="{{ $requestType->type_id }}" {{ request('request_type_id') == (string) $requestType->type_id ? 'selected' : '' }}>
                        {{ $requestType->type_name }}
                    </option>
                    @endforeach
                </select>

                <div class="d-flex justify-content-end mt-2">
                    <a href="{{ route('admin.tickets.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-eraser me-1"></i>Limpiar filtros
                    </a>
                </div>
            </form>
        </div>
        <div class="col-md-6 col-lg-7">
            <div class="alert alert-info mb-0 py-2 px-3">
                <i class="fas fa-circle-info me-1"></i>
                Puedes filtrar los tickets por estado y por tópico.
            </div>
        </div>
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
                            <th>Solicitante</th>
                            <th>Mediador</th>
                            <th>Estado</th>
                            <th>Progreso</th>
                            <th>Fecha</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tickets as $ticket)
                        <tr class="{{ (int) $ticket->priority === 4 ? 'table-danger' : ((int) $ticket->priority === 3 ? 'table-warning' : '') }}">
                            <td>#{{ $ticket->ticket_number }}</td>
                            <td>
                                @if((int) $ticket->priority === 4)
                                    <span class="badge bg-danger me-1"><i class="fas fa-bolt"></i> Urgente</span>
                                @elseif((int) $ticket->priority === 3)
                                    <span class="badge bg-warning text-dark me-1"><i class="fas fa-exclamation-triangle"></i> Alta</span>
                                @endif
                                {{ $ticket->title }}
                            </td>
                            <td>{{ $ticket->requester->user_name }}</td>
                            <td>
                                @if($ticket->mediator)
                                    {{ $ticket->mediator->user_name }}
                                @else
                                    <span class="text-muted">Sin asignar</span>
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
                                @php $displayProgress = $ticket->auto_progress ?? $ticket->progress_percentage ?? 0; @endphp
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar bg-info" role="progressbar" 
                                         style="width: {{ $displayProgress }}%;" 
                                         aria-valuenow="{{ $displayProgress }}" 
                                         aria-valuemin="0" aria-valuemax="100">
                                        {{ $displayProgress }}%
                                    </div>
                                </div>
                            </td>
                            <td>{{ $ticket->created_at->format('d/m/Y') }}</td>
                            <td>
                                <a href="{{ route('admin.tickets.show', $ticket->ticket_id) }}" 
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i> Ver
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="fas fa-inbox fa-3x mb-3"></i>
                                <p>No hay tickets registrados</p>
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
</div>
@endsection

@push('styles')
<style>
.tickets-filter-box {
    background-color: var(--bs-tertiary-bg);
}

[data-bs-theme="dark"] .tickets-filter-box {
    border-color: var(--bs-border-color) !important;
}

[data-bs-theme="dark"] .tickets-filter-box .form-label {
    color: var(--bs-body-color);
}

[data-bs-theme="dark"] .tickets-filter-box .form-select {
    background-color: var(--bs-body-bg);
    color: var(--bs-body-color);
    border-color: var(--bs-border-color);
}
</style>
@endpush
