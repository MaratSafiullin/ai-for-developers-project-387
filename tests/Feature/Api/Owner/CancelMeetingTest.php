<?php

use App\Models\Meeting;
use App\Models\MeetingType;
use Carbon\CarbonImmutable;

beforeEach(function () {
    config(['owner.timezone' => 'Europe/Moscow']);
    $this->travelTo(CarbonImmutable::parse('2026-11-03 12:00', 'Europe/Moscow'));
});

test('owner cancels an upcoming meeting and the same time can be booked again by another type', function () {
    $hourType = MeetingType::factory()->create(['duration_minutes' => 60]);
    $halfHourType = MeetingType::factory()->create(['duration_minutes' => 30]);
    $meeting = Meeting::factory()->for($hourType)->startingAt('2026-11-04 10:00')->create();

    $this->deleteJson("/api/owner/v1/meetings/{$meeting->id}")
        ->assertNoContent();

    $this->getJson('/api/owner/v1/meetings')->assertExactJson(['data' => []]);

    $this->postJson('/api/guest/v1/meetings', [
        'meetingTypeId' => $halfHourType->id,
        'startsAt' => '2026-11-04T10:15:00+03:00',
        'guestName' => 'Анна',
    ])->assertCreated();
});

test('cancelling a meeting leaves the meeting adjoining it intact', function () {
    $meetingType = MeetingType::factory()->create(['duration_minutes' => 60]);
    $meeting = Meeting::factory()->for($meetingType)->startingAt('2026-11-04 10:00')->create();
    $adjacent = Meeting::factory()->for($meetingType)->startingAt('2026-11-04 11:00')->create();

    $this->deleteJson("/api/owner/v1/meetings/{$meeting->id}")->assertNoContent();

    $this->getJson('/api/owner/v1/meetings')->assertJsonPath('data.*.id', [$adjacent->id]);
    $this->postJson('/api/guest/v1/meetings', [
        'meetingTypeId' => $meetingType->id,
        'startsAt' => '2026-11-04T11:00:00+03:00',
        'guestName' => 'Борис',
    ])->assertConflict()->assertJsonPath('code', 'slot_taken');
});

test('cancelling an already cancelled or unknown meeting answers 404', function () {
    $meeting = Meeting::factory()->startingAt('2026-11-04 10:00')->create();

    $this->deleteJson("/api/owner/v1/meetings/{$meeting->id}")->assertNoContent();

    foreach ([$meeting->id, 99999] as $id) {
        $this->deleteJson("/api/owner/v1/meetings/{$id}")
            ->assertNotFound()
            ->assertJsonPath('message', 'Встреча не найдена.');
    }
});

test('owner cannot cancel a meeting that has started or is over', function (string $startsAt) {
    $meeting = Meeting::factory()->startingAt($startsAt)->create();

    $this->deleteJson("/api/owner/v1/meetings/{$meeting->id}")
        ->assertConflict()
        ->assertExactJson([
            'message' => 'Встреча уже началась, её нельзя отменить.',
            'code' => 'meeting_started',
        ]);

    $this->assertModelExists($meeting);
})->with([
    'going on' => '2026-11-03 11:45',
    'starting right now' => '2026-11-03 12:00',
    'over' => '2026-11-03 09:00',
]);

test('a free slot is still booked and a taken one is still refused', function () {
    $meetingType = MeetingType::factory()->create(['duration_minutes' => 30]);
    Meeting::factory()->for($meetingType)->startingAt('2026-11-04 10:00')->create();

    $this->postJson('/api/guest/v1/meetings', [
        'meetingTypeId' => $meetingType->id,
        'startsAt' => '2026-11-04T10:30:00+03:00',
        'guestName' => 'Анна',
    ])->assertCreated();

    $this->postJson('/api/guest/v1/meetings', [
        'meetingTypeId' => $meetingType->id,
        'startsAt' => '2026-11-04T10:00:00+03:00',
        'guestName' => 'Борис',
    ])->assertConflict()->assertJsonPath('code', 'slot_taken');
});
