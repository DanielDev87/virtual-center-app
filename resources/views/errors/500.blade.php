@extends('layouts.app')

@section('title', 'Error del Servidor - Virtual Center')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 text-center">
            <div class="py-5">
                <div class="mb-4">
                    <i class="fas fa-server fa-5x text-danger"></i>
                </div>
                <h1 class="display-1 fw-bold text-danger">500</h1>
                <h2 class="h3 mb-3">Error del Servidor</h2>
                <p class="lead text-muted mb-4">
                    Ha ocurrido un error interno en el servidor. Nuestro equipo técnico ha sido notificado.
                </p>
                <div class="d-flex justify-content-center gap-3">
                    <a href="{{ route('home') }}" class="btn btn-primary">
                        <i class="fas fa-home me-2"></i>Ir al Inicio
                    </a>
                    <button onclick="location.reload()" class="btn btn-outline-secondary">
                        <i class="fas fa-refresh me-2"></i>Intentar Nuevamente
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
