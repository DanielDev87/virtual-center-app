@extends('layouts.admin')

@section('title', 'Crear Usuario - Admin')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Crear Nuevo Usuario</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>Volver
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header">
                    <h5 class="card-title mb-0">Información del Usuario</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.users.store') }}">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="user_name" class="form-label">Nombre Completo <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('user_name') is-invalid @enderror" 
                                   id="user_name" name="user_name" value="{{ old('user_name') }}" required>
                            @error('user_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="user_email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('user_email') is-invalid @enderror" 
                                   id="user_email" name="user_email" value="{{ old('user_email') }}" required>
                            @error('user_email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="role_id" class="form-label">Rol <span class="text-danger">*</span></label>
                            <select class="form-select @error('role_id') is-invalid @enderror" 
                                    id="role_id" name="role_id" required>
                                <option value="">Seleccionar rol</option>
                                @foreach($roles as $role)
                                <option value="{{ $role->role_id }}"
                                        data-role-name="{{ mb_strtolower($role->role_name, 'UTF-8') }}"
                                        data-role-description="{{ $role->role_description }}"
                                        {{ old('role_id') == $role->role_id ? 'selected' : '' }}>
                                    {{ $role->role_name }}
                                </option>
                                @endforeach
                            </select>
                            <div id="role-description" class="form-text">Seleccione el rol que tendrá este usuario.</div>
                            @error('role_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3" id="area-container" style="display: none;">
                            <label for="area_id" class="form-label" id="area-label">Área</label>
                            <select class="form-select @error('area_id') is-invalid @enderror" 
                                    id="area_id" name="area_id">
                                <option value="">Seleccionar área</option>
                                @foreach($areas as $area)
                                <option value="{{ $area->area_id }}" {{ old('area_id') == $area->area_id ? 'selected' : '' }}>
                                    {{ $area->area_name }}
                                </option>
                                @endforeach
                            </select>
                            @error('area_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Puestos de Trabajo (Opcional)</label>
                            <div class="card card-body bg-light">
                                <div class="row">
                                    @foreach($jobPositions as $position)
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" 
                                                   name="job_positions[]" 
                                                   value="{{ $position->job_position_id }}" 
                                                   id="position_{{ $position->job_position_id }}"
                                                   {{ in_array($position->job_position_id, old('job_positions', [])) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="position_{{ $position->job_position_id }}">
                                                <span class="badge me-1" style="background-color: {{ $position->position_color }}; width: 10px; height: 10px; display: inline-block; border-radius: 50%;"></span>
                                                {{ $position->position_name }}
                                            </label>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            <small class="text-muted">Seleccione los perfiles profesionales de este usuario.</small>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Contraseña <span class="text-danger">*</span></label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                   id="password" name="password" required>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Confirmar Contraseña <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" 
                                   id="password_confirmation" name="password_confirmation" required>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Crear Usuario
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow">
                <div class="card-header">
                    <h5 class="card-title mb-0">Información</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle me-2"></i>Roles Disponibles</h6>
                        <ul class="mb-0 ps-3">
                            @foreach($roles as $role)
                            <li><strong>{{ $role->role_name }}</strong>: {{ $role->role_description }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const roleSelect = document.getElementById('role_id');
        const areaContainer = document.getElementById('area-container');
        const areaInput = document.getElementById('area_id');
        const areaLabel = document.getElementById('area-label');
        const roleDescription = document.getElementById('role-description');

        function normalizeRoleName(roleName) {
            return (roleName || '')
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .toLowerCase()
                .trim();
        }

        function toggleAreaContainer() {
            const selected = roleSelect.options[roleSelect.selectedIndex];
            const roleName = normalizeRoleName(selected?.dataset.roleName || selected?.text);
            roleDescription.textContent = selected?.dataset.roleDescription || 'Seleccione el rol que tendrá este usuario.';
            const isContributorOrGestor = roleName === 'contributor' || roleName === 'gestor';
            const isAreaAdmin = roleName === 'admin area';

            if (isContributorOrGestor || isAreaAdmin) {
                areaContainer.style.display = 'block';
                areaInput.required = isAreaAdmin;
                areaLabel.innerHTML = isAreaAdmin
                    ? 'Área <span class="text-danger">*</span> (obligatoria para Admin Área)'
                    : 'Área';
            } else {
                areaContainer.style.display = 'none';
                areaInput.required = false;
                areaInput.value = '';
                areaLabel.textContent = 'Área';
            }
        }

        roleSelect.addEventListener('change', toggleAreaContainer);
        toggleAreaContainer(); // Ejecutar on load
    });
</script>
@endpush
@endsection
