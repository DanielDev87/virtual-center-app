@extends('layouts.app')

@section('title', 'Colaboradores - Virtual Center')

@section('content')
<!-- Hero Section -->
<div class="bg-primary text-white py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="display-4 fw-bold mb-3">Nuestros Colaboradores</h1>
                <p class="lead mb-4">Conoce al equipo de profesionales que hacen posible el éxito de nuestro centro virtual.</p>
                <div class="d-flex gap-3">
                    <span class="badge bg-light text-primary fs-6">{{ $collaborators->count() }} Colaboradores</span>
                    <span class="badge bg-light text-primary fs-6">Equipo Multidisciplinario</span>
                </div>
            </div>
            <div class="col-lg-4 text-center">
                <i class="fas fa-users fa-5x opacity-75"></i>
            </div>
        </div>
    </div>
</div>

<!-- Collaborators Grid -->
<div class="container my-5">
    @if($collaborators->count() > 0)
        <div class="row g-4">
            @foreach($collaborators as $collaborator)
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            @if($collaborator->user_avatar)
                                <img src="{{ asset('storage/' . $collaborator->user_avatar) }}" 
                                     alt="{{ $collaborator->user_name }}" 
                                     class="rounded-circle" 
                                     width="80" 
                                     height="80"
                                     style="object-fit: cover;">
                            @else
                                <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center" 
                                     style="width: 80px; height: 80px;">
                                    <i class="fas fa-user fa-2x"></i>
                                </div>
                            @endif
                        </div>
                        
                        <h5 class="card-title mb-2">{{ $collaborator->user_name }}</h5>
                        
                        @if($collaborator->role)
                            <span class="badge bg-{{ $collaborator->role->role_color ?? 'primary' }} mb-3">
                                {{ $collaborator->role->role_name }}
                            </span>
                        @endif
                        
                        @if($collaborator->user_bio)
                            <p class="card-text text-muted">{{ Str::limit($collaborator->user_bio, 100) }}</p>
                        @endif
                        
                        <div class="d-flex justify-content-center gap-2">
                            @if($collaborator->user_email)
                                <a href="mailto:{{ $collaborator->user_email }}" 
                                   class="btn btn-outline-primary btn-sm" 
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

<!-- Statistics Section -->
<div class="bg-light py-5">
    <div class="container">
        <div class="row text-center">
            <div class="col-md-3 col-6 mb-4">
                <div class="card border-0 h-100">
                    <div class="card-body">
                        <i class="fas fa-users fa-3x text-primary mb-3"></i>
                        <h3 class="fw-bold">{{ $collaborators->count() }}</h3>
                        <p class="text-muted mb-0">Colaboradores Activos</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-4">
                <div class="card border-0 h-100">
                    <div class="card-body">
                        <i class="fas fa-graduation-cap fa-3x text-success mb-3"></i>
                        <h3 class="fw-bold">{{ $collaborators->where('role.role_name', 'like', '%educador%')->count() }}</h3>
                        <p class="text-muted mb-0">Educadores</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-4">
                <div class="card border-0 h-100">
                    <div class="card-body">
                        <i class="fas fa-laptop-code fa-3x text-warning mb-3"></i>
                        <h3 class="fw-bold">{{ $collaborators->where('role.role_name', 'like', '%tecnolog%')->count() }}</h3>
                        <p class="text-muted mb-0">Tecnólogos</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-4">
                <div class="card border-0 h-100">
                    <div class="card-body">
                        <i class="fas fa-handshake fa-3x text-info mb-3"></i>
                        <h3 class="fw-bold">{{ $collaborators->where('role.role_name', 'like', '%mediador%')->count() }}</h3>
                        <p class="text-muted mb-0">Mediadores</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Join Us Section -->
<div class="container my-5">
    <div class="row align-items-center">
        <div class="col-lg-6">
            <h2 class="fw-bold text-primary mb-4">¿Quieres ser parte de nuestro equipo?</h2>
            <p class="lead text-muted mb-4">
                Estamos siempre buscando profesionales apasionados por la educación virtual y la innovación tecnológica.
            </p>
            <ul class="list-unstyled">
                <li class="mb-2">
                    <i class="fas fa-check text-success me-2"></i>
                    Ambiente de trabajo colaborativo
                </li>
                <li class="mb-2">
                    <i class="fas fa-check text-success me-2"></i>
                    Oportunidades de crecimiento profesional
                </li>
                <li class="mb-2">
                    <i class="fas fa-check text-success me-2"></i>
                    Proyectos innovadores y desafiantes
                </li>
                <li class="mb-2">
                    <i class="fas fa-check text-success me-2"></i>
                    Flexibilidad horaria y trabajo remoto
                </li>
            </ul>
            <button class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#contactModal">
                <i class="fas fa-paper-plane me-2"></i>Contáctanos
            </button>
        </div>
        <div class="col-lg-6 text-center">
            <img src="{{ asset('img/collaboration.jpg') }}" alt="Colaboración" class="img-fluid rounded">
        </div>
    </div>
</div>

<!-- Contact Modal -->
<div class="modal fade" id="contactModal" tabindex="-1" aria-labelledby="contactModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="contactModalLabel">Contáctanos</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-4">
                    Para unirte a nuestro equipo, por favor envía tu información de contacto y te responderemos pronto.
                </p>
                <form>
                    <div class="mb-3">
                        <label for="contactName" class="form-label">Nombre Completo</label>
                        <input type="text" class="form-control" id="contactName" required>
                    </div>
                    <div class="mb-3">
                        <label for="contactEmail" class="form-label">Correo Electrónico</label>
                        <input type="email" class="form-control" id="contactEmail" required>
                    </div>
                    <div class="mb-3">
                        <label for="contactPhone" class="form-label">Teléfono</label>
                        <input type="tel" class="form-control" id="contactPhone">
                    </div>
                    <div class="mb-3">
                        <label for="contactMessage" class="form-label">Mensaje</label>
                        <textarea class="form-control" id="contactMessage" rows="4" 
                                  placeholder="Cuéntanos sobre tu experiencia y por qué te interesa unirte a nuestro equipo..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary">
                    <i class="fas fa-paper-plane me-2"></i>Enviar Mensaje
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize contact form
    $('#contactModal form').on('submit', function(e) {
        e.preventDefault();
        
        const formData = {
            name: $('#contactName').val(),
            email: $('#contactEmail').val(),
            phone: $('#contactPhone').val(),
            message: $('#contactMessage').val()
        };
        
        // Validate form
        if (!formData.name || !formData.email || !formData.message) {
            VirtualCenter.showAlert('Por favor, completa todos los campos obligatorios', 'warning');
            return;
        }
        
        // Simulate form submission
        VirtualCenter.showAlert('¡Gracias por tu interés! Te contactaremos pronto.', 'success');
        $('#contactModal').modal('hide');
        $(this)[0].reset();
    });
});
</script>
@endpush


