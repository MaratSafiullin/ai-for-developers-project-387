<?php

namespace App\Services\Booking;

use App\Exceptions\SlotTakenException;
use App\Models\Meeting;
use App\Models\MeetingType;
use Carbon\CarbonImmutable;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

/**
 * Books a validated slot start. There is no separate overlap check: the
 * meeting and its cells are inserted in one transaction, and the unique
 * index on cells rejects an overlap with any meeting, including one
 * committed by a concurrent request (ADR 0002).
 */
class MeetingBooker
{
    /**
     * Create the meeting with its cells.
     *
     * The transaction only writes, so under SQLite's DEFERRED mode a
     * concurrent booking waits for the lock (busy_timeout) instead of
     * failing with SQLITE_BUSY, and then hits the unique index.
     *
     * @throws SlotTakenException when the interval overlaps another meeting
     */
    public function book(MeetingType $meetingType, CarbonImmutable $startsAt, string $guestName): Meeting
    {
        $startsAt = $startsAt->utc();

        try {
            $meeting = DB::transaction(function () use ($meetingType, $startsAt, $guestName): Meeting {
                $meeting = Meeting::query()->create([
                    'meeting_type_id' => $meetingType->id,
                    'guest_name' => $guestName,
                    'starts_at' => $startsAt,
                    'ends_at' => $startsAt->addMinutes($meetingType->duration_minutes),
                ]);

                $meeting->occupyCells();

                return $meeting;
            });
        } catch (UniqueConstraintViolationException) {
            throw new SlotTakenException;
        }

        return $meeting->setRelation('meetingType', $meetingType);
    }
}
