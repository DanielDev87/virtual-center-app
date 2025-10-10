@props(['size' => 'md', 'text' => 'Cargando...'])

@php
$sizeClasses = [
    'sm' => 'spinner-border-sm',
    'md' => '',
    'lg' => 'spinner-border-lg'
];
@endphp

<div class="d-flex align-items-center justify-content-center p-4">
    <div class="text-center">
        <div class="spinner-border {{ $sizeClasses[$size] ?? '' }} text-primary" role="status">
            <span class="visually-hidden">Cargando...</span>
        </div>
        @if($text)
        <div class="mt-2 text-muted">{{ $text }}</div>
        @endif
    </div>
</div>
