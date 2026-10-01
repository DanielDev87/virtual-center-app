@extends('layouts.admin')

@section('title', 'Configuracion Tecnica de Archivos')
@section('topbar_title', 'Configuracion Tecnica de Archivos')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h4 mb-0">Almacenamiento de Evidencias</h1>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if($errors->has('evidence_storage_root_path') || $errors->has('rich_text_image_storage_root_path'))
    <div class="alert alert-danger" role="alert">
        <strong>No se pudo guardar la ruta de almacenamiento.</strong>
        <div class="mt-2">Revisa los siguientes campos:</div>
        <ul class="mb-0 mt-2 ps-3">
            @error('evidence_storage_root_path')
            <li>Ruta de almacenamiento: {{ $message }}</li>
            @enderror
            @error('rich_text_image_storage_root_path')
            <li>Ruta para imagenes pegadas: {{ $message }}</li>
            @enderror
        </ul>
    </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            <p class="text-muted mb-3">
                Define el proveedor y la ruta fisica de respaldo para almacenar archivos adjuntos nuevos.
                Los adjuntos historicos ya almacenados en otros medios siguen siendo accesibles sin cambios.
            </p>
            <p class="text-muted mb-3">
                Tambien puedes definir una ruta separada para imagenes pegadas dentro del editor enriquecido de descripcion.
            </p>

            <form method="POST" action="{{ route('technical.storage-settings.update') }}" class="row g-3">
                @csrf
                @method('PUT')

                <div class="col-12">
                    <label for="evidence_storage_provider" class="form-label fw-semibold">Proveedor de almacenamiento</label>
                    <select
                        id="evidence_storage_provider"
                        name="evidence_storage_provider"
                        class="form-select @error('evidence_storage_provider') is-invalid @enderror"
                    >
                        <option value="filesystem" {{ old('evidence_storage_provider', $configuredProvider) === 'filesystem' ? 'selected' : '' }}>
                            Filesystem local (recomendado)
                        </option>
                        <option value="google_drive" {{ old('evidence_storage_provider', $configuredProvider) === 'google_drive' ? 'selected' : '' }}>
                            Google Drive (si esta configurado en services.php/.env)
                        </option>
                        <option value="custom" {{ old('evidence_storage_provider', $configuredProvider) === 'custom' ? 'selected' : '' }}>
                            S3 / MinIO (custom)
                        </option>
                    </select>
                    @error('evidence_storage_provider')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">
                        Si el proveedor seleccionado falla o no esta configurado, el sistema hace fallback automatico a filesystem local.
                    </div>
                </div>

                <div class="col-12 custom-storage-fields">
                    <div class="border rounded p-3 bg-light-subtle">
                        <h2 class="h6 mb-3">Configuracion S3 / MinIO</h2>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="custom_storage_bucket" class="form-label">Bucket</label>
                                <input type="text" id="custom_storage_bucket" name="custom_storage_bucket" class="form-control" value="{{ old('custom_storage_bucket', $customSettings['bucket']) }}" placeholder="mi-bucket">
                            </div>
                            <div class="col-md-6">
                                <label for="custom_storage_region" class="form-label">Region</label>
                                <input type="text" id="custom_storage_region" name="custom_storage_region" class="form-control" value="{{ old('custom_storage_region', $customSettings['region']) }}" placeholder="us-east-1">
                            </div>

                            <div class="col-md-6">
                                <label for="custom_storage_key" class="form-label">Access Key</label>
                                <input type="text" id="custom_storage_key" name="custom_storage_key" class="form-control" value="{{ old('custom_storage_key') }}" placeholder="(dejar vacio para conservar la actual)">
                            </div>
                            <div class="col-md-6">
                                <label for="custom_storage_secret" class="form-label">Secret Key</label>
                                <input type="password" id="custom_storage_secret" name="custom_storage_secret" class="form-control" value="" placeholder="(dejar vacio para conservar la actual)">
                            </div>

                            <div class="col-md-6">
                                <label for="custom_storage_endpoint" class="form-label">Endpoint (MinIO/S3 compatible)</label>
                                <input type="text" id="custom_storage_endpoint" name="custom_storage_endpoint" class="form-control" value="{{ old('custom_storage_endpoint', $customSettings['endpoint']) }}" placeholder="http://127.0.0.1:9000">
                            </div>
                            <div class="col-md-6">
                                <label for="custom_storage_url" class="form-label">URL publica base (opcional)</label>
                                <input type="text" id="custom_storage_url" name="custom_storage_url" class="form-control" value="{{ old('custom_storage_url', $customSettings['url']) }}" placeholder="https://cdn.midominio.com">
                            </div>

                            <div class="col-md-6">
                                <label for="custom_storage_prefix" class="form-label">Prefijo de carpeta</label>
                                <input type="text" id="custom_storage_prefix" name="custom_storage_prefix" class="form-control" value="{{ old('custom_storage_prefix', $customSettings['prefix']) }}" placeholder="tickets/evidences">
                            </div>
                            <div class="col-md-3">
                                <label for="custom_storage_visibility" class="form-label">Visibilidad</label>
                                <select id="custom_storage_visibility" name="custom_storage_visibility" class="form-select">
                                    <option value="private" {{ old('custom_storage_visibility', $customSettings['visibility']) === 'private' ? 'selected' : '' }}>Privado</option>
                                    <option value="public" {{ old('custom_storage_visibility', $customSettings['visibility']) === 'public' ? 'selected' : '' }}>Publico</option>
                                </select>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="custom_storage_use_path_style" name="custom_storage_use_path_style" value="1" {{ old('custom_storage_use_path_style', $customSettings['use_path_style'] ? '1' : '0') === '1' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="custom_storage_use_path_style">
                                        Use path style endpoint
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <label for="evidence_storage_root_path" class="form-label fw-semibold">Ruta de almacenamiento</label>
                    <input
                        type="text"
                        id="evidence_storage_root_path"
                        name="evidence_storage_root_path"
                        value="{{ old('evidence_storage_root_path', $configuredPath) }}"
                        class="form-control @error('evidence_storage_root_path') is-invalid @enderror"
                        placeholder="Ejemplo: C:/srv/app/evidencias o storage/app/private/ticket-evidences"
                    >
                    @error('evidence_storage_root_path')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">
                        Puedes usar ruta absoluta o relativa al proyecto. Evita usar ".." y no ingreses URLs (http/https).
                        <span
                            class="js-storage-path-example d-block mt-1"
                            data-win="C:/srv/app/evidencias"
                            data-unix="/srv/app/evidencias"
                            data-relative="storage/app/private/ticket-evidences"
                        ></span>
                    </div>
                </div>

                <div class="col-12">
                    <label for="rich_text_image_storage_root_path" class="form-label fw-semibold">Ruta para imagenes pegadas en descripcion</label>
                    <input
                        type="text"
                        id="rich_text_image_storage_root_path"
                        name="rich_text_image_storage_root_path"
                        value="{{ old('rich_text_image_storage_root_path', $configuredRichTextPath) }}"
                        class="form-control @error('rich_text_image_storage_root_path') is-invalid @enderror"
                        placeholder="Ejemplo: C:/srv/app/rich-text-images o storage/app/private/ticket-rich-text-images"
                    >
                    @error('rich_text_image_storage_root_path')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">
                        Esta ruta almacena exclusivamente imagenes embebidas desde el editor. Evita usar ".." y no ingreses URLs (http/https).
                        <span
                            class="js-storage-path-example d-block mt-1"
                            data-win="C:/srv/app/rich-text-images"
                            data-unix="/srv/app/rich-text-images"
                            data-relative="storage/app/private/ticket-rich-text-images"
                        ></span>
                    </div>
                </div>

                <div class="col-12">
                    <div class="alert alert-info mb-0">
                        <strong>Ruta efectiva actual:</strong>
                        <div class="small mt-1">{{ $effectivePath }}</div>
                        <hr>
                        <strong>Ruta efectiva de imagenes embebidas:</strong>
                        <div class="small mt-1">{{ $effectiveRichTextPath }}</div>
                    </div>
                </div>

                <div class="col-12 d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Guardar configuracion
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const providerSelect = document.getElementById('evidence_storage_provider');
        const customFields = document.querySelector('.custom-storage-fields');

        function toggleCustomFields() {
            const show = providerSelect && providerSelect.value === 'custom';
            if (customFields) {
                customFields.classList.toggle('d-none', !show);
            }
        }

        if (providerSelect) {
            providerSelect.addEventListener('change', toggleCustomFields);
            toggleCustomFields();
        }

        const pathExamples = document.querySelectorAll('.js-storage-path-example');
        const isWindows = navigator.platform && navigator.platform.toLowerCase().includes('win');

        pathExamples.forEach(function (node) {
            const absolute = isWindows ? node.dataset.win : node.dataset.unix;
            const relative = node.dataset.relative;
            node.textContent = 'Ejemplos segun tu sistema: ' + absolute + ' o ' + relative;
        });
    });
</script>
@endpush
