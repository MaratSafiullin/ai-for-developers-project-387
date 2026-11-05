<?php

namespace App\Http\Resources;

use App\Models\MeetingType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin MeetingType
 */
class MeetingTypeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array{id: int, name: string, description: string|null, durationMinutes: int}
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'durationMinutes' => $this->duration_minutes,
        ];
    }
}
