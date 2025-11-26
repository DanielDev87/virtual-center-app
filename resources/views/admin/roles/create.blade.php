@extends('layouts.admin')

@section('title', 'Crear Rol - Admin')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Crear Nuevo Rol</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="{{ route('admin.roles.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>Volver
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header">
                    <h5 class="card-title mb-0">Información del Rol</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.roles.store') }}">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="role_name" class="form-label">Nombre del Rol <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('role_name') is-invalid @enderror" 
                                   id="role_name" name="role_name" value="{{ old('role_name') }}" required>
                            @error('role_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Ejemplo: Admin, Contributor, Monitor, Requester</small>
                        </div>

                        <div class="mb-3">
                            <label for="role_description" class="form-label">Descripción</label>
                            <textarea class="form-control @error('role_description') is-invalid @enderror" 
                                      id="role_description" name="role_description" rows="3">{{ old('role_description') }}</textarea>
                            @error('role_description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="role_color" class="form-label">Color del Rol</label>
                            <input type="color" class="form-control @error('role_color') is-invalid @enderror" 
                                   id="role_color" name="role_color" value="{{ old('role_color', '#6c757d') }}" 
                                   style="width: 100px; height: 40px;">
                            @error('role_color')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Color para identificar el rol en la interfaz</small>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Crear Rol
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
                        <h6><i class="fas fa-info-circle me-2"></i>Roles Comunes</h6>
                        <ul class="mb-0 ps-3">
                            <li><strong>Admin</strong>: Acceso total al sistema</li>
                            <li><strong>Contributor</strong>: Colaborador que trabaja en tickets</li>
                            <li><strong>Monitor</strong>: Supervisor de proyectos</li>
                            <li><strong>Requester</strong>: Usuario que crea solicitudes</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
