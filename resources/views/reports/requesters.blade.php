@php($role = auth()->user()->role?->role_name)
@extends($role === 'Admin Área' ? 'layouts.area-admin' : ($role === 'Contributor' ? 'layouts.contributor' : 'layouts.admin'))

@section('title', 'Solicitantes frecuentes')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Solicitantes frecuentes</h1>
            <p class="text-muted mb-0">Solicitantes ordenados por cantidad de tickets registrados.</p>
        </div>
        <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">Volver</a>
    </div>

    <form method="GET" action="{{ request()->url() }}" class="card shadow-sm mb-4">
        <div class="card-body row g-3 align-items-end">
            <div class="col-md-3"><label class="form-label">Fecha inicio</label><input type="date" name="start_date" value="{{ request('start_date') }}" class="form-control"></div>
            <div class="col-md-3"><label class="form-label">Fecha fin</label><input type="date" name="end_date" value="{{ request('end_date') }}" class="form-control"></div>
            <div class="col-md-2"><label class="form-label">Estado</label><select name="status" class="form-select"><option value="">Todos</option><option value="1">Pendiente</option><option value="2">En Progreso</option><option value="3">Completado</option><option value="4">Cancelado</option></select></div>
            <div class="col-md-3"><label class="form-label">Tópico</label><select name="request_type_id" class="form-select"><option value="">Todos</option>@foreach($topicOptions as $topic)<option value="{{ $topic->type_id }}" {{ request('request_type_id') == $topic->type_id ? 'selected' : '' }}>{{ $topic->type_name }}</option>@endforeach</select></div>
            <div class="col-md-1 d-grid"><button class="btn btn-primary" type="submit">Filtrar</button></div>
        </div>
    </form>

    <div class="card shadow">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-dark"><tr><th>#</th><th>Solicitante</th><th>Documento</th><th>Correo</th><th>Total</th><th>Pendientes</th><th>En progreso</th><th>Completados</th><th>Cancelados</th></tr></thead>
                <tbody>
                    @forelse($requesterStats as $index => $item)
                        <tr><td>{{ $index + 1 }}</td><td class="fw-semibold">{{ $item->user_name }}</td><td>{{ $item->document_number ?: 'N/A' }}</td><td>{{ $item->user_email ?: 'N/A' }}</td><td><span class="badge bg-primary">{{ $item->total_tickets }}</span></td><td>{{ $item->pending_tickets }}</td><td>{{ $item->in_progress_tickets }}</td><td>{{ $item->completed_tickets }}</td><td>{{ $item->canceled_tickets }}</td></tr>
                    @empty
                        <tr><td colspan="9" class="text-center text-muted py-4">No hay datos con los filtros aplicados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
