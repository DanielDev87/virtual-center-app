@extends('layouts.app')

@section('title', 'Operario')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Panel Operario</h2>
            <p class="text-muted mb-0">Tickets asignados para atención rápida.</p>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small">Total</div>
                            <h3 class="mb-0">{{ $stats['total'] }}</h3>
                        </div>
                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-3"><i class="fas fa-list"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small">Pendientes</div>
                            <h3 class="mb-0">{{ $stats['pending'] }}</h3>
                        </div>
                        <div class="bg-warning bg-opacity-10 text-warning rounded-circle p-3"><i class="fas fa-clock"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small">En proceso</div>
                            <h3 class="mb-0">{{ $stats['in_progress'] }}</h3>
                        </div>
                        <div class="bg-success bg-opacity-10 text-success rounded-circle p-3"><i class="fas fa-spinner"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($tickets->isEmpty())
        <div class="alert alert-light border text-center py-4">
            No tienes tickets asignados para atender en este momento.
        </div>
    @else
        <div class="list-group">
            @foreach($tickets as $ticket)
                <a href="{{ route('operario.tickets.show', $ticket->ticket_id) }}" class="list-group-item list-group-item-action mb-2 rounded border shadow-sm {{ (int) $ticket->priority === 4 ? 'border-danger border-2' : ((int) $ticket->priority === 3 ? 'border-warning border-2' : '') }}">
                    <div class="d-flex justify-content-between align-items-start gap-3">
                        <div>
                            <div class="fw-bold">
                                #{{ $ticket->ticket_number }} -
                                @if((int) $ticket->priority === 4)
                                    <span class="badge bg-danger"><i class="fas fa-bolt"></i> Urgente</span>
                                @elseif((int) $ticket->priority === 3)
                                    <span class="badge bg-warning text-dark"><i class="fas fa-exclamation-triangle"></i> Alta</span>
                                @endif
                                {{ $ticket->title }}
                            </div>
                            <div class="small text-muted mt-1">
                                {{ $ticket->requestType?->type_name ?? 'Sin tópico' }} · {{ $ticket->requester?->user_name ?? 'Solicitante' }}
                            </div>
                        </div>
                        <span class="badge @if($ticket->status == 1) bg-warning text-dark @elseif($ticket->status == 2) bg-primary @elseif($ticket->status == 5) bg-info text-dark @else bg-success @endif">
                            @switch($ticket->status)
                                @case(1) Pendiente @break
                                @case(2) En proceso @break
                                @case(5) Realizado para auditoría @break
                                @default Sin estado @break
                            @endswitch
                        </span>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="mt-4 d-flex justify-content-center">
            {{ $tickets->links() }}
        </div>
    @endif
</div>
@endsection
