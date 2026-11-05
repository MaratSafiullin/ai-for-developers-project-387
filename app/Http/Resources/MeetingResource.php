<?php

namespace App\Http\Resources;

use App\Models\Meeting;
use App\Services\Booking\BookingRules;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A meeting with its meeting type, with ISO 8601 times carrying the owner
 * timezone offset.
 *
 * @mixin Meeting
 */
class MeetingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array{id: int, meetingTypeId: int, guestName: string, startsAt: string, endsAt: string, meetingType: array{id: int, name: string, durationMinutes: int}}
     */
    public function toArray(Request $request): array
    {
        $timezone = resolve(BookingRules::class)->timezone();

        return [
            'id' => $this->id,
            'meetingTypeId' => $this->meeting_type_id,
            'guestName' => $this->guest_name,
            'startsAt' => $this->starts_at->setTimezone($timezone)->toIso8601String(),
            'endsAt' => $this->ends_at->setTimezone($timezone)->toIso8601String(),
            'meetingType' => [
                'id' => $this->meetingType->id,
                'name' => $this->meetingType->name,
                'durationMinutes' => $this->meetingType->duration_minutes,
            ],
        ];
    }
}
