@extends('layouts.app')

@section('title', 'Analitica de Monitoreo - Virtual Center')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2 monitor-simple-header">
        <h1 class="h3 mb-0">Analitica de Monitoreo</h1>
        <a href="{{ route('monitor.index') }}" class="btn btn-outline-secondary btn-sm monitor-back-btn">Volver al panel</a>
    </div>

    <div class="row g-3">
        <div class="col-12 col-xl-6">
            <div class="card shadow-sm h-100">
                <div class="card-header">Visualizaciones de pagina</div>
                <div class="card-body table-responsive">
                    <table class="table table-sm table-striped mb-0">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th class="text-end">Vistas</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($analytics['page_views'] as $row)
                                <tr>
                                    <td>{{ $row['date'] }}</td>
                                    <td class="text-end">{{ $row['views'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-muted">Sin datos disponibles.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-3">
            <div class="card shadow-sm h-100">
                <div class="card-header">Crecimiento de usuarios</div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        @forelse($analytics['user_growth'] as $row)
                            <li class="list-group-item d-flex justify-content-between">
                                <span>{{ $row['month'] }}</span>
                                <span class="badge bg-primary">{{ $row['users'] }}</span>
                            </li>
                        @empty
                            <li class="list-group-item text-muted">Sin datos disponibles.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-3">
            <div class="card shadow-sm h-100">
                <div class="card-header">Tendencias de proyectos</div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        @forelse($analytics['project_trends'] as $row)
                            <li class="list-group-item d-flex justify-content-between">
                                <span>{{ $row['date'] }}</span>
                                <span class="badge bg-success">{{ $row['projects'] }}</span>
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

    .monitor-simple-header + .row .list-group-item,
    .monitor-simple-header + .row .table td,
    .monitor-simple-header + .row .table th {
        font-size: 0.86rem;
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
