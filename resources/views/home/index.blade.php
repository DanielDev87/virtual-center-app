@extends('layouts.app')

@section('title', 'Inicio - Virtual Center')

@section('content')
<!-- Hero Section -->
<div id="heroCarousel" class="carousel slide" data-bs-ride="carousel">
    <div class="carousel-inner">
        <div class="carousel-item active">
            <img src="{{ asset('img/banners/banner-001.jpg') }}" class="d-block w-100" alt="Banner 1" style="height: 400px; object-fit: cover;">
        </div>
        <div class="carousel-item">
            <img src="{{ asset('img/banners/banner-002.jpg') }}" class="d-block w-100" alt="Banner 2" style="height: 400px; object-fit: cover;">
        </div>
        <div class="carousel-item">
            <img src="{{ asset('img/banners/banner-003.jpg') }}" class="d-block w-100" alt="Banner 3" style="height: 400px; object-fit: cover;">
        </div>
    </div>
</div>

<!-- Services Section -->
<div class="bg-primary text-white py-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-laptop-code fa-3x text-primary mb-3"></i>
                        <h5 class="card-title text-dark">Gestión de Servicios</h5>
                        <p class="card-text text-muted">Consulta el estado del proceso de elaboración de proyectos y servicios.</p>
                        <a href="{{ route('service-management.index') }}" class="stretched-link"></a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-users fa-3x text-primary mb-3"></i>
                        <h5 class="card-title text-dark">Colaboradores</h5>
                        <p class="card-text text-muted">Conoce nuestros colaboradores del Centro Virtual.</p>
                        <a href="{{ route('collaborators') }}" class="stretched-link"></a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-radio fa-3x text-primary mb-3"></i>
                        <h5 class="card-title text-dark">Emisora en Vivo</h5>
                        <p class="card-text text-muted">Micrositio de entretenimiento. Emisora Universitaria.</p>
                        <a href="{{ route('radio-station') }}" target="_blank" class="stretched-link"></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Components Section -->
<div class="container my-5">
    <div class="text-center mb-5">
        <h2 class="display-4 text-primary">Componentes</h2>
        <p class="lead text-muted">Descubre las áreas principales de nuestro centro virtual</p>
    </div>

    <!-- Pedagogical Component -->
    <section class="my-5">
        <div class="row align-items-center">
            <div class="col-lg-6 order-lg-2">
                <div class="p-5">
                    <img class="img-fluid rounded-circle" src="{{ asset('img/components/pedagogical.jpg') }}" alt="Componente Pedagógico">
                </div>
            </div>
            <div class="col-lg-6 order-lg-1">
                <div class="p-5">
                    <h2 class="fw-bold text-primary">Pedagógico</h2>
                    <p class="lead">Desarrollo de estrategias educativas innovadoras y metodologías de enseñanza virtual que potencian el aprendizaje.</p>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-check text-success me-2"></i>Diseño instruccional</li>
                        <li><i class="fas fa-check text-success me-2"></i>Metodologías activas</li>
                        <li><i class="fas fa-check text-success me-2"></i>Evaluación formativa</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Mediation Component -->
    <section class="my-5">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <div class="p-5">
                    <img class="img-fluid rounded-circle" src="{{ asset('img/components/mediation.jpg') }}" alt="Componente Mediacional">
                </div>
            </div>
            <div class="col-lg-6">
                <div class="p-5">
                    <h2 class="fw-bold text-primary">Mediacional</h2>
                    <p class="lead">Facilitación de procesos de comunicación y resolución de conflictos en entornos virtuales.</p>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-check text-success me-2"></i>Mediación virtual</li>
                        <li><i class="fas fa-check text-success me-2"></i>Resolución de conflictos</li>
                        <li><i class="fas fa-check text-success me-2"></i>Comunicación efectiva</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Recent Projects Section -->
@if($projects->count() > 0)
<div class="bg-light py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 text-primary">Proyectos Recientes</h2>
            <p class="lead text-muted">Conoce los últimos proyectos en desarrollo</p>
        </div>
        
        <div class="row g-4">
            @foreach($projects as $project)
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <h5 class="card-title">{{ $project->project_name }}</h5>
                            <span class="badge bg-{{ $project->project_status === 'completed' ? 'success' : ($project->project_status === 'in_progress' ? 'warning' : 'secondary') }}">
                                {{ ucfirst($project->project_status) }}
                            </span>
                        </div>
                        <p class="card-text text-muted">{{ Str::limit($project->project_description, 100) }}</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                <i class="fas fa-building me-1"></i>{{ $project->institution->institution_name }}
                            </small>
                            <small class="text-muted">
                                <i class="fas fa-tag me-1"></i>{{ $project->materialType->material_type_name }}
                            </small>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif

<!-- Info Modal -->
<div class="modal fade" id="infoModal" tabindex="-1" aria-labelledby="infoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <img src="{{ asset('img/logo-virtual-center.png') }}" alt="Virtual Center" height="30" class="me-2">
                <h5 class="modal-title" id="infoModalLabel">Virtual Center</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Aún nos encontramos trabajando para tener esta sección disponible lo más pronto posible.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Entendido!</button>
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

