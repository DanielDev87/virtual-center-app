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

    @if(session('question_success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('question_success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif
    @if(session('question_error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('question_error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

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

            @php
                $questions = $requestType->questions;
                $fieldTypes = \App\Models\RequestTypeQuestion::FIELD_TYPES;
                $isNewFormOld = old('_form') === 'new';
            @endphp

            <!-- Preguntas adicionales del formulario (HU-047) -->
            <div class="card shadow mb-4" id="preguntas">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Preguntas adicionales del formulario</h6>
                    <span class="badge bg-secondary">{{ $questions->count() }}</span>
                </div>
                <div class="card-body">
                    <p class="small text-muted">
                        Estas preguntas aparecen en el formulario de solicitud cuando el solicitante elige este tópico.
                        Si no agregas ninguna, el formulario se mantiene como está.
                        Guarda primero los cambios del tópico con el botón <strong>Actualizar Tópico</strong>; esta sección se guarda por separado.
                    </p>

                    @if($errors->question->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->question->all() as $questionError)
                                <li>{{ $questionError }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    @forelse($questions as $question)
                    <div class="border rounded p-3 mb-3">
                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                            <div>
                                <div class="fw-bold">{{ $question->label }}</div>
                                <div class="mt-1">
                                    <span class="badge bg-info text-dark">{{ $fieldTypes[$question->field_type] ?? $question->field_type }}</span>
                                    @if($question->is_required)
                                        <span class="badge bg-danger">Obligatoria</span>
                                    @else
                                        <span class="badge bg-secondary">Opcional</span>
                                    @endif
                                    @unless($question->is_active)
                                        <span class="badge bg-warning text-dark">Inactiva</span>
                                    @endunless
                                    <span class="small text-muted ms-1">Orden: {{ $question->sort_order }}</span>
                                </div>
                                @if($question->field_type === 'select' && !empty($question->options))
                                    <div class="small text-muted mt-1">Opciones: {{ implode(', ', $question->options) }}</div>
                                @endif
                            </div>
                            <div class="d-flex gap-2">
                                <button class="btn btn-sm btn-outline-primary" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#editQuestion{{ $question->question_id }}">
                                    <i class="fas fa-pen me-1"></i>Editar
                                </button>
                                <form action="{{ route('admin.request-types.questions.destroy', ['id' => $requestType->type_id, 'questionId' => $question->question_id]) }}"
                                      method="POST" onsubmit="return confirm('¿Eliminar esta pregunta? Las respuestas de tickets anteriores se conservan.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-trash me-1"></i>Eliminar
                                    </button>
                                </form>
                            </div>
                        </div>

                        <div class="collapse mt-3" id="editQuestion{{ $question->question_id }}">
                            <form action="{{ route('admin.request-types.questions.update', ['id' => $requestType->type_id, 'questionId' => $question->question_id]) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="row g-2">
                                    <div class="col-12">
                                        <label class="form-label small fw-semibold mb-1" for="qLabel{{ $question->question_id }}">Pregunta</label>
                                        <input type="text" id="qLabel{{ $question->question_id }}" name="label" class="form-control form-control-sm" maxlength="255" value="{{ $question->label }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold mb-1" for="qType{{ $question->question_id }}">Tipo de respuesta</label>
                                        <select id="qType{{ $question->question_id }}" name="field_type" class="form-select form-select-sm">
                                            @foreach($fieldTypes as $typeKey => $typeName)
                                                <option value="{{ $typeKey }}" {{ $question->field_type === $typeKey ? 'selected' : '' }}>{{ $typeName }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold mb-1" for="qOrder{{ $question->question_id }}">Orden</label>
                                        <input type="number" id="qOrder{{ $question->question_id }}" name="sort_order" class="form-control form-control-sm" min="0" max="999" value="{{ $question->sort_order }}">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small fw-semibold mb-1" for="qOptions{{ $question->question_id }}">Opciones (solo para "Lista de opciones", una por línea)</label>
                                        <textarea id="qOptions{{ $question->question_id }}" name="options" class="form-control form-control-sm" rows="3">{{ implode("\n", $question->options ?? []) }}</textarea>
                                    </div>
                                    <div class="col-12 d-flex flex-wrap gap-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="is_required" value="1" id="qReq{{ $question->question_id }}" {{ $question->is_required ? 'checked' : '' }}>
                                            <label class="form-check-label small" for="qReq{{ $question->question_id }}">Obligatoria</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="qAct{{ $question->question_id }}" {{ $question->is_active ? 'checked' : '' }}>
                                            <label class="form-check-label small" for="qAct{{ $question->question_id }}">Activa (visible en el formulario)</label>
                                        </div>
                                    </div>
                                    <div class="col-12 text-end">
                                        <button type="submit" class="btn btn-sm btn-primary">
                                            <i class="fas fa-save me-1"></i>Guardar cambios
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    @empty
                    <div class="text-center text-muted py-3">
                        <i class="fas fa-question-circle fa-2x mb-2"></i>
                        <p class="mb-0">Este tópico aún no tiene preguntas adicionales.</p>
                    </div>
                    @endforelse

                    <hr>

                    <h6 class="fw-bold mb-3">Agregar una pregunta</h6>
                    <form action="{{ route('admin.request-types.questions.store', ['id' => $requestType->type_id]) }}" method="POST">
                        @csrf
                        <input type="hidden" name="_form" value="new">
                        <div class="row g-2">
                            <div class="col-12">
                                <label class="form-label small fw-semibold mb-1" for="newQLabel">Pregunta <span class="text-danger">*</span></label>
                                <input type="text" id="newQLabel" name="label" class="form-control" maxlength="255"
                                       value="{{ $isNewFormOld ? old('label') : '' }}" placeholder="Ej.: ¿Cuál es el número de serie del equipo?" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold mb-1" for="newQType">Tipo de respuesta</label>
                                <select id="newQType" name="field_type" class="form-select">
                                    @foreach($fieldTypes as $typeKey => $typeName)
                                        <option value="{{ $typeKey }}" {{ ($isNewFormOld ? old('field_type') : 'text') === $typeKey ? 'selected' : '' }}>{{ $typeName }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold mb-1" for="newQOrder">Orden (opcional)</label>
                                <input type="number" id="newQOrder" name="sort_order" class="form-control" min="0" max="999"
                                       value="{{ $isNewFormOld ? old('sort_order') : '' }}" placeholder="Se agrega al final">
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-semibold mb-1" for="newQOptions">Opciones (solo para "Lista de opciones", una por línea)</label>
                                <textarea id="newQOptions" name="options" class="form-control" rows="3"
                                          placeholder="Opción 1&#10;Opción 2">{{ $isNewFormOld ? old('options') : '' }}</textarea>
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_required" value="1" id="newQReq"
                                           {{ $isNewFormOld && old('is_required') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="newQReq">Respuesta obligatoria</label>
                                </div>
                            </div>
                            <div class="col-12 text-end">
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-plus me-1"></i>Agregar pregunta
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection