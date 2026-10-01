@extends('layouts.area-admin')

@section('title', 'Tickets del Área')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2 mb-0">Tickets del Área</h1>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(!empty($regionalScope['isRegionalScoped']) && $regionalScope['isRegionalScoped'])
    <div class="alert alert-info d-flex flex-wrap align-items-center gap-2">
        <i class="fas fa-map-marker-alt me-1"></i>
        <span class="fw-semibold">Mostrando tickets de las regionales:</span>
        @foreach(($regionalScope['regionalScopeNames'] ?? collect()) as $regionalName)
            <span class="badge bg-primary-subtle text-primary border">{{ $regionalName }}</span>
        @endforeach
    </div>
    @endif

    <!-- Filtros -->
    <div class="row mb-4 g-2 align-items-end">
        <div class="col-md-7 col-lg-5">
            <form method="GET" action="{{ route('area-admin.tickets.index') }}" class="border rounded p-2">
                <div class="mb-2">
                    <label class="form-label form-label-sm fw-semibold mb-1">
                        <i class="fas fa-filter me-1"></i>Estado
                    </label>
                    <select class="form-select form-select-sm" name="status" onchange="this.form.submit()">
                        <option value="">Todos los estados</option>
                        <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Pendiente</option>
                        <option value="2" {{ request('status') == '2' ? 'selected' : '' }}>En Progreso</option>
                        <option value="5" {{ request('status') == '5' ? 'selected' : '' }}>Realizado por Operario</option>
                        <option value="3" {{ request('status') == '3' ? 'selected' : '' }}>Completado</option>
                        <option value="4" {{ request('status') == '4' ? 'selected' : '' }}>Cancelado</option>
                    </select>
                </div>
                <div class="mb-2">
                    <label class="form-label form-label-sm fw-semibold mb-1">
                        <i class="fas fa-tags me-1"></i>Tópico
                    </label>
                    <select class="form-select form-select-sm" name="request_type_id" onchange="this.form.submit()">
                        <option value="">Todos los tópicos del área</option>
                        @foreach($areaTopics as $topic)
                        <option value="{{ $topic->type_id }}" {{ request('request_type_id') == $topic->type_id ? 'selected' : '' }}>
                            {{ $topic->type_name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-2">
                    <label class="form-label form-label-sm fw-semibold mb-1">
                        <i class="fas fa-user-tie me-1"></i>Mediador
                    </label>
                    <select class="form-select form-select-sm" name="mediator_id" onchange="this.form.submit()">
                        <option value="">Todos los mediadores</option>
                        @foreach($areaMediators as $mediator)
                        <option value="{{ $mediator->user_id }}" {{ request('mediator_id') == $mediator->user_id ? 'selected' : '' }}>
                            {{ $mediator->user_name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="text-end">
                    <a href="{{ route('area-admin.tickets.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-eraser me-1"></i>Limpiar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de tickets -->
    <div class="card shadow">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th># Ticket</th>
                            <th>Título</th>
                            <th>Tópico</th>
                            <th>Solicitante</th>
                            <th>Mediador</th>
                            <th>Estado</th>
                            <th>Progreso</th>
                            <th>Fecha</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $statusColors = [1 => 'secondary', 2 => 'warning', 3 => 'success', 4 => 'danger', 5 => 'info'];
                            $statusNames  = [1 => 'Pendiente', 2 => 'En Progreso', 3 => 'Completado', 4 => 'Cancelado', 5 => 'Realizado por Operario'];
                        @endphp
                        @forelse($tickets as $ticket)
                        <tr class="{{ (int) $ticket->priority === 4 ? 'table-danger' : ((int) $ticket->priority === 3 ? 'table-warning' : '') }}">
                            <td>#{{ $ticket->ticket_number }}</td>
                            <td class="text-truncate" style="max-width: 200px;">
                                @if((int) $ticket->priority === 4)
                                    <span class="badge bg-danger me-1"><i class="fas fa-bolt"></i> Urgente</span>
                                @elseif((int) $ticket->priority === 3)
                                    <span class="badge bg-warning text-dark me-1"><i class="fas fa-exclamation-triangle"></i> Alta</span>
                                @endif
                                {{ $ticket->title }}
                            </td>
                            <td>
                                @if($ticket->requestType)
                                    <span class="badge" style="background-color: {{ $ticket->requestType->type_color ?? '#6c757d' }}">
                                        <i class="fas {{ $ticket->requestType->type_icon ?? 'fa-tag' }} me-1"></i>
                                        {{ $ticket->requestType->type_name }}
                                    </span>
                                @else
                                    <span class="text-muted small">Sin tópico</span>
                                @endif
                            </td>
                            <td>{{ $ticket->requester->user_name ?? '—' }}</td>
                            <td>{{ $ticket->mediator->user_name ?? 'Sin asignar' }}</td>
                            <td>
                                <span class="badge bg-{{ $statusColors[$ticket->status] ?? 'secondary' }}">
                                    {{ $statusNames[$ticket->status] ?? '—' }}
                                </span>
                            </td>
                            <td style="min-width: 100px;">
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar {{ $ticket->auto_progress == 100 ? 'bg-success' : 'bg-primary' }}"
                                         style="width: {{ $ticket->auto_progress }}%"></div>
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
                            <td colspan="9" class="text-center text-muted py-4">
                                No hay tickets para esta área con los filtros aplicados.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($tickets->hasPages())
        <div class="card-footer">
            {{ $tickets->appends(request()->query())->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
