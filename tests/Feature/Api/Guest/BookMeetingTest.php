<?php

use App\Models\Meeting;
use App\Models\MeetingType;
use Carbon\CarbonImmutable;

beforeEach(function () {
    config(['owner.timezone' => 'Europe/Moscow']);
    $this->travelTo(CarbonImmutable::parse('2026-11-03 12:00', 'Europe/Moscow'));
});

test('guest books a free slot and the time is no longer offered', function () {
    $meetingType = MeetingType::factory()->create(['name' => 'Знакомство', 'duration_minutes' => 30]);

    $response = $this->postJson('/api/guest/v1/meetings', [
        'meetingTypeId' => $meetingType->id,
        'startsAt' => '2026-11-04T10:00:00+03:00',
        'guestName' => '  Анна  ',
    ]);

    $response->assertCreated()
        ->assertExactJson(['data' => [
            'id' => $response->json('data.id'),
            'meetingTypeId' => $meetingType->id,
            'guestName' => 'Анна',
            'startsAt' => '2026-11-04T10:00:00+03:00',
            'endsAt' => '2026-11-04T10:30:00+03:00',
            'meetingType' => [
                'id' => $meetingType->id,
                'name' => 'Знакомство',
                'durationMinutes' => 30,
            ],
        ]]);

    $starts = collect($this->getJson("/api/guest/v1/slots?meetingTypeId={$meetingType->id}")
        ->json('data.days.0.slots'))->pluck('startsAt');

    expect($starts)->toContain('2026-11-04T09:30:00+03:00', '2026-11-04T10:30:00+03:00')
        ->not->toContain('2026-11-04T09:45:00+03:00', '2026-11-04T10:00:00+03:00', '2026-11-04T10:15:00+03:00');
});

test('guest gets 409 for a slot partially overlapping a meeting of another type', function () {
    $hourMeetingType = MeetingType::factory()->create(['duration_minutes' => 60]);
    $halfHourMeetingType = MeetingType::factory()->create(['duration_minutes' => 30]);
    Meeting::factory()->for($hourMeetingType)->startingAt('2026-11-04 10:00')->create();

    $response = $this->postJson('/api/guest/v1/meetings', [
        'meetingTypeId' => $halfHourMeetingType->id,
        'startsAt' => '2026-11-04T10:30:00+03:00',
        'guestName' => 'Анна',
    ]);

    $response->assertConflict()
        ->assertExactJson([
            'message' => 'Это время только что заняли. Выберите другой слот.',
            'code' => 'slot_taken',
        ]);
    $this->assertDatabaseCount('meetings', 1);
    $this->assertDatabaseCount('meeting_cells', 4);
});

test('guest books a slot that starts right when another meeting ends', function () {
    $hourMeetingType = MeetingType::factory()->create(['duration_minutes' => 60]);
    $halfHourMeetingType = MeetingType::factory()->create(['duration_minutes' => 30]);
    Meeting::factory()->for($hourMeetingType)->startingAt('2026-11-04 10:00')->create();

    $this->postJson('/api/guest/v1/meetings', [
        'meetingTypeId' => $halfHourMeetingType->id,
        'startsAt' => '2026-11-04T11:00:00+03:00',
        'guestName' => 'Анна',
    ])
        ->assertCreated()
        ->assertJsonPath('data.startsAt', '2026-11-04T11:00:00+03:00')
        ->assertJsonPath('data.endsAt', '2026-11-04T11:30:00+03:00');

    $this->assertDatabaseCount('meetings', 2);
});

test('guest gets a validation error for an invalid field', function (array $invalidFields, string $invalidField) {
    $meetingType = MeetingType::factory()->create(['duration_minutes' => 30]);

    $this->withoutRequestValidation()
        ->postJson('/api/guest/v1/meetings', [
            'meetingTypeId' => $meetingType->id,
            'startsAt' => '2026-11-04T10:00:00+03:00',
            'guestName' => 'Анна',
            ...$invalidFields,
        ])
        ->assertUnprocessable()
        ->assertOnlyJsonValidationErrors([$invalidField]);

    $this->assertDatabaseEmpty('meetings');
})->with([
    'name empty' => [['guestName' => ''], 'guestName'],
    'name whitespace only' => [['guestName' => '   '], 'guestName'],
    'name 101 characters' => [['guestName' => str_repeat('я', 101)], 'guestName'],
    'start not a date-time' => [['startsAt' => 'завтра в 10'], 'startsAt'],
    'start without an offset' => [['startsAt' => '2026-11-04T10:00:00'], 'startsAt'],
    'start with an empty fraction' => [['startsAt' => '2026-11-04T10:00:00.+03:00'], 'startsAt'],
    'meeting type not an integer' => [['meetingTypeId' => 'abc'], 'meetingTypeId'],
]);

