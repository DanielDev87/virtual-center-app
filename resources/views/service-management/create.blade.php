@extends('layouts.requester')

@section('title', 'Crear Servicio - Virtual Center')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Crear Nueva Solicitud</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="{{ route('service-management.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>Volver
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header">
                    <h5 class="card-title mb-0">Información de la Solicitud</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('service-management.store') }}" id="projectForm">
                        @csrf
                        
                        <!-- Request Type Selection -->
                        <div class="mb-4">
                            <label class="form-label">Tipo de Solicitud <span class="text-danger">*</span></label>
                            <div class="row g-3">
                                @foreach($requestTypes as $type)
                                <div class="col-md-6">
                                    <input type="radio" class="btn-check" name="request_type_id" 
                                           id="type_{{ $type->type_id }}" value="{{ $type->type_id }}" 
                                           {{ old('request_type_id') == $type->type_id ? 'checked' : '' }} required>
                                    <label class="btn btn-outline-primary w-100 text-start p-3" 
                                           for="type_{{ $type->type_id }}" style="border-width: 2px;">
                                        <div class="d-flex align-items-center">
                                            <div class="me-3" style="font-size: 2rem; color: {{ $type->type_color }}">
                                                <i class="fas {{ $type->type_icon }}"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold">{{ $type->type_name }}</div>
                                                <small class="text-muted">{{ $type->type_description }}</small>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                                @endforeach
                            </div>
                            @error('request_type_id')
                                <div class="text-danger mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="title" class="form-label">Título de la Solicitud <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                   id="title" name="title" value="{{ old('title') }}" required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Descripción Detallada</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="6" 
                                      placeholder="Describe lo que necesitas...">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Academic Association (Optional) -->
                        <div class="card mb-3 bg-light">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="fas fa-graduation-cap me-2"></i>Asociación Académica (Opcional)</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="faculty_id" class="form-label">Facultad</label>
                                        <select class="form-select" id="faculty_id" name="faculty_id">
                                            <option value="">-- Seleccionar --</option>
                                            @foreach($faculties as $faculty)
                                            <option value="{{ $faculty->faculty_id }}" {{ old('faculty_id') == $faculty->faculty_id ? 'selected' : '' }}>
                                                {{ $faculty->faculty_name }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="program_id" class="form-label">Programa</label>
                                        <select class="form-select" id="program_id" name="program_id">
                                            <option value="">-- Seleccionar --</option>
                                            @foreach($programs as $program)
                                            <option value="{{ $program->program_id }}" data-faculty="{{ $program->faculty_id }}" 
                                                    {{ old('program_id') == $program->program_id ? 'selected' : '' }}>
                                                {{ $program->program_name }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="course_id" class="form-label">Curso</label>
                                        <select class="form-select" id="course_id" name="course_id">
                                            <option value="">-- Seleccionar --</option>
                                            @foreach($courses as $course)
                                            <option value="{{ $course->course_id }}" data-program="{{ $course->program_id }}"
                                                    {{ old('course_id') == $course->course_id ? 'selected' : '' }}>
                                                {{ $course->course_code }} - {{ $course->course_name }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <small class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Asocia tu solicitud con una facultad, programa o curso específico si aplica.
                                </small>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('service-management.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane me-2"></i>Enviar Solicitud
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow">
                <div class="card-header">
                    <h5 class="card-title mb-0">Información de Ayuda</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle me-2"></i>Proceso</h6>
                        <ol class="mb-0 ps-3">
                            <li>Envía tu solicitud.</li>
                            <li>Un administrador la revisará.</li>
                            <li>Se asignará un colaborador.</li>
                            <li>Podrás ver el avance en "Mis Solicitudes".</li>
                        </ol>
                    </div>

                    <div class="text-center mt-4">
                        <i class="fas fa-headset fa-3x text-primary mb-3"></i>
                        <p class="text-muted">¿Dudas? Contacta a soporte.</p>
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
    $('#projectForm').on('submit', function(e) {
        const startDate = $('#start_date').val();
        const endDate = $('#end_date').val();
        
        if (startDate && endDate && startDate > endDate) {
            e.preventDefault();
            VirtualCenter.showAlert('La fecha de fin debe ser posterior a la fecha de inicio', 'warning');
            return false;
        }
        
        // Show loading
        const submitBtn = $(this).find('button[type="submit"]');
        VirtualCenter.showLoading(submitBtn);
    });

    // Date validation
    $('#start_date, #end_date').on('change', function() {
        const startDate = $('#start_date').val();
        const endDate = $('#end_date').val();
        
        if (startDate && endDate && startDate > endDate) {
            $(this).addClass('is-invalid');
            if (!$(this).next('.invalid-feedback').length) {
                $(this).after('<div class="invalid-feedback">La fecha de fin debe ser posterior a la fecha de inicio</div>');
            }
        } else {
            $(this).removeClass('is-invalid');
            $(this).next('.invalid-feedback').remove();
        }
    });
});
</script>
@endpush

