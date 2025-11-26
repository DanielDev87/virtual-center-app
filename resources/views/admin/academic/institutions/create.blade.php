@extends('layouts.admin')

@section('title', 'Nueva Institución - Admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center pb-2 mb-3 border-bottom">
        <h1 class="h2"><i class="fas fa-university me-2"></i>Nueva Institución</h1>
        <a href="{{ route('admin.academic.institutions.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Volver
        </a>
    </div>

    <div class="card shadow">
        <div class="card-body">
            <form action="{{ route('admin.academic.institutions.store') }}" method="POST">
                @csrf
                
                <div class="mb-3">
                    <label for="institution_name" class="form-label">Nombre <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('institution_name') is-invalid @enderror" 
                           id="institution_name" name="institution_name" value="{{ old('institution_name') }}" required>
                    @error('institution_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="institution_description" class="form-label">Descripción</label>
                    <textarea class="form-control @error('institution_description') is-invalid @enderror" 
                              id="institution_description" name="institution_description" rows="4">{{ old('institution_description') }}</textarea>
                    @error('institution_description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="institution_logo" class="form-label">Logo (URL)</label>
                    <input type="text" class="form-control @error('institution_logo') is-invalid @enderror" 
                           id="institution_logo" name="institution_logo" value="{{ old('institution_logo') }}">
                    @error('institution_logo')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.academic.institutions.index') }}" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
