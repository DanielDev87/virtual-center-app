@extends('layouts.app')

@section('title', 'Reportes de Monitoreo - Virtual Center')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2 monitor-simple-header">
        <h1 class="h3 mb-0">Reportes de Monitoreo</h1>
        <a href="{{ route('monitor.index') }}" class="btn btn-outline-secondary btn-sm monitor-back-btn">Volver al panel</a>
    </div>

    <div class="row g-3">
        <div class="col-12 col-lg-4">
            <div class="card h-100 shadow-sm">
                <div class="card-header">Actividad diaria</div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        @forelse($reports['daily_activity'] as $row)
                            <li class="list-group-item d-flex justify-content-between">
                                <span>{{ $row->date }}</span>
                                <span class="badge bg-primary">{{ $row->count }}</span>
                            </li>
                        @empty
                            <li class="list-group-item text-muted">Sin datos disponibles.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <div class="card h-100 shadow-sm">
                <div class="card-header">Engagement de usuarios</div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        @forelse($reports['user_engagement'] as $row)
                            <li class="list-group-item d-flex justify-content-between">
                                <span>{{ $row->date }}</span>
                                <span class="badge bg-success">{{ $row->count }}</span>
                            </li>
                        @empty
                            <li class="list-group-item text-muted">Sin datos disponibles.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <div class="card h-100 shadow-sm">
                <div class="card-header">Rendimiento de proyectos</div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        @forelse($reports['project_performance'] as $row)
                            <li class="list-group-item d-flex justify-content-between">
                                <span>{{ $row->project_status ?? 'N/A' }}</span>
                                <span class="badge bg-info text-dark">{{ $row->count }}</span>
                            </li>
                        @empty
                            <li class="list-group-item text-muted">Sin datos disponibles.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
@media (max-width: 767.98px) {
    .monitor-simple-header h1 {
        width: 100%;
    }

    .monitor-back-btn {
        width: 100%;
    }

    .monitor-simple-header + .row .card-header {
        font-size: 0.92rem;
    }

    .monitor-simple-header + .row .list-group-item {
        font-size: 0.86rem;
        padding: 0.55rem 0.65rem;
    }
}

@media (max-width: 399.98px) {
    .monitor-simple-header h1 {
        font-size: 1.15rem;
    }

    .monitor-simple-header + .row .card-body {
        padding: 0.75rem;
    }
}
</style>
@endpush
