@extends('layouts.admin')

@section('title', 'Carga Masiva - Admin')

@section('content')
@php
    $entityDefinitions = $entities ?? [];
    $selectedEntity = old('entity', 'users');
    $currentDefinition = $entityDefinitions[$selectedEntity] ?? null;
    $summary = session('import_summary');
    $preview = session('import_preview');
@endphp
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-xl-10 col-lg-11">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="h4 mb-0">Carga masiva desde CSV</h2>
                <a id="templateDownloadBtn" href="{{ route('admin.bulk-import.template', ['entity' => $selectedEntity]) }}" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-download me-1"></i>Descargar plantilla
                </a>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-1"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-1"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger" role="alert">
                    <strong>Se encontraron errores de validacion:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if($preview)
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <strong>Resultado de previsualizacion (sin guardar)</strong>
                        @if(!empty($preview['errors']))
                            <a href="{{ route('admin.bulk-import.errors-report') }}" class="btn btn-outline-danger btn-sm">
                                <i class="fas fa-file-download me-1"></i>Descargar reporte de errores
                            </a>
                        @endif
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info py-2 small">
                            Esto es solo una previsualizacion: todavia no se guardo ningun registro.
                        </div>

                        <div class="row g-2 mb-2">
                            <div class="col-md-3"><span class="badge bg-dark w-100 py-2">Filas procesadas: {{ $preview['total'] }}</span></div>
                            <div class="col-md-3"><span class="badge bg-success w-100 py-2">Validas: {{ $preview['valid'] }}</span></div>
                            <div class="col-md-3"><span class="badge bg-danger w-100 py-2">Invalidas: {{ $preview['invalid'] }}</span></div>
                            <div class="col-md-3"><span class="badge bg-warning text-dark w-100 py-2">Posibles duplicados: {{ $preview['duplicates'] }}</span></div>
                        </div>
                        <p class="small text-muted mb-3">
                            De las filas validas: {{ $preview['created'] }} se crearian, {{ $preview['updated'] }} se actualizarian y {{ $preview['skipped'] }} se omitirian.
                            Un posible duplicado es una fila que repite el identificador (por ejemplo el correo o el nombre) de otra fila del mismo archivo.
                        </p>

                        @if($preview['invalid'] > 0)
                            <div class="alert alert-warning py-2 small">
                                Hay {{ $preview['invalid'] }} fila(s) con errores. Si procesa el archivo asi, <strong>no se guardara ningun registro</strong>,
                                salvo que marque la opcion de importacion parcial.
                            </div>
                        @endif

                        @if(!empty($preview['rows']))
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 90px;">Fila</th>
                                            <th style="width: 190px;">Resultado</th>
                                            <th style="width: 180px;">Campo</th>
                                            <th>Motivo</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($preview['rows'] as $item)
                                            <tr>
                                                <td>{{ $item['line'] }}</td>
                                                <td>
                                                    @if($item['status'] === 'error')
                                                        <span class="badge bg-danger">Error</span>
                                                    @elseif($item['operation'] === 'created')
                                                        <span class="badge bg-success">Crear</span>
                                                    @elseif($item['operation'] === 'updated')
                                                        <span class="badge bg-primary">Actualizar</span>
                                                    @else
                                                        <span class="badge bg-secondary">Omitir</span>
                                                    @endif
                                                    @if($item['status'] === 'warning')
                                                        <span class="badge bg-warning text-dark">Duplicado</span>
                                                    @endif
                                                </td>
                                                <td><code>{{ $item['field'] ?? '-' }}</code></td>
                                                <td>{{ $item['message'] }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            @if($summary)
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <strong>Resultado de la ultima importacion</strong>
                        @if(($summary['errors_total'] ?? 0) > 0)
                            <a href="{{ route('admin.bulk-import.errors-report') }}" class="btn btn-outline-danger btn-sm">
                                <i class="fas fa-file-download me-1"></i>Descargar reporte de errores
                            </a>
                        @endif
                    </div>
                    <div class="card-body">
                        @if(!empty($summary['rolled_back']))
                            <div class="alert alert-danger">
                                <strong>No se guardo ningun registro.</strong>
                                {{ $summary['errors_total'] }} fila(s) tienen errores, por eso se cancelo toda la importacion.
                                Corrija el archivo o marque la importacion parcial para guardar solo las filas validas
                                ({{ $summary['valid_rows'] }} fila(s) eran validas).
                            </div>
                        @elseif(!empty($summary['partial']))
                            <div class="alert alert-warning">
                                <strong>Importacion parcial.</strong>
                                Se guardaron {{ $summary['saved_total'] }} registro(s) y {{ $summary['errors_total'] }} fila(s) no se guardaron por errores.
                                Revise abajo cuales se guardaron y cuales no.
                            </div>
                        @else
                            <div class="alert alert-success">
                                <strong>Importacion completa.</strong> Todas las filas se procesaron sin errores.
                            </div>
                        @endif

                        <div class="row g-2 mb-3">
                            <div class="col-md-2"><span class="badge bg-dark w-100 py-2">Filas: {{ $summary['total'] }}</span></div>
                            <div class="col-md-2"><span class="badge bg-success w-100 py-2">Creados: {{ $summary['created'] }}</span></div>
                            <div class="col-md-3"><span class="badge bg-primary w-100 py-2">Actualizados: {{ $summary['updated'] }}</span></div>
                            <div class="col-md-2"><span class="badge bg-secondary w-100 py-2">Omitidos: {{ $summary['skipped'] }}</span></div>
                            <div class="col-md-3"><span class="badge bg-danger w-100 py-2">Con error: {{ $summary['errors_total'] ?? 0 }}</span></div>
                        </div>

                        @if(!empty($summary['errors']))
                            <h6>Filas que no se guardaron</h6>
                            <div class="table-responsive mb-3">
                                <table class="table table-sm table-bordered mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 90px;">Fila</th>
                                            <th style="width: 180px;">Campo</th>
                                            <th>Motivo</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($summary['errors'] as $error)
                                            <tr>
                                                <td>{{ $error['line'] }}</td>
                                                <td><code>{{ $error['field'] ?? '-' }}</code></td>
                                                <td>{{ $error['message'] }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @if(($summary['errors_total'] ?? 0) > count($summary['errors']))
                                <p class="small text-muted">Se muestran las primeras {{ count($summary['errors']) }}; el reporte descargable incluye todas.</p>
                            @endif
                        @endif

                        @if(!empty($summary['saved']))
                            <h6>Registros guardados</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 90px;">Fila</th>
                                            <th style="width: 150px;">Operacion</th>
                                            <th>Registro</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($summary['saved'] as $item)
                                            <tr>
                                                <td>{{ $item['line'] }}</td>
                                                <td>
                                                    @if($item['operation'] === 'created')
                                                        <span class="badge bg-success">Creado</span>
                                                    @else
                                                        <span class="badge bg-primary">Actualizado</span>
                                                    @endif
                                                </td>
                                                <td>{{ $item['identifier'] ?? '-' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @if(($summary['saved_total'] ?? 0) > count($summary['saved']))
                                <p class="small text-muted mt-2">Se muestran los primeros {{ count($summary['saved']) }} de {{ $summary['saved_total'] }} registros guardados.</p>
                            @endif
                        @endif
                    </div>
                </div>
            @endif

            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.bulk-import.store') }}" enctype="multipart/form-data" class="row g-3">
                        @csrf
                        <input type="hidden" name="action" id="bulkActionInput" value="import">

                        <div class="col-md-4">
                            <label for="entity" class="form-label fw-semibold">Entidad a importar</label>
                            <select class="form-select @error('entity') is-invalid @enderror" id="entity" name="entity" required>
                                @foreach($entityDefinitions as $key => $definition)
                                    <option value="{{ $key }}" {{ $selectedEntity === $key ? 'selected' : '' }}>
                                        {{ $definition['label'] }}
                                    </option>
                                @endforeach
                            </select>
                            @error('entity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-8">
                            <label for="file" class="form-label fw-semibold">Archivo</label>
                            <input type="file" class="form-control @error('file') is-invalid @enderror" id="file" name="file" accept=".csv,.txt" required>
                            @error('file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Formato recomendado: UTF-8, primera fila con encabezados y textos entre comillas dobles (") cuando contengan comas. Delimitadores soportados: coma, punto y coma, tabulacion.</small>
                        </div>

                        <div class="col-12">
                            <div class="card border-light bg-light">
                                <div class="card-body py-3">
                                    <h6 class="mb-3">Guia de columnas</h6>
                                    <div id="entityHelp">
                                        @if($currentDefinition)
                                            <div class="mb-2"><strong>Requeridas:</strong> {{ implode(', ', $currentDefinition['required']) }}</div>
                                            <div class="mb-2"><strong>Opcionales:</strong> {{ implode(', ', $currentDefinition['optional']) }}</div>
                                            <div><strong>Encabezado ejemplo:</strong> <code>{{ $currentDefinition['sample_headers'] }}</code></div>
                                            <div class="mt-2 d-flex flex-wrap align-items-center gap-2">
                                                <strong>Fila ejemplo:</strong>
                                                <code id="sampleRowText">{{ implode(',', $currentDefinition['sample_row']) }}</code>
                                                <button type="button" id="copySampleBtn" class="btn btn-outline-secondary btn-sm">
                                                    <i class="fas fa-copy me-1"></i>Copiar ejemplo
                                                </button>
                                            </div>
                                        @endif
                                    </div>
                                    <hr>
                                    <div id="entityTips" class="small text-muted">
                                        <ul class="mb-0">
                                            <li>El sistema crea o actualiza segun coincidencia por identificador principal (email, nombre o id).</li>
                                            <li>Por defecto, si alguna fila tiene errores no se guarda ningun registro (todo o nada).</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="allow_partial" id="allow_partial" value="1">
                                <label class="form-check-label" for="allow_partial">
                                    Permitir importacion parcial: guardar las filas validas aunque otras tengan errores
                                </label>
                            </div>
                            <small class="text-muted">
                                Si no la marca, ante cualquier fila con error no se guardara ningun registro.
                                Si la marca, al terminar se le informara cuales registros se guardaron y cuales no.
                            </small>
                        </div>

                        <div class="col-12 d-flex justify-content-end">
                            <button type="submit" class="btn btn-outline-secondary px-4 me-2" onclick="document.getElementById('bulkActionInput').value='preview'">
                                <i class="fas fa-eye me-1"></i> Previsualizar
                            </button>
                            <button type="submit" class="btn btn-primary px-4" onclick="document.getElementById('bulkActionInput').value='import'">
                                <i class="fas fa-file-import me-1"></i> Procesar archivo
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        const definitions = @json($entityDefinitions);
        const entitySelect = document.getElementById('entity');
        const helpContainer = document.getElementById('entityHelp');
        const tipsContainer = document.getElementById('entityTips');
        const templateButton = document.getElementById('templateDownloadBtn');
        const templateBase = @json(url('admin/bulk-import/template'));
        const copySampleBtn = document.getElementById('copySampleBtn');
        const defaultCopyButtonHTML = copySampleBtn ? copySampleBtn.innerHTML : '';
        const entityTips = {
            users: [
                'Usuarios: job_positions puede incluir varios nombres separados por | o ;.',
                'Si el usuario no existe, password es obligatorio.'
            ],
            request_types: [
                'Topicos: collaborator_emails o collaborator_ids admite varios valores separados por | o ;.',
                'Si no existe el topico, debe indicar al menos un colaborador/gestor.'
            ],
            faculties: [
                'Facultades: puede asociar institucion por institution_id o institution_name.',
                'Si hay facultades homonimas, use faculty_id o institution_name para desambiguar.'
            ],
            areas: [
                'Areas: puede asociar facultad por faculty_id o faculty_name.',
                'Si hay nombres repetidos, agregue institution_name/faculty_name para mayor precision.'
            ],
            programs: [
                'Programas: para crear requiere faculty_id o faculty_name.',
                'program_code ayuda a evitar ambiguedad en actualizaciones.'
            ],
            courses: [
                'Cursos: para crear requiere course_name, course_code y al menos un programa (program_id/program_code/program_name o listas program_ids/program_codes/program_names separadas por | o ;).',
                'Si un program_code o program_name se repite y no envias faculty/institution, el curso se vinculara a todos los programas coincidentes.',
                'credits debe estar entre 1 y 10.'
            ]
        };

        function renderHelp(entityKey) {
            const definition = definitions[entityKey];
            if (!definition || !helpContainer) {
                return;
            }

            helpContainer.innerHTML = `
                <div class="mb-2"><strong>Requeridas:</strong> ${definition.required.join(', ')}</div>
                <div class="mb-2"><strong>Opcionales:</strong> ${definition.optional.join(', ')}</div>
                <div><strong>Encabezado ejemplo:</strong> <code>${definition.sample_headers}</code></div>
                <div class="mt-2 d-flex flex-wrap align-items-center gap-2">
                    <strong>Fila ejemplo:</strong>
                    <code id="sampleRowText">${definition.sample_row.join(',')}</code>
                    <button type="button" id="copySampleBtn" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-copy me-1"></i>Copiar ejemplo
                    </button>
                </div>
            `;

            if (tipsContainer) {
                const commonTips = [
                    'El sistema crea o actualiza segun coincidencia por identificador principal (email, nombre o id).',
                    'Por defecto, si alguna fila tiene errores no se guarda ningun registro (todo o nada).'
                ];
                const selectedTips = entityTips[entityKey] || [];
                const allTips = [...selectedTips, ...commonTips];

                tipsContainer.innerHTML = `<ul class="mb-0">${allTips.map((tip) => `<li>${tip}</li>`).join('')}</ul>`;
            }

            if (templateButton) {
                templateButton.setAttribute('href', `${templateBase}/${entityKey}`);
            }

            attachCopyHandler();
        }

        function attachCopyHandler() {
            const dynamicCopyBtn = document.getElementById('copySampleBtn');
            const sampleRowText = document.getElementById('sampleRowText');

            if (!dynamicCopyBtn || !sampleRowText) {
                return;
            }

            dynamicCopyBtn.addEventListener('click', async function () {
                const example = sampleRowText.textContent || '';

                try {
                    await navigator.clipboard.writeText(example);
                    const original = dynamicCopyBtn.innerHTML;
                    dynamicCopyBtn.innerHTML = '<i class="fas fa-check me-1"></i>Copiado';
                    dynamicCopyBtn.classList.remove('btn-outline-secondary');
                    dynamicCopyBtn.classList.add('btn-success');

                    setTimeout(() => {
                        dynamicCopyBtn.innerHTML = original || defaultCopyButtonHTML;
                        dynamicCopyBtn.classList.remove('btn-success');
                        dynamicCopyBtn.classList.add('btn-outline-secondary');
                    }, 1200);
                } catch (error) {
                    const original = dynamicCopyBtn.innerHTML;
                    dynamicCopyBtn.innerHTML = '<i class="fas fa-times me-1"></i>No se pudo copiar';
                    dynamicCopyBtn.classList.remove('btn-outline-secondary');
                    dynamicCopyBtn.classList.add('btn-danger');

                    setTimeout(() => {
                        dynamicCopyBtn.innerHTML = original || defaultCopyButtonHTML;
                        dynamicCopyBtn.classList.remove('btn-danger');
                        dynamicCopyBtn.classList.add('btn-outline-secondary');
                    }, 1400);
                }
            });
        }

        if (entitySelect) {
            entitySelect.addEventListener('change', function () {
                renderHelp(this.value);
            });

            renderHelp(entitySelect.value);
        } else {
            attachCopyHandler();
        }
    })();
</script>
@endpush