@php
    $slaInfo = app(\App\Services\SlaRiskService::class)->describe($ticket);
    $slaBadge = ['on_time' => 'success', 'at_risk' => 'warning', 'overdue' => 'danger'];
@endphp
@if($slaInfo)
<div class="mt-3 mb-3">
    <h6 class="fw-bold mb-2">
        <i class="fas fa-hourglass-half me-1"></i>Tiempo de atención (SLA)
    </h6>
    <p class="mb-1">
        <span class="badge bg-{{ $slaBadge[$slaInfo['state']] }}">{{ $slaInfo['label'] }}</span>
        <span class="ms-1">{{ $slaInfo['remaining_text'] }}</span>
    </p>
    <p class="small text-muted mb-0">
        <i class="fas fa-flag me-1"></i>Siguiente hito: {{ $slaInfo['next_milestone'] }}
    </p>
</div>
@endif