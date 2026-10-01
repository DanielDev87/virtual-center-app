@extends('layouts.admin')

@section('title', 'Reportes - A-DDIE')

@section('content')
<div class="container-fluid reports-page">
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row g-3 report-cards-row">
        <!-- Frequent Requesters Report -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow h-100 report-card">
                <div class="card-header bg-pink text-white report-card-header" style="background-color: #d63384;">
                    <h5 class="card-title mb-0 report-card-title"><i class="fas fa-user-clock me-2"></i>Solicitantes Frecuentes</h5>
                </div>
                <div class="card-body">
                    <p class="card-text">Identifica los solicitantes con mayor número de tickets y su distribución por estado.</p>
                    <form action="{{ route('admin.reports.requesters') }}" method="GET" class="report-filter-form" data-filter-fields="start_date,end_date,status,request_type_id">
                        <div class="mb-3">
                            <label class="form-label small">Fecha Inicio</label>
                            <input type="date" name="start_date" class="form-control form-control-sm">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">Fecha Fin</label>
                            <input type="date" name="end_date" class="form-control form-control-sm">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">Estado</label>
                            <select name="status" class="form-select form-select-sm">
                                <option value="">Todos</option>
                                <option value="1">Pendiente</option>
                                <option value="2">En Progreso</option>
                                <option value="3">Completado</option>
                                <option value="4">Cancelado</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">Tópico</label>
                            <select name="request_type_id" class="form-select form-select-sm">
                                <option value="">Todos</option>
                                @foreach($topicOptions as $topic)
                                    <option value="{{ $topic->type_id }}">{{ $topic->type_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">Formato</label>
                            <select name="format" class="form-select form-select-sm">
                                <option value="excel">Excel (.xls)</option>
                                <option value="csv">CSV (.csv)</option>
                                <option value="pdf">PDF (.pdf)</option>
                            </select>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" name="preview" value="1" class="btn btn-outline-secondary w-100">Previsualizar</button>
                            <button type="submit" name="preview" value="0" class="btn text-white w-100" style="background-color:#d63384;">Exportar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Tickets Report -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow h-100 report-card">
                <div class="card-header bg-primary text-white report-card-header">
                    <h5 class="card-title mb-0 report-card-title"><i class="fas fa-ticket-alt me-2"></i>Reporte de Tickets</h5>
                </div>
                <div class="card-body">
                    <p class="card-text">Exporta información detallada de todos los tickets con filtros personalizables.</p>
                    
                    <form action="{{ route('admin.reports.tickets') }}" method="GET" class="report-filter-form" data-filter-fields="start_date,end_date,status,priority,current_phase">
                        <div class="mb-3">
                            <label class="form-label small">Fecha Inicio</label>
                            <input type="date" name="start_date" class="form-control form-control-sm">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">Fecha Fin</label>
                            <input type="date" name="end_date" class="form-control form-control-sm">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">Estado</label>
                            <select name="status" class="form-select form-select-sm">
                                <option value="">Todos</option>
                                <option value="1">Pendiente</option>
                                <option value="2">En Progreso</option>
                                <option value="3">Completado</option>
                                <option value="4">Cancelado</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">Prioridad</label>
                            <select name="priority" class="form-select form-select-sm">
                                <option value="">Todas</option>
                                <option value="low">Baja</option>
                                <option value="medium">Media</option>
                                <option value="high">Alta</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">Fase ADDIE</label>
                            <select name="current_phase" class="form-select form-select-sm">
                                <option value="">Todas</option>
                                <option value="Analysis">Análisis</option>
                                <option value="Design">Diseño</option>
                                <option value="Development">Desarrollo</option>
                                <option value="Implementation">Implementación</option>
                                <option value="Evaluation">Evaluación</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">Formato de Exportación</label>
                            <select name="format" class="form-select form-select-sm">
                                <option value="excel" selected>Excel (.xls)</option>
                                <option value="csv">CSV (.csv)</option>
                                <option value="pdf">PDF (.pdf)</option>
                            </select>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" name="preview" value="1" class="btn btn-outline-primary w-100">
                                <i class="fas fa-eye me-1"></i>Previsualizar
                            </button>
                            <button type="submit" name="preview" value="0" class="btn btn-primary w-100 report-export-submit">
                                <i class="fas fa-print me-1"></i>Imprimir / Exportar Excel
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-clear-report-filters" title="Limpiar filtros">
                                <i class="fas fa-eraser me-1"></i>Limpiar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Collaborators Report -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow h-100 report-card">
                <div class="card-header bg-success text-white report-card-header">
                    <h5 class="card-title mb-0 report-card-title"><i class="fas fa-users me-2"></i>Reporte de Colaboradores</h5>
                </div>
                <div class="card-body">
                    <p class="card-text">Exporta el rendimiento y estadísticas de todos los colaboradores.</p>
                    
                    <div class="alert alert-info small">
                        <i class="fas fa-info-circle me-1"></i>
                        Este reporte incluye:
                        <ul class="mb-0 mt-2">
                            <li>Total de tickets asignados</li>
                            <li>Tickets completados</li>
                            <li>Tickets en progreso</li>
                            <li>Tasa de completitud</li>
                        </ul>
                    </div>

                    <form action="{{ route('admin.reports.collaborators') }}" method="GET" class="mt-auto report-filter-form" data-filter-fields="is_active">
                        <div class="mb-3">
                            <label class="form-label small">Estado del Colaborador</label>
                            <select name="is_active" class="form-select form-select-sm">
                                <option value="">Seleccionar...</option>
                                <option value="1">Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">Formato de Exportación</label>
                            <select name="format" class="form-select form-select-sm">
                                <option value="excel" selected>Excel (.xls)</option>
                                <option value="csv">CSV (.csv)</option>
                                <option value="pdf">PDF (.pdf)</option>
                            </select>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" name="preview" value="1" class="btn btn-outline-success w-100">
                                <i class="fas fa-eye me-1"></i>Previsualizar
                            </button>
                            <button type="submit" name="preview" value="0" class="btn btn-success w-100 report-export-submit">
                                <i class="fas fa-print me-1"></i>Imprimir / Exportar Excel
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-clear-report-filters" title="Limpiar filtros">
                                <i class="fas fa-eraser me-1"></i>Limpiar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Progress Report -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow h-100 report-card">
                <div class="card-header bg-info text-white report-card-header">
                    <h5 class="card-title mb-0 report-card-title"><i class="fas fa-chart-line me-2"></i>Reporte de Progreso</h5>
                </div>
                <div class="card-body">
                    <p class="card-text">Exporta el historial de avances registrados por los colaboradores.</p>
                    
                    <form action="{{ route('admin.reports.progress') }}" method="GET" class="report-filter-form" data-filter-fields="start_date,end_date">
                        <div class="mb-3">
                            <label class="form-label small">Fecha Inicio</label>
                            <input type="date" name="start_date" class="form-control form-control-sm">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">Fecha Fin</label>
                            <input type="date" name="end_date" class="form-control form-control-sm">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">Formato de Exportación</label>
                            <select name="format" class="form-select form-select-sm">
                                <option value="excel" selected>Excel (.xls)</option>
                                <option value="csv">CSV (.csv)</option>
                                <option value="pdf">PDF (.pdf)</option>
                            </select>
                        </div>
                        
                        <div class="alert alert-warning small">
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            Incluye todas las actualizaciones de progreso registradas en el sistema.
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" name="preview" value="1" class="btn btn-outline-info w-100">
                                <i class="fas fa-eye me-1"></i>Previsualizar
                            </button>
                            <button type="submit" name="preview" value="0" class="btn btn-info w-100 report-export-submit">
                                <i class="fas fa-print me-1"></i>Imprimir / Exportar Excel
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-clear-report-filters" title="Limpiar filtros">
                                <i class="fas fa-eraser me-1"></i>Limpiar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Topics Report -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow h-100 report-card">
                <div class="card-header bg-secondary text-white report-card-header">
                    <h5 class="card-title mb-0 report-card-title"><i class="fas fa-tags me-2"></i>Reporte por Tópico</h5>
                </div>
                <div class="card-body">
                    <p class="card-text">Exporta estadísticas de solicitudes agrupadas por tópico con distribución por estado.</p>

                    <form action="{{ route('admin.reports.topics') }}" method="GET" class="report-filter-form" data-filter-fields="start_date,end_date,status,request_type_id">
                        <div class="mb-3">
                            <label class="form-label small">Fecha Inicio</label>
                            <input type="date" name="start_date" class="form-control form-control-sm">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">Fecha Fin</label>
                            <input type="date" name="end_date" class="form-control form-control-sm">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">Estado</label>
                            <select name="status" class="form-select form-select-sm">
                                <option value="">Todos</option>
                                <option value="1">Pendiente</option>
                                <option value="2">En Progreso</option>
                                <option value="3">Completado</option>
                                <option value="4">Cancelado</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">Tópico</label>
                            <select name="request_type_id" class="form-select form-select-sm">
                                <option value="">Todos los tópicos</option>
                                @foreach(($topicOptions ?? collect()) as $topic)
                                <option value="{{ $topic->type_id }}" {{ request('request_type_id') == $topic->type_id ? 'selected' : '' }}>
                                    {{ $topic->type_name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">Formato de Exportación</label>
                            <select name="format" class="form-select form-select-sm">
                                <option value="excel" selected>Excel (.xls)</option>
                                <option value="csv">CSV (.csv)</option>
                                <option value="pdf">PDF (.pdf)</option>
                            </select>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" name="preview" value="1" class="btn btn-outline-secondary w-100">
                                <i class="fas fa-eye me-1"></i>Previsualizar
                            </button>
                            <button type="submit" name="preview" value="0" class="btn btn-secondary w-100 report-export-submit">
                                <i class="fas fa-print me-1"></i>Imprimir / Exportar Excel
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-clear-report-filters" title="Limpiar filtros">
                                <i class="fas fa-eraser me-1"></i>Limpiar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @if(!empty($previewReport))
    <div class="row mt-2">
        <div class="col-12">
            <div class="card shadow border-primary">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-table me-2"></i>Previsualización: {{ $previewReport['title'] }}
                    </h5>
                    <span class="badge bg-light text-dark">Generado: {{ $previewReport['generated_at'] }}</span>
                </div>
                <div class="card-body">
                    @if(!empty($previewReport['filters']))
                    <div class="mb-3">
                        <h6 class="text-muted mb-2">Filtros aplicados</h6>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($previewReport['filters'] as $label => $value)
                            <span class="badge bg-light text-primary border">{{ $label }}: {{ $value }}</span>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-sm table-striped align-middle">
                            <thead>
                                <tr>
                                    @foreach($previewReport['columns'] as $column)
                                    <th>{{ $column }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($previewReport['rows'] as $row)
                                <tr>
                                    @foreach($row as $cell)
                                    <td>{{ $cell }}</td>
                                    @endforeach
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="{{ count($previewReport['columns']) }}" class="text-center text-muted py-3">
                                        Sin resultados para los filtros aplicados.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Info Section -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-info-circle me-2"></i>Información sobre los Reportes</h5>
                    <div class="row">
                        <div class="col-md-4">
                            <h6 class="text-primary">Formato de Exportación</h6>
                            <p class="small">Los reportes se pueden exportar en Excel (.xls), CSV (.csv) o PDF (.pdf).</p>
                        </div>
                        <div class="col-md-4">
                            <h6 class="text-success">Filtros Disponibles</h6>
                            <p class="small">Puedes filtrar por fechas, estados, prioridades y fases ADDIE para obtener reportes personalizados.</p>
                        </div>
                        <div class="col-md-4">
                            <h6 class="text-info">Datos Incluidos</h6>
                            <p class="small">Cada reporte incluye información detallada y actualizada del sistema en tiempo real.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const reportForms = document.querySelectorAll('.report-filter-form');

    function updateExportButtonLabel(form) {
        const formatField = form.querySelector('select[name="format"]');
        const submitButton = form.querySelector('.report-export-submit');
        if (!formatField || !submitButton) {
            return;
        }

        const format = String(formatField.value || 'excel').toLowerCase();
        if (format === 'csv') {
            submitButton.innerHTML = '<i class="fas fa-print me-1"></i>Imprimir / Exportar CSV';
            return;
        }

        if (format === 'pdf') {
            submitButton.innerHTML = '<i class="fas fa-print me-1"></i>Imprimir / Exportar PDF';
            return;
        }

        submitButton.innerHTML = '<i class="fas fa-print me-1"></i>Imprimir / Exportar Excel';
    }

    reportForms.forEach(function (form) {
        const clearButton = form.querySelector('.btn-clear-report-filters');
        if (clearButton) {
            clearButton.addEventListener('click', function () {
                form.reset();
                updateExportButtonLabel(form);
            });
        }

        const formatField = form.querySelector('select[name="format"]');
        if (formatField) {
            formatField.addEventListener('change', function () {
                updateExportButtonLabel(form);
            });
        }

        updateExportButtonLabel(form);

        form.addEventListener('submit', function (event) {
            const fields = (form.dataset.filterFields || '')
                .split(',')
                .map(field => field.trim())
                .filter(Boolean);

            const hasFilter = fields.some(function (fieldName) {
                const field = form.querySelector('[name="' + fieldName + '"]');
                return field && String(field.value).trim() !== '';
            });

            if (!hasFilter) {
                event.preventDefault();
                if (window.VirtualCenter && typeof window.VirtualCenter.showAlert === 'function') {
                    window.VirtualCenter.showAlert('Debes aplicar al menos un filtro antes de generar el reporte.', 'warning');
                } else {
                    alert('Debes aplicar al menos un filtro antes de generar el reporte.');
                }
            }
        });
    });
});
</script>
@endpush

@push('styles')
<style>
.reports-page .report-cards-row {
    margin-top: 0.15rem;
    padding-top: 0.35rem;
}

.reports-page .report-card {
    border-radius: 0.65rem;
    overflow: hidden;
}

.reports-page .report-card-header {
    min-height: 58px;
    display: flex;
    align-items: center;
}

.reports-page .report-card-title {
    line-height: 1.2;
    font-size: 1.2rem;
}

@media (max-width: 991.98px) {
    .reports-page .report-cards-row {
        padding-top: 0.15rem;
    }

    .reports-page .report-card-header {
        min-height: 52px;
    }

    .reports-page .report-card-title {
        font-size: 1.08rem;
    }
}
</style>
@endpush
@endsection
