<?php

use App\Models\Meeting;
use App\Models\MeetingType;
use Carbon\CarbonImmutable;

beforeEach(function () {
    config(['owner.timezone' => 'Europe/Moscow']);
    $this->travelTo(CarbonImmutable::parse('2026-11-03 12:00', 'Europe/Moscow'));
});

test('owner sees upcoming meetings of all types with their meeting type', function () {
    $meetingType = MeetingType::factory()->create(['name' => 'Знакомство', 'duration_minutes' => 30]);
    $meeting = Meeting::factory()->for($meetingType)->startingAt('2026-11-04 10:00')->create(['guest_name' => 'Анна']);

    $this->getJson('/api/owner/v1/meetings')
        ->assertOk()
        ->assertExactJson(['data' => [
            [
                'id' => $meeting->id,
                'meetingTypeId' => $meetingType->id,
                'guestName' => 'Анна',
                'startsAt' => '2026-11-04T10:00:00+03:00',
                'endsAt' => '2026-11-04T10:30:00+03:00',
                'meetingType' => [
                    'id' => $meetingType->id,
                    'name' => 'Знакомство',
                    'durationMinutes' => 30,
                ],
            ],
        ]]);
});

/**
 * Documents the tie-break by id rather than guarding it: on SQLite the id is
 * the rowid, and both the starts_at index and a table scan return rows with
 * the same start in id order, so this test passes even without orderBy('id').
 */
test('owner sees meetings sorted by start, then by id for the same start', function () {
    $halfHourMeetingType = MeetingType::factory()->create(['duration_minutes' => 30]);
    $hourMeetingType = MeetingType::factory()->create(['duration_minutes' => 60]);
    Meeting::factory()->for($halfHourMeetingType)->startingAt('2026-11-05 09:00')->create(['id' => 10]);
    Meeting::factory()->for($hourMeetingType)->startingAt('2026-11-04 15:00')->create(['id' => 20]);

    // Booking never lets two meetings share a start, so these rows skip the cells.
    Meeting::factory()->for($hourMeetingType)->startingAt('2026-11-06 11:00')->make(['id' => 40])->save();
    Meeting::factory()->for($halfHourMeetingType)->startingAt('2026-11-06 11:00')->make(['id' => 30])->save();

    $this->getJson('/api/owner/v1/meetings')
        ->assertOk()
        ->assertJsonPath('data.*.id', [20, 10, 30, 40]);
});

test('owner list loads meeting types without a query per meeting', function () {
    Meeting::factory()->startingAt('2026-11-04 10:00')->create();
    Meeting::factory()->startingAt('2026-11-05 10:00')->create();
    Meeting::factory()->startingAt('2026-11-06 10:00')->create();

    $this->expectsDatabaseQueryCount(2);

    $this->getJson('/api/owner/v1/meetings')
        ->assertOk()
        ->assertJsonCount(3, 'data');
});

test('owner does not see a meeting that has ended', function () {
    $meetingType = MeetingType::factory()->create(['duration_minutes' => 30]);
    Meeting::factory()->for($meetingType)->startingAt('2026-11-03 11:00')->create();
    Meeting::factory()->for($meetingType)->startingAt('2026-11-03 11:30')->create();

    $this->getJson('/api/owner/v1/meetings')
        ->assertOk()
        ->assertExactJson(['data' => []]);
});

test('owner sees a meeting that is going on now until it ends', function () {
    $meetingType = MeetingType::factory()->create(['duration_minutes' => 60]);
    $ongoing = Meeting::factory()->for($meetingType)->startingAt('2026-11-03 11:30')->create();

    $this->getJson('/api/owner/v1/meetings')
        ->assertOk()
        ->assertJsonPath('data.*.id', [$ongoing->id]);
});
