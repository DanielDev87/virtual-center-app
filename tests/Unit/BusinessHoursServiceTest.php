<?php

namespace Tests\Unit;

use App\Services\BusinessHoursService;
use Carbon\Carbon;
use Tests\TestCase;

class BusinessHoursServiceTest extends TestCase
{
    /** @test */
    public function it_pauses_sla_over_the_weekend()
    {
        $service = new BusinessHoursService();
        $deadline = $service->addMinutes(Carbon::parse('2026-09-04 16:00:00'), 120);

        $this->assertSame('2026-09-07 08:00:00', $deadline->format('Y-m-d H:i:s'));
    }

    /** @test */
    public function it_moves_tickets_created_after_hours_to_next_business_start()
    {
        $service = new BusinessHoursService();
        $deadline = $service->addMinutes(Carbon::parse('2026-09-07 18:30:00'), 60);

        $this->assertSame('2026-09-08 08:00:00', $deadline->format('Y-m-d H:i:s'));
    }

    /** @test */
    public function it_counts_only_business_minutes_between_dates()
    {
        $service = new BusinessHoursService();
        $minutes = $service->businessMinutesBetween(
            Carbon::parse('2026-09-04 16:00:00'),
            Carbon::parse('2026-09-07 08:00:00')
        );

        $this->assertSame(120, $minutes);
    }
}
