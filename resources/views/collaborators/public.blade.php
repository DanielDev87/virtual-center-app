@extends('layouts.app')

@section('title', 'Colaboradores - A-DDIE')

@section('content')
<!-- Hero Section -->
<div class="bg-success text-white py-5">
    <div class="container">
        <div class="text-center">
            <h1 class="display-4 fw-bold mb-3">Nuestros Colaboradores</h1>
            <p class="lead mb-0">Conoce al equipo de profesionales que hacen posible el éxito de nuestro centro virtual</p>
        </div>
    </div>
</div>

<!-- Collaborators Grid -->
<div class="container my-5">
    @if($collaborators->count() > 0)
        <div class="row g-4">
            @foreach($collaborators as $collaborator)
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm hover-card">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            @if($collaborator->user_avatar)
                                <img src="{{ asset('storage/' . $collaborator->user_avatar) }}" 
                                     alt="{{ $collaborator->user_name }}" 
                                     class="rounded-circle" 
                                     width="100" 
                                     height="100"
                                     style="object-fit: cover;">
                            @else
                                <div class="bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center" 
                                     style="width: 100px; height: 100px;">
                                    <i class="fas fa-user fa-3x"></i>
                                </div>
                            @endif
                        </div>
                        
                        <h5 class="card-title mb-2">{{ $collaborator->user_name }}</h5>
                        
                        @if($collaborator->role)
                            <span class="badge bg-success mb-3">
                                {{ $collaborator->role->role_name }}
                            </span>
                        @endif
                        
                        @if($collaborator->user_bio)
                            <p class="card-text text-muted small">{{ Str::limit($collaborator->user_bio, 100) }}</p>
                        @endif
                        
                        <div class="d-flex justify-content-center gap-2 mt-3">
                            @if($collaborator->user_email)
                                <a href="mailto:{{ $collaborator->user_email }}" 
                                   class="btn btn-outline-success btn-sm" 
                                   title="Enviar correo">
                                    <i class="fas fa-envelope"></i>
                                </a>
                            @endif
                            
                            @if($collaborator->user_phone)
                                <a href="tel:{{ $collaborator->user_phone }}" 
                                   class="btn btn-outline-success btn-sm" 
                                   title="Llamar">
                                    <i class="fas fa-phone"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-5">
            <i class="fas fa-users fa-5x text-muted mb-4"></i>
            <h3 class="text-muted">No hay colaboradores disponibles</h3>
            <p class="text-muted">Los colaboradores aparecerán aquí cuando estén registrados en el sistema.</p>
        </div>
    @endif
</div>

<style>
.hover-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.hover-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 1rem rgba(0,0,0,.15) !important;
}
</style>
@endsection
