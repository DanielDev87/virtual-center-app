@extends('layouts.app')

@section('title', 'Acerca de Nosotros - Virtual Center')

@section('content')
<div class="container-fluid">
    <!-- Hero Section -->
    <div class="row bg-primary text-white py-5">
        <div class="col-12 text-center">
            <h1 class="display-4 fw-bold mb-3">Acerca de Virtual Center</h1>
            <p class="lead">Transformando la educación a través de la tecnología</p>
        </div>
    </div>

    <!-- Mission Section -->
    <div class="container py-5">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h2 class="h1 fw-bold text-primary mb-4">Nuestra Misión</h2>
                <p class="lead text-muted mb-4">
                    Virtual Center es una plataforma integral diseñada para facilitar la gestión de proyectos educativos, 
                    promover la colaboración entre instituciones y optimizar los procesos de enseñanza y aprendizaje.
                </p>
                <p class="text-muted">
                    Nuestro objetivo es crear un ecosistema digital que conecte a educadores, estudiantes y administradores 
                    en un entorno colaborativo y eficiente, utilizando las mejores prácticas en tecnología educativa.
                </p>
            </div>
            <div class="col-lg-6">
                <div class="text-center">
                    <i class="fas fa-bullseye fa-5x text-primary mb-4"></i>
                    <div class="row">
                        <div class="col-6">
                            <div class="text-center">
                                <h3 class="text-primary fw-bold">500+</h3>
                                <p class="text-muted">Proyectos Activos</p>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center">
                                <h3 class="text-primary fw-bold">50+</h3>
                                <p class="text-muted">Instituciones</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Features Section -->
    <div class="bg-light py-5">
        <div class="container">
            <div class="row text-center mb-5">
                <div class="col-12">
                    <h2 class="h1 fw-bold text-primary mb-3">Características Principales</h2>
                    <p class="lead text-muted">Herramientas poderosas para la gestión educativa</p>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                                <i class="fas fa-project-diagram fa-2x"></i>
                            </div>
                            <h5 class="card-title">Gestión de Proyectos</h5>
                            <p class="card-text text-muted">
                                Organiza y supervisa proyectos educativos de manera eficiente con herramientas avanzadas de seguimiento.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <div class="bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                                <i class="fas fa-users fa-2x"></i>
                            </div>
                            <h5 class="card-title">Colaboración</h5>
                            <p class="card-text text-muted">
                                Conecta con otros educadores y colaboradores para trabajar en proyectos conjuntos.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <div class="bg-warning text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                                <i class="fas fa-chart-line fa-2x"></i>
                            </div>
                            <h5 class="card-title">Monitoreo</h5>
                            <p class="card-text text-muted">
                                Supervisa el progreso de proyectos y obtén insights valiosos con nuestro sistema de monitoreo.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <div class="bg-info text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                                <i class="fas fa-radio fa-2x"></i>
                            </div>
                            <h5 class="card-title">Emisora Virtual</h5>
                            <p class="card-text text-muted">
                                Accede a contenido educativo a través de nuestra emisora virtual con programación especializada.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <div class="bg-danger text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                                <i class="fas fa-cogs fa-2x"></i>
                            </div>
                            <h5 class="card-title">Gestión de Servicios</h5>
                            <p class="card-text text-muted">
                                Administra servicios educativos y recursos de manera centralizada y organizada.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <div class="bg-secondary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                                <i class="fas fa-shield-alt fa-2x"></i>
                            </div>
                            <h5 class="card-title">Seguridad</h5>
                            <p class="card-text text-muted">
                                Protege la información educativa con medidas de seguridad avanzadas y controles de acceso.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Team Section -->
    <div class="container py-5">
        <div class="row text-center mb-5">
            <div class="col-12">
                <h2 class="h1 fw-bold text-primary mb-3">Nuestro Equipo</h2>
                <p class="lead text-muted">Profesionales comprometidos con la excelencia educativa</p>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 100px; height: 100px;">
                            <i class="fas fa-user fa-3x"></i>
                        </div>
                        <h5 class="card-title">Equipo de Desarrollo</h5>
                        <p class="card-text text-muted">
                            Especialistas en tecnología educativa y desarrollo de software.
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <div class="bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 100px; height: 100px;">
                            <i class="fas fa-graduation-cap fa-3x"></i>
                        </div>
                        <h5 class="card-title">Consultores Educativos</h5>
                        <p class="card-text text-muted">
                            Expertos en pedagogía y metodologías de enseñanza innovadoras.
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <div class="bg-warning text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 100px; height: 100px;">
                            <i class="fas fa-headset fa-3x"></i>
                        </div>
                        <h5 class="card-title">Soporte Técnico</h5>
                        <p class="card-text text-muted">
                            Profesionales dedicados a brindar asistencia y soporte continuo.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Contact Section -->
    <div class="bg-primary text-white py-5">
        <div class="container">
            <div class="row text-center">
                <div class="col-12">
                    <h2 class="h1 fw-bold mb-3">¿Tienes Preguntas?</h2>
                    <p class="lead mb-4">Estamos aquí para ayudarte en tu journey educativo</p>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <i class="fas fa-envelope fa-2x mb-3"></i>
                            <h5>Correo Electrónico</h5>
                            <p>contacto@virtualcenter.edu</p>
                        </div>
                        <div class="col-md-4 mb-3">
                            <i class="fas fa-phone fa-2x mb-3"></i>
                            <h5>Teléfono</h5>
                            <p>+1 (555) 123-4567</p>
                        </div>
                        <div class="col-md-4 mb-3">
                            <i class="fas fa-map-marker-alt fa-2x mb-3"></i>
                            <h5>Ubicación</h5>
                            <p>Centro de Innovación Educativa</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


