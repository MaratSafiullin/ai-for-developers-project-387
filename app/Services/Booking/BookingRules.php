<?php

namespace App\Services\Booking;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Config\Repository;

/**
 * The owner's booking rules from config: the booking window of dates
 * starting tomorrow, the working hours and the shared 15-minute start grid.
 * All dates and times are in the owner timezone.
 */
class BookingRules
{
    /**
     * The step of the start grid, equal to the size of a meeting cell.
     */
    public const int GRID_MINUTES = 15;

    public function __construct(private Repository $config) {}

    /**
     * Get the single timezone of the service.
     */
    public function timezone(): string
    {
        return $this->config->string('owner.timezone');
    }

    /**
     * Get the start of the booking window: the start of tomorrow by the
     * owner's current date.
     */
    public function windowStart(): CarbonImmutable
    {
        return CarbonImmutable::now($this->timezone())->startOfDay()->addDay();
    }

    /**
     * Get the exclusive end of the booking window: the start of the day after its last date.
     */
    public function windowEnd(): CarbonImmutable
    {
        return $this->windowStart()->addDays($this->windowDays());
    }

    /**
     * Get the dates of the booking window, each at the start of its day.
     *
     * @return list<CarbonImmutable>
     */
    public function windowDates(): array
    {
        $windowStart = $this->windowStart();

        return array_map(
            fn (int $offset): CarbonImmutable => $windowStart->addDays($offset),
            range(0, $this->windowDays() - 1),
        );
    }

    /**
     * Get the intervals of the given duration that start on the grid of a
     * date and fit entirely within the working hours, regardless of occupancy.
     *
     * @return list<Slot>
     */
    public function gridSlotsOn(CarbonImmutable $date, int $durationMinutes): array
    {
        $workingHoursStart = $date->setTimeFromTimeString($this->config->string('owner.working_hours.start'));
        $workingHoursEnd = $date->setTimeFromTimeString($this->config->string('owner.working_hours.end'));

        $slots = [];

        for ($startsAt = $workingHoursStart; $startsAt->addMinutes($durationMinutes) <= $workingHoursEnd; $startsAt = $startsAt->addMinutes(self::GRID_MINUTES)) {
            $slots[] = new Slot($startsAt, $startsAt->addMinutes($durationMinutes));
        }

        return $slots;
    }

    /**
     * Determine whether a meeting of the given duration may start at the
     * moment: on a booking window date, on the grid, and ending within the
     * working hours. Occupancy is not checked here.
     */
    public function isBookableStart(CarbonImmutable $startsAt, int $durationMinutes): bool
    {
        $localStartsAt = $startsAt->setTimezone($this->timezone());

        if ($localStartsAt < $this->windowStart() || $localStartsAt >= $this->windowEnd()) {
            return false;
        }

        foreach ($this->gridSlotsOn($localStartsAt->startOfDay(), $durationMinutes) as $slot) {
            if ($slot->startsAt->equalTo($localStartsAt)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the number of dates in the booking window.
     */
    private function windowDays(): int
    {
        return $this->config->integer('owner.booking_window_days');
    }
}
