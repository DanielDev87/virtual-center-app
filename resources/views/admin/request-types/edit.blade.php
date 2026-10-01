@extends('layouts.admin')

@section('title', 'Editar Tópico - Admin')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3 mb-0 text-gray-800">Editar Tópico: {{ $requestType->type_name }}</h2>
        <a href="{{ route('admin.request-types.index') }}" class="btn btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50 me-1"></i> Volver a Tópicos
        </a>
    </div>

    <div class="row">
        <div class="col-xl-8 col-lg-10">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Detalles del Tópico</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.request-types.update', $requestType->type_id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-3">
                            <label for="type_name" class="form-label fw-bold">Nombre del Tópico <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('type_name') is-invalid @enderror" id="type_name" name="type_name" value="{{ old('type_name', $requestType->type_name) }}" required>
                            @error('type_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="type_description" class="form-label fw-bold">Descripción</label>
                            <textarea class="form-control @error('type_description') is-invalid @enderror" id="type_description" name="type_description" rows="3">{{ old('type_description', $requestType->type_description) }}</textarea>
                            @error('type_description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="area_id" class="form-label fw-bold">Área Asociada (Opcional)</label>
                                <select class="form-select @error('area_id') is-invalid @enderror" id="area_id" name="area_id">
                                    <option value="">-- Seleccione un Área --</option>
                                    @foreach($areas as $area)
                                        <option value="{{ $area->area_id }}" {{ old('area_id', $requestType->area_id) == $area->area_id ? 'selected' : '' }}>
                                            {{ $area->area_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('area_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Asocie este tópico a un área específica.</small>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="type_icon" class="form-label fw-bold">Ícono FontAwesome</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas {{ $requestType->type_icon ?? 'fa-tag' }}"></i></span>
                                    <input type="text" class="form-control @error('type_icon') is-invalid @enderror" id="type_icon" name="type_icon" value="{{ old('type_icon', $requestType->type_icon) }}" placeholder="fa-wifi, fa-laptop, etc.">
                                </div>
                                @error('type_icon')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="type_color" class="form-label fw-bold">Color del Tópico</label>
                                <input type="color" class="form-control form-control-color w-100 @error('type_color') is-invalid @enderror" id="type_color" name="type_color" value="{{ old('type_color', $requestType->type_color) }}">
                                @error('type_color')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Asignación por Tópico y Regional (Opcional)</label>
                            <div class="alert alert-light border small mb-2">
                                Si este tópico debe enrutarse por sede/regional, asigna aquí los responsables por cada regional (colaboradores y/o administradores de área). Si dejas todo vacío, conserva el flujo actual.
                            </div>
                            <div class="table-responsive border rounded">
                                <table class="table table-sm mb-0 align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Regional</th>
                                            <th>Colaboradores responsables en esta regional</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($institutions as $institution)
                                        <tr>
                                            <td>{{ $institution->institution_name }}</td>
                                            <td>
                                                @php
                                                    $regionalSelectedIds = old('regional_assignments.' . $institution->institution_id, $regionalAssignments[$institution->institution_id] ?? []);
                                                @endphp
                                                <div class="border rounded p-2" style="max-height: 180px; overflow-y: auto;">
                                                    @foreach($regionalResponsibleUsers as $responsibleUser)
                                                        <div class="form-check mb-1">
                                                            <input
                                                                class="form-check-input"
                                                                type="checkbox"
                                                                id="regional_{{ $institution->institution_id }}_user_{{ $responsibleUser->user_id }}"
                                                                name="regional_assignments[{{ $institution->institution_id }}][]"
                                                                value="{{ $responsibleUser->user_id }}"
                                                                {{ in_array($responsibleUser->user_id, $regionalSelectedIds) ? 'checked' : '' }}
                                                            >
                                                            <label class="form-check-label" for="regional_{{ $institution->institution_id }}_user_{{ $responsibleUser->user_id }}">
                                                                {{ $responsibleUser->user_name }}
                                                                <small class="text-muted">({{ optional($responsibleUser->role)->role_name ?? 'Sin rol' }})</small>
                                                            </label>
                                                        </div>
                                                    @endforeach
                                                </div>
                                                @error('regional_assignments')
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                                @error('regional_assignments.' . $institution->institution_id)
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                                @error('regional_assignments.' . $institution->institution_id . '.*')
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="2" class="text-muted">No hay regionales/instituciones activas configuradas.</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="mb-4 form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" {{ old('is_active', $requestType->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold" for="is_active">Tópico Activo</label>
                        </div>

                        <hr>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="fas fa-sync-alt me-1"></i> Actualizar Tópico
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
