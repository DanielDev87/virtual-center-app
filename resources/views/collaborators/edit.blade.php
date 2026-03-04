@extends('layouts.app')

@section('title', 'Editar Colaborador - Virtual Center')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Editar Colaborador</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="{{ route('collaborators.show', $collaborator->user_id) }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-eye me-1"></i>Ver Perfil
            </a>
            <a href="{{ route('collaborators.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>Volver
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header">
                    <h5 class="card-title mb-0">Información del Colaborador</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('collaborators.update', $collaborator->user_id) }}" id="collaboratorForm">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="user_name" class="form-label">Nombre Completo <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('user_name') is-invalid @enderror" 
                                       id="user_name" name="user_name" 
                                       value="{{ old('user_name', $collaborator->user_name) }}" required>
                                @error('user_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="user_email" class="form-label">Correo Electrónico <span class="text-danger">*</span></label>
                                <input type="email" class="form-control @error('user_email') is-invalid @enderror" 
                                       id="user_email" name="user_email" 
                                       value="{{ old('user_email', $collaborator->user_email) }}" required>
                                @error('user_email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="user_phone" class="form-label">Teléfono</label>
                                <input type="tel" class="form-control @error('user_phone') is-invalid @enderror" 
                                       id="user_phone" name="user_phone" 
                                       value="{{ old('user_phone', $collaborator->user_phone) }}">
                                @error('user_phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="user_role_id" class="form-label">Rol <span class="text-danger">*</span></label>
                                <select class="form-select @error('user_role_id') is-invalid @enderror" 
                                        id="user_role_id" name="user_role_id" required>
                                    <option value="">Seleccionar rol</option>
                                    @foreach($userRoles as $role)
                                    <option value="{{ $role->role_id }}" 
                                            {{ old('user_role_id', $collaborator->user_role_id) == $role->role_id ? 'selected' : '' }}>
                                        {{ $role->role_name }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('user_role_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="institution_id" class="form-label">Institución</label>
                                <select class="form-select @error('institution_id') is-invalid @enderror" 
                                        id="institution_id" name="institution_id">
                                    <option value="">Seleccionar institución</option>
                                    @foreach($institutions as $institution)
                                    <option value="{{ $institution->institution_id }}" 
                                            {{ old('institution_id', $collaborator->institution_id) == $institution->institution_id ? 'selected' : '' }}>
                                        {{ $institution->institution_name }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('institution_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="user_status" class="form-label">Estado</label>
                                <select class="form-select @error('user_status') is-invalid @enderror" 
                                        id="user_status" name="user_status">
                                    <option value="active" {{ old('user_status', $collaborator->user_status) == 'active' ? 'selected' : '' }}>Activo</option>
                                    <option value="inactive" {{ old('user_status', $collaborator->user_status) == 'inactive' ? 'selected' : '' }}>Inactivo</option>
                                    <option value="pending" {{ old('user_status', $collaborator->user_status) == 'pending' ? 'selected' : '' }}>Pendiente</option>
                                </select>
                                @error('user_status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="user_password" class="form-label">Nueva Contraseña</label>
                                <input type="password" class="form-control @error('user_password') is-invalid @enderror" 
                                       id="user_password" name="user_password" 
                                       placeholder="Dejar vacío para mantener la actual">
                                @error('user_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="user_password_confirmation" class="form-label">Confirmar Nueva Contraseña</label>
                                <input type="password" class="form-control @error('user_password_confirmation') is-invalid @enderror" 
                                       id="user_password_confirmation" name="user_password_confirmation" 
                                       placeholder="Confirmar nueva contraseña">
                                @error('user_password_confirmation')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="user_bio" class="form-label">Biografía</label>
                            <textarea class="form-control @error('user_bio') is-invalid @enderror" 
                                      id="user_bio" name="user_bio" rows="4" 
                                      placeholder="Describe la experiencia y habilidades del colaborador...">{{ old('user_bio', $collaborator->user_bio) }}</textarea>
                            @error('user_bio')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="user_skills" class="form-label">Habilidades</label>
                            <textarea class="form-control @error('user_skills') is-invalid @enderror" 
                                      id="user_skills" name="user_skills" rows="3" 
                                      placeholder="Lista las habilidades técnicas y profesionales...">{{ old('user_skills', $collaborator->user_skills) }}</textarea>
                            @error('user_skills')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('collaborators.show', $collaborator->user_id) }}" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Actualizar Colaborador
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Current Profile Info -->
            <div class="card shadow mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Información Actual</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td class="fw-bold">ID:</td>
                            <td>{{ $collaborator->user_id }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Creado:</td>
                            <td>{{ $collaborator->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Actualizado:</td>
                            <td>{{ $collaborator->updated_at->format('d/m/Y H:i') }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Estado Actual:</td>
                            <td>
                                <span class="badge bg-{{ $collaborator->user_status === 'active' ? 'success' : ($collaborator->user_status === 'inactive' ? 'secondary' : 'warning') }}">
                                    {{ ucfirst($collaborator->user_status) }}
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Help Information -->
            <div class="card shadow mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Información de Ayuda</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle me-2"></i>Campos Requeridos</h6>
                        <ul class="mb-0">
                            <li>Nombre Completo</li>
                            <li>Correo Electrónico</li>
                            <li>Rol</li>
                        </ul>
                    </div>

                    <div class="alert alert-warning">
                        <h6><i class="fas fa-exclamation-triangle me-2"></i>Consejos</h6>
                        <ul class="mb-0">
                            <li>Los cambios se guardarán inmediatamente</li>
                            <li>Deja la contraseña vacía para mantener la actual</li>
                            <li>Puedes cambiar el estado en cualquier momento</li>
                        </ul>
                    </div>

                    <div class="text-center">
                        <i class="fas fa-user-edit fa-3x text-primary mb-3"></i>
                        <p class="text-muted">Edita la información del colaborador según sea necesario.</p>
                    </div>
                </div>
            </div>

            <!-- Role Information -->
            <div class="card shadow">
                <div class="card-header">
                    <h5 class="card-title mb-0">Roles Disponibles</h5>
                </div>
                <div class="card-body">
                    @foreach($userRoles as $role)
                    <div class="d-flex align-items-center mb-2">
                        <div class="flex-shrink-0">
                            <i class="fas fa-user-tag text-primary"></i>
                        </div>
                        <div class="flex-grow-1 ms-2">
                            <div class="fw-bold">{{ $role->role_name }}</div>
                            <small class="text-muted">{{ $role->role_description ?? 'Sin descripción' }}</small>
                        </div>
                    </div>
                    @endforeach
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
    $('#collaboratorForm').on('submit', function(e) {
        const password = $('#user_password').val();
        const confirmPassword = $('#user_password_confirmation').val();
        
        if (password && confirmPassword && password !== confirmPassword) {
            e.preventDefault();
            VirtualCenter.showAlert('Las contraseñas no coinciden', 'warning');
            return false;
        }
        
        if (password && password.length < 8) {
            e.preventDefault();
            VirtualCenter.showAlert('La contraseña debe tener al menos 8 caracteres', 'warning');
            return false;
        }
        
        // Show loading
        const submitBtn = $(this).find('button[type="submit"]');
        VirtualCenter.showLoading(submitBtn);
    });

    // Password confirmation validation
    $('#user_password_confirmation').on('keyup', function() {
        const password = $('#user_password').val();
        const confirmPassword = $(this).val();
        
        if (confirmPassword && password !== confirmPassword) {
            $(this).addClass('is-invalid');
            if (!$(this).next('.invalid-feedback').length) {
                $(this).after('<div class="invalid-feedback">Las contraseñas no coinciden</div>');
            }
        } else {
            $(this).removeClass('is-invalid');
            $(this).next('.invalid-feedback').remove();
        }
    });

    // Email validation
    $('#user_email').on('blur', function() {
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
    $('#user_phone').on('blur', function() {
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

    // Status change warning
    $('#user_status').on('change', function() {
        const currentStatus = '{{ $collaborator->user_status }}';
        const newStatus = $(this).val();
        
        if (currentStatus !== newStatus) {
            const statusNames = {
                'active': 'Activo',
                'inactive': 'Inactivo',
                'pending': 'Pendiente'
            };
            
            VirtualCenter.showAlert(`Estado cambiado a: ${statusNames[newStatus]}`, 'info', 3000);
        }
    });

    // Role change warning
    $('#user_role_id').on('change', function() {
        const currentRole = '{{ $collaborator->user_role_id }}';
        const newRole = $(this).val();
        
        if (currentRole !== newRole) {
            const roleName = $(this).find('option:selected').text();
            VirtualCenter.showAlert(`Rol cambiado a: ${roleName}`, 'info', 3000);
        }
    });
});
</script>
@endpush


