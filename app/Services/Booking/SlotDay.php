<?php

namespace App\Services\Booking;

use Carbon\CarbonImmutable;

/**
 * A booking window date with its free slots in ascending order; the list is
 * empty when the date is fully booked.
 */
final readonly class SlotDay
{
    /**
     * @param  list<Slot>  $slots
     */
    public function __construct(
        public CarbonImmutable $date,
        public array $slots,
    ) {}
}
