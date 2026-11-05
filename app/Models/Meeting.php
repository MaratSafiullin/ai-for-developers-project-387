<?php

namespace App\Models;

use App\Services\Booking\MeetingCells;
use Carbon\CarbonImmutable;
use Database\Factories\MeetingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A guest's confirmed booking of an interval for a meeting type. Never
 * cancelled or moved. Times are stored in UTC.
 *
 * @property int $id
 * @property int $meeting_type_id
 * @property string $guest_name
 * @property CarbonImmutable $starts_at
 * @property CarbonImmutable $ends_at
 */
#[Fillable(['meeting_type_id', 'guest_name', 'starts_at', 'ends_at'])]
class Meeting extends Model
{
    /** @use HasFactory<MeetingFactory> */
    use HasFactory;

    /**
     * Get the meeting type of the meeting.
     *
     * @return BelongsTo<MeetingType, $this>
     */
    public function meetingType(): BelongsTo
    {
        return $this->belongsTo(MeetingType::class);
    }

    /**
     * Get the 15-minute cells the meeting occupies.
     *
     * @return HasMany<MeetingCell, $this>
     */
    public function cells(): HasMany
    {
        return $this->hasMany(MeetingCell::class);
    }

    /**
     * Store a cell for every 15 minutes of the meeting. Fails with a unique
     * constraint violation when the interval overlaps another meeting, so a
     * booking must create the meeting and call this in one transaction (ADR 0002).
     */
    public function occupyCells(): void
    {
        $this->cells()->createMany(array_map(
            fn (CarbonImmutable $cellStartsAt): array => ['starts_at' => $cellStartsAt],
            MeetingCells::startsBetween($this->starts_at, $this->ends_at),
        ));
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'starts_at' => 'immutable_datetime',
            'ends_at' => 'immutable_datetime',
        ];
    }
}
