@extends('layouts.admin')

@section('title', 'Editar Facultad - Admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center pb-2 mb-3 border-bottom">
        <h1 class="h2"><i class="fas fa-building me-2"></i>Editar Facultad</h1>
        <a href="{{ route('admin.academic.faculties.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Volver
        </a>
    </div>

    <div class="card shadow">
        <div class="card-body">
            <form action="{{ route('admin.academic.faculties.update', $faculty->faculty_id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="mb-3">
                    <label for="institution_id" class="form-label">Institución</label>
                    <select class="form-select @error('institution_id') is-invalid @enderror" 
                            id="institution_id" name="institution_id">
                        <option value="">-- Seleccionar --</option>
                        @foreach($institutions as $institution)
                        <option value="{{ $institution->institution_id }}" 
                                {{ old('institution_id', $faculty->institution_id) == $institution->institution_id ? 'selected' : '' }}>
                            {{ $institution->institution_name }}
                        </option>
                        @endforeach
                    </select>
                    @error('institution_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="faculty_name" class="form-label">Nombre <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('faculty_name') is-invalid @enderror" 
                           id="faculty_name" name="faculty_name" 
                           value="{{ old('faculty_name', $faculty->faculty_name) }}" required>
                    @error('faculty_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="faculty_description" class="form-label">Descripción</label>
                    <textarea class="form-control @error('faculty_description') is-invalid @enderror" 
                              id="faculty_description" name="faculty_description" rows="4">{{ old('faculty_description', $faculty->faculty_description) }}</textarea>
                    @error('faculty_description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" 
                               {{ old('is_active', $faculty->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">
                            Activa
                        </label>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.academic.faculties.index') }}" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Actualizar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
