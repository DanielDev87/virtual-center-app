@extends('layouts.admin')

@section('title', 'Gestión de Tickets - Admin')

@section('content')
@php
    $hasFilters = collect(request()->only(['status', 'request_type_id', 'mediator_id', 'date_from', 'date_to', 'search']))
        ->filter(fn ($value) => $value !== null && $value !== '')
        ->isNotEmpty();
@endphp
<div class="container-fluid">
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Filter -->
    <div class="row pt-3 mb-4 align-items-center g-2">
        <div class="col-12">
            <form method="GET" action="{{ route('admin.tickets.index') }}" class="border rounded p-3 tickets-filter-box">
                <div class="row g-2">
                    <div class="col-md-6 col-lg-3">
                        <label for="ticketStatusFilter" class="form-label form-label-sm fw-semibold mb-1">
                            <i class="fas fa-filter me-1"></i>Estado
                        </label>
                        <select id="ticketStatusFilter" class="form-select" name="status" onchange="this.form.submit()">
                            <option value="">Todos los estados</option>
                            <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Pendiente</option>
                            <option value="2" {{ request('status') == '2' ? 'selected' : '' }}>En Progreso</option>
                            <option value="3" {{ request('status') == '3' ? 'selected' : '' }}>Completado</option>
                            <option value="4" {{ request('status') == '4' ? 'selected' : '' }}>Cancelado</option>
                        </select>
                    </div>

                    <div class="col-md-6 col-lg-3">
                        <label for="ticketTopicFilter" class="form-label form-label-sm fw-semibold mb-1">
                            <i class="fas fa-tags me-1"></i>Tópico
                        </label>
                        <select id="ticketTopicFilter" class="form-select" name="request_type_id" onchange="this.form.submit()">
                            <option value="">Todos los tópicos</option>
                            @foreach($requestTypes as $requestType)
                            <option value="{{ $requestType->type_id }}" {{ request('request_type_id') == (string) $requestType->type_id ? 'selected' : '' }}>
                                {{ $requestType->type_name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6 col-lg-3">
                        <label for="ticketMediatorFilter" class="form-label form-label-sm fw-semibold mb-1">
                            <i class="fas fa-user-check me-1"></i>Responsable
                        </label>
                        <select id="ticketMediatorFilter" class="form-select" name="mediator_id" onchange="this.form.submit()">
                            <option value="">Todos los responsables</option>
                            @foreach($mediators as $mediatorOption)
                            <option value="{{ $mediatorOption->user_id }}" {{ request('mediator_id') == (string) $mediatorOption->user_id ? 'selected' : '' }}>
                                {{ $mediatorOption->user_name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6 col-lg-3">
                        <label for="ticketSearchFilter" class="form-label form-label-sm fw-semibold mb-1">
                            <i class="fas fa-search me-1"></i>Buscar
                        </label>
                        <input id="ticketSearchFilter" type="text" class="form-control" name="search"
                               value="{{ request('search') }}" maxlength="100"
                               placeholder="Número o título del ticket">
                    </div>

                    <div class="col-md-6 col-lg-3">
                        <label for="ticketDateFrom" class="form-label form-label-sm fw-semibold mb-1">
                            <i class="fas fa-calendar-alt me-1"></i>Creado desde
                        </label>
                        <input id="ticketDateFrom" type="date" class="form-control" name="date_from"
                               value="{{ request('date_from') }}">
                    </div>

                    <div class="col-md-6 col-lg-3">
                        <label for="ticketDateTo" class="form-label form-label-sm fw-semibold mb-1">
                            <i class="fas fa-calendar-alt me-1"></i>Creado hasta
                        </label>
                        <input id="ticketDateTo" type="date" class="form-control" name="date_to"
                               value="{{ request('date_to') }}">
                    </div>

                    <div class="col-lg-6 d-flex align-items-end justify-content-end gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-check me-1"></i>Aplicar
                        </button>
                        <a href="{{ route('admin.tickets.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-eraser me-1"></i>Limpiar filtros
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Tickets Table -->
    <div class="card shadow">
        <div class="card-body">
            @if($hasFilters)
            <p class="text-muted small mb-3">
                <i class="fas fa-filter me-1"></i>{{ $tickets->total() }} resultado(s) con los filtros aplicados.
            </p>
            @endif

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
                                @if($hasFilters)
                                    <i class="fas fa-search fa-3x mb-3"></i>
                                    <p class="mb-2">No se encontraron tickets con los filtros aplicados.</p>
                                    <a href="{{ route('admin.tickets.index') }}" class="btn btn-sm btn-outline-secondary">
                                        <i class="fas fa-eraser me-1"></i>Quitar filtros
                                    </a>
                                @else
                                    <i class="fas fa-inbox fa-3x mb-3"></i>
                                    <p>No hay tickets registrados</p>
                                @endif
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

[data-bs-theme="dark"] .tickets-filter-box .form-select,
[data-bs-theme="dark"] .tickets-filter-box .form-control {
    background-color: var(--bs-body-bg);
    color: var(--bs-body-color);
    border-color: var(--bs-border-color);
}
</style>
@endpush