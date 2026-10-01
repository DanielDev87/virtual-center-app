<?php

namespace Tests\Unit;

use App\Models\Ticket;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class TicketModelTest extends TestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    /** @test */
    public function priority_sla_hours_returns_null_when_priority_is_missing()
    {
        $ticket = new Ticket();

        $this->assertNull($ticket->priority_sla_hours);
    }

    /** @test */
    public function priority_sla_hours_uses_configured_map_for_known_priorities()
    {
        $this->assertSame(72, (new Ticket(['priority' => 1]))->priority_sla_hours);
        $this->assertSame(48, (new Ticket(['priority' => 2]))->priority_sla_hours);
        $this->assertSame(24, (new Ticket(['priority' => 3]))->priority_sla_hours);
        $this->assertSame(8, (new Ticket(['priority' => 4]))->priority_sla_hours);
    }

    /** @test */
    public function priority_sla_hours_respects_runtime_config_override()
    {
        Config::set('sla.priority_hours', [
            1 => 100,
            2 => 50,
            3 => 25,
            4 => 10,
        ]);

        $this->assertSame(100, (new Ticket(['priority' => 1]))->priority_sla_hours);
        $this->assertSame(10, (new Ticket(['priority' => 4]))->priority_sla_hours);
    }

    /** @test */
    public function priority_sla_hours_returns_null_for_unknown_priority()
    {
        $ticket = new Ticket(['priority' => 99]);

        $this->assertNull($ticket->priority_sla_hours);
    }

    /** @test */
    public function response_deadline_is_null_without_created_at_or_without_priority()
    {
        $withoutCreatedAt = new Ticket(['priority' => 2]);
        $withoutPriority = new Ticket();
        $withoutPriority->created_at = Carbon::parse('2026-01-01 08:00:00');

        $this->assertNull($withoutCreatedAt->response_deadline);
        $this->assertNull($withoutPriority->response_deadline);
    }

    /** @test */
    public function response_deadline_is_calculated_from_created_at_plus_sla_hours()
    {
        $ticket = new Ticket(['priority' => 3]);
        $ticket->created_at = Carbon::parse('2026-01-01 08:00:00');

        $this->assertSame('2026-01-02 08:00:00', $ticket->response_deadline->format('Y-m-d H:i:s'));
    }

    /** @test */
    public function remaining_response_hours_is_rounded_up_for_fractional_hours()
    {
        Carbon::setTestNow(Carbon::parse('2026-01-01 09:31:00'));

        $ticket = new Ticket(['priority' => 2]); // 48h
        $ticket->created_at = Carbon::parse('2026-01-01 08:00:00');

        // Deadline: 2026-01-03 08:00:00; remaining ~= 46h 29m => ceil => 47
        $this->assertSame(47, $ticket->remaining_response_hours);
    }

    /** @test */
    public function remaining_response_hours_can_be_negative_when_ticket_is_overdue()
    {
        Carbon::setTestNow(Carbon::parse('2026-01-04 12:00:00'));

        $ticket = new Ticket(['priority' => 1]); // 72h
        $ticket->created_at = Carbon::parse('2026-01-01 08:00:00');

        $this->assertLessThan(0, $ticket->remaining_response_hours);
    }

    /** @test */
    public function remaining_response_hours_is_null_when_created_at_is_missing()
    {
        $ticket = new Ticket(['priority' => 1]);

        $this->assertNull($ticket->remaining_response_hours);
    }

    /** @test */
    public function response_deadline_is_null_when_priority_is_not_mapped_in_partial_config()
    {
        Config::set('sla.priority_hours', [1 => 12]);

        $ticket = new Ticket(['priority' => 2]);
        $ticket->created_at = Carbon::parse('2026-01-01 08:00:00');

        $this->assertNull($ticket->response_deadline);
    }

    /** @test */
    public function is_response_overdue_is_true_for_open_ticket_past_deadline()
    {
        Carbon::setTestNow(Carbon::parse('2026-01-05 08:00:01'));

        $ticket = new Ticket(['priority' => 1, 'status' => 2]);
        $ticket->created_at = Carbon::parse('2026-01-01 08:00:00');

        $this->assertTrue($ticket->is_response_overdue);
    }

    /** @test */
    public function is_response_overdue_is_false_for_completed_or_cancelled_tickets()
    {
        Carbon::setTestNow(Carbon::parse('2026-01-05 08:00:01'));

        $completed = new Ticket(['priority' => 1, 'status' => 3]);
        $completed->created_at = Carbon::parse('2026-01-01 08:00:00');

        $cancelled = new Ticket(['priority' => 1, 'status' => 4]);
        $cancelled->created_at = Carbon::parse('2026-01-01 08:00:00');

        $this->assertFalse($completed->is_response_overdue);
        $this->assertFalse($cancelled->is_response_overdue);
    }

    /** @test */
    public function is_response_overdue_is_false_at_exact_deadline_boundary()
    {
        $ticket = new Ticket(['priority' => 2, 'status' => 2]); // 48h
        $ticket->created_at = Carbon::parse('2026-01-01 08:00:00');

        Carbon::setTestNow(Carbon::parse('2026-01-03 08:00:00'));

        $this->assertFalse($ticket->is_response_overdue);
    }

    /** @test */
    public function is_response_overdue_is_false_when_deadline_cannot_be_calculated()
    {
        $ticket = new Ticket(['status' => 1]);

        $this->assertFalse($ticket->is_response_overdue);
    }
}
