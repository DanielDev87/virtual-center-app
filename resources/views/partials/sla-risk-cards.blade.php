@php
    $slaCards = [
        'on_time' => ['Dentro del plazo', 'success', 'fa-check-circle'],
        'at_risk' => ['Próximos a vencer', 'warning', 'fa-exclamation-triangle'],
        'overdue' => ['Vencidos', 'danger', 'fa-times-circle'],
    ];
@endphp
<div class="row g-3 mb-4">
    @foreach($slaCards as $slaKey => [$slaLabel, $slaColor, $slaIcon])
    <div class="col-md-4">
        <a href="{{ route($slaRoute, ['sla' => $slaKey]) }}" class="text-decoration-none">
            <div class="card border-{{ $slaColor }} shadow-sm h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small text-muted">{{ $slaLabel }}</div>
                        <div class="h3 mb-0">{{ $slaSummary[$slaKey] ?? 0 }}</div>
                    </div>
                    <i class="fas {{ $slaIcon }} fa-2x text-{{ $slaColor }}"></i>
                </div>
            </div>
        </a>
    </div>
    @endforeach
</div>