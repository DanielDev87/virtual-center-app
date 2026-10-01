<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Mail\TicketClosed;
use App\Models\Ticket;

class TicketClosedMailableTest extends TestCase
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
        $mailable = new TicketClosed($this->makeTicket('TKT-2024-888'));
        $envelope = $mailable->envelope();

        $this->assertStringContainsString('TKT-2024-888', $envelope->subject);
    }

    /** @test */
    public function envelope_subject_contains_servicio_finalizado(): void
    {
        $mailable = new TicketClosed($this->makeTicket('TKT-999'));
        $envelope = $mailable->envelope();

        $this->assertStringContainsString('Servicio Finalizado', $envelope->subject);
    }

    /** @test */
    public function content_uses_ticket_closed_view(): void
    {
        $mailable = new TicketClosed($this->makeTicket());
        $content = $mailable->content();

        $this->assertSame('emails.ticket_closed', $content->view);
    }

    /** @test */
    public function attachments_are_empty(): void
    {
        $mailable = new TicketClosed($this->makeTicket());

        $this->assertSame([], $mailable->attachments());
    }

    /** @test */
    public function ticket_property_is_set_on_mailable(): void
    {
        $ticket = $this->makeTicket('TKT-456');
        $mailable = new TicketClosed($ticket);

        $this->assertSame($ticket, $mailable->ticket);
    }
}
