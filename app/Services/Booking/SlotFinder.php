<?php

namespace App\Services\Booking;

use App\Models\MeetingCell;
use App\Models\MeetingType;
use Carbon\CarbonImmutable;

/**
 * Finds the free slots of a meeting type: grid intervals within the working
 * hours of every booking window date whose cells are all unoccupied by
 * meetings of any type.
 */
class SlotFinder
{
    public function __construct(private BookingRules $rules) {}

    /**
     * Get the free slots of the meeting type for every window date,
     * including dates without free slots.
     */
    public function slotCalendar(MeetingType $meetingType): SlotCalendar
    {
        $occupiedCellTimestamps = $this->occupiedCellTimestamps(
            $this->rules->windowStart(),
            $this->rules->windowEnd(),
        );

        return new SlotCalendar(
            $this->rules->timezone(),
            array_map(
                fn (CarbonImmutable $date): SlotDay => new SlotDay($date, array_values(array_filter(
                    $this->rules->gridSlotsOn($date, $meetingType->duration_minutes),
                    fn (Slot $slot): bool => $this->isFree($slot, $occupiedCellTimestamps),
                ))),
                $this->rules->windowDates(),
            ),
        );
    }

    /**
     * Get the Unix timestamps of the occupied cells in [from, until) as array
     * keys, for constant-time lookup.
     *
     * @return array<int, int>
     */
    private function occupiedCellTimestamps(CarbonImmutable $from, CarbonImmutable $until): array
    {
        return array_flip(MeetingCell::query()
            ->where('starts_at', '>=', $from->utc())
            ->where('starts_at', '<', $until->utc())
            ->pluck('starts_at')
            ->map(fn (CarbonImmutable $cellStartsAt): int => $cellStartsAt->getTimestamp())
            ->all());
    }

    /**
     * Determine whether none of the cells of the slot is occupied.
     *
     * @param  array<int, int>  $occupiedCellTimestamps
     */
    private function isFree(Slot $slot, array $occupiedCellTimestamps): bool
    {
        foreach (MeetingCells::startsBetween($slot->startsAt, $slot->endsAt) as $cellStartsAt) {
            if (isset($occupiedCellTimestamps[$cellStartsAt->getTimestamp()])) {
                return false;
            }
        }

        return true;
    }
}
