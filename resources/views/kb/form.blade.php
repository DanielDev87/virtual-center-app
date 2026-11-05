@extends($kbLayout)

@section('title', $article ? 'Editar artículo' : 'Nuevo artículo')

@section('content')
@php
    $isEdit = $article !== null;
    $value = fn ($field, $default = '') => old($field, $isEdit ? ($article->{$field} ?? $default) : $default);
@endphp
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3 border-bottom pb-2">
        <h1 class="h3 mb-0">{{ $isEdit ? 'Editar artículo' : 'Nuevo artículo' }}</h1>
        <a href="{{ $isEdit ? route('kb.show', $article->article_id) : route('kb.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Cancelar
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if($isEdit && $article->status === 'approved')
        <div class="alert alert-info small">
            Al guardar los cambios, este artículo volverá a borrador y necesitará una nueva aprobación.
        </div>
    @endif

    <form method="POST" action="{{ $isEdit ? route('kb.update', $article->article_id) : route('kb.store') }}" class="card shadow-sm">
        @csrf
        @if($isEdit)
            @method('PUT')
        @endif
        <div class="card-body">
            <div class="row g-3">
                <div class="col-12">
                    <label for="kbTitle" class="form-label fw-semibold">Título <span class="text-danger">*</span></label>
                    <input id="kbTitle" type="text" name="title" class="form-control" maxlength="255" value="{{ $value('title') }}" required>
                </div>

                <div class="col-12">
                    <label for="kbSummary" class="form-label fw-semibold">Resumen</label>
                    <input id="kbSummary" type="text" name="summary" class="form-control" maxlength="500" value="{{ $value('summary') }}">
                </div>

                <div class="col-12">
                    <label for="kbSteps" class="form-label fw-semibold">Pasos <span class="text-danger">*</span></label>
                    <textarea id="kbSteps" name="steps" class="form-control" rows="6" maxlength="5000" required>{{ $value('steps') }}</textarea>
                    <div class="form-text">Escribe un paso por línea.</div>
                </div>

                <div class="col-12">
                    <label for="kbBody" class="form-label fw-semibold">Detalle (opcional)</label>
                    <textarea id="kbBody" name="body" class="form-control" rows="4" maxlength="8000">{{ $value('body') }}</textarea>
                </div>

                <div class="col-md-4">
                    <label for="kbTopic" class="form-label fw-semibold">Tópico @if($isAreaAdmin)<span class="text-danger">*</span>@endif</label>
                    <select id="kbTopic" name="request_type_id" class="form-select" {{ $isAreaAdmin ? 'required' : '' }}>
                        @unless($isAreaAdmin)
                            <option value="">General (todos los tópicos)</option>
                        @endunless
                        @foreach($topics as $topic)
                            <option value="{{ $topic->type_id }}" {{ (string) $value('request_type_id') === (string) $topic->type_id ? 'selected' : '' }}>{{ $topic->type_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="kbTask" class="form-label fw-semibold">Tarea</label>
                    <select id="kbTask" name="task" class="form-select">
                        <option value="">Sin tarea específica</option>
                        @foreach($tasks as $taskKey => $taskLabel)
                            <option value="{{ $taskKey }}" {{ $value('task') === $taskKey ? 'selected' : '' }}>{{ $taskLabel }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="kbScreen" class="form-label fw-semibold">Pantalla relacionada</label>
                    <select id="kbScreen" name="screen_key" class="form-select">
                        <option value="">Ninguna</option>
                        @foreach($screens as $screenKey => $screen)
                            <option value="{{ $screenKey }}" {{ $value('screen_key') === $screenKey ? 'selected' : '' }}>{{ $screen['label'] }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label for="kbKeywords" class="form-label fw-semibold">Palabras clave</label>
                    <input id="kbKeywords" type="text" name="keywords" class="form-control" maxlength="500" value="{{ $value('keywords') }}">
                    <div class="form-text">Separadas por comas. Ayudan a encontrar el artículo.</div>
                </div>

                <div class="col-md-3">
                    <label for="kbOwner" class="form-label fw-semibold">Responsable</label>
                    <select id="kbOwner" name="owner_id" class="form-select">
                        @foreach($owners as $owner)
                            <option value="{{ $owner->user_id }}" {{ (string) ($value('owner_id') ?: auth()->id()) === (string) $owner->user_id ? 'selected' : '' }}>{{ $owner->user_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="kbValid" class="form-label fw-semibold">Vigente hasta</label>
                    <input id="kbValid" type="date" name="valid_until" class="form-control"
                           value="{{ old('valid_until', $isEdit && $article->valid_until ? $article->valid_until->format('Y-m-d') : '') }}">
                </div>
            </div>
        </div>
        <div class="card-footer text-end">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Guardar</button>
        </div>
    </form>
</div>
@endsection