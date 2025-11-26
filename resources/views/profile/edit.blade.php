@extends('layouts.app')

@section('title', 'Editar Perfil')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-user-edit me-2"></i>Editar Perfil</h2>
                <a href="javascript:history.back()" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Volver
                </a>
            </div>

            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <!-- Avatar Section -->
                <div class="card shadow mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-image me-2"></i>Foto de Perfil</h5>
                    </div>
                    <div class="card-body text-center">
                        <div class="mb-3">
                            @if($user->user_avatar)
                            <img src="{{ asset('storage/' . $user->user_avatar) }}" 
                                 alt="Avatar" class="rounded-circle" style="width: 150px; height: 150px; object-fit: cover;">
                            @else
                            <div class="rounded-circle bg-secondary d-inline-flex align-items-center justify-content-center" 
                                 style="width: 150px; height: 150px;">
                                <i class="fas fa-user fa-4x text-white"></i>
                            </div>
                            @endif
                        </div>
                        <div class="mb-3">
                            <input type="file" class="form-control" name="user_avatar" accept="image/*">
                            <small class="text-muted">JPG, PNG o GIF. Máximo 2MB.</small>
                        </div>
                        @if($user->user_avatar)
                        <form action="{{ route('profile.avatar.delete') }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" 
                                    onclick="return confirm('¿Eliminar foto de perfil?')">
                                <i class="fas fa-trash me-1"></i>Eliminar Foto
                            </button>
                        </form>
                        @endif
                    </div>
                </div>

                <!-- Personal Information -->
                <div class="card shadow mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-user me-2"></i>Información Personal</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="user_name" class="form-label">Nombre Completo <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('user_name') is-invalid @enderror" 
                                       id="user_name" name="user_name" value="{{ old('user_name', $user->user_name) }}" required>
                                @error('user_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="user_email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control @error('user_email') is-invalid @enderror" 
                                       id="user_email" name="user_email" value="{{ old('user_email', $user->user_email) }}" required>
                                @error('user_email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="user_phone" class="form-label">Teléfono</label>
                                <input type="text" class="form-control @error('user_phone') is-invalid @enderror" 
                                       id="user_phone" name="user_phone" value="{{ old('user_phone', $user->user_phone) }}">
                                @error('user_phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="user_profession" class="form-label">Profesión</label>
                                <input type="text" class="form-control @error('user_profession') is-invalid @enderror" 
                                       id="user_profession" name="user_profession" value="{{ old('user_profession', $user->user_profession) }}" 
                                       placeholder="Ej: Diseñador Gráfico">
                                @error('user_profession')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="user_bio" class="form-label">Biografía / Frase</label>
                            <textarea class="form-control @error('user_bio') is-invalid @enderror" 
                                      id="user_bio" name="user_bio" rows="3" 
                                      placeholder="Cuéntanos algo sobre ti...">{{ old('user_bio', $user->user_bio) }}</textarea>
                            <small class="text-muted">Máximo 500 caracteres</small>
                            @error('user_bio')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Change Password -->
                <div class="card shadow mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-lock me-2"></i>Cambiar Contraseña</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">Deja estos campos en blanco si no deseas cambiar tu contraseña.</p>
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Contraseña Actual</label>
                            <input type="password" class="form-control @error('current_password') is-invalid @enderror" 
                                   id="current_password" name="current_password">
                            @error('current_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="new_password" class="form-label">Nueva Contraseña</label>
                                <input type="password" class="form-control @error('new_password') is-invalid @enderror" 
                                       id="new_password" name="new_password">
                                @error('new_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="new_password_confirmation" class="form-label">Confirmar Nueva Contraseña</label>
                                <input type="password" class="form-control" 
                                       id="new_password_confirmation" name="new_password_confirmation">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-save me-2"></i>Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
