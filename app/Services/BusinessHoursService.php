<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Holiday;

class BusinessHoursService
{
    private array $holidayCache = [];

    public function isWithinBusinessHours(?Carbon $date = null): bool
    {
        $current = ($date ?: now())->copy();

        return $this->isBusinessDay($current)
            && $current->hour >= $this->startHour()
            && $current->hour < $this->endHour();
    }

    public function nextBusinessStart(?Carbon $date = null): Carbon
    {
        return $this->moveToBusinessTime(($date ?: now())->copy());
    }

    public function addMinutes(Carbon $start, int $minutes): Carbon
    {
        $current = $start->copy();
        $remaining = max(0, $minutes);

        while ($remaining > 0) {
            $current = $this->moveToBusinessTime($current);
            $endOfDay = $current->copy()->setTime($this->endHour(), 0, 0);
            $availableToday = $current->diffInMinutes($endOfDay);

            if ($remaining <= $availableToday) {
                return $current->addMinutes($remaining);
            }

            $remaining -= $availableToday;
            $current = $this->nextBusinessDay($current)->setTime($this->startHour(), 0, 0);
        }

        return $this->moveToBusinessTime($current);
    }

    public function businessMinutesBetween(Carbon $from, Carbon $to): int
    {
        if ($from->equalTo($to)) {
            return 0;
        }

        $sign = $from->lessThan($to) ? 1 : -1;
        $start = $sign === 1 ? $from->copy() : $to->copy();
        $end = $sign === 1 ? $to->copy() : $from->copy();
        $minutes = 0;
        $cursor = $start->copy();

        while ($cursor->lt($end)) {
            if ($this->isBusinessDay($cursor)) {
                $dayStart = $cursor->copy()->setTime($this->startHour(), 0, 0);
                $dayEnd = $cursor->copy()->setTime($this->endHour(), 0, 0);
                $segmentStart = $cursor->greaterThan($dayStart) ? $cursor : $dayStart;
                $segmentEnd = $end->lessThan($dayEnd) ? $end : $dayEnd;

                if ($segmentEnd->greaterThan($segmentStart)) {
                    $minutes += $segmentStart->diffInMinutes($segmentEnd);
                }
            }

            $cursor = $cursor->copy()->addDay()->startOfDay();
        }

        return $minutes * $sign;
    }

    private function moveToBusinessTime(Carbon $date): Carbon
    {
        $current = $date->copy();

        while (!$this->isBusinessDay($current)) {
            $current = $this->nextBusinessDay($current)->setTime($this->startHour(), 0, 0);
        }

        if ($current->hour < $this->startHour()) {
            return $current->setTime($this->startHour(), 0, 0);
        }

        if ($current->hour >= $this->endHour()) {
            return $this->nextBusinessDay($current)->setTime($this->startHour(), 0, 0);
        }

        return $current;
    }

    private function nextBusinessDay(Carbon $date): Carbon
    {
        $next = $date->copy()->addDay();
        while (!$this->isBusinessDay($next)) {
            $next->addDay();
        }

        return $next;
    }

    private function isBusinessDay(Carbon $date): bool
    {
        return $date->isWeekday() && !$this->isHoliday($date);
    }

    private function isHoliday(Carbon $date): bool
    {
        $year = $date->year;
        if (!array_key_exists($year, $this->holidayCache)) {
            $this->holidayCache[$year] = Holiday::whereYear('holiday_date', $year)
                ->where('is_active', true)
                ->pluck('holiday_date')
                ->map(fn ($holiday) => Carbon::parse($holiday)->toDateString())
                ->all();
        }

        return in_array($date->toDateString(), $this->holidayCache[$year], true);
    }

    private function startHour(): int
    {
        return (int) config('sla.business_hours.start', 7);
    }

    private function endHour(): int
    {
        return (int) config('sla.business_hours.end', 17);
    }
}
