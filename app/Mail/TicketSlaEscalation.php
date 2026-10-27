<?php

namespace App\Mail;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TicketSlaEscalation extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Ticket $ticket, public array $info, public int $thresholdPercent)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Escalamiento: ticket próximo a vencer su SLA #' . $this->ticket->ticket_number,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.ticket_sla_escalation');
    }

    public function attachments(): array
    {
        return [];
    }
}