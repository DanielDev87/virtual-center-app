@extends('layouts.admin')

@section('title', 'Tendencias de calificación')

@section('content')
@php
    $exportUrl = route('admin.reports.ratings', array_merge(request()->query(), ['export' => 'csv']));
    $barColors = [5 => 'success', 4 => 'success', 3 => 'warning', 2 => 'danger', 1 => 'danger'];
@endphp
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3 border-bottom pb-2">
        <h1 class="h3 mb-0">Tendencias de calificación del servicio</h1>
        <a href="{{ route('admin.reports.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Volver a reportes
        </a>
    </div>

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="GET" action="{{ route('admin.reports.ratings') }}" class="card card-body mb-3">
        <div class="row g-2 align-items-end">
            <div class="col-md-3">
                <label for="rtStart" class="form-label small fw-semibold mb-1">Desde</label>
                <input id="rtStart" type="date" name="start_date" class="form-control" value="{{ $filters['start_date'] }}">
            </div>
            <div class="col-md-3">
                <label for="rtEnd" class="form-label small fw-semibold mb-1">Hasta</label>
                <input id="rtEnd" type="date" name="end_date" class="form-control" value="{{ $filters['end_date'] }}">
            </div>
            <div class="col-md-3">
                <label for="rtTopic" class="form-label small fw-semibold mb-1">Tópico</label>
                <select id="rtTopic" name="request_type_id" class="form-select">
                    <option value="">Todos los tópicos</option>
                    @foreach($topics as $topic)
                        <option value="{{ $topic->type_id }}" {{ (int) $filters['request_type_id'] === (int) $topic->type_id ? 'selected' : '' }}>{{ $topic->type_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="rtArea" class="form-label small fw-semibold mb-1">Área</label>
                <select id="rtArea" name="area_id" class="form-select">
                    <option value="">Todas las áreas</option>
                    @foreach($areas as $area)
                        <option value="{{ $area->area_id }}" {{ (int) $filters['area_id'] === (int) $area->area_id ? 'selected' : '' }}>{{ $area->area_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 d-flex justify-content-end gap-2">
                <button type="submit" class="btn btn-primary"><i class="fas fa-check me-1"></i>Aplicar</button>
                <a href="{{ route('admin.reports.ratings') }}" class="btn btn-outline-secondary"><i class="fas fa-eraser me-1"></i>Limpiar filtros</a>
                @if($total > 0)
                    <a href="{{ $exportUrl }}" class="btn btn-outline-success"><i class="fas fa-file-csv me-1"></i>Exportar CSV</a>
                @endif
            </div>
        </div>
        <div class="form-text mt-2">
            @if($defaultPeriod) Sin fechas elegidas se usan los últimos 6 meses. @endif
            La fecha de cada calificación es la de la última actualización del ticket.
        </div>
    </form>

    @if($total === 0)
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-1"></i>No hay calificaciones en este periodo con los filtros elegidos.
        </div>
    @else
        @if($insufficient)
            <div class="alert alert-warning">
                <i class="fas fa-triangle-exclamation me-1"></i>
                <strong>Datos insuficientes:</strong> hay solo {{ $total }} calificación(es) en este periodo (se recomiendan al menos {{ $minTotal }}).
                Los promedios son orientativos y no conviene sacar conclusiones de ellos.
            </div>
        @endif

        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <div class="card shadow-sm h-100"><div class="card-body text-center">
                    <div class="small text-muted">Respuestas</div>
                    <div class="display-6">{{ $total }}</div>
                </div></div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm h-100"><div class="card-body text-center">
                    <div class="small text-muted">Promedio general</div>
                    <div class="display-6">{{ number_format($average, 2) }} <i class="fas fa-star text-warning fs-5"></i></div>
                    @if($insufficient)<div class="small text-muted">orientativo</div>@endif
                </div></div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm h-100"><div class="card-body">
                    <div class="small text-muted mb-1">Cambio en el periodo</div>
                    @if($change && $change['enough'])
                        <div>
                            Entre <strong>{{ $change['from'] }}</strong> y <strong>{{ $change['to'] }}</strong>, el promedio
                            @if($change['diff'] > 0) <span class="text-success fw-semibold">subió {{ number_format($change['diff'], 2) }}</span>
                            @elseif($change['diff'] < 0) <span class="text-danger fw-semibold">bajó {{ number_format(abs($change['diff']), 2) }}</span>
                            @else <span class="fw-semibold">no cambió</span> @endif
                            puntos.
                        </div>
                    @elseif($change)
                        <div class="text-muted">No hay suficientes respuestas en el primer y el último mes (mínimo {{ $minGroup }} en cada uno) para afirmar una tendencia.</div>
                    @else
                        <div class="text-muted">Se necesitan al menos dos meses con calificaciones para ver un cambio.</div>
                    @endif
                </div></div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-lg-5">
                <div class="card shadow-sm h-100">
                    <div class="card-header">Distribución de calificaciones</div>
                    <div class="card-body">
                        @foreach($distribution as $row)
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <div style="width: 70px;">{{ $row['stars'] }} <i class="fas fa-star text-warning"></i></div>
                                <div class="progress flex-grow-1" style="height: 16px;">
                                    <div class="progress-bar bg-{{ $barColors[$row['stars']] }}" role="progressbar" style="width: {{ $row['percent'] }}%"></div>
                                </div>
                                <div style="width: 110px;" class="small text-end">{{ $row['count'] }} ({{ $row['percent'] }}%)</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="card shadow-sm h-100">
                    <div class="card-header">Evolución por mes</div>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead class="table-light"><tr><th>Mes</th><th class="text-end">Respuestas</th><th class="text-end">Promedio</th></tr></thead>
                            <tbody>
                                @foreach($trend as $row)
                                    <tr>
                                        <td class="text-capitalize">{{ $row['label'] }}</td>
                                        <td class="text-end">{{ $row['total'] }}</td>
                                        <td class="text-end">
                                            @if($row['enough']) {{ number_format($row['average'], 2) }}
                                            @else <span class="badge bg-secondary">Pocas respuestas</span> @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            @foreach([['Por tópico', $byTopic], ['Por área', $byArea]] as [$groupTitle, $groupRows])
                <div class="col-lg-6">
                    <div class="card shadow-sm h-100">
                        <div class="card-header">{{ $groupTitle }}</div>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead class="table-light"><tr><th>{{ $groupTitle === 'Por tópico' ? 'Tópico' : 'Área' }}</th><th class="text-end">Respuestas</th><th class="text-end">Promedio</th></tr></thead>
                                <tbody>
                                    @foreach($groupRows as $row)
                                        <tr>
                                            <td>{{ $row['label'] }}</td>
                                            <td class="text-end">{{ $row['total'] }}</td>
                                            <td class="text-end">
                                                @if($row['enough']) {{ number_format($row['average'], 2) }}
                                                @else <span class="badge bg-secondary">Pocas respuestas</span> @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        @if($isAdmin && $comments->isNotEmpty())
            <div class="card shadow-sm mb-3">
                <div class="card-header">Comentarios recientes <span class="small text-muted">(solo visible para Admin; sin datos del solicitante)</span></div>
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light"><tr><th>Ticket</th><th>Calificación</th><th>Comentario</th><th>Fecha</th></tr></thead>
                        <tbody>
                            @foreach($comments as $comment)
                                <tr>
                                    <td><a href="{{ route('admin.tickets.show', $comment->ticket_id) }}">#{{ $comment->ticket_number }}</a></td>
                                    <td>{{ $comment->rating }} <i class="fas fa-star text-warning"></i></td>
                                    <td>{{ \Illuminate\Support\Str::limit($comment->feedback, 200) }}</td>
                                    <td>{{ optional($comment->updated_at)->format('d/m/Y') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    @endif
</div>
@endsection