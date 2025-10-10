@props(['type' => 'button', 'variant' => 'primary', 'size' => 'md', 'outline' => false, 'block' => false, 'disabled' => false])

@php
$variantClasses = [
    'primary' => 'btn-primary',
    'secondary' => 'btn-secondary',
    'success' => 'btn-success',
    'danger' => 'btn-danger',
    'warning' => 'btn-warning',
    'info' => 'btn-info',
    'light' => 'btn-light',
    'dark' => 'btn-dark',
    'link' => 'btn-link'
];

$sizeClasses = [
    'sm' => 'btn-sm',
    'md' => '',
    'lg' => 'btn-lg'
];

$classes = 'btn ' . ($outline ? 'btn-outline-' . $variant : $variantClasses[$variant] ?? 'btn-primary');
$classes .= ' ' . ($sizeClasses[$size] ?? '');
$classes .= $block ? ' btn-block w-100' : '';
@endphp

<button type="{{ $type }}" class="{{ $classes }}" {{ $disabled ? 'disabled' : '' }} {{ $attributes }}>
    {{ $slot }}
</button>
