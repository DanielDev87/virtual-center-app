@extends('layouts.admin')

@section('title', 'Nuevo Puesto de Trabajo - Admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center pb-2 mb-3 border-bottom">
        <h1 class="h2"><i class="fas fa-briefcase me-2"></i>Nuevo Puesto de Trabajo</h1>
        <a href="{{ route('admin.job-positions.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Volver
        </a>
    </div>

    <div class="card shadow">
        <div class="card-body">
            <form action="{{ route('admin.job-positions.store') }}" method="POST">
                @csrf
                
                <div class="mb-3">
                    <label for="position_name" class="form-label">Nombre del Puesto <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('position_name') is-invalid @enderror" 
                           id="position_name" name="position_name" value="{{ old('position_name') }}" required>
                    @error('position_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="position_description" class="form-label">Descripción</label>
                    <textarea class="form-control @error('position_description') is-invalid @enderror" 
                              id="position_description" name="position_description" rows="4">{{ old('position_description') }}</textarea>
                    @error('position_description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="position_color" class="form-label">Color (Hex)</label>
                    <input type="color" class="form-control @error('position_color') is-invalid @enderror" 
                           id="position_color" name="position_color" value="{{ old('position_color', '#4ECDC4') }}">
                    @error('position_color')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">Color para identificar visualmente este puesto</small>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.job-positions.index') }}" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
