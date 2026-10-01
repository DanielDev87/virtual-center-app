<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Mail\ServiceRated;
use App\Models\Ticket;

class ServiceRatedMailableTest extends TestCase
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
        $mailable = new ServiceRated($this->makeTicket('TKT-2024-777'));
        $envelope = $mailable->envelope();

        $this->assertStringContainsString('TKT-2024-777', $envelope->subject);
    }

    /** @test */
    public function envelope_subject_contains_calificacion_de_servicio(): void
    {
        $mailable = new ServiceRated($this->makeTicket('TKT-999'));
        $envelope = $mailable->envelope();

        $this->assertStringContainsString('Calificación de Servicio', $envelope->subject);
    }

    /** @test */
    public function content_uses_service_rated_view(): void
    {
        $mailable = new ServiceRated($this->makeTicket());
        $content = $mailable->content();

        $this->assertSame('emails.service_rated', $content->view);
    }

    /** @test */
    public function attachments_are_empty(): void
    {
        $mailable = new ServiceRated($this->makeTicket());

        $this->assertSame([], $mailable->attachments());
    }

    /** @test */
    public function ticket_property_is_set_on_mailable(): void
    {
        $ticket = $this->makeTicket('TKT-789');
        $mailable = new ServiceRated($ticket);

        $this->assertSame($ticket, $mailable->ticket);
    }
}
