<?php

namespace App\Mail;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RequesterMessageReceived extends Mailable
{
    use Queueable, SerializesModels;

    public $ticket;
    public $messageBody;

    /**
     * Crear una nueva instancia del mensaje.
     */
    public function __construct(Ticket $ticket, string $messageBody)
    {
        $this->ticket = $ticket;
        $this->messageBody = $messageBody;
    }

    /**
     * Obtener el sobre del mensaje.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nuevo mensaje del solicitante - Ticket #' . $this->ticket->ticket_number,
        );
    }

    /**
     * Obtener la definicion del contenido del mensaje.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.requester_message_received',
        );
    }

    /**
     * Obtener los adjuntos del mensaje.
     */
    public function attachments(): array
    {
        return [];
    }
}
