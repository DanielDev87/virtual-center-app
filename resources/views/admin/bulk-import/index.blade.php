@extends('layouts.admin')

@section('title', 'Carga Masiva - Admin')

@section('content')
@php
    $entityDefinitions = $entities ?? [];
    $selectedEntity = old('entity', 'users');
    $currentDefinition = $entityDefinitions[$selectedEntity] ?? null;
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

            @php
                $summary = session('import_summary');
                $preview = session('import_preview');
            @endphp
            @if($preview)
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-light">
                        <strong>Resultado de previsualizacion (sin guardar)</strong>
                    </div>
                    <div class="card-body">
                        <div class="row g-2 mb-3">
                            <div class="col-md-3"><span class="badge bg-dark w-100 py-2">Filas procesadas: {{ $preview['total'] }}</span></div>
                            <div class="col-md-3"><span class="badge bg-success w-100 py-2">Se crearian: {{ $preview['created'] }}</span></div>
                            <div class="col-md-3"><span class="badge bg-primary w-100 py-2">Se actualizarian: {{ $preview['updated'] }}</span></div>
                            <div class="col-md-3"><span class="badge bg-secondary w-100 py-2">Se omitirian: {{ $preview['skipped'] }}</span></div>
                        </div>

                        @if(!empty($preview['rows']))
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 110px;">Linea</th>
                                            <th style="width: 150px;">Resultado</th>
                                            <th>Detalle</th>
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
                                                </td>
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
                    <div class="card-header bg-light">
                        <strong>Resultado de la ultima importacion</strong>
                    </div>
                    <div class="card-body">
                        <div class="row g-2 mb-3">
                            <div class="col-md-3"><span class="badge bg-dark w-100 py-2">Filas procesadas: {{ $summary['total'] }}</span></div>
                            <div class="col-md-3"><span class="badge bg-success w-100 py-2">Creados: {{ $summary['created'] }}</span></div>
                            <div class="col-md-3"><span class="badge bg-primary w-100 py-2">Actualizados: {{ $summary['updated'] }}</span></div>
                            <div class="col-md-3"><span class="badge bg-secondary w-100 py-2">Omitidos: {{ $summary['skipped'] }}</span></div>
                        </div>

                        @if(!empty($summary['errors']))
                            <div class="alert alert-warning mb-0">
                                <strong>Filas con error</strong>
                                <div class="table-responsive mt-2">
                                    <table class="table table-sm table-bordered mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width: 120px;">Linea</th>
                                                <th>Detalle</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($summary['errors'] as $error)
                                                <tr>
                                                    <td>{{ $error['line'] }}</td>
                                                    <td>{{ $error['message'] }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
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
                                            <li>Las filas con error se omiten sin detener la importacion completa.</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
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
                    'Las filas con error se omiten sin detener la importacion completa.'
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
