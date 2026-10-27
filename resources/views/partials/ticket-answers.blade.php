@php
    $ticketAnswers = \Illuminate\Support\Facades\Schema::hasTable('ticket_answers')
        ? \App\Models\TicketAnswer::where('ticket_id', $ticket->ticket_id)->orderBy('answer_id')->get()
        : collect();
@endphp
@if($ticketAnswers->isNotEmpty())
<div class="mt-3 mb-3">
    <h6 class="fw-bold mb-2">
        <i class="fas fa-clipboard-list me-1"></i>Información adicional del formulario
    </h6>
    <dl class="row mb-0 small">
        @foreach($ticketAnswers as $answer)
            <dt class="col-md-4">{{ $answer->question_label }}</dt>
            <dd class="col-md-8">{{ $answer->answer_value }}</dd>
        @endforeach
    </dl>
</div>
@endif