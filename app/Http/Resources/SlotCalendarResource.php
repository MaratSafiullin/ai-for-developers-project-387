<?php

namespace App\Http\Resources;

use App\Services\Booking\Slot;
use App\Services\Booking\SlotCalendar;
use App\Services\Booking\SlotDay;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Free slots grouped by booking window date, with ISO 8601 times carrying
 * the owner timezone offset.
 *
 * @property SlotCalendar $resource
 */
class SlotCalendarResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array{timezone: string, days: list<array{date: string, slots: list<array{startsAt: string, endsAt: string}>}>}
     */
    public function toArray(Request $request): array
    {
        return [
            'timezone' => $this->resource->timezone,
            'days' => array_map(fn (SlotDay $day): array => [
                'date' => $day->date->toDateString(),
                'slots' => array_map(fn (Slot $slot): array => [
                    'startsAt' => $slot->startsAt->toIso8601String(),
                    'endsAt' => $slot->endsAt->toIso8601String(),
                ], $day->slots),
            ], $this->resource->days),
        ];
    }
}
