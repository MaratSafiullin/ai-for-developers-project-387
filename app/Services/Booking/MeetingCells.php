<?php

namespace App\Services\Booking;

use Carbon\CarbonImmutable;

/**
 * Splits a time interval into the 15-minute cells it occupies (ADR 0002).
 * A meeting stores one cell per start; a slot is free when none of its
 * cells is stored.
 */
class MeetingCells
{
    /**
     * Get the UTC starts of the cells of the half-open interval [startsAt, endsAt).
     *
     * @return list<CarbonImmutable>
     */
    public static function startsBetween(CarbonImmutable $startsAt, CarbonImmutable $endsAt): array
    {
        $starts = [];

        for ($cellStartsAt = $startsAt->utc(); $cellStartsAt < $endsAt; $cellStartsAt = $cellStartsAt->addMinutes(BookingRules::GRID_MINUTES)) {
            $starts[] = $cellStartsAt;
        }

        return $starts;
    }
}
