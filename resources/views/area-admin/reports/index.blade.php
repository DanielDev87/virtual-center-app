@extends('layouts.area-admin')

@section('title', 'Reportes del Área')

@push('styles')
<style>
@media print {
    .sidebar,
    .navbar,
    .btn,
    form,
    .alert,
    .no-print {
        display: none !important;
    }

    .container-fluid {
        width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    .card {
        border: 1px solid #ddd !important;
        box-shadow: none !important;
        break-inside: avoid;
    }

    .table {
        font-size: 12px;
    }
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <div>
            <h1 class="h2 mb-0">Reportes</h1>
            <p class="text-muted mb-0 small">
                <i class="fas fa-sitemap me-1"></i>{{ $area->area_name ?? 'Área' }}
            </p>
        </div>
        <a href="{{ route('area-admin.reports.requesters') }}" class="btn btn-outline-primary btn-sm no-print">
            <i class="fas fa-user-clock me-1"></i>Solicitantes frecuentes
        </a>
        @if($reportData)
        <div class="no-print d-flex gap-2 align-items-center">
            <select name="format" class="form-select form-select-sm" id="reportFormat" form="areaReportForm" style="min-width: 150px;">
                <option value="excel" {{ request('format', 'excel') === 'excel' ? 'selected' : '' }}>Excel (.xls)</option>
                <option value="csv" {{ request('format') === 'csv' ? 'selected' : '' }}>CSV (.csv)</option>
                <option value="pdf" {{ request('format') === 'pdf' ? 'selected' : '' }}>PDF (.pdf)</option>
            </select>
            <button type="submit" class="btn btn-secondary btn-sm" id="printReportBtn" form="areaReportForm" name="export" value="1">
                <i class="fas fa-print me-1"></i>Imprimir / Exportar
            </button>
        </div>
        @endif
    </div>

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(!empty($regionalScope['isRegionalScoped']) && $regionalScope['isRegionalScoped'])
    <div class="alert alert-info d-flex flex-wrap align-items-center gap-2">
        <i class="fas fa-map-marker-alt me-1"></i>
        <span class="fw-semibold">Reporte aplicado a regionales:</span>
        @foreach(($regionalScope['regionalScopeNames'] ?? collect()) as $regionalName)
            <span class="badge bg-primary-subtle text-primary border">{{ $regionalName }}</span>
        @endforeach
    </div>
    @endif

    <!-- Formulario de filtros -->
    <div class="card shadow mb-4">
        <div class="card-header fw-semibold"><i class="fas fa-filter me-1"></i>Filtros del Reporte</div>
        <div class="card-body">
            <form method="GET" action="{{ route('area-admin.reports.index') }}" class="row g-3 align-items-end" id="areaReportForm">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Fecha Inicio</label>
                    <input type="date" name="start_date" class="form-control form-control-sm"
                           value="{{ request('start_date') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Fecha Fin</label>
                    <input type="date" name="end_date" class="form-control form-control-sm"
                           value="{{ request('end_date') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Estado</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Todos</option>
                        <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Pendiente</option>
                        <option value="2" {{ request('status') == '2' ? 'selected' : '' }}>En Progreso</option>
                        <option value="3" {{ request('status') == '3' ? 'selected' : '' }}>Completado</option>
                        <option value="4" {{ request('status') == '4' ? 'selected' : '' }}>Cancelado</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Prioridad</label>
                    <select name="priority" class="form-select form-select-sm">
                        <option value="">Todas</option>
                        <option value="1" {{ request('priority') == '1' ? 'selected' : '' }}>Baja</option>
                        <option value="2" {{ request('priority') == '2' ? 'selected' : '' }}>Media</option>
                        <option value="3" {{ request('priority') == '3' ? 'selected' : '' }}>Alta (Afecta operación)</option>
                        <option value="4" {{ request('priority') == '4' ? 'selected' : '' }}>Urgente (Suspende operación)</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Tópico</label>
                    <select name="request_type_id" class="form-select form-select-sm">
                        <option value="">Todos del área</option>
                        @foreach($areaTopics as $topic)
                        <option value="{{ $topic->type_id }}" {{ request('request_type_id') == $topic->type_id ? 'selected' : '' }}>
                            {{ $topic->type_name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Fase ADDIE</label>
                    <select name="current_phase" class="form-select form-select-sm">
                        <option value="">Todas</option>
                        <option value="Analysis"       {{ request('current_phase') == 'Analysis'       ? 'selected' : '' }}>Análisis</option>
                        <option value="Design"         {{ request('current_phase') == 'Design'         ? 'selected' : '' }}>Diseño</option>
                        <option value="Development"    {{ request('current_phase') == 'Development'    ? 'selected' : '' }}>Desarrollo</option>
                        <option value="Implementation" {{ request('current_phase') == 'Implementation' ? 'selected' : '' }}>Implementación</option>
                        <option value="Evaluation"     {{ request('current_phase') == 'Evaluation'     ? 'selected' : '' }}>Evaluación</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
                        <i class="fas fa-search me-1"></i>Filtrar
                    </button>
                    <a href="{{ route('area-admin.reports.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-eraser"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    @if($reportData)
    <!-- Resumen -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm text-center py-3">
                <div class="fw-bold fs-3 text-primary">{{ $reportData['total'] }}</div>
                <div class="small text-muted">Total Tickets</div>
            </div>
        </div>
        @php
            $statusNames = [1 => 'Pendiente', 2 => 'En Progreso', 3 => 'Completado', 4 => 'Cancelado'];
            $statusColorMap = [1 => 'warning', 2 => 'info', 3 => 'success', 4 => 'danger'];
        @endphp
        @foreach($reportData['by_status'] as $statusKey => $count)
        <div class="col-md-2">
            <div class="card shadow-sm text-center py-3">
                <div class="fw-bold fs-4 text-{{ $statusColorMap[$statusKey] ?? 'secondary' }}">{{ $count }}</div>
                <div class="small text-muted">{{ $statusNames[$statusKey] ?? '—' }}</div>
            </div>
        </div>
        @endforeach
        @if($reportData['avg_rating'])
        <div class="col-md-2">
            <div class="card shadow-sm text-center py-3">
                <div class="fw-bold fs-3 text-warning">{{ number_format($reportData['avg_rating'], 1) }} <i class="fas fa-star"></i></div>
                <div class="small text-muted">Calif. Promedio</div>
            </div>
        </div>
        @endif
    </div>

    <!-- Tabla de resultados -->
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between align-items-center fw-semibold">
            <span><i class="fas fa-table me-1"></i>Resultados ({{ $reportData['total'] }} tickets)</span>
            <small class="text-muted">Generado: {{ $reportData['generated_at'] }}</small>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0 table-sm">
                    <thead class="table-dark">
                        <tr>
                            <th># Ticket</th>
                            <th>Título</th>
                            <th>Tópico</th>
                            <th>Estado</th>
                            <th>Prioridad</th>
                            <th>Fase ADDIE</th>
                            <th>Progreso</th>
                            <th>Calificación</th>
                            <th>Solicitante</th>
                            <th>Mediador</th>
                            <th>Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $statusColors  = [1 => 'secondary', 2 => 'warning', 3 => 'success', 4 => 'danger'];
                            $phaseNames    = ['Analysis' => 'Análisis', 'Design' => 'Diseño', 'Development' => 'Desarrollo', 'Implementation' => 'Implementación', 'Evaluation' => 'Evaluación'];
                            $priorityNames = [1 => '🟢 Baja', 2 => '🟡 Media', 3 => '🟠 Alta (Afecta operación)', 4 => '🔴 Urgente (Suspende operación)'];
                        @endphp
                        @forelse($reportData['tickets'] as $ticket)
                        <tr>
                            <td><a href="{{ route('area-admin.tickets.show', $ticket->ticket_id) }}" class="fw-semibold">#{{ $ticket->ticket_number }}</a></td>
                            <td class="text-truncate" style="max-width: 180px;">{{ $ticket->title }}</td>
                            <td>{{ $ticket->requestType->type_name ?? '—' }}</td>
                            <td>
                                <span class="badge bg-{{ $statusColors[$ticket->status] ?? 'secondary' }}">
                                    {{ $statusNames[$ticket->status] ?? '—' }}
                                </span>
                            </td>
                            <td>{{ $priorityNames[$ticket->priority] ?? '—' }}</td>
                            <td>{{ $phaseNames[$ticket->current_phase] ?? ($ticket->current_phase ?? '—') }}</td>
                            <td>{{ $ticket->progress_percentage ?? 0 }}%</td>
                            <td>
                                @if($ticket->rating)
                                    <span class="text-warning">{{ str_repeat('★', $ticket->rating) }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>{{ $ticket->requester->user_name ?? '—' }}</td>
                            <td>{{ $ticket->mediator->user_name ?? 'Sin asignar' }}</td>
                            <td class="small">{{ optional($ticket->created_at)->format('d/m/Y') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="11" class="text-center text-muted py-4">
                                No hay tickets con los filtros aplicados.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @else
    <div class="alert alert-info">
        <i class="fas fa-info-circle me-2"></i>
        Aplica al menos un filtro para ver los resultados del reporte de tu área.
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const printBtn = document.getElementById('printReportBtn');
    const formatSelect = document.getElementById('reportFormat');

    if (printBtn && formatSelect) {
        function updatePrintButtonLabel() {
            const format = String(formatSelect.value || 'excel').toLowerCase();
            if (format === 'csv') {
                printBtn.innerHTML = '<i class="fas fa-print me-1"></i>Imprimir / Exportar CSV';
                return;
            }

            if (format === 'pdf') {
                printBtn.innerHTML = '<i class="fas fa-print me-1"></i>Imprimir / Exportar PDF';
                return;
            }

            printBtn.innerHTML = '<i class="fas fa-print me-1"></i>Imprimir / Exportar Excel';
        }

        formatSelect.addEventListener('change', updatePrintButtonLabel);
        updatePrintButtonLabel();
    }
});
</script>
@endpush
