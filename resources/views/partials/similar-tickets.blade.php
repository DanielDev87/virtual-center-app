@php
    $similarTickets = in_array((int) $ticket->status, [1, 2, 5], true)
        ? app(\App\Services\TicketSuggestionService::class)->suggest(
            (int) $ticket->request_type_id,
            (string) $ticket->title,
            mb_substr(strip_tags((string) $ticket->requester_info), 0, 600),
            auth()->user(),
            (int) $ticket->ticket_id
        )
        : [];
@endphp
@if(!empty($similarTickets))
<div class="mt-3 mb-3">
    <h6 class="fw-bold mb-2">
        <i class="fas fa-clone me-1"></i>Solicitudes posiblemente relacionadas
    </h6>
    <ul class="small mb-1">
        @foreach($similarTickets as $similar)
            <li>
                @if($similar['accessible'] && $similar['url'])
                    <a href="{{ $similar['url'] }}">#{{ $similar['ticket_number'] }} - {{ $similar['title'] }}</a>
                    <span class="text-muted">({{ $similar['status'] }}, {{ $similar['created_at'] }})</span>
                @else
                    Solicitud abierta similar
                    <span class="text-muted">({{ $similar['status'] }}, {{ $similar['created_at'] }}). No tienes permiso para abrirla.</span>
                @endif
            </li>
        @endforeach
    </ul>
    <div class="small text-muted">
        Son solo sugerencias: el sistema no combina ni cierra tickets. Si corresponde, usa la opción de asociación de este ticket.
    </div>
</div>
@endif