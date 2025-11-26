@extends('layouts.admin')

@section('title', 'Editar Curso - Admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center pb-2 mb-3 border-bottom">
        <h1 class="h2"><i class="fas fa-book me-2"></i>Editar Curso</h1>
        <a href="{{ route('admin.academic.courses.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Volver
        </a>
    </div>

    <div class="card shadow">
        <div class="card-body">
            <form action="{{ route('admin.academic.courses.update', $course->course_id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="mb-3">
                    <label for="program_id" class="form-label">Programa <span class="text-danger">*</span></label>
                    <select class="form-select @error('program_id') is-invalid @enderror" 
                            id="program_id" name="program_id" required>
                        <option value="">-- Seleccionar --</option>
                        @foreach($programs as $program)
                        <option value="{{ $program->program_id }}" 
                                {{ old('program_id', $course->program_id) == $program->program_id ? 'selected' : '' }}>
                            {{ $program->program_name }} ({{ $program->faculty->faculty_name ?? 'N/A' }})
                        </option>
                        @endforeach
                    </select>
                    @error('program_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label for="course_code" class="form-label">Código <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('course_code') is-invalid @enderror" 
                               id="course_code" name="course_code" value="{{ old('course_code', $course->course_code) }}" required>
                        @error('course_code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-7 mb-3">
                        <label for="course_name" class="form-label">Nombre <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('course_name') is-invalid @enderror" 
                               id="course_name" name="course_name" value="{{ old('course_name', $course->course_name) }}" required>
                        @error('course_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-2 mb-3">
                        <label for="credits" class="form-label">Créditos</label>
                        <input type="number" class="form-control @error('credits') is-invalid @enderror" 
                               id="credits" name="credits" value="{{ old('credits', $course->credits) }}" min="1" max="10">
                        @error('credits')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label for="course_description" class="form-label">Descripción</label>
                    <textarea class="form-control @error('course_description') is-invalid @enderror" 
                              id="course_description" name="course_description" rows="4">{{ old('course_description', $course->course_description) }}</textarea>
                    @error('course_description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" 
                               {{ old('is_active', $course->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">
                            Activo
                        </label>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.academic.courses.index') }}" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Actualizar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
