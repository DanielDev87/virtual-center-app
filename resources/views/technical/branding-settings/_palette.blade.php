@php
    $paletteConfig = \App\Services\BrandPaletteService::settings();
    $paletteDefaults = \App\Services\BrandPaletteService::DEFAULT_PALETTE;
    $paletteHasOld = old('palette_1') !== null;
    $paletteValues = [];
    foreach (range(1, 6) as $paletteIndex) {
        $paletteValues[$paletteIndex] = old('palette_' . $paletteIndex, $paletteConfig['palette'][$paletteIndex - 1]);
    }
    $paletteEnabled = $paletteHasOld ? (bool) old('palette_enabled') : $paletteConfig['enabled'];
    $paletteSuggested = session('palette_suggested');
    $paletteFailures = session('palette_failures', []);
    $paletteRoles = [
        1 => 'Fondo (oscuro) y texto (claro)',
        2 => 'Tarjetas y paneles (oscuro)',
        3 => 'Fondos secundarios (oscuro) y texto secundario (claro)',
        4 => 'Bordes (oscuro)',
        5 => 'Texto secundario (oscuro) y bordes (claro)',
        6 => 'Texto (oscuro) y fondo (claro)',
    ];
@endphp
<div class="container-fluid pb-4">
    <div class="card shadow-sm">
        <div class="card-body">
            <h2 class="h6 mb-1">Paleta de colores de la plataforma</h2>
            <p class="text-muted small">
                Elige los seis colores, del más oscuro al más claro, y revisa la vista previa y el contraste antes de guardar.
                Al guardar con la paleta activada se aplica a fondos, tarjetas, bordes y textos en modo claro y oscuro.
                Si la desactivas, la plataforma conserva su apariencia actual.
            </p>

            @if($errors->palette->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->palette->all() as $paletteError)
                        <li>{{ $paletteError }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            @if(!empty($paletteFailures))
            <div class="alert alert-danger">
                <strong>Combinaciones que no cumplen:</strong>
                <ul class="mb-0">
                    @foreach($paletteFailures as $paletteFailure)
                        <li>{{ $paletteFailure }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            @if(is_array($paletteSuggested))
            <div class="alert alert-warning">
                <strong>Alternativa legible sugerida.</strong>
                Ajusté solo los colores que no cumplían, manteniendo su tono lo más posible.
                <div class="d-flex flex-wrap gap-2 my-2">
                    @foreach($paletteSuggested as $suggestedIndex => $suggestedColor)
                        <span class="badge border" style="background: {{ $suggestedColor }}; color: {{ \App\Services\BrandPaletteService::ratio('#FFFFFF', $suggestedColor) >= \App\Services\BrandPaletteService::ratio('#000000', $suggestedColor) ? '#FFFFFF' : '#000000' }};">
                            {{ $suggestedIndex + 1 }}: {{ $suggestedColor }}
                        </span>
                    @endforeach
                </div>
                <button type="button" class="btn btn-sm btn-warning" id="paletteUseSuggestion" data-colors='@json($paletteSuggested)'>
                    <i class="fas fa-magic me-1"></i>Usar alternativa legible
                </button>
            </div>
            @endif

            <form method="POST" action="{{ route('technical.branding-palette.update') }}" id="paletteForm">
                @csrf
                @method('PUT')

                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" role="switch" id="palette_enabled" name="palette_enabled" value="1" {{ $paletteEnabled ? 'checked' : '' }}>
                    <label class="form-check-label fw-semibold" for="palette_enabled">Aplicar esta paleta a la plataforma</label>
                </div>

                <div class="row g-3">
                    @foreach($paletteRoles as $paletteNumber => $paletteRole)
                    <div class="col-md-4 col-lg-2">
                        <label class="form-label fw-semibold mb-0" for="palette_{{ $paletteNumber }}">Color {{ $paletteNumber }}</label>
                        <div class="form-text mt-0 mb-1" style="min-height: 3.6em;">{{ $paletteRole }}</div>
                        <div class="input-group">
                            <input id="palette_{{ $paletteNumber }}" name="palette_{{ $paletteNumber }}" type="color" class="form-control form-control-color" value="{{ $paletteValues[$paletteNumber] }}">
                            <input id="palette_{{ $paletteNumber }}_text" type="text" class="form-control" value="{{ $paletteValues[$paletteNumber] }}" maxlength="7" aria-label="Color {{ $paletteNumber }} en hexadecimal">
                        </div>
                    </div>
                    @endforeach
                </div>

                <div class="row g-3 mt-1">
                    <div class="col-md-6">
                        <div id="previewDark" class="rounded p-3" style="background: var(--pv-bg); color: var(--pv-text); border: 1px solid var(--pv-border);">
                            <div class="small fw-bold mb-2">Vista previa: modo oscuro</div>
                            <div class="rounded p-2 mb-2" style="background: var(--pv-card); border: 1px solid var(--pv-border);">
                                <div>Texto principal</div>
                                <div class="small" style="color: var(--pv-muted);">Texto secundario</div>
                            </div>
                            <button type="button" class="btn btn-sm border-0" style="background: var(--pv-btn); color: var(--pv-btn-text);">Botón primario</button>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div id="previewLight" class="rounded p-3" style="background: var(--pv-bg); color: var(--pv-text); border: 1px solid var(--pv-border);">
                            <div class="small fw-bold mb-2">Vista previa: modo claro</div>
                            <div class="rounded p-2 mb-2" style="background: var(--pv-card); border: 1px solid var(--pv-border);">
                                <div>Texto principal</div>
                                <div class="small" style="color: var(--pv-muted);">Texto secundario</div>
                            </div>
                            <button type="button" class="btn btn-sm border-0" style="background: var(--pv-btn); color: var(--pv-btn-text);">Botón primario</button>
                        </div>
                    </div>
                </div>

                <div class="table-responsive mt-3">
                    <table class="table table-sm align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Combinación de texto normal (mínimo 4.5:1)</th>
                                <th class="text-end">Contraste</th>
                                <th class="text-center">Resultado</th>
                            </tr>
                        </thead>
                        <tbody id="paletteChecks"></tbody>
                    </table>
                </div>
                <p id="paletteSummary" class="small mt-2 mb-0"></p>

                <div class="d-flex justify-content-end gap-2 mt-3">
                    <button type="button" class="btn btn-outline-secondary" id="paletteReset">
                        <i class="fas fa-undo me-1"></i>Restablecer paleta de referencia
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Guardar paleta
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    (function () {
        const defaults = @json($paletteDefaults);
        const primaryColor = @json($settings['branding_primary_color'] ?? '#0280AE');
        const colorInputs = [];
        const textInputs = [];

        for (let i = 1; i <= 6; i++) {
            colorInputs.push(document.getElementById('palette_' + i));
            textInputs.push(document.getElementById('palette_' + i + '_text'));
        }

        const checksBody = document.getElementById('paletteChecks');
        const summary = document.getElementById('paletteSummary');

        function expand(hex) {
            hex = (hex || '').trim();
            if (/^#[0-9a-fA-F]{3}$/.test(hex)) {
                hex = '#' + hex[1] + hex[1] + hex[2] + hex[2] + hex[3] + hex[3];
            }
            return /^#[0-9a-fA-F]{6}$/.test(hex) ? hex.toUpperCase() : null;
        }

        function rgb(hex) {
            return [parseInt(hex.slice(1, 3), 16), parseInt(hex.slice(3, 5), 16), parseInt(hex.slice(5, 7), 16)];
        }

        function toHex(channels) {
            return '#' + channels.map(function (v) {
                return Math.max(0, Math.min(255, Math.round(v))).toString(16).padStart(2, '0');
            }).join('').toUpperCase();
        }

        function mix(a, b, weight) {
            const x = rgb(a), y = rgb(b);
            return toHex([0, 1, 2].map(function (i) { return x[i] * (1 - weight) + y[i] * weight; }));
        }

        function luminance(hex) {
            const c = rgb(hex).map(function (v) {
                v = v / 255;
                return v <= 0.03928 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4);
            });
            return 0.2126 * c[0] + 0.7152 * c[1] + 0.0722 * c[2];
        }

        function ratio(a, b) {
            const l1 = luminance(a), l2 = luminance(b);
            return (Math.max(l1, l2) + 0.05) / (Math.min(l1, l2) + 0.05);
        }

        function setVars(element, vars) {
            Object.keys(vars).forEach(function (name) { element.style.setProperty(name, vars[name]); });
        }

        function render() {
            const p = colorInputs.map(function (input) { return expand(input.value); });
            if (p.some(function (value) { return value === null; })) {
                return;
            }

            const surface = mix(p[5], '#FFFFFF', 0.6);
            const secondary = mix(p[5], '#000000', 0.06);
            const primary = expand(primaryColor) || '#0280AE';
            const buttonText = ratio('#FFFFFF', primary) >= ratio(p[0], primary) ? '#FFFFFF' : p[0];

            setVars(document.getElementById('previewDark'), {
                '--pv-bg': p[0], '--pv-card': p[1], '--pv-text': p[5], '--pv-muted': p[4],
                '--pv-border': p[3], '--pv-btn': primary, '--pv-btn-text': buttonText
            });
            setVars(document.getElementById('previewLight'), {
                '--pv-bg': p[5], '--pv-card': surface, '--pv-text': p[0], '--pv-muted': p[2],
                '--pv-border': p[4], '--pv-btn': primary, '--pv-btn-text': buttonText
            });

            const checks = [
                ['Modo oscuro: texto principal sobre el fondo', p[5], p[0]],
                ['Modo oscuro: texto principal sobre las tarjetas', p[5], p[1]],
                ['Modo oscuro: texto principal sobre fondos secundarios', p[5], p[2]],
                ['Modo oscuro: texto secundario sobre el fondo', p[4], p[0]],
                ['Modo oscuro: texto secundario sobre las tarjetas', p[4], p[1]],
                ['Modo claro: texto principal sobre el fondo', p[0], p[5]],
                ['Modo claro: texto principal sobre las tarjetas', p[0], surface],
                ['Modo claro: texto principal sobre fondos secundarios', p[0], secondary],
                ['Modo claro: texto secundario sobre el fondo', p[2], p[5]],
                ['Modo claro: texto secundario sobre las tarjetas', p[2], surface]
            ];

            checksBody.innerHTML = '';
            let failures = 0;

            checks.forEach(function (check) {
                const value = ratio(check[1], check[2]);
                const ok = value >= 4.5;
                if (!ok) { failures++; }

                const row = document.createElement('tr');
                const label = document.createElement('td');
                label.textContent = check[0];
                const number = document.createElement('td');
                number.className = 'text-end';
                number.textContent = value.toFixed(2) + ':1';
                const result = document.createElement('td');
                result.className = 'text-center';
                const badge = document.createElement('span');
                badge.className = 'badge ' + (ok ? 'bg-success' : 'bg-danger');
                badge.textContent = ok ? 'Cumple' : 'No cumple';
                result.appendChild(badge);
                row.appendChild(label);
                row.appendChild(number);
                row.appendChild(result);
                checksBody.appendChild(row);
            });

            summary.textContent = failures === 0
                ? 'Todas las combinaciones cumplen WCAG AA.'
                : failures + ' combinación(es) no cumplen. Si intentas guardar, te propondremos una alternativa legible.';
            summary.className = 'small mt-2 mb-0 ' + (failures === 0 ? 'text-success' : 'text-danger');
        }

        function setColors(colors) {
            colors.forEach(function (color, index) {
                const value = expand(color);
                if (value) {
                    colorInputs[index].value = value;
                    textInputs[index].value = value;
                }
            });
            render();
        }

        colorInputs.forEach(function (input, index) {
            input.addEventListener('input', function () {
                textInputs[index].value = input.value.toUpperCase();
                render();
            });
        });

        textInputs.forEach(function (input, index) {
            input.addEventListener('input', function () {
                const value = expand(input.value);
                if (value) {
                    colorInputs[index].value = value;
                    render();
                }
            });
        });

        document.getElementById('paletteReset').addEventListener('click', function () {
            setColors(defaults);
        });

        const useSuggestion = document.getElementById('paletteUseSuggestion');
        if (useSuggestion) {
            useSuggestion.addEventListener('click', function () {
                setColors(JSON.parse(useSuggestion.getAttribute('data-colors')));
            });
        }

        render();
    })();
</script>
@endpush