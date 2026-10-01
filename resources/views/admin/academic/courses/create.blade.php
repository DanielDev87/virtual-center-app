@extends('layouts.admin')

@section('title', 'Nuevo Curso - Admin')

@section('content')
@php
    $oldProgramIds = collect(old('program_ids', []))->map(fn ($id) => (int) $id)->filter()->values();
    $primaryProgramId = (int) old('program_id', $oldProgramIds->first() ?? 0);
@endphp
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center pb-2 mb-3 border-bottom">
        <h1 class="h2"><i class="fas fa-book me-2"></i>Nuevo Curso</h1>
        <a href="{{ route('admin.academic.courses.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Volver
        </a>
    </div>

    <div class="card shadow">
        <div class="card-body">
            <form action="{{ route('admin.academic.courses.store') }}" method="POST">
                @csrf
                
                <div class="mb-3">
                    <label for="program_ids" class="form-label">Programas <span class="text-danger">*</span></label>
                    <input type="hidden" id="program_id" name="program_id" value="{{ $primaryProgramId ?: '' }}">
                    <select class="form-select @error('program_id') is-invalid @enderror @error('program_ids') is-invalid @enderror" 
                            id="program_ids" name="program_ids[]" multiple size="8" required>
                        @foreach($programs as $program)
                        <option value="{{ $program->program_id }}" {{ $oldProgramIds->contains((int) $program->program_id) || (!$oldProgramIds->count() && $primaryProgramId === (int) $program->program_id) ? 'selected' : '' }}>
                            {{ $program->program_name }} ({{ $program->faculty->faculty_name ?? 'N/A' }})
                        </option>
                        @endforeach
                    </select>
                    <small class="text-muted d-block mt-2">Busca y selecciona uno o varios programas. Puedes eliminar selecciones desde las etiquetas.</small>
                    @error('program_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    @error('program_ids')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label for="course_code" class="form-label">Código <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('course_code') is-invalid @enderror" 
                               id="course_code" name="course_code" value="{{ old('course_code') }}" required>
                        @error('course_code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-7 mb-3">
                        <label for="course_name" class="form-label">Nombre <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('course_name') is-invalid @enderror" 
                               id="course_name" name="course_name" value="{{ old('course_name') }}" required>
                        @error('course_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-2 mb-3">
                        <label for="credits" class="form-label">Créditos</label>
                        <input type="number" class="form-control @error('credits') is-invalid @enderror" 
                               id="credits" name="credits" value="{{ old('credits') }}" min="1" max="10">
                        @error('credits')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label for="course_description" class="form-label">Descripción</label>
                    <textarea class="form-control @error('course_description') is-invalid @enderror" 
                              id="course_description" name="course_description" rows="4">{{ old('course_description') }}</textarea>
                    @error('course_description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.academic.courses.index') }}" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
<style>
    .ts-control {
        min-height: calc(1.5em + 0.75rem + 2px);
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>
    (function () {
        const programIdsSelect = document.getElementById('program_ids');
        const primaryProgramInput = document.getElementById('program_id');

        if (!programIdsSelect || !primaryProgramInput) {
            return;
        }

        function getSelectedValues() {
            return Array.from(programIdsSelect.selectedOptions).map(option => option.value).filter(Boolean);
        }

        function syncPrimaryProgram() {
            const selectedValues = getSelectedValues();

            primaryProgramInput.value = selectedValues.length > 0 ? selectedValues[0] : '';
        }

        if (window.TomSelect) {
            const selectControl = new TomSelect(programIdsSelect, {
                plugins: {
                    remove_button: {
                        title: 'Quitar'
                    }
                },
                create: false,
                hideSelected: true,
                closeAfterSelect: false,
                maxOptions: 500,
                placeholder: 'Selecciona programas...',
                render: {
                    no_results: function (data, escape) {
                        return '<div class="no-results">Sin resultados para "' + escape(data.input) + '"</div>';
                    }
                }
            });

            selectControl.on('change', syncPrimaryProgram);
        } else {
            programIdsSelect.addEventListener('change', syncPrimaryProgram);
        }

        syncPrimaryProgram();
    })();
</script>
@endpush
