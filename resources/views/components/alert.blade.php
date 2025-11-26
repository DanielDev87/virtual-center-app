@props(['type' => 'info', 'dismissible' => true, 'message' => ''])

@php
$alertClasses = [
    'success' => 'alert-success',
    'danger' => 'alert-danger',
    'warning' => 'alert-warning',
    'info' => 'alert-info',
    'primary' => 'alert-primary',
    'secondary' => 'alert-secondary',
    'light' => 'alert-light',
    'dark' => 'alert-dark'
];

$iconClasses = [
    'success' => 'fas fa-check-circle',
    'danger' => 'fas fa-exclamation-triangle',
    'warning' => 'fas fa-exclamation-circle',
    'info' => 'fas fa-info-circle',
    'primary' => 'fas fa-info-circle',
    'secondary' => 'fas fa-info-circle',
    'light' => 'fas fa-info-circle',
    'dark' => 'fas fa-info-circle'
];
@endphp

<div class="alert {{ $alertClasses[$type] ?? 'alert-info' }} {{ $dismissible ? 'alert-dismissible fade show' : '' }}" role="alert">
    <div class="d-flex align-items-center">
        <i class="{{ $iconClasses[$type] ?? 'fas fa-info-circle' }} me-2"></i>
        <div class="flex-grow-1">
            {{ $message ?: $slot }}
        </div>
    </div>
    @if($dismissible)
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    @endif
</div>

