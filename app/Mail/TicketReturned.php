<?php

namespace App\Mail;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TicketReturned extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Ticket $ticket,
        public string $reason,
        public User $assigner
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Ticket devuelto - #' . $this->ticket->ticket_number,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.ticket_returned');
    }

    public function attachments(): array
    {
        return [];
    }
}