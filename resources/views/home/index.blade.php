@extends('layouts.app')

@section('title', 'Inicio - Sistema de Soporte Universitario')

@section('content')
<!-- Services Section -->
<div class="home-landing-section py-5 min-vh-100 d-flex align-items-center">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold vc-accent-text">Acceso Rápido</h2>
            <p class="lead text-muted">Selecciona una opción para comenzar</p>
        </div>
        
        <div class="row justify-content-center g-4">
            
            <!-- Card 1: Portal Público de Tickets -->
            <div class="col-12 col-md-10 col-lg-8 col-xl-7">
                <div class="card border-0 shadow-lg h-100 hover-card home-access-card">
                    <div class="card-body text-center p-5 d-flex flex-column">
                        <div class="mb-4 home-access-icon-wrap">
                            <img src="{{ asset('img/logo-soporte-Fondo-Blanco.png') }}" alt="Logo Soporte" class="home-access-logo">
                        </div>
                        <p class="card-text text-muted d-sm-none home-mobile-summary mb-2">
                            Crea una solicitud y consulta su estado con el número de ticket en cualquier momento.
                        </p>
                        <div class="card-text text-muted mb-3 text-start collapse d-sm-block" id="publicPortalDetails" style="font-size: 0.9rem;">
                            <p>Por este medio podrá solicitar atención y soporte técnico relacionado con claves de acceso y funcionamiento técnico de todos los sistemas informáticos institucionales, tales como:</p>
                            <ul class="ps-3 mb-3">
                                <li class="mb-2"><strong>Sistemas de uso público:</strong> Red Inalámbrica (sede Medellín), Sistema Académico, Bases de datos en línea, Correo electrónico @amigo.edu.co, Intranet, y Sistema de Bienestar Virtual.</li>
                                <li><strong>Sistemas de uso exclusivo para docentes y empleados:</strong> Intranet Redentor, Correo electrónico @amigo.edu.co, Infraestructura tecnológica (hardware y software), Conexión de redes, Solicitud de Reportes SUI.</li>
                            </ul>
                            <p class="mb-0 text-justify">Una vez ingresada su solicitud, se le asignará un único número o código que se le enviará al correo ingresado para así visualizar el progreso y respuestas en línea. Además, este sistema le proporciona los archivos y la historia completa de todas sus solicitudes realizadas.</p>
                        </div>
                        <button class="btn btn-sm btn-outline-secondary d-sm-none mb-2 home-mobile-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#publicPortalDetails" aria-expanded="false" aria-controls="publicPortalDetails">
                            <i class="fas fa-align-left me-1"></i>Ver detalles del portal
                        </button>
                        
                        <div class="d-grid gap-2 d-sm-flex justify-content-sm-center mt-auto">
                            <a href="{{ route('service-management.create') }}" class="btn btn-primary px-3" title="Clic aquí para abrir una solicitud nueva, por favor ser lo más detallado posible, gracias.">
                                <i class="fas fa-plus-circle me-1"></i>Nueva Solicitud
                            </a>
                            <a href="{{ route('service-management.track') }}" class="btn btn-outline-vc-accent px-3" title="Clic aquí para observar el estado de mi solicitud enviado.">
                                <i class="fas fa-search me-1"></i>Consultar Estado
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.home-access-logo {
    width: 220px;
    height: auto;
}

@media (max-width: 991.98px) {
    .home-access-card {
        border-radius: 0.85rem;
    }

    .home-access-logo {
        width: 180px;
    }

    .home-access-icon {
        font-size: 2.6rem !important;
    }

    .home-access-icon-wrap {
        margin-bottom: 0.9rem !important;
    }
}

@media (max-width: 991.98px) {
    .home-landing-section {
        padding-top: 1.5rem !important;
        padding-bottom: 1.5rem !important;
        align-items: flex-start !important;
    }

    .home-landing-section .lead {
        font-size: 1rem;
    }

    .home-landing-section .display-5 {
        font-size: 1.8rem;
    }

    .home-landing-section .card-body {
        padding: 1.25rem !important;
    }

    .home-landing-section .card-text {
        font-size: 0.95rem !important;
    }
}

@media (max-width: 575.98px) {
    .home-landing-section {
        min-height: auto !important;
    }

    .home-landing-section .display-5 {
        font-size: 1.55rem;
    }

    .home-landing-section .container {
        padding-left: 0.9rem;
        padding-right: 0.9rem;
    }

    .home-landing-section .mb-5 {
        margin-bottom: 1.2rem !important;
    }

    .home-landing-section .card-title {
        font-size: 1.2rem;
    }

    .home-landing-section .card-text {
        font-size: 0.88rem !important;
    }

    .home-mobile-summary {
        font-size: 0.85rem !important;
        line-height: 1.35;
    }

    .home-landing-section .card-body {
        padding: 0.9rem !important;
    }

    .home-access-icon {
        font-size: 1.95rem !important;
    }

    .home-access-logo {
        width: 145px;
    }

    .home-mobile-toggle {
        font-size: 0.78rem;
        padding-top: 0.2rem;
        padding-bottom: 0.2rem;
    }

    .home-landing-section .row.g-4 {
        --bs-gutter-y: 0.7rem;
    }

    .home-landing-section .btn {
        width: 100%;
        padding-top: 0.4rem;
        padding-bottom: 0.4rem;
        font-size: 0.9rem;
    }

    .home-landing-section .d-sm-flex {
        display: flex !important;
        flex-direction: column;
        width: 100%;
    }
}
</style>
@endpush

<!-- Info Modal -->
<div class="modal fade" id="infoModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header vc-accent-bg text-white">
                <img src="{{ asset('img/logo-virtual-center.png') }}" alt="Sistema de Soporte Universitario" height="30" class="me-2">
                <h5 class="modal-title" id="infoModalLabel">Sistema de Soporte Universitario</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <h6 class="fw-bold">Sistema de Gestión de Proyectos Educativos</h6>
                <p>El Sistema de Soporte Universitario (basado en A-DDIE) es una plataforma integral para la gestión eficiente de solicitudes y requerimientos técnicos institucionales.</p>
                
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

