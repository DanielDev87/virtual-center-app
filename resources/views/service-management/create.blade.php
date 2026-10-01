@extends('layouts.requester')

@section('title', 'Nueva Solicitud - Virtual Center')

@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-lg border-0 mb-5" style="border-radius: 12px; overflow: hidden;">
                <div class="card-header bg-primary bg-gradient text-white p-4">
                    <h3 class="mb-0 fw-bold"><i class="fas fa-plus-circle me-2"></i>Nueva Solicitud de Servicio</h3>
                    <p class="mb-0 text-white-50">Por favor completa los siguientes pasos para generar tu ticket y asignarlo al departamento correspondiente.</p>
                </div>
                <div class="card-body p-5">
                    @if($outsideBusinessHours ?? false)
                        <div class="alert alert-info border-info shadow-sm d-flex align-items-start gap-2" role="alert">
                            <i class="fas fa-clock mt-1"></i>
                            <div>
                                <strong>Solicitud fuera del horario laboral</strong>
                                <div>Tu ticket será recibido y comenzará a gestionarse el siguiente día hábil dentro del horario laboral, de lunes a viernes de 7:00 a. m. a 5:00 p. m.</div>
                                @if($nextBusinessStart)
                                    <small class="text-muted">Próximo inicio de jornada: {{ $nextBusinessStart->format('d/m/Y H:i') }}</small>
                                @endif
                            </div>
                        </div>
                    @endif
                    
                    <!-- Progress Bar for Wizard -->
                    <div class="position-relative mb-5 d-none d-md-block" id="wizard-progress-container">
                        <div class="progress" style="height: 4px;">
                            <div class="progress-bar bg-success" role="progressbar" style="width: 0%;" id="wizard-progress"></div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('service-management.store') }}" id="serviceRequestForm" enctype="multipart/form-data">
                        @csrf
                        
                        <!-- STEP 1: Identification -->
                        @guest
                        <div id="step1-identity" class="step-section">
                            <h4 class="text-primary mb-4 border-bottom pb-2">Paso 1: Identificación</h4>
                            <div class="row justify-content-center">
                                <div class="col-md-8 text-center mb-3">
                                    <i class="fas fa-id-card fa-4x text-muted mb-3"></i>
                                    <p class="text-muted">Para brindarte un mejor servicio, necesitamos consultar tus datos básicos.</p>
                                </div>
                                <div class="col-md-8">
                                    <label for="document_number_search" class="form-label fw-bold">Digita tu Identificación (Cédula/TI) <span class="text-danger">*</span></label>
                                    <div class="input-group input-group-lg shadow-sm">
                                        <span class="input-group-text bg-white"><i class="fas fa-fingerprint text-primary"></i></span>
                                        <input type="text" class="form-control" id="document_number_search" placeholder="Escribe tu número sin puntos ni comillas">
                                        <button class="btn btn-primary px-4 fw-bold" type="button" id="btn-verify-doc">
                                            <span class="spinner-border spinner-border-sm d-none me-2" id="doc-spinner"></span>Validar
                                        </button>
                                    </div>
                                    <div class="text-danger mt-2 d-none fw-bold" id="doc-error">Por favor ingresa un número de documento válido.</div>
                                </div>
                            </div>
                        </div>

                        <!-- STEP 2: Profile -->
                        <div id="step2-profile" class="step-section d-none">
                            <h4 class="text-primary mb-4 border-bottom pb-2">Paso 2: Información Personal</h4>
                            
                            <div class="alert alert-success border-0 shadow-sm mb-4 d-none" id="profile-found-msg">
                                <h6><i class="fas fa-check-circle me-2"></i>¡Bienvenido de nuevo!</h6>
                                <p class="mb-0">Hemos encontrado tus datos. Por favor verifica que sean correctos antes de continuar.</p>
                            </div>
                            <div class="alert alert-info border-0 shadow-sm mb-4 d-none" id="profile-new-msg">
                                <h6><i class="fas fa-info-circle me-2"></i>Usuario Nuevo</h6>
                                <p class="mb-0">Es la primera vez que registras una solicitud. Llena tus datos para continuar.</p>
                            </div>

                            <input type="hidden" name="document_number" id="document_number_final">
                            
                            <div class="row g-4 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-muted">Nombre Completo <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-lg bg-light" name="requester_name" id="requester_name" required readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-muted">Correo Electrónico Institucional/Personal <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control form-control-lg bg-light" name="requester_email" id="requester_email" required readonly>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label fw-bold text-muted">Vínculo con la Institución <span class="text-danger">*</span></label>
                                    <select class="form-select form-select-lg bg-light" name="institution_link" id="institution_link" required disabled>
                                        <option value="">Seleccione su rol...</option>
                                        <option value="Estudiante">Estudiante</option>
                                        <option value="Docente">Docente</option>
                                        <option value="Administrativo">Administrativo</option>
                                        <option value="Egresado">Egresado</option>
                                        <option value="Externo">Persona Externa</option>
                                    </select>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center bg-light p-4 rounded border">
                                <div>
                                    <strong class="text-dark fs-5">¿Es correcta tu información?</strong>
                                    <div class="text-muted small">Confirma para habilitar el formulario de servicios.</div>
                                </div>
                                <div>
                                    <button type="button" class="btn btn-outline-secondary me-2 px-4 shadow-sm" id="btn-edit-profile">
                                        <i class="fas fa-pencil-alt me-2"></i>Modificar
                                    </button>
                                    <button type="button" class="btn btn-success px-4 shadow-sm fw-bold" id="btn-confirm-profile">
                                        <i class="fas fa-check me-2"></i>Confirmar Datos
                                    </button>
                                </div>
                            </div>
                        </div>
                        @endguest

                        <!-- STEP 3: Topic Selection -->
                        <div id="step3-topic" class="step-section @guest d-none @endguest">
                            <h4 class="text-primary mb-4 border-bottom pb-2">Paso 3: Naturaleza del Servicio</h4>
                            @error('request_type_id')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                            <div class="row g-4 mb-4">
                                <div class="col-md-12">
                                    <label class="form-label fw-bold topic-main-label fs-5 mb-3">Tópico Principal <span class="text-danger">*</span></label>
                                    <p class="text-muted small mb-3">Escoge estratégicamente de la lista para que tu caso sea enrutado al departamento correcto que posee el tiempo de respuesta (SLA) indicado.</p>
                                    <select class="form-select form-select-lg shadow-sm" name="request_type_id" id="request_type_id" required>
                                        <option value="">-- Clic aquí para desplegar las opciones --</option>
                                        @foreach($requestTypes as $type)
                                        <option value="{{ $type->type_id }}"
                                            data-incident-active="{{ $type->incident_active ? '1' : '0' }}"
                                            data-incident-title="{{ $type->incident_title }}"
                                            data-incident-message="{{ $type->incident_message }}">
                                            {{ $type->type_name }} 
                                            @if($type->department) 
                                             --> [Departamento: {{ $type->department->department_name }}]
                                             --> @if($type->sla_hours) [SLA: {{ $type->sla_hours }} Horas] @endif
                                            @endif
                                        </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-12 d-none" id="regional-selection-wrapper">
                                    <label for="institution_id" class="form-label fw-bold">Regional / Sede que atenderá la solicitud <span class="text-danger">*</span></label>
                                    <select class="form-select form-select-lg @error('institution_id') is-invalid @enderror" name="institution_id" id="institution_id">
                                        <option value="">Seleccione una regional</option>
                                    </select>
                                    @error('institution_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted d-block mt-2">Esta selección solo aparece en tópicos que se gestionan por regional.</small>
                                </div>
                            </div>
                        </div>

                        <!-- STEP 4: Details & Captcha -->
                        <div id="step4-details" class="step-section @guest d-none @endguest" @auth style="display:none;" @endauth>
                            <h4 class="text-primary mb-4 border-bottom pb-2">Paso 4: Detalles de Solicitud</h4>
                            
                            <div class="row g-4 mb-4">
                                <div class="col-md-8">
                                    <label for="title" class="form-label fw-bold">Título o Resumen Breve <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-lg @error('title') is-invalid @enderror" 
                                           id="title" name="title" value="{{ old('title') }}" placeholder="Ej: No logro acceder al sistema con mi clave..." required>
                                </div>
                                <div class="col-md-4">
                                    <label for="priority" class="form-label fw-bold">Prioridad Inicial <span class="text-danger">*</span></label>
                                    <select class="form-select form-select-lg @error('priority') is-invalid @enderror" name="priority" id="priority" required>
                                        <option value="1" {{ old('priority', '1') == '1' ? 'selected' : '' }}>Baja (No urgente)</option>
                                        <option value="2" {{ old('priority') == '2' ? 'selected' : '' }}>Media (Importante)</option>
                                        <option value="3" {{ old('priority') == '3' ? 'selected' : '' }}>Alta (Afecta operación)</option>
                                        <option value="4" {{ old('priority') == '4' ? 'selected' : '' }}>Urgente (Suspende operación)</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-4 d-none" id="priority-justification-wrapper">
                                <label for="priority_justification" class="form-label fw-bold">
                                    Justificacion de Prioridad <span class="text-danger">*</span>
                                </label>
                                <textarea
                                    class="form-control @error('priority_justification') is-invalid @enderror"
                                    id="priority_justification"
                                    name="priority_justification"
                                    rows="3"
                                    minlength="20"
                                    maxlength="1000"
                                    placeholder="Explica por que esta solicitud requiere prioridad alta o urgente..."
                                >{{ old('priority_justification') }}</textarea>
                                @error('priority_justification')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted d-block mt-2" id="priority-justification-hint">
                                    Obligatorio para prioridad Alta o Urgente. Minimo 20 caracteres con contenido descriptivo.
                                </small>
                            </div>

                            <div class="mb-4">
                                <label for="description" class="form-label fw-bold">Explicación Total de los Hechos <span class="text-danger">*</span></label>
                                <input type="hidden" id="description" name="description" value="{{ old('description') }}">
                                <div id="description_editor" class="bg-light border rounded @error('description') border-danger @enderror" style="min-height: 180px;"></div>
                                @error('description')
                                    <div class="text-danger small mt-2">{{ $message }}</div>
                                @enderror
                                <small class="text-muted d-block mt-2">Puedes usar formato enriquecido: listas, negrita, cursiva, enlaces, citas y pegar imagenes.</small>
                            </div>

                            <div class="mb-4">
                                <label for="evidence_files" class="form-label fw-bold">Adjuntar Evidencias (Opcional)</label>
                                <input type="file" class="form-control @error('evidence_files') is-invalid @enderror @error('evidence_files.*') is-invalid @enderror"
                                       id="evidence_files" name="evidence_files[]" multiple
                                       accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.zip,.rar">
                                @error('evidence_files')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                @error('evidence_files.*')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted d-block mt-2">Máximo 5 archivos, 2 MB por archivo. Formatos permitidos: PDF, Office, imágenes, TXT y comprimidos.</small>
                            </div>

                            <div class="mb-5">
                                <label for="evidence_drive_link" class="form-label fw-bold">Enlace de Carpeta en Google Drive (Opcional)</label>
                                <input type="url" class="form-control @error('evidence_drive_link') is-invalid @enderror"
                                       id="evidence_drive_link" name="evidence_drive_link"
                                       value="{{ old('evidence_drive_link') }}" placeholder="https://drive.google.com/...">
                                @error('evidence_drive_link')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted d-block mt-2">Si tu organización usa Google Drive, puedes anexar aquí la carpeta de soporte de la solicitud.</small>
                            </div>

                            <div class="card mb-5 border-0 shadow-sm" style="background-color: #f8f9fa;">
                                <div class="card-body p-4">
                                    <h5 class="fw-bold mb-3"><i class="fas fa-shield-alt text-success me-2"></i>Seguridad y Firma</h5>
                                    
                                    <div class="form-check mb-4 mt-3 ms-2">
                                        <input class="form-check-input border-secondary" type="checkbox" name="policy_accepted" id="policy_accepted" required style="transform: scale(1.3); margin-top: 0.3rem;">
                                        <label class="form-check-label fw-bold ms-2" for="policy_accepted">
                                            Acepto la <a href="#" data-bs-toggle="modal" data-bs-target="#policyModal" class="text-decoration-none text-primary">Política de Tratamiento de Datos Personales</a>. <span class="text-danger">*</span>
                                        </label>
                                    </div>
                                    
                                    <hr class="text-muted">
                                    
                                    <div class="row align-items-center mt-4">
                                        <div class="col-md-8">
                                            <label class="form-label mb-2 fw-bold text-dark">Llenar CAPTCHA de Seguridad <span class="text-danger">*</span></label>
                                            <p class="text-muted small mb-2">Para comprobar que eres humano, resuelve esta sencilla operación geométrica o matemática.</p>
                                            
                                            <div class="d-flex align-items-center">
                                                <div id="captcha-question" class="fs-3 fw-bold text-success me-3 user-select-none border rounded px-3 py-2 bg-white shadow-sm" style="letter-spacing: 2px;"></div>
                                                <input type="number" id="captcha_answer" class="form-control form-control-lg text-center fw-bold shadow-sm" style="width: 120px;" placeholder="=" required>
                                            </div>
                                            <div class="invalid-feedback d-none fw-bold" id="captcha-error">Respuesta incorrecta.</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex flex-column flex-md-row justify-content-between pt-4 border-top">
                                <a href="{{ url('/') }}" class="btn btn-outline-secondary btn-lg mb-3 mb-md-0 px-4 fw-bold">
                                    <i class="fas fa-times me-2"></i>Cancelar
                                </a>
                                <div class="d-flex flex-column flex-md-row gap-2">
                                    <button type="button" class="btn btn-light border btn-lg px-4" onclick="location.reload();">
                                        <i class="fas fa-undo me-2"></i>Reiniciar
                                    </button>
                                    <button type="submit" class="btn btn-primary btn-lg px-5 shadow-sm fw-bold" id="btn-submit">
                                        <i class="fas fa-paper-plane me-2"></i>Crear Ticket Formalmente
                                    </button>
                                </div>
                            </div>
                        </div>

                    </form>

                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="topicIncidentModal" tabindex="-1" aria-labelledby="topicIncidentModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-warning">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="topicIncidentModalLabel"><i class="fas fa-triangle-exclamation me-2"></i>Tópico temporalmente bloqueado</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <h6 id="topicIncidentTitle" class="fw-bold"></h6>
                <p id="topicIncidentMessage" class="mb-0"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Entendido</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de servicios pendientes por calificar -->
<div class="modal fade" id="pendingRatingsModal" tabindex="-1" aria-labelledby="pendingRatingsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-warning">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="pendingRatingsModalLabel">
                    <i class="fas fa-star-half-alt me-2"></i>Tienes servicios sin calificar
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3">Antes de crear una nueva solicitud, debes calificar tus servicios completados pendientes.</p>
                <div id="pending-ratings-list" class="list-group mb-3"></div>
                <div class="alert alert-info mb-0">
                    <i class="fas fa-info-circle me-1"></i>
                    Al terminar de calificar, vuelve a validar tu documento para continuar con la nueva solicitud.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Política de Tratamiento de Datos -->
<div class="modal fade" id="policyModal" tabindex="-1" aria-labelledby="policyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary fw-bold text-white">
                <h5 class="modal-title" id="policyModalLabel"><i class="fas fa-file-contract me-2"></i>Tratamiento de Datos</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="text-align: justify; line-height: 1.6;">
                <p>Actuando en mi propio nombre y derecho, y en pleno uso de mis facultades, DECLARO de manera libre, expresa, inequívoca e informada, que AUTORIZO a la Universidad Católica Luis Amigó, con NIT: 890985189, para que, en mi condición de titular de los datos, y en los términos del artículo 9 de la Ley 1581 de 2012, realice la recolección, almacenamiento, uso, circulación, supresión y, en general, el tratamiento de mis datos personales.</p>
                <p>La finalidad de este tratamiento es el envío de información relacionada con las actividades desarrolladas por la Universidad Católica Luis Amigó, noticias, y oferta de sus bienes y servicios, específicamente aquellos vinculados a los programas de pregrado a los que aspiro.</p>
                <p>Declaro que se me ha informado de manera clara y comprensible que tengo los siguientes derechos como titular de los datos personales:</p>
                <ul>
                    <li>Conocer, actualizar y rectificar los datos personales proporcionados.</li>
                    <li>Solicitar prueba de esta autorización.</li>
                    <li>Solicitar información sobre el uso que se le ha dado a mis datos personales.</li>
                    <li>Presentar quejas ante la Superintendencia de Industria y Comercio por el uso indebido de mis datos personales.</li>
                    <li>Revocar esta autorización o solicitar la supresión de los datos personales suministrados.</li>
                    <li>Acceder de forma gratuita a mis datos personales.</li>
                </ul>
                <p class="mb-0">Para ejercer cualquiera de estos derechos, puede dirigir una comunicación escrita al correo electrónico <a href="mailto:protecciondedatos@amigo.edu.co">protecciondedatos@amigo.edu.co</a> o comunicarme al teléfono (604) 448 7666, ext.: 9570. Para mayor detalle sobre la política de tratamiento de datos personales de la universidad, puedo consultar <a href="https://www.ucatolicaluisamigo.edu.co" target="_blank">www.ucatolicaluisamigo.edu.co</a>.</p>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary px-4 fw-bold" data-bs-dismiss="modal">Entendido y Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para mostrar ticket creado -->
@if(session('new_ticket'))
<div class="modal fade" id="ticketCreatedModal" tabindex="-1" aria-labelledby="ticketCreatedModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="ticketCreatedModalLabel">
                    <i class="fas fa-check-circle me-2"></i>Solicitud Creada Exitosamente
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <i class="fas fa-ticket-alt fa-3x text-success mb-3"></i>
                    <h4>Número de Ticket: <strong>#{{ session('new_ticket')->ticket_number }}</strong></h4>
                </div>
                <div class="row">
                    <div class="col-sm-6">
                        <strong>Título:</strong><br>
                        {{ session('new_ticket')->title }}
                    </div>
                    <div class="col-sm-6">
                        <strong>Tipo de Solicitud:</strong><br>
                        {{ session('new_ticket')->requestType ? session('new_ticket')->requestType->type_name : 'N/A' }}
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-sm-6">
                        <strong>Fecha de Creación:</strong><br>
                        {{ session('new_ticket')->created_at->format('d/m/Y H:i') }}
                    </div>
                    <div class="col-sm-6">
                        <strong>Estado:</strong><br>
                        <span class="badge bg-warning">Pendiente</span>
                    </div>
                </div>
                <div class="alert alert-info mt-3">
                    <i class="fas fa-info-circle me-2"></i>
                    Se ha enviado un correo electrónico con los detalles de tu solicitud. Puedes hacer seguimiento de tu ticket usando el número proporcionado.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <a href="{{ route('service-management.track') }}?ticket_number={{ session('new_ticket')->ticket_number }}" class="btn btn-primary">
                    <i class="fas fa-search me-1"></i>Ver Estado del Ticket
                </a>
            </div>
        </div>
    </div>
</div>
@endif

@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.snow.css" rel="stylesheet">
<style>
@media (max-width: 991.98px) {
    #serviceRequestForm .form-control-lg,
    #serviceRequestForm .form-select-lg,
    #serviceRequestForm .btn-lg {
        font-size: 1rem;
    }

    #serviceRequestForm .step-section h4 {
        font-size: 1.15rem;
    }

    #serviceRequestForm .card-body {
        padding: 1.25rem !important;
    }

    #serviceRequestForm .d-flex.justify-content-between.align-items-center.bg-light {
        flex-direction: column;
        align-items: stretch !important;
        gap: 0.75rem;
    }

    #serviceRequestForm .d-flex.justify-content-between.align-items-center.bg-light > div:last-child {
        display: grid;
        grid-template-columns: 1fr;
        gap: 0.5rem;
    }

    #serviceRequestForm #captcha-question {
        font-size: 1.5rem !important;
        margin-right: 0.75rem !important;
    }

    #serviceRequestForm #captcha_answer {
        width: 100% !important;
        max-width: 150px;
    }
}

