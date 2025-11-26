@extends('layouts.contributor')

@section('title', 'Detalle del Ticket - Colaborador')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Ticket #{{ $ticket->ticket_number }}</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('contributors.dashboard') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Volver
        </a>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="row">
    <!-- Ticket Information -->
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Información del Ticket</h5>
                @php
                    $statusColors = [1 => 'secondary', 2 => 'warning', 3 => 'success', 4 => 'danger'];
                    $statusNames = [1 => 'Pendiente', 2 => 'En Progreso', 3 => 'Completado', 4 => 'Cancelado'];
                @endphp
                <span class="badge bg-{{ $statusColors[$ticket->status] ?? 'secondary' }} fs-6">
                    {{ $statusNames[$ticket->status] ?? 'Desconocido' }}
                </span>
            </div>
            <div class="card-body">
                <h5 class="fw-bold">{{ $ticket->title }}</h5>
                
                @if($ticket->requestType)
                <div class="mb-3">
                    <span class="badge" style="background-color: {{ $ticket->requestType->type_color }}; font-size: 1rem;">
                        <i class="fas {{ $ticket->requestType->type_icon }} me-1"></i>
                        {{ $ticket->requestType->type_name }}
                    </span>
                </div>
                @endif
                
                <p class="text-muted">{{ $ticket->requester_info }}</p>
                
                <hr>
                
                <div class="row">
                    <div class="col-md-6">
                        <small class="text-muted d-block">Solicitante:</small>
                        <strong>{{ $ticket->requester->user_name }}</strong>
                        <br>
                        <small>{{ $ticket->requester->user_email }}</small>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">Creado:</small>
                        <strong>{{ $ticket->created_at->format('d/m/Y H:i') }}</strong>
                    </div>
                </div>
            </div>
            </div>
        </div>

        <!-- Team Section -->
        <div class="card shadow mb-4">
            <div class="card-header bg-info text-white">
                <h5 class="card-title mb-0"><i class="fas fa-users me-2"></i>Equipo de Trabajo</h5>
            </div>
            <div class="card-body">
                @if($ticket->assignments->where('status', 'active')->count() > 0)
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Mediador</th>
                                <th>Puesto</th>
                                <th>Asignado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($ticket->assignments->where('status', 'active') as $assignment)
                            <tr>
                                <td>
                                    <strong>{{ $assignment->mediator->user_name }}</strong>
                                </td>
                                <td>
                                    <span class="badge" style="background-color: {{ $assignment->jobPosition->position_color }}">
                                        {{ $assignment->jobPosition->position_name }}
                                    </span>
                                </td>
                                <td>{{ $assignment->assigned_at->format('d/m/Y') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <p class="text-muted mb-0">No hay equipo asignado (solo mediador principal).</p>
                @endif
            </div>
        </div>

        <!-- Progress History -->
        <div class="card shadow mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="fas fa-history me-2"></i>Historial de Avances</h5>
            </div>
            <div class="card-body">
                @forelse($ticket->progress()->latest()->get() as $progress)
                <div class="border-start border-3 border-primary ps-3 mb-3">
                    <div class="d-flex justify-content-between">
                        <strong>{{ $progress->user->user_name }}</strong>
                        <small class="text-muted">{{ $progress->created_at->format('d/m/Y H:i') }}</small>
                    </div>
                    <p class="mb-1">{{ $progress->progress_description }}</p>
                    <div class="progress" style="height: 20px;">
                        <div class="progress-bar bg-success" role="progressbar" 
                             style="width: {{ $progress->progress_percentage }}%">
                            {{ $progress->progress_percentage }}%
                        </div>
                    </div>
                </div>
                @empty
                <p class="text-muted text-center">No hay avances registrados aún</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Register Progress -->
    <div class="col-lg-4">
        @if($ticket->status != 3 && $ticket->status != 4)
        <div class="card shadow mb-4 border-success">
            <div class="card-header bg-success text-white">
                <h5 class="card-title mb-0"><i class="fas fa-plus-circle me-2"></i>Registrar Avance</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('contributors.tickets.progress', $ticket->ticket_id) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="progress_description" class="form-label">Descripción del Avance</label>
                        <textarea class="form-control @error('progress_description') is-invalid @enderror" 
                                  id="progress_description" name="progress_description" rows="4" 
                                  required>{{ old('progress_description') }}</textarea>
                        @error('progress_description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="progress_percentage" class="form-label">Porcentaje de Avance</label>
                        <input type="range" class="form-range" id="progress_percentage" 
                               name="progress_percentage" min="0" max="100" value="{{ old('progress_percentage', 0) }}" 
                               oninput="this.nextElementSibling.value = this.value + '%'">
                        <output class="badge bg-primary">0%</output>
                        @error('progress_percentage')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save me-2"></i>Guardar Avance
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @else
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            Este ticket ya está {{ $ticket->status == 3 ? 'completado' : 'cancelado' }}.
        </div>
        @endif
    </div>
</div>
@endsection
