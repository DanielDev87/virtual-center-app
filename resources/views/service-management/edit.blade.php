@extends('layouts.app')

@section('title', 'Editar Proyecto - Virtual Center')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Editar Proyecto</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="{{ route('service-management.show', $project->tracking_id) }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-eye me-1"></i>Ver Detalles
            </a>
            <a href="{{ route('service-management.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>Volver
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header">
                    <h5 class="card-title mb-0">Información del Proyecto</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('service-management.update', $project->tracking_id) }}" id="projectForm">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="project_name" class="form-label">Nombre del Proyecto <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('project_name') is-invalid @enderror" 
                                       id="project_name" name="project_name" 
                                       value="{{ old('project_name', $project->project_name) }}" required>
                                @error('project_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="institution_id" class="form-label">Institución <span class="text-danger">*</span></label>
                                <select class="form-select @error('institution_id') is-invalid @enderror" 
                                        id="institution_id" name="institution_id" required>
                                    <option value="">Seleccionar institución</option>
                                    @foreach($institutions as $institution)
                                    <option value="{{ $institution->institution_id }}" 
                                            {{ old('institution_id', $project->institution_id) == $institution->institution_id ? 'selected' : '' }}>
                                        {{ $institution->institution_name }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('institution_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="material_type_id" class="form-label">Tipo de Material <span class="text-danger">*</span></label>
                                <select class="form-select @error('material_type_id') is-invalid @enderror" 
                                        id="material_type_id" name="material_type_id" required>
                                    <option value="">Seleccionar tipo</option>
                                    @foreach($materialTypes as $type)
                                    <option value="{{ $type->material_type_id }}" 
                                            {{ old('material_type_id', $project->material_type_id) == $type->material_type_id ? 'selected' : '' }}>
                                        {{ $type->material_type_name }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('material_type_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="project_status" class="form-label">Estado</label>
                                <select class="form-select @error('project_status') is-invalid @enderror" 
                                        id="project_status" name="project_status">
                                    <option value="pending" {{ old('project_status', $project->project_status) == 'pending' ? 'selected' : '' }}>Pendiente</option>
                                    <option value="in_progress" {{ old('project_status', $project->project_status) == 'in_progress' ? 'selected' : '' }}>En Progreso</option>
                                    <option value="completed" {{ old('project_status', $project->project_status) == 'completed' ? 'selected' : '' }}>Completado</option>
                                    <option value="cancelled" {{ old('project_status', $project->project_status) == 'cancelled' ? 'selected' : '' }}>Cancelado</option>
                                </select>
                                @error('project_status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="start_date" class="form-label">Fecha de Inicio</label>
                                <input type="date" class="form-control @error('start_date') is-invalid @enderror" 
                                       id="start_date" name="start_date" 
                                       value="{{ old('start_date', $project->start_date ? $project->start_date->format('Y-m-d') : '') }}">
                                @error('start_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="end_date" class="form-label">Fecha de Fin</label>
                                <input type="date" class="form-control @error('end_date') is-invalid @enderror" 
                                       id="end_date" name="end_date" 
                                       value="{{ old('end_date', $project->end_date ? $project->end_date->format('Y-m-d') : '') }}">
                                @error('end_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="project_description" class="form-label">Descripción del Proyecto</label>
                            <textarea class="form-control @error('project_description') is-invalid @enderror" 
                                      id="project_description" name="project_description" rows="4" 
                                      placeholder="Describe los objetivos y alcance del proyecto...">{{ old('project_description', $project->project_description) }}</textarea>
                            @error('project_description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="project_notes" class="form-label">Notas Adicionales</label>
                            <textarea class="form-control @error('project_notes') is-invalid @enderror" 
                                      id="project_notes" name="project_notes" rows="3" 
                                      placeholder="Cualquier información adicional relevante...">{{ old('project_notes', $project->project_notes) }}</textarea>
                            @error('project_notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('service-management.show', $project->tracking_id) }}" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Actualizar Proyecto
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Project Info -->
            <div class="card shadow mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Información del Proyecto</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td class="fw-bold">ID:</td>
                            <td>{{ $project->tracking_id }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Creado:</td>
                            <td>{{ $project->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Actualizado:</td>
                            <td>{{ $project->updated_at->format('d/m/Y H:i') }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Estado Actual:</td>
                            <td>
                                <span class="badge bg-{{ $project->project_status === 'completed' ? 'success' : ($project->project_status === 'in_progress' ? 'warning' : ($project->project_status === 'cancelled' ? 'danger' : 'secondary')) }}">
                                    {{ ucfirst($project->project_status) }}
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Help Information -->
            <div class="card shadow">
                <div class="card-header">
                    <h5 class="card-title mb-0">Información de Ayuda</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle me-2"></i>Campos Requeridos</h6>
                        <ul class="mb-0">
                            <li>Nombre del Proyecto</li>
                            <li>Institución</li>
                            <li>Tipo de Material</li>
                        </ul>
                    </div>

                    <div class="alert alert-warning">
                        <h6><i class="fas fa-exclamation-triangle me-2"></i>Consejos</h6>
                        <ul class="mb-0">
                            <li>Los cambios se guardarán inmediatamente</li>
                            <li>La fecha de fin debe ser posterior a la fecha de inicio</li>
                            <li>Puedes cambiar el estado en cualquier momento</li>
                        </ul>
                    </div>

                    <div class="text-center">
                        <i class="fas fa-edit fa-3x text-primary mb-3"></i>
                        <p class="text-muted">Edita la información del proyecto según sea necesario.</p>
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

    // Status change warning
    $('#project_status').on('change', function() {
        const currentStatus = '{{ $project->project_status }}';
        const newStatus = $(this).val();
        
        if (currentStatus !== newStatus) {
            const statusNames = {
                'pending': 'Pendiente',
                'in_progress': 'En Progreso',
                'completed': 'Completado',
                'cancelled': 'Cancelado'
            };
            
            VirtualCenter.showAlert(`Estado cambiado a: ${statusNames[newStatus]}`, 'info', 3000);
        }
    });
});
</script>
@endpush
