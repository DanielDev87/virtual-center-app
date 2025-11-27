@extends('layouts.admin')

@section('title', 'Reportes - A-DDIE')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
        <h1 class="h2"><i class="fas fa-file-export me-2"></i>Módulo de Reportes</h1>
    </div>

    <div class="row">
        <!-- Tickets Report -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0"><i class="fas fa-ticket-alt me-2"></i>Reporte de Tickets</h5>
                </div>
                <div class="card-body">
                    <p class="card-text">Exporta información detallada de todos los tickets con filtros personalizables.</p>
                    
                    <form action="{{ route('admin.reports.tickets') }}" method="GET">
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
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-download me-1"></i>Exportar Excel
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Collaborators Report -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow h-100">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0"><i class="fas fa-users me-2"></i>Reporte de Colaboradores</h5>
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

                    <form action="{{ route('admin.reports.collaborators') }}" method="GET" class="mt-auto">
                        <button type="submit" class="btn btn-success w-100">
                            <i class="fas fa-download me-1"></i>Exportar Excel
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Progress Report -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow h-100">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0"><i class="fas fa-chart-line me-2"></i>Reporte de Progreso</h5>
                </div>
                <div class="card-body">
                    <p class="card-text">Exporta el historial de avances registrados por los colaboradores.</p>
                    
                    <form action="{{ route('admin.reports.progress') }}" method="GET">
                        <div class="mb-3">
                            <label class="form-label small">Fecha Inicio</label>
                            <input type="date" name="start_date" class="form-control form-control-sm">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">Fecha Fin</label>
                            <input type="date" name="end_date" class="form-control form-control-sm">
                        </div>
                        
                        <div class="alert alert-warning small">
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            Incluye todas las actualizaciones de progreso registradas en el sistema.
                        </div>

                        <button type="submit" class="btn btn-info w-100">
                            <i class="fas fa-download me-1"></i>Exportar Excel
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Info Section -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-info-circle me-2"></i>Información sobre los Reportes</h5>
                    <div class="row">
                        <div class="col-md-4">
                            <h6 class="text-primary">Formato de Exportación</h6>
                            <p class="small">Los reportes se exportan en formato CSV compatible con Excel, con codificación UTF-8 para caracteres especiales.</p>
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
@endsection
