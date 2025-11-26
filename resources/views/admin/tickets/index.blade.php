@extends('layouts.admin')

@section('title', 'Gestión de Tickets - Admin')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Gestión de Tickets</h1>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Filter -->
    <div class="row mb-4">
        <div class="col-md-3">
            <form method="GET" action="{{ route('admin.tickets.index') }}">
                <select class="form-select" name="status" onchange="this.form.submit()">
                    <option value="">Todos los estados</option>
                    <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Pendiente</option>
                    <option value="2" {{ request('status') == '2' ? 'selected' : '' }}>En Progreso</option>
                    <option value="3" {{ request('status') == '3' ? 'selected' : '' }}>Completado</option>
                    <option value="4" {{ request('status') == '4' ? 'selected' : '' }}>Cancelado</option>
                </select>
            </form>
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
                            <th>Fecha</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tickets as $ticket)
                        <tr>
                            <td>#{{ $ticket->ticket_number }}</td>
                            <td>{{ $ticket->title }}</td>
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
                            <td colspan="7" class="text-center text-muted py-4">
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
                {{ $tickets->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
