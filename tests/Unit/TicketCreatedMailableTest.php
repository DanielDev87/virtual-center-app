<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Mail\TicketCreated;
use App\Models\Ticket;

class TicketCreatedMailableTest extends TestCase
{
    private function makeTicket(string $ticketNumber = 'TKT-001'): Ticket
    {
        $ticket = new Ticket();
        $ticket->ticket_number = $ticketNumber;
        return $ticket;
    }

    /** @test */
    public function envelope_subject_contains_ticket_number(): void
    {
        $mailable = new TicketCreated($this->makeTicket('TKT-2024-001'));
        $envelope = $mailable->envelope();

        $this->assertStringContainsString('TKT-2024-001', $envelope->subject);
    }

    /** @test */
    public function envelope_subject_contains_solicitud_recibida(): void
    {
        $mailable = new TicketCreated($this->makeTicket('TKT-999'));
        $envelope = $mailable->envelope();

        $this->assertStringContainsString('Solicitud Recibida', $envelope->subject);
    }

    /** @test */
    public function content_uses_ticket_created_view(): void
    {
        $mailable = new TicketCreated($this->makeTicket());
        $content = $mailable->content();

        $this->assertSame('emails.ticket_created', $content->view);
    }

    /** @test */
    public function attachments_are_empty(): void
    {
        $mailable = new TicketCreated($this->makeTicket());

        $this->assertSame([], $mailable->attachments());
    }

    /** @test */
    public function ticket_property_is_set_on_mailable(): void
    {
        $ticket = $this->makeTicket('TKT-123');
        $mailable = new TicketCreated($ticket);

        $this->assertSame($ticket, $mailable->ticket);
    }
}