@media (max-width: 575.98px) {
    #serviceRequestForm .btn,
    #serviceRequestForm .btn-lg {
        width: 100%;
    }

    #serviceRequestForm .d-flex.align-items-center {
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    #serviceRequestForm .d-flex.flex-column.flex-md-row.justify-content-between {
        gap: 0.5rem;
    }

    #serviceRequestForm .d-flex.flex-column.flex-md-row.gap-2 {
        width: 100%;
    }
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.min.js"></script>
<script>
$(document).ready(function() {
    const topicRegionalConfig = @json($requestTypeRegionalMap ?? []);

    const quill = new Quill('#description_editor', {
        theme: 'snow',
        placeholder: 'Aporta todo el contexto posible (Nombres exactos, Cursos, Horas exactas) para facilitarle el trabajo a nuestros analistas...',
        modules: {
            toolbar: [
                [{ header: [3, 4, false] }],
                ['bold', 'italic', 'underline'],
                [{ list: 'ordered' }, { list: 'bullet' }],
                ['blockquote', 'link'],
                ['clean']
            ]
        }
    });

    const initialDescription = $('#description').val();
    if (initialDescription) {
        quill.clipboard.dangerouslyPasteHTML(initialDescription);
    }
    
    // Configuración CSRF
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Mostrar modal si hay un ticket recién creado
    @if(session('new_ticket'))
    $('#ticketCreatedModal').modal('show');
    @endif

    @guest
    function buildPendingRatingsList(documentNumber, pendingTickets) {
        const list = $('#pending-ratings-list');
        list.empty();

        pendingTickets.forEach((ticket) => {
            const ticketNumber = ticket.ticket_number;
            const title = ticket.title || 'Ticket sin título';
            const trackUrl = `{{ route('service-management.track') }}?ticket_number=${encodeURIComponent(ticketNumber)}&document_number=${encodeURIComponent(documentNumber)}`;

            list.append(`
                <div class="list-group-item d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2">
                    <div>
                        <strong>#${ticketNumber}</strong>
                        <div class="text-muted small">${title}</div>
                    </div>
                    <a href="${trackUrl}" class="btn btn-sm btn-warning text-dark fw-bold">
                        <i class="fas fa-star me-1"></i>Calificar ahora
                    </a>
                </div>
            `);
        });
    }

    // Wizard Progress logic
    function updateProgress(percent) {
        $('#wizard-progress').css('width', percent + '%');
    }

    // Paso 1: Buscar documento
    $('#btn-verify-doc').click(function() {
        const docInput = $('#document_number_search');
        const docVal = docInput.val().trim();
        const errorDiv = $('#doc-error');
        const spinner = $('#doc-spinner');
        
        if(docVal === '') {
            errorDiv.removeClass('d-none');
            return;
        }
        
        errorDiv.addClass('d-none');
        spinner.removeClass('d-none');
        $(this).prop('disabled', true);
        
        $.ajax({
            url: '{{ route("service-management.checkRequester") }}',
            type: 'POST',
            data: { document_number: docVal },
            success: function(res) {
                $('#document_number_final').val(docVal);

                if (res.has_pending_ratings) {
                    buildPendingRatingsList(docVal, res.pending_tickets || []);
                    $('#pendingRatingsModal').modal('show');
                    return;
                }
                
                if(res.exists) {
                    // Populate
                    $('#requester_name').val(res.user.user_name);
                    $('#requester_email').val(res.user.user_email);
                    $('#institution_link').val(res.user.institution_link);
                    
                    $('#profile-found-msg').removeClass('d-none');
                    $('#profile-new-msg').addClass('d-none');
                } else {
                    $('#profile-new-msg').removeClass('d-none');
                    $('#profile-found-msg').addClass('d-none');
                    // Enable edition right away
                    enableProfileEdition();
                }
                
                // Show Step 2
                $('#step1-identity').slideUp();
                $('#step2-profile').hide().removeClass('d-none').slideDown();
                updateProgress(30);
            },
            complete: function() {
                spinner.addClass('d-none');
                $('#btn-verify-doc').prop('disabled', false);
            }
        });
    });
    
    // Press Enter to verify
    $('#document_number_search').keypress(function(e) {
        if(e.which == 13) {
            e.preventDefault();
            $('#btn-verify-doc').click();
        }
    });

    function enableProfileEdition() {
        $('#requester_name, #requester_email').prop('readonly', false).removeClass('bg-light');
        $('#institution_link').prop('disabled', false).removeClass('bg-light');
    }

    function lockProfileEdition() {
        $('#requester_name, #requester_email').prop('readonly', true).addClass('bg-light');
        $('#institution_link').prop('disabled', true).addClass('bg-light');
    }

    function hasValidEmailStructure(email) {
        return /^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(email);
    }

    $('#btn-edit-profile').click(function() {
        enableProfileEdition();
        $('#requester_name').focus();
    });

    $('#btn-confirm-profile').click(function() {
        // Validate inputs
        if(!$('#requester_name').val() || !$('#requester_email').val() || !$('#institution_link').val()) {
            VirtualCenter.showAlert('Debes completar Nombre, Email y Vínculo Institucional', 'warning');
            return;
        }

        const requesterEmail = $('#requester_email').val().trim();
        if(!hasValidEmailStructure(requesterEmail)) {
            VirtualCenter.showAlert('Debes ingresar un correo válido', 'warning');
            $('#requester_email').focus();
            return;
        }
        
        lockProfileEdition();
        $(this).html('<i class="fas fa-check-double me-2"></i>Confirmado').removeClass('btn-success').addClass('btn-secondary');
        $('#btn-edit-profile').hide();
        
        // Show Step 3
        $('#step3-topic').hide().removeClass('d-none').slideDown();
        updateProgress(60);
    });
    @else
        // For authenticated users, jump directly to step 3 topic selection
        @if(Auth::check())
            $('#step4-details').hide();
            $('#request_type_id').val('');
        @endif
    @endguest

    // Paso 3 al 4
    function updatePriorityJustificationVisibility() {
        const priorityValue = parseInt($('#priority').val() || '0', 10);
        const shouldRequire = priorityValue === 3 || priorityValue === 4;
        const wrapper = $('#priority-justification-wrapper');
        const textarea = $('#priority_justification');
        const hint = $('#priority-justification-hint');

        wrapper.toggleClass('d-none', !shouldRequire);
        textarea.prop('required', shouldRequire);

        if (shouldRequire) {
            const label = priorityValue === 4 ? 'Urgente' : 'Alta';
            hint.text(`Obligatorio para prioridad ${label}. Minimo 20 caracteres con contenido descriptivo.`);
        }
    }

    $('#priority').on('change', function() {
        updatePriorityJustificationVisibility();
    });

    updatePriorityJustificationVisibility();

    function updateRegionalSelectorByTopic(topicId) {
        const institutions = topicRegionalConfig[topicId] || [];
        const regionalWrapper = $('#regional-selection-wrapper');
        const regionalSelect = $('#institution_id');

        regionalSelect.empty().append('<option value="">Seleccione una regional</option>');

        if (institutions.length === 0) {
            regionalWrapper.addClass('d-none');
            regionalSelect.prop('required', false);
            return false;
        }

        institutions.forEach((item) => {
            regionalSelect.append(
                $('<option></option>').val(item.institution_id).text(item.institution_name)
            );
        });

        regionalWrapper.removeClass('d-none');
        regionalSelect.prop('required', true);

        const oldValue = '{{ old('institution_id') }}';
        if (oldValue) {
            regionalSelect.val(oldValue);
        }

        return true;
    }

    $('#request_type_id').change(function() {
        const val = $(this).val();
        if(val !== '') {
            const selected = $(this).find('option:selected');
            if (selected.data('incident-active') === 1 || selected.data('incident-active') === '1') {
                $('#topicIncidentTitle').text(selected.data('incident-title') || 'Tópico temporalmente bloqueado');
                $('#topicIncidentMessage').text(selected.data('incident-message') || 'Este tópico no está disponible mientras se resuelve una incidencia.');
                $('#request_type_id').val('');
                $('#step4-details').slideUp();
                bootstrap.Modal.getOrCreateInstance(document.getElementById('topicIncidentModal')).show();
                return;
            }
            updateRegionalSelectorByTopic(val);
            $('#step4-details').hide().removeClass('d-none').slideDown();
            @guest updateProgress(100); @endguest
            generateCaptcha();
        } else {
            $('#regional-selection-wrapper').addClass('d-none');
            $('#institution_id').prop('required', false).val('');
            $('#step4-details').slideUp();
            @guest updateProgress(60); @endguest
        }
    });

    const initialTopic = $('#request_type_id').val();
    if (initialTopic) {
        updateRegionalSelectorByTopic(initialTopic);
        $('#step4-details').removeClass('d-none').show();
    }

    // CAPTCHA Logic
    let currentCaptchaAnswer = 0;
    
    function generateCaptcha() {
        const num1 = Math.floor(Math.random() * 10) + 1;
        const num2 = Math.floor(Math.random() * 10) + 1;
        currentCaptchaAnswer = num1 + num2;
        $('#captcha-question').text(num1 + ' + ' + num2);
        $('#captcha_answer').val('');
        $('#captcha-error').addClass('d-none').removeClass('d-block');
        $('#captcha_answer').removeClass('is-invalid');
    }

    // Submit Valdation
    $('#serviceRequestForm').on('submit', function(e) {
        const descriptionHtml = quill.root.innerHTML;
        const descriptionText = quill.getText().trim();
        $('#description').val(descriptionHtml);

        if (!descriptionText) {
            e.preventDefault();
            VirtualCenter.showAlert('Debes ingresar la explicación de la solicitud', 'danger');
            return false;
        }

        const priorityValue = parseInt($('#priority').val() || '0', 10);
        if (priorityValue === 3 || priorityValue === 4) {
            const justification = ($('#priority_justification').val() || '').trim();
            const meaningfulChars = (justification.match(/[A-Za-z0-9ÁÉÍÓÚÜÑáéíóúüñ]/g) || []).length;

            if (justification.length < 20 || meaningfulChars < 12) {
                e.preventDefault();
                VirtualCenter.showAlert('Debes justificar la prioridad alta/urgente con al menos 20 caracteres de contenido descriptivo.', 'danger');
                $('#priority_justification').focus();
                return false;
            }
        }
        
        // Check array disabled elements workaround
        @guest
        $('#institution_link').prop('disabled', false); // re-enable to submit value

        const requesterEmail = $('#requester_email').val().trim();
        if(!hasValidEmailStructure(requesterEmail)) {
            e.preventDefault();
            VirtualCenter.showAlert('Debes ingresar un correo válido', 'danger');
            $('#requester_email').focus();
            return false;
        }
        @endguest

        // Captcha validation
        const userAnswer = parseInt($('#captcha_answer').val());
        if(userAnswer !== currentCaptchaAnswer) {
            e.preventDefault();
            $('#captcha_answer').addClass('is-invalid');
            $('#captcha-error').addClass('d-block').removeClass('d-none');
            VirtualCenter.showAlert('El número del CAPTCHA no es válido', 'danger');
            generateCaptcha();
            return false;
        }

        // Show loading
        const submitBtn = $('#btn-submit');
        VirtualCenter.showLoading(submitBtn);
    });

});
</script>
@endpush
