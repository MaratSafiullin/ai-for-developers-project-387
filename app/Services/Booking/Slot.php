<?php

namespace App\Services\Booking;

use Carbon\CarbonImmutable;

/**
 * A free interval for a meeting type, as long as its duration. Not stored:
 * identified by its meeting type and start.
 */
final readonly class Slot
{
    public function __construct(
        public CarbonImmutable $startsAt,
        public CarbonImmutable $endsAt,
    ) {}
}
