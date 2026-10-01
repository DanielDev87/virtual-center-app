@extends('layouts.admin')

@section('title', 'Crear Área - Admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center pb-2 mb-3 border-bottom">
        <h1 class="h2"><i class="fas fa-plus-circle me-2"></i>Crear Área</h1>
        <a href="{{ route('admin.academic.areas.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Volver
        </a>
    </div>

    <div class="card shadow">
        <div class="card-body">
            <form action="{{ route('admin.academic.areas.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label for="faculty_id" class="form-label">Facultad (Opcional - dejar en blanco para áreas administrativas)</label>
                    <select name="faculty_id" id="faculty_id" class="form-select @error('faculty_id') is-invalid @enderror">
                        <option value="">-- Sin Facultad (Área Administrativa) --</option>
                        @foreach($faculties as $faculty)
                            <option value="{{ $faculty->faculty_id }}" {{ old('faculty_id') == $faculty->faculty_id ? 'selected' : '' }}>
                                {{ $faculty->faculty_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('faculty_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="area_name" class="form-label">Nombre del Área <span class="text-danger">*</span></label>
                    <input type="text" name="area_name" id="area_name" class="form-control @error('area_name') is-invalid @enderror" 
                           value="{{ old('area_name') }}" required>
                    @error('area_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="area_description" class="form-label">Descripción</label>
                    <textarea name="area_description" id="area_description" rows="3" 
                              class="form-control @error('area_description') is-invalid @enderror">{{ old('area_description') }}</textarea>
                    @error('area_description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">Área Activa</label>
                    </div>
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Guardar Área
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
