@extends('layouts.admin')

@section('title', 'Nuevo Programa - Admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center pb-2 mb-3 border-bottom">
        <h1 class="h2"><i class="fas fa-graduation-cap me-2"></i>Nuevo Programa</h1>
        <a href="{{ route('admin.academic.programs.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Volver
        </a>
    </div>

    <div class="card shadow">
        <div class="card-body">
            <form action="{{ route('admin.academic.programs.store') }}" method="POST">
                @csrf
                
                <div class="mb-3">
                    <label for="faculty_id" class="form-label">Facultad <span class="text-danger">*</span></label>
                    <select class="form-select @error('faculty_id') is-invalid @enderror" 
                            id="faculty_id" name="faculty_id" required>
                        <option value="">-- Seleccionar --</option>
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

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="program_code" class="form-label">Código</label>
                        <input type="text" class="form-control @error('program_code') is-invalid @enderror" 
                               id="program_code" name="program_code" value="{{ old('program_code') }}">
                        @error('program_code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-8 mb-3">
                        <label for="program_name" class="form-label">Nombre <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('program_name') is-invalid @enderror" 
                               id="program_name" name="program_name" value="{{ old('program_name') }}" required>
                        @error('program_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label for="program_description" class="form-label">Descripción</label>
                    <textarea class="form-control @error('program_description') is-invalid @enderror" 
                              id="program_description" name="program_description" rows="4">{{ old('program_description') }}</textarea>
                    @error('program_description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.academic.programs.index') }}" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
