@extends('layouts.app')

@section('title', 'Inicio - A-DDIE')

@section('content')
<!-- Services Section -->
<div class="bg-light py-5 min-vh-100 d-flex align-items-center">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold text-success">Acceso Rápido</h2>
            <p class="lead text-muted">Selecciona una opción para comenzar</p>
        </div>
        
        <div class="row justify-content-center g-4">
            <div class="col-md-5 col-lg-4">
                <div class="card border-0 shadow-lg h-100 hover-card">
                    <div class="card-body text-center p-5">
                        <div class="mb-4">
                            <i class="fas fa-laptop-code fa-4x text-success"></i>
                        </div>
                        <h4 class="card-title fw-bold mb-3">Gestión de Servicios</h4>
                        <p class="card-text text-muted mb-4">Consulta el estado del proceso de elaboración de proyectos y servicios.</p>
                        <a href="{{ route('service-management.index') }}" class="btn btn-success btn-lg">
                            <i class="fas fa-arrow-right me-2"></i>Acceder
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-5 col-lg-4">
                <div class="card border-0 shadow-lg h-100 hover-card">
                    <div class="card-body text-center p-5">
                        <div class="mb-4">
                            <i class="fas fa-users fa-4x text-success"></i>
                        </div>
                        <h4 class="card-title fw-bold mb-3">Colaboradores</h4>
                        <p class="card-text text-muted mb-4">Conoce nuestros colaboradores del Centro Virtual y su experiencia.</p>
                        <a href="{{ route('collaborators') }}" class="btn btn-success btn-lg">
                            <i class="fas fa-arrow-right me-2"></i>Ver Equipo
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Info Modal -->
<div class="modal fade" id="infoModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <img src="{{ asset('img/logo-virtual-center.png') }}" alt="A-DDIE" height="30" class="me-2">
                <h5 class="modal-title" id="infoModalLabel">A-DDIE</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <h6 class="fw-bold">Sistema de Gestión de Proyectos Educativos</h6>
                <p>A-DDIE es una plataforma integral que combina la metodología ADDIE (Análisis, Diseño, Desarrollo, Implementación, Evaluación) con prácticas ágiles de SCRUM para la gestión eficiente de proyectos educativos.</p>
                
                <h6 class="fw-bold mt-4">Características Principales:</h6>
                <ul>
                    <li>Gestión de tickets con seguimiento de progreso</li>
                    <li>Metodología ADDIE integrada</li>
                    <li>Tableros Kanban para gestión ágil</li>
                    <li>Sprints y gestión de tareas</li>
                    <li>Reportes y análisis de rendimiento</li>
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<style>
.hover-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.hover-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 1rem 3rem rgba(0,0,0,.175) !important;
}

.carousel-caption {
    bottom: 20%;
}
</style>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Inicializar carousel
    $('#heroCarousel').carousel({
        interval: 5000,
        wrap: true
    });
});
</script>
@endpush
