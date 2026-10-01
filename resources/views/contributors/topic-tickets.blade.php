@extends('layouts.contributor')

@section('title', 'Tickets de Mi Tópico - Colaborador')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Tickets de Mi Tópico <span class="text-primary">- {{ $topicHeaderName ?? 'Sin tópico asignado' }}</span></h1>
    <small class="text-muted">Pool compartido de solicitudes para tomar y asignarse</small>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-1"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle me-1"></i>{{ $errors->first() }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="card shadow-sm mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('contributors.topic-tickets.index') }}" class="row g-2 align-items-end">
            <div class="col-12 col-md-8 col-lg-6">
                <label for="topic_id" class="form-label mb-1">Filtrar por tópico</label>
                <select id="topic_id" name="topic_id" class="form-select">
                    <option value="">Todos mis tópicos</option>
                    @foreach(($topics ?? collect()) as $topic)
                        <option value="{{ $topic->type_id }}" {{ (int) ($selectedTopicId ?? 0) === (int) $topic->type_id ? 'selected' : '' }}>
                            {{ $topic->type_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-auto">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-filter me-1"></i>Aplicar
                </button>
            </div>
            <div class="col-12 col-md-auto">
                <a href="{{ route('contributors.topic-tickets.index') }}" class="btn btn-outline-secondary w-100">
                    Limpiar
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card shadow d-none d-md-block">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-dark">
                    <tr>
                        <th># Ticket</th>
                        <th>Título</th>
                        <th>Tópico</th>
                        <th>Solicitante</th>
                        <th>Estado</th>
                        <th>Fecha</th>
                        <th>Disponibilidad</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tickets as $ticket)
                    <tr>
                        <td>#{{ $ticket->ticket_number }}</td>
                        <td>
                            @if($ticket->pool_available)
                                <span class="badge bg-success">Disponible</span>
                            @else
                                <span class="badge bg-secondary">No disponible</span>
                            @endif
                        </td>
                        <td>
                            <div class="fw-bold">{{ $ticket->title }}</div>
                            <small class="text-muted">{{ Str::limit(strip_tags($ticket->requester_info), 60) }}</small>
                            @if($ticket->institution)
                                <div class="mt-1">
                                    <span class="badge bg-info-subtle text-info border">
                                        <i class="fas fa-map-marker-alt me-1"></i>Regional: {{ $ticket->institution->institution_name }}
                                    </span>
                                </div>
                            @endif
                        </td>
                        <td>
                            @if($ticket->requestType)
                                <span class="badge" style="background-color: {{ $ticket->requestType->type_color ?? '#6c757d' }}">
                                    <i class="fas {{ $ticket->requestType->type_icon ?? 'fa-tag' }} me-1"></i>
                                    {{ $ticket->requestType->type_name }}
                                </span>
                            @else
                                <span class="text-muted">Sin tópico</span>
                            @endif
                        </td>
                        <td>{{ optional($ticket->requester)->user_name ?? 'N/A' }}</td>
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
                            <div class="d-flex gap-2 align-items-center">
                                <a href="{{ route('contributors.tickets.show', $ticket->ticket_id) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-eye me-1"></i>Ver
                                </a>
                                @if($ticket->pool_available)
                                <form action="{{ route('contributors.topic-tickets.self-assign', $ticket->ticket_id) }}" method="POST" onsubmit="return confirm('¿Deseas tomar este ticket?')">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-primary">
                                        <i class="fas fa-hand-paper me-1"></i>Tomar ticket
                                    </button>
                                </form>
                                @else
                                <form action="{{ route('contributors.tickets.join-request', $ticket->ticket_id) }}" method="POST" onsubmit="return confirm('¿Solicitar unirte al equipo de este ticket?')">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-user-plus me-1"></i>Solicitar unirse
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            <i class="fas fa-inbox fa-3x mb-3"></i>
                            <p class="mb-0">No hay tickets pendientes en tus tópicos.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($tickets->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $tickets->withQueryString()->links('pagination::bootstrap-5') }}
        </div>
        @endif
    </div>
</div>

<div class="d-md-none">
    @forelse($tickets as $ticket)
        @php
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

                <p class="small mb-3">
                    <span class="text-muted">Tópico:</span>
                    <strong>{{ optional($ticket->requestType)->type_name ?? 'Sin tópico' }}</strong>
                </p>

                @if($ticket->institution)
                    <p class="small mb-3">
                        <span class="badge bg-info-subtle text-info border">
                            <i class="fas fa-map-marker-alt me-1"></i>Regional: {{ $ticket->institution->institution_name }}
                        </span>
                    </p>
                @endif

                <p class="small mb-3">
                    <span class="text-muted">Disponibilidad:</span>
                    @if($ticket->pool_available)
                        <span class="badge bg-success">Disponible</span>
                    @else
                        <span class="badge bg-secondary">No disponible</span>
                    @endif
                </p>

                <div class="d-flex gap-2">
                    <a href="{{ route('contributors.tickets.show', $ticket->ticket_id) }}" class="btn btn-outline-secondary btn-sm w-50">
                        <i class="fas fa-eye me-1"></i>Ver
                    </a>
                    @if($ticket->pool_available)
                    <form action="{{ route('contributors.topic-tickets.self-assign', $ticket->ticket_id) }}" method="POST" class="w-50" onsubmit="return confirm('¿Deseas tomar este ticket?')">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-sm w-100">
                            <i class="fas fa-hand-paper me-1"></i>Tomar ticket
                        </button>
                    </form>
                    @else
                    <form action="{{ route('contributors.tickets.join-request', $ticket->ticket_id) }}" method="POST" class="w-50" onsubmit="return confirm('¿Solicitar unirte al equipo de este ticket?')">
                        @csrf
                        <button type="submit" class="btn btn-outline-primary btn-sm w-100">
                            <i class="fas fa-user-plus me-1"></i>Unirme
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="card shadow-sm">
            <div class="card-body text-center text-muted py-4">
                <i class="fas fa-inbox fa-2x mb-3"></i>
                <p class="mb-0">No hay tickets pendientes en tus tópicos.</p>
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
