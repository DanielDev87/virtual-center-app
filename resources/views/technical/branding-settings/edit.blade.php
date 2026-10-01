@extends('layouts.admin')

@section('title', 'Personalizacion de App')
@section('topbar_title', 'Personalizacion de App')

@php
    $resolveBrandingPreviewUrl = function ($path, $fallback) {
        $normalizedPath = ltrim((string) ($path ?: $fallback), '/');

        if (str_starts_with($normalizedPath, 'storage/branding/') || str_starts_with($normalizedPath, 'branding/')) {
            return url('/branding-assets/'.rawurlencode(basename($normalizedPath)));
        }

        return asset($normalizedPath);
    };
@endphp

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h4 mb-0">Personalizacion de Marca</h1>
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

    <div class="row g-3 mb-3">
        <div class="col-lg-8">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h2 class="h6 mb-3">Configuracion de la Empresa</h2>
                    <form method="POST" action="{{ route('technical.branding-settings.update') }}" class="row g-3" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="col-md-8">
                            <label class="form-label fw-semibold" for="branding_app_name">Nombre principal de la app</label>
                            <input id="branding_app_name" name="branding_app_name" type="text" class="form-control @error('branding_app_name') is-invalid @enderror" value="{{ old('branding_app_name', $settings['branding_app_name']) }}" maxlength="120" required>
                            @error('branding_app_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold" for="branding_short_name">Nombre corto</label>
                            <input id="branding_short_name" name="branding_short_name" type="text" class="form-control @error('branding_short_name') is-invalid @enderror" value="{{ old('branding_short_name', $settings['branding_short_name']) }}" maxlength="50" required>
                            @error('branding_short_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="branding_logo_path">Ruta de logo principal</label>
                            <input id="branding_logo_path" name="branding_logo_path" type="text" class="form-control @error('branding_logo_path') is-invalid @enderror" value="{{ old('branding_logo_path', $settings['branding_logo_path']) }}" required>
                            @error('branding_logo_path')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div class="form-text">Ejemplo: img/mi-logo.png</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="branding_logo_file">Subir logo principal (opcional)</label>
                            <input id="branding_logo_file" name="branding_logo_file" type="file" class="form-control @error('branding_logo_file') is-invalid @enderror" accept=".png,.jpg,.jpeg,.webp,.svg,image/*">
                            @error('branding_logo_file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div class="form-text">Si subes archivo, reemplaza la ruta anterior automaticamente.</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="branding_footer_logo_path">Ruta de logo de footer</label>
                            <input id="branding_footer_logo_path" name="branding_footer_logo_path" type="text" class="form-control @error('branding_footer_logo_path') is-invalid @enderror" value="{{ old('branding_footer_logo_path', $settings['branding_footer_logo_path']) }}" required>
                            @error('branding_footer_logo_path')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="branding_footer_logo_file">Subir logo de footer (opcional)</label>
                            <input id="branding_footer_logo_file" name="branding_footer_logo_file" type="file" class="form-control @error('branding_footer_logo_file') is-invalid @enderror" accept=".png,.jpg,.jpeg,.webp,.svg,image/*">
                            @error('branding_footer_logo_file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="branding_favicon_path">Ruta de favicon</label>
                            <input id="branding_favicon_path" name="branding_favicon_path" type="text" class="form-control @error('branding_favicon_path') is-invalid @enderror" value="{{ old('branding_favicon_path', $settings['branding_favicon_path']) }}" required>
                            @error('branding_favicon_path')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="branding_favicon_file">Subir favicon (opcional)</label>
                            <input id="branding_favicon_file" name="branding_favicon_file" type="file" class="form-control @error('branding_favicon_file') is-invalid @enderror" accept=".ico,.png,.svg,.webp,image/*">
                            @error('branding_favicon_file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold" for="branding_primary_color">Color primario</label>
                            <div class="input-group">
                                <input id="branding_primary_color" name="branding_primary_color" type="color" class="form-control form-control-color @error('branding_primary_color') is-invalid @enderror" value="{{ old('branding_primary_color', $settings['branding_primary_color']) }}" required>
                                <input id="branding_primary_color_text" type="text" class="form-control" value="{{ old('branding_primary_color', $settings['branding_primary_color']) }}" maxlength="7">
                            </div>
                            @error('branding_primary_color')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold" for="branding_secondary_color">Color secundario</label>
                            <div class="input-group">
                                <input id="branding_secondary_color" name="branding_secondary_color" type="color" class="form-control form-control-color @error('branding_secondary_color') is-invalid @enderror" value="{{ old('branding_secondary_color', $settings['branding_secondary_color']) }}" required>
                                <input id="branding_secondary_color_text" type="text" class="form-control" value="{{ old('branding_secondary_color', $settings['branding_secondary_color']) }}" maxlength="7">
                            </div>
                            @error('branding_secondary_color')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12 mt-2">
                            <h2 class="h6 mb-2">Configuracion de correo saliente</h2>
                            <p class="text-muted small mb-0">Define el remitente global usado por los correos automaticos de la aplicacion.</p>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="mail_from_name">Nombre del remitente</label>
                            <input id="mail_from_name" name="mail_from_name" type="text" class="form-control @error('mail_from_name') is-invalid @enderror" value="{{ old('mail_from_name', $settings['mail_from_name']) }}" maxlength="120" required>
                            @error('mail_from_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div class="form-text">Ejemplo: Mesa de Ayuda ULA</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="mail_from_address">Correo del remitente</label>
                            <input id="mail_from_address" name="mail_from_address" type="email" class="form-control @error('mail_from_address') is-invalid @enderror" value="{{ old('mail_from_address', $settings['mail_from_address']) }}" maxlength="190" required>
                            @error('mail_from_address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div class="form-text">Ejemplo: no-reply@tu-dominio.edu</div>
                        </div>

                        <div class="col-12 mt-2">
                            <h2 class="h6 mb-2">Configuracion SMTP (sin credenciales)</h2>
                            <p class="text-muted small mb-0">Aqui solo parametrizas host, puerto y encriptacion. Usuario y clave permanecen en variables de entorno por seguridad.</p>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="mail_smtp_host">SMTP Host</label>
                            <input id="mail_smtp_host" name="mail_smtp_host" type="text" class="form-control @error('mail_smtp_host') is-invalid @enderror" value="{{ old('mail_smtp_host', $settings['mail_smtp_host']) }}" maxlength="190" required>
                            @error('mail_smtp_host')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div class="form-text">Ejemplo: smtp.office365.com</div>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold" for="mail_smtp_port">SMTP Puerto</label>
                            <input id="mail_smtp_port" name="mail_smtp_port" type="number" min="1" max="65535" class="form-control @error('mail_smtp_port') is-invalid @enderror" value="{{ old('mail_smtp_port', $settings['mail_smtp_port']) }}" required>
                            @error('mail_smtp_port')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold" for="mail_smtp_encryption">Encriptacion</label>
                            <select id="mail_smtp_encryption" name="mail_smtp_encryption" class="form-select @error('mail_smtp_encryption') is-invalid @enderror">
                                <option value="" {{ old('mail_smtp_encryption', $settings['mail_smtp_encryption']) === '' ? 'selected' : '' }}>Ninguna</option>
                                <option value="tls" {{ old('mail_smtp_encryption', $settings['mail_smtp_encryption']) === 'tls' ? 'selected' : '' }}>TLS</option>
                                <option value="ssl" {{ old('mail_smtp_encryption', $settings['mail_smtp_encryption']) === 'ssl' ? 'selected' : '' }}>SSL</option>
                            </select>
                            @error('mail_smtp_encryption')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12 d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Guardar personalizacion
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h2 class="h6 mb-3">Vista previa rapida</h2>
                    <div id="brandingPreview" class="border rounded overflow-hidden">
                        <div class="preview-browser-bar px-3 pt-3 pb-2">
                            <div class="preview-browser-tab">
                                <img id="previewFavicon" src="{{ $resolveBrandingPreviewUrl($settings['branding_favicon_path'], 'img/logomsula.png') }}" alt="Favicon" class="preview-tab-favicon">
                                <span id="previewTabName" class="preview-tab-name">{{ $settings['branding_short_name'] }}</span>
                            </div>
                        </div>
                        <div class="preview-nav p-2 d-flex align-items-center gap-2">
                            <img id="previewLogo" src="{{ $resolveBrandingPreviewUrl($settings['branding_logo_path'], 'img/logomsula.png') }}" alt="Logo" style="height: 30px; width: auto;">
                            <span id="previewShortName" class="fw-semibold text-white">{{ $settings['branding_short_name'] }}</span>
                        </div>
                        <div class="p-3 border-bottom preview-content-area">
                            <h5 id="previewAppName" class="mb-2">{{ $settings['branding_app_name'] }}</h5>
                            <button type="button" class="btn btn-sm text-white preview-btn">Boton Primario</button>
                        </div>
                        <div class="preview-footer-showcase p-3">
                            <div class="preview-device-grid">
                                <div class="preview-device-block">
                                    <div class="preview-device-label">Footer en escritorio</div>
                                    <div class="preview-footer-desktop">
                                        <div class="preview-footer-branding-row">
                                            <img src="{{ $resolveBrandingPreviewUrl($settings['branding_footer_logo_path'], 'img/LogoCampus.png') }}" alt="Logo footer" class="preview-footer-logo-image bg-white rounded p-1">
                                            <div>
                                                <h6 class="mb-1 preview-footer-app-name-text">{{ $settings['branding_app_name'] }}</h6>
                                                <p class="mb-0 small text-muted">Basado en la idea original de la app A-DDIE (Código abierto)</p>
                                            </div>
                                        </div>
                                        <div class="preview-footer-meta-block text-md-end">
                                            <p class="mb-0 preview-footer-copy-text">&copy; {{ date('Y') }} {{ $settings['branding_app_name'] }}.</p>
                                            <p class="mb-0 small text-muted">Diseñado y desarrollado por DanielDev87</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="preview-device-block">
                                    <div class="preview-device-label">Footer en móvil</div>
                                    <div class="preview-mobile-frame mx-auto">
                                        <div class="preview-footer-mobile">
                                            <img src="{{ $resolveBrandingPreviewUrl($settings['branding_footer_logo_path'], 'img/LogoCampus.png') }}" alt="Logo footer móvil" class="preview-footer-logo-image bg-white rounded p-1">
                                            <div>
                                                <h6 class="mb-1 preview-footer-app-name-text">{{ $settings['branding_app_name'] }}</h6>
                                                <p class="mb-0 small text-muted">Basado en la idea original de la app A-DDIE (Código abierto)</p>
                                            </div>
                                            <div class="preview-footer-meta-mobile mt-3">
                                                <p class="mb-0 preview-footer-copy-text">&copy; {{ date('Y') }} {{ $settings['branding_app_name'] }}.</p>
                                                <p class="mb-0 small text-muted">Diseñado y desarrollado por DanielDev87</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <h2 class="h6 mb-3">Replicar en otra empresa</h2>
                    <div class="d-grid gap-2 mb-3">
                        <a href="{{ route('technical.branding-settings.export') }}" class="btn btn-outline-primary">
                            <i class="fas fa-download me-1"></i>Exportar configuracion JSON
                        </a>
                    </div>

                    <form method="POST" action="{{ route('technical.branding-settings.import') }}" enctype="multipart/form-data" class="row g-2">
                        @csrf
                        <div class="col-12">
                            <label class="form-label fw-semibold" for="branding_file">Importar configuracion JSON</label>
                            <input id="branding_file" name="branding_file" type="file" class="form-control @error('branding_file') is-invalid @enderror" accept=".json,application/json" required>
                            @error('branding_file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 d-grid">
                            <button type="submit" class="btn btn-outline-success">
                                <i class="fas fa-upload me-1"></i>Importar y aplicar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .preview-browser-bar {
        background: #dfe5ec;
    }

    .preview-browser-tab {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        max-width: 240px;
        padding: 0.5rem 0.75rem;
        border-radius: 0.7rem 0.7rem 0 0;
        background: #ffffff;
        box-shadow: 0 1px 0 rgba(15, 23, 42, 0.08);
    }

    .preview-tab-favicon {
        width: 16px;
        height: 16px;
        object-fit: contain;
        flex: 0 0 auto;
    }

    .preview-tab-name {
        display: inline-block;
        max-width: 180px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        font-size: 0.8rem;
        color: #334155;
    }

    .preview-nav {
        background: linear-gradient(135deg, {{ $settings['branding_primary_color'] }} 0%, {{ $settings['branding_secondary_color'] }} 100%);
    }

    .preview-content-area {
        background: #212529;
        color: #f8f9fa;
    }

    .preview-btn {
        background-color: {{ $settings['branding_primary_color'] }};
        border-color: {{ $settings['branding_primary_color'] }};
    }

    .preview-footer-showcase {
        background: #f3f4f6;
    }

    .preview-device-grid {
        display: grid;
        gap: 1rem;
    }

    .preview-device-label {
        margin-bottom: 0.5rem;
        font-size: 0.78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #64748b;
    }

    .preview-footer-desktop,
    .preview-footer-mobile {
        background: #212529;
        color: #f8f9fa;
        border-radius: 0.9rem;
        padding: 1rem;
        box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.05);
    }

    .preview-footer-desktop {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
    }

    .preview-footer-branding-row {
        display: flex;
        align-items: center;
        gap: 0.9rem;
        min-width: 0;
        flex: 1 1 auto;
    }

    .preview-footer-branding-row > div,
    .preview-footer-mobile > div {
        min-width: 0;
    }

    .preview-footer-logo-image {
        max-height: 48px;
        width: auto;
        max-width: 140px;
        object-fit: contain;
        flex: 0 0 auto;
    }

    .preview-footer-app-name-text {
        color: #f8f9fa;
        font-size: 1rem;
        line-height: 1.2;
        word-break: break-word;
    }

    .preview-footer-copy-text {
        color: #f8f9fa;
    }

    .preview-footer-meta-block {
        flex: 0 0 34%;
        min-width: 180px;
    }

    .preview-mobile-frame {
        max-width: 270px;
        padding: 0.4rem;
        border-radius: 1.4rem;
        background: #111827;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.18);
    }

    .preview-footer-mobile {
        text-align: center;
    }

    .preview-footer-mobile .preview-footer-logo-image {
        margin: 0 auto 0.75rem;
        max-height: 40px;
    }

    .preview-footer-meta-mobile {
        padding-top: 0.75rem;
        border-top: 1px solid rgba(255, 255, 255, 0.08);
    }

    @media (min-width: 992px) {
        .preview-device-grid {
            grid-template-columns: minmax(0, 1.3fr) minmax(0, 0.9fr);
            align-items: start;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const appName = document.getElementById('branding_app_name');
        const shortName = document.getElementById('branding_short_name');
        const logoPath = document.getElementById('branding_logo_path');
        const logoFile = document.getElementById('branding_logo_file');
        const footerLogoPath = document.getElementById('branding_footer_logo_path');
        const footerLogoFile = document.getElementById('branding_footer_logo_file');
        const faviconPath = document.getElementById('branding_favicon_path');
        const faviconFile = document.getElementById('branding_favicon_file');
        const primary = document.getElementById('branding_primary_color');
        const secondary = document.getElementById('branding_secondary_color');

        const primaryText = document.getElementById('branding_primary_color_text');
        const secondaryText = document.getElementById('branding_secondary_color_text');

        const previewAppName = document.getElementById('previewAppName');
        const previewShortName = document.getElementById('previewShortName');
        const previewTabName = document.getElementById('previewTabName');
        const previewLogo = document.getElementById('previewLogo');
        const previewFavicon = document.getElementById('previewFavicon');
        const previewFooterLogos = Array.from(document.querySelectorAll('.preview-footer-logo-image'));
        const previewFooterAppNames = Array.from(document.querySelectorAll('.preview-footer-app-name-text'));
        const previewFooterCopyTexts = Array.from(document.querySelectorAll('.preview-footer-copy-text'));
        const previewNav = document.querySelector('.preview-nav');
        const previewBtn = document.querySelector('.preview-btn');

        function assetUrl(path, fallback) {
            const normalizedPath = (path || fallback || '').replace(/^\/+/, '');

            if (normalizedPath.startsWith('storage/branding/') || normalizedPath.startsWith('branding/')) {
                const filename = normalizedPath.split('/').pop();
                return '{{ url('/branding-assets') }}/' + encodeURIComponent(filename);
            }

            return '{{ asset('') }}' + normalizedPath;
        }

        function setImageTargets(targets, src) {
            targets.forEach(function (target) {
                if (target) {
                    target.src = src;
                }
            });
        }

        function previewSelectedFile(fileInput, imageTargets, fallbackUpdater) {
            const targets = Array.isArray(imageTargets) ? imageTargets : [imageTargets];

            if (!fileInput || !targets.length) {
                return;
            }

            fileInput.addEventListener('change', function () {
                const file = fileInput.files && fileInput.files[0] ? fileInput.files[0] : null;
                if (!file) {
                    fallbackUpdater();
                    return;
                }

                const reader = new FileReader();
                reader.onload = function (event) {
                    if (event.target && event.target.result) {
                        setImageTargets(targets, event.target.result);
                    }
                };
                reader.readAsDataURL(file);
            });
        }

        function normalizeHex(value) {
            const raw = (value || '').trim();
            return /^#(?:[0-9a-fA-F]{3}){1,2}$/.test(raw) ? raw : null;
        }

        function syncColorInputs(colorInput, textInput) {
            colorInput.addEventListener('input', function () {
                textInput.value = colorInput.value;
                updatePreview();
            });

            textInput.addEventListener('input', function () {
                const normalized = normalizeHex(textInput.value);
                if (normalized) {
                    colorInput.value = normalized;
                    updatePreview();
                }
            });
        }

        function updatePreview() {
            const appNameValue = appName.value || 'Nombre de App';
            const shortNameValue = shortName.value || 'Nombre corto';

            previewAppName.textContent = appNameValue;
            previewShortName.textContent = shortNameValue;
            previewTabName.textContent = shortNameValue;
            previewLogo.src = assetUrl(logoPath.value, 'img/logomsula.png');
            setImageTargets(previewFooterLogos, assetUrl(footerLogoPath.value, 'img/LogoCampus.png'));
            previewFavicon.src = assetUrl(faviconPath.value, 'img/logomsula.png');
            previewFooterAppNames.forEach(function (element) {
                element.textContent = appNameValue;
            });
            previewFooterCopyTexts.forEach(function (element) {
                element.textContent = '© {{ date('Y') }} ' + appNameValue + '.';
            });

            const primaryColor = normalizeHex(primary.value) || '#0280AE';
            const secondaryColor = normalizeHex(secondary.value) || '#17a2b8';
            previewNav.style.background = 'linear-gradient(135deg, ' + primaryColor + ' 0%, ' + secondaryColor + ' 100%)';
            previewBtn.style.backgroundColor = primaryColor;
            previewBtn.style.borderColor = primaryColor;
        }

        [appName, shortName, logoPath, footerLogoPath, faviconPath, primary, secondary].forEach(function (el) {
            el.addEventListener('input', updatePreview);
        });

        previewSelectedFile(logoFile, previewLogo, updatePreview);
        previewSelectedFile(footerLogoFile, previewFooterLogos, updatePreview);
        previewSelectedFile(faviconFile, previewFavicon, updatePreview);

        syncColorInputs(primary, primaryText);
        syncColorInputs(secondary, secondaryText);
        updatePreview();
    });
</script>
@endpush
