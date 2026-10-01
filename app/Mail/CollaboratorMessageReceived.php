<?php

namespace App\Mail;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CollaboratorMessageReceived extends Mailable
{
    use Queueable, SerializesModels;

    public $ticket;
    public $messageBody;
    public $senderName;

    /**
     * Crear una nueva instancia del mensaje.
     */
    public function __construct(Ticket $ticket, string $messageBody, string $senderName)
    {
        $this->ticket = $ticket;
        $this->messageBody = $messageBody;
        $this->senderName = $senderName;
    }

    /**
     * Obtener el sobre del mensaje.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nuevo mensaje del colaborador - Ticket #' . $this->ticket->ticket_number,
        );
    }

    /**
     * Obtener la definicion del contenido del mensaje.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.collaborator_message_received',
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
