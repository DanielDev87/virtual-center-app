@props(['type' => 'primary', 'size' => 'md', 'pill' => false])

@php
$typeClasses = [
    'primary' => 'bg-primary',
    'secondary' => 'bg-secondary',
    'success' => 'bg-success',
    'danger' => 'bg-danger',
    'warning' => 'bg-warning',
    'info' => 'bg-info',
    'light' => 'bg-light text-dark',
    'dark' => 'bg-dark'
];

$sizeClasses = [
    'sm' => 'badge-sm',
    'md' => '',
    'lg' => 'badge-lg'
];
@endphp

<span class="badge {{ $typeClasses[$type] ?? 'bg-primary' }} {{ $sizeClasses[$size] ?? '' }} {{ $pill ? 'rounded-pill' : '' }}">
    {{ $slot }}
</span>