test('guest gets a validation error for a nonexistent date that would roll over to a bookable slot', function () {
    $this->travelTo(CarbonImmutable::parse('2026-11-29 12:00', 'Europe/Moscow'));
    $meetingType = MeetingType::factory()->create(['duration_minutes' => 30]);

    $this->withoutRequestValidation()
        ->postJson('/api/guest/v1/meetings', [
            'meetingTypeId' => $meetingType->id,
            'startsAt' => '2026-11-31T10:00:00+03:00',
            'guestName' => 'Анна',
        ])
        ->assertUnprocessable()
        ->assertOnlyJsonValidationErrors(['startsAt']);

    $this->assertDatabaseEmpty('meetings');
});

test('guest gets a validation error rather than a conflict for invalid data at a taken time', function () {
    $meetingType = MeetingType::factory()->create(['duration_minutes' => 30]);
    Meeting::factory()->for($meetingType)->startingAt('2026-11-04 10:00')->create();

    $this->withoutRequestValidation()
        ->postJson('/api/guest/v1/meetings', [
            'meetingTypeId' => $meetingType->id,
            'startsAt' => '2026-11-04T10:00:00+03:00',
            'guestName' => '   ',
        ])
        ->assertUnprocessable()
        ->assertOnlyJsonValidationErrors(['guestName']);
});

test('guest gets a validation error for an unknown meeting type', function () {
    $this->postJson('/api/guest/v1/meetings', [
        'meetingTypeId' => 999,
        'startsAt' => '2026-11-04T10:00:00+03:00',
        'guestName' => 'Анна',
    ])
        ->assertUnprocessable()
        ->assertOnlyJsonValidationErrors(['meetingTypeId']);

    $this->assertDatabaseEmpty('meetings');
});

test('guest gets a validation error for a start that is not a bookable slot', function (string $startsAt) {
    $meetingType = MeetingType::factory()->create(['duration_minutes' => 30]);

    $this->postJson('/api/guest/v1/meetings', [
        'meetingTypeId' => $meetingType->id,
        'startsAt' => $startsAt,
        'guestName' => 'Анна',
    ])
        ->assertUnprocessable()
        ->assertOnlyJsonValidationErrors(['startsAt'])
        ->assertJsonPath('errors.startsAt', ['Это время недоступно для записи. Выберите слот из списка.']);

    $this->assertDatabaseEmpty('meetings');
})->with([
    'today' => ['2026-11-03T15:00:00+03:00'],
    'past' => ['2026-11-02T10:00:00+03:00'],
    'after the window' => ['2026-11-18T10:00:00+03:00'],
    'off the grid' => ['2026-11-04T10:10:00+03:00'],
    'with seconds' => ['2026-11-04T10:00:30+03:00'],
    'with fractional seconds' => ['2026-11-04T10:00:00.5+03:00'],
    'before the working hours' => ['2026-11-04T08:45:00+03:00'],
    'ending after 18:00' => ['2026-11-04T17:45:00+03:00'],
]);

test('guest books the first and the last slots of the window', function (string $startsAt, string $ownerStartsAt) {
    $meetingType = MeetingType::factory()->create(['duration_minutes' => 30]);

    $this->postJson('/api/guest/v1/meetings', [
        'meetingTypeId' => $meetingType->id,
        'startsAt' => $startsAt,
        'guestName' => 'Анна',
    ])
        ->assertCreated()
        ->assertJsonPath('data.startsAt', $ownerStartsAt);
})->with([
    'first' => ['2026-11-04T09:00:00+03:00', '2026-11-04T09:00:00+03:00'],
    'last' => ['2026-11-17T17:30:00+03:00', '2026-11-17T17:30:00+03:00'],
    'last given in UTC' => ['2026-11-17T14:30:00+00:00', '2026-11-17T17:30:00+03:00'],
    'last given in UTC with Z' => ['2026-11-17T14:30:00Z', '2026-11-17T17:30:00+03:00'],
    'first with zero fractional seconds' => ['2026-11-04T09:00:00.000+03:00', '2026-11-04T09:00:00+03:00'],
    'first with zero fractional seconds and Z' => ['2026-11-04T06:00:00.000000Z', '2026-11-04T09:00:00+03:00'],
]);
