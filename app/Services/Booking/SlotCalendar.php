<?php

namespace App\Services\Booking;

/**
 * The free slots of a meeting type for every date of the booking window,
 * with times in the owner timezone.
 */
final readonly class SlotCalendar
{
    /**
     * @param  list<SlotDay>  $days
     */
    public function __construct(
        public string $timezone,
        public array $days,
    ) {}
}
