<?php

use App\Models\Meeting;
use App\Models\MeetingType;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

test('the database rejects a cell occupied by another meeting', function () {
    $meetingType = MeetingType::factory()->create(['duration_minutes' => 60]);
    $meeting = Meeting::factory()->for($meetingType)->startingAt('2026-11-04 10:00')->create();
    $overlappingMeeting = Meeting::query()->create([
        'meeting_type_id' => $meetingType->id,
        'guest_name' => 'Анна',
        'starts_at' => $meeting->starts_at->addMinutes(30),
        'ends_at' => $meeting->starts_at->addMinutes(90),
    ]);

    expect(fn () => DB::table('meeting_cells')->insert([
        'meeting_id' => $overlappingMeeting->id,
        'starts_at' => $meeting->starts_at->addMinutes(45),
    ]))->toThrow(UniqueConstraintViolationException::class);
});

test('the database accepts a cell right after another meeting', function () {
    $meetingType = MeetingType::factory()->create(['duration_minutes' => 60]);
    $meeting = Meeting::factory()->for($meetingType)->startingAt('2026-11-04 10:00')->create();
    $adjacentMeeting = Meeting::query()->create([
        'meeting_type_id' => $meetingType->id,
        'guest_name' => 'Анна',
        'starts_at' => $meeting->ends_at,
        'ends_at' => $meeting->ends_at->addMinutes(15),
    ]);

    DB::table('meeting_cells')->insert([
        'meeting_id' => $adjacentMeeting->id,
        'starts_at' => $meeting->ends_at,
    ]);

    $this->assertDatabaseCount('meeting_cells', 5);
});
