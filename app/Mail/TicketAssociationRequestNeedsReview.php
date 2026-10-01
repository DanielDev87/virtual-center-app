<?php

namespace App\Mail;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TicketAssociationRequestNeedsReview extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Ticket $parentTicket,
        public string $reason
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Revisa tu solicitud de asociación - Ticket #' . $this->parentTicket->ticket_number,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.ticket_association_needs_review');
    }

    public function attachments(): array
    {
        return [];
    }
}
