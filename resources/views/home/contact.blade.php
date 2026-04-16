@extends('layouts.app')

@section('title', 'Contacto - Virtual Center')

@section('content')
<div class="container-fluid">
    <!-- Hero Section -->
    <div class="row bg-primary text-white py-5">
        <div class="col-12 text-center">
            <h1 class="display-4 fw-bold mb-3">Contáctanos</h1>
            <p class="lead">Estamos aquí para ayudarte</p>
        </div>
    </div>

    <div class="container py-5">
        <div class="row">
            <!-- Contact Form -->
            <div class="col-lg-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-envelope me-2"></i>
                            Envíanos un Mensaje
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <form id="contactForm">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">Nombre Completo <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Correo Electrónico <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="phone" class="form-label">Teléfono</label>
                                    <input type="tel" class="form-control" id="phone" name="phone">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="subject" class="form-label">Asunto <span class="text-danger">*</span></label>
                                    <select class="form-select" id="subject" name="subject" required>
                                        <option value="">Seleccionar asunto</option>
                                        <option value="general">Consulta General</option>
                                        <option value="technical">Soporte Técnico</option>
                                        <option value="billing">Facturación</option>
                                        <option value="feature">Solicitud de Funcionalidad</option>
                                        <option value="bug">Reporte de Error</option>
                                        <option value="other">Otro</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="message" class="form-label">Mensaje <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="message" name="message" rows="6" required></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="newsletter" name="newsletter">
                                    <label class="form-check-label" for="newsletter">
                                        Suscribirme al boletín de noticias
                                    </label>
                                </div>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-paper-plane me-2"></i>
                                    Enviar Mensaje
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="col-lg-4">
                <div class="card shadow mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            Información de Contacto
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">Dirección</h6>
                                <p class="text-muted mb-0">Centro de Innovación Educativa<br>Ciudad, País</p>
                            </div>
                        </div>
                        
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                <i class="fas fa-phone"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">Teléfono</h6>
                                <p class="text-muted mb-0">+1 (555) 123-4567</p>
                            </div>
                        </div>
                        
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-warning text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">Correo Electrónico</h6>
                                <p class="text-muted mb-0">contacto@virtualcenter.edu</p>
                            </div>
                        </div>
                        
                        <div class="d-flex align-items-center">
                            <div class="bg-info text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">Horario de Atención</h6>
                                <p class="text-muted mb-0">Lunes - Viernes<br>8:00 AM - 6:00 PM</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="card shadow">
                    <div class="card-header bg-info text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-link me-2"></i>
                            Enlaces Rápidos
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="list-group list-group-flush">
                            <a href="{{ route('home.about') }}" class="list-group-item list-group-item-action">
                                <i class="fas fa-info-circle text-primary me-2"></i>
                                Acerca de Nosotros
                            </a>
                            <a href="#" class="list-group-item list-group-item-action">
                                <i class="fas fa-question-circle text-success me-2"></i>
                                Preguntas Frecuentes
                            </a>
                            <a href="#" class="list-group-item list-group-item-action">
                                <i class="fas fa-book text-warning me-2"></i>
                                Documentación
                            </a>
                            <a href="#" class="list-group-item list-group-item-action">
                                <i class="fas fa-life-ring text-info me-2"></i>
                                Soporte Técnico
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Map Section -->
    <div class="bg-light py-5">
        <div class="container">
            <div class="row text-center mb-4">
                <div class="col-12">
                    <h2 class="h1 fw-bold text-primary mb-3">Nuestra Ubicación</h2>
                    <p class="lead text-muted">Visítanos en nuestro centro de innovación</p>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="card shadow">
                        <div class="card-body p-0">
                            <div class="bg-secondary text-white d-flex align-items-center justify-content-center" style="height: 400px;">
                                <div class="text-center">
                                    <i class="fas fa-map fa-5x mb-3"></i>
                                    <h4>Mapa Interactivo</h4>
                                    <p class="mb-0">Centro de Innovación Educativa</p>
                                    <p class="mb-0">Ciudad, País</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- FAQ Section -->
    <div class="container py-5">
        <div class="row text-center mb-5">
            <div class="col-12">
                <h2 class="h1 fw-bold text-primary mb-3">Preguntas Frecuentes</h2>
                <p class="lead text-muted">Respuestas a las consultas más comunes</p>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="accordion" id="faqAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="faq1">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse1" aria-expanded="true" aria-controls="collapse1">
                                ¿Cómo puedo registrarme en Virtual Center?
                            </button>
                        </h2>
                        <div id="collapse1" class="accordion-collapse collapse show" aria-labelledby="faq1" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Puedes registrarte haciendo clic en el botón "Registrarse" en la página principal. 
                                Necesitarás proporcionar tu nombre, correo electrónico y crear una contraseña segura.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="faq2">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse2" aria-expanded="false" aria-controls="collapse2">
                                ¿Qué tipos de proyectos puedo gestionar?
                            </button>
                        </h2>
                        <div id="collapse2" class="accordion-collapse collapse" aria-labelledby="faq2" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Virtual Center permite gestionar diversos tipos de proyectos educativos, 
                                incluyendo cursos virtuales, investigaciones, programas de capacitación y más.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="faq3">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse3" aria-expanded="false" aria-controls="collapse3">
                                ¿Hay soporte técnico disponible?
                            </button>
                        </h2>
                        <div id="collapse3" class="accordion-collapse collapse" aria-labelledby="faq3" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Sí, ofrecemos soporte técnico completo durante el horario de atención. 
                                Puedes contactarnos por correo electrónico, teléfono o a través del formulario de contacto.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Form validation
    $('#contactForm').on('submit', function(e) {
        e.preventDefault();
        
        const name = $('#name').val();
        const email = $('#email').val();
        const subject = $('#subject').val();
        const message = $('#message').val();
        
        if (!name || !email || !subject || !message) {
            VirtualCenter.showAlert('Por favor completa todos los campos requeridos', 'warning');
            return;
        }
        
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            VirtualCenter.showAlert('Formato de correo electrónico inválido', 'warning');
            return;
        }
        
        // Show loading
        const submitBtn = $(this).find('button[type="submit"]');
        VirtualCenter.showLoading(submitBtn);
        
        // Simulate form submission
        setTimeout(() => {
            VirtualCenter.showAlert('Mensaje enviado correctamente. Te contactaremos pronto.', 'success');
            $('#contactForm')[0].reset();
        }, 2000);
    });

    // Email validation
    $('#email').on('blur', function() {
        const email = $(this).val();
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        
        if (email && !emailRegex.test(email)) {
            $(this).addClass('is-invalid');
            if (!$(this).next('.invalid-feedback').length) {
                $(this).after('<div class="invalid-feedback">Formato de correo electrónico inválido</div>');
            }
        } else {
            $(this).removeClass('is-invalid');
            $(this).next('.invalid-feedback').remove();
        }
    });

    // Phone validation
    $('#phone').on('blur', function() {
        const phone = $(this).val();
        const phoneRegex = /^[\+]?[1-9][\d]{0,15}$/;
        
        if (phone && !phoneRegex.test(phone.replace(/\s/g, ''))) {
            $(this).addClass('is-invalid');
            if (!$(this).next('.invalid-feedback').length) {
                $(this).after('<div class="invalid-feedback">Formato de teléfono inválido</div>');
            }
        } else {
            $(this).removeClass('is-invalid');
            $(this).next('.invalid-feedback').remove();
        }
    });
});
</script>
@endpush


