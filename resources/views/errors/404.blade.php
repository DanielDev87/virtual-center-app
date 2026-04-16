@extends('layouts.app')

@section('title', 'Página No Encontrada - Virtual Center')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 text-center">
            <div class="py-5">
                <div class="mb-4">
                    <i class="fas fa-exclamation-triangle fa-5x text-warning"></i>
                </div>
                <h1 class="display-1 fw-bold text-primary">404</h1>
                <h2 class="h3 mb-3">Página No Encontrada</h2>
                <p class="lead text-muted mb-4">
                    Lo sentimos, la página que buscas no existe o ha sido movida.
                </p>
                <div class="d-flex justify-content-center gap-3">
                    <a href="{{ route('home') }}" class="btn btn-primary">
                        <i class="fas fa-home me-2"></i>Ir al Inicio
                    </a>
                    <button onclick="history.back()" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Volver Atrás
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


