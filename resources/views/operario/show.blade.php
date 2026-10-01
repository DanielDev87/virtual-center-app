@extends('layouts.app')

@section('title', 'Ticket Operario')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <a href="{{ route('operario.dashboard') }}" class="btn btn-outline-secondary btn-sm">← Volver</a>
        </div>
        <div class="fw-bold">#{{ $ticket->ticket_number }}</div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                <div>
                    <h3 class="mb-1">{{ $ticket->title }}</h3>
                    <div class="text-muted small">
                        {{ $ticket->requestType?->type_name ?? 'Sin tópico' }} · Solicitante: {{ $ticket->requester?->user_name ?? 'No disponible' }}
                    </div>
                </div>
                <span class="badge @if($ticket->status == 1) bg-warning text-dark @elseif($ticket->status == 2) bg-primary @else bg-success @endif">
                    @switch($ticket->status)
                        @case(1) Pendiente @break
                        @case(2) En proceso @break
                        @case(5) Realizado para auditoría @break
                        @default Sin estado @break
                    @endswitch
                </span>
            </div>

            <div class="mb-3">
                <h6 class="text-uppercase text-muted small">Descripción</h6>
                <div class="border rounded p-3 bg-light">
                    {!! $ticket->requester_info ?: '<em>Sin descripción adicional.</em>' !!}
                </div>
            </div>

            @if($canModify)
            <form method="POST" action="{{ route('operario.tickets.status', $ticket->ticket_id) }}" class="mb-4">
                @csrf
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">Actualizar estado</label>
                        <select name="status" class="form-select">
                            <option value="1" {{ $ticket->status == 1 ? 'selected' : '' }}>Pendiente</option>
                            <option value="2" {{ $ticket->status == 2 ? 'selected' : '' }}>En proceso</option>
                            <option value="5" {{ $ticket->status == 5 ? 'selected' : '' }}>Realizado para auditoría</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Nota</label>
                        <input type="text" name="note" class="form-control" placeholder="Ej.: revisé el equipo y quedó en proceso..." maxlength="1000">
                    </div>
                    <div class="col-md-2 d-grid">
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </div>
            </form>

            @if(!in_array((int) $ticket->status, [3, 4], true))
                <form method="POST" action="{{ route('operario.tickets.return', $ticket->ticket_id) }}" id="returnTicketForm" class="border-top pt-3">
                    @csrf
                    <label for="return_reason" class="form-label">Devolver ticket a la cola</label>
                    <div class="row g-2 align-items-start">
                        <div class="col-md-9">
                            <textarea id="return_reason" name="return_reason" class="form-control" rows="2" minlength="10" maxlength="1000" required placeholder="Explica por qué no puedes atender este ticket..."></textarea>
                            @error('return_reason')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3 d-grid">
                            <button type="button" id="openReturnTicketModal" class="btn btn-outline-danger">
                                <i class="fas fa-undo me-1"></i>Devolver ticket
                            </button>
                        </div>
                    </div>
                </form>
                <div class="modal fade" id="returnTicketModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Confirmar devolución</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                            </div>
                            <div class="modal-body">
                                El ticket regresará a la lista de no asignados y desaparecerá de tu panel. ¿Deseas continuar?
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                <button type="button" id="confirmReturnTicket" class="btn btn-danger">Sí, devolver ticket</button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            @else
                <div class="alert alert-info mb-4">
                    <i class="fas fa-eye me-1"></i>
                    Modo consulta: trabajas en equipo con el Operario responsable
                    @if($ticket->mediator)
                        <strong>{{ $ticket->mediator->user_name }}</strong>
                    @endif
                    @if($ticket->assignments->where('status', 'active')->contains(fn ($assignment) => $assignment->mediator?->role?->role_name === 'Contributor'))
                        o un Contributor. Solo el responsable autorizado puede actualizar el ticket.
                    @else
                        .
                    @endif
                </div>
            @endif
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <h5 class="mb-3">Evidencias</h5>

            @if($canModify)
            <form method="POST" action="{{ route('operario.tickets.evidence', $ticket->ticket_id) }}" enctype="multipart/form-data" class="mb-3">
                @csrf
                <div class="row g-3 align-items-end">
                    <div class="col-md-7">
                        <label class="form-label">Adjuntar fotos o archivos</label>
                        <input type="file" name="evidence_files[]" class="form-control" multiple accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.zip,.rar,.webp">
                        <small class="text-muted">Máximo 5 archivos de 2 MB cada uno.</small>
                    </div>
                    <div class="col-md-3">
                        <input type="text" name="note" class="form-control" placeholder="Descripción de la evidencia" maxlength="1000">
                    </div>
                    <div class="col-md-2 d-grid">
                        <button type="submit" class="btn btn-success">Adjuntar</button>
                    </div>
                </div>
            </form>
            @else
                <div class="alert alert-light border small">Las evidencias están disponibles en modo consulta.</div>
            @endif

            @if($ticket->evidences->isEmpty())
                <div class="text-muted">Todavía no hay evidencias adjuntas.</div>
            @else
                <div class="list-group">
                    @foreach($ticket->evidences as $evidence)
                        <a href="{{ route('evidences.view', $evidence->evidence_id) }}" class="list-group-item list-group-item-action" target="_blank">
                            {{ $evidence->file_name }}
                            <span class="float-end text-muted small">{{ $evidence->file_size ? round($evidence->file_size / 1024, 1) . ' KB' : 'N/A' }}</span>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modalElement = document.getElementById('returnTicketModal');
        const openButton = document.getElementById('openReturnTicketModal');
        const confirmButton = document.getElementById('confirmReturnTicket');
        const returnForm = document.getElementById('returnTicketForm');

        if (!modalElement || !openButton || !confirmButton || !returnForm || typeof bootstrap === 'undefined') {
            return;
        }

        document.body.appendChild(modalElement);
        const returnModal = bootstrap.Modal.getOrCreateInstance(modalElement, {
            backdrop: 'static',
            keyboard: false,
        });

        openButton.addEventListener('click', function () {
            returnModal.show();
        });

        confirmButton.addEventListener('click', function () {
            if (returnForm.reportValidity()) {
                returnForm.submit();
            }
        });
    });
</script>
@endpush
@endsection
