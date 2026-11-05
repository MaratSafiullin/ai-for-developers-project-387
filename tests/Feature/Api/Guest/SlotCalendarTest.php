<?php

use App\Models\Meeting;
use App\Models\MeetingType;
use Carbon\CarbonImmutable;

beforeEach(function () {
    config(['owner.timezone' => 'Europe/Moscow']);
    $this->travelTo(CarbonImmutable::parse('2026-11-03 12:00', 'Europe/Moscow'));
});

test('guest sees 14 dates starting tomorrow in the owner timezone', function () {
    $meetingType = MeetingType::factory()->create(['duration_minutes' => 30]);

    $response = $this->getJson("/api/guest/v1/slots?meetingTypeId={$meetingType->id}")
        ->assertOk()
        ->assertJsonPath('data.timezone', 'Europe/Moscow');

    expect($response->json('data.days.*.date'))->toBe([
        '2026-11-04', '2026-11-05', '2026-11-06', '2026-11-07', '2026-11-08',
        '2026-11-09', '2026-11-10', '2026-11-11', '2026-11-12', '2026-11-13',
        '2026-11-14', '2026-11-15', '2026-11-16', '2026-11-17',
    ]);
});

test('guest sees slots every 15 minutes from 09:00 that end by 18:00', function () {
    $meetingType = MeetingType::factory()->create(['duration_minutes' => 30]);

    $slots = $this->getJson("/api/guest/v1/slots?meetingTypeId={$meetingType->id}")
        ->assertOk()
        ->json('data.days.0.slots');

    expect($slots)->toHaveCount(35)
        ->and($slots[0])->toBe([
            'startsAt' => '2026-11-04T09:00:00+03:00',
            'endsAt' => '2026-11-04T09:30:00+03:00',
        ])
        ->and($slots[1]['startsAt'])->toBe('2026-11-04T09:15:00+03:00')
        ->and($slots[34])->toBe([
            'startsAt' => '2026-11-04T17:30:00+03:00',
            'endsAt' => '2026-11-04T18:00:00+03:00',
        ]);
});

test('guest does not see slots overlapping a meeting of another type but sees adjacent ones', function () {
    $meetingType = MeetingType::factory()->create(['duration_minutes' => 30]);
    $otherMeetingType = MeetingType::factory()->create(['duration_minutes' => 60]);
    Meeting::factory()->for($otherMeetingType)->startingAt('2026-11-04 10:00')->create();

    $starts = collect($this->getJson("/api/guest/v1/slots?meetingTypeId={$meetingType->id}")
        ->assertOk()
        ->json('data.days.0.slots'))->pluck('startsAt');

    expect($starts)->toContain('2026-11-04T09:30:00+03:00', '2026-11-04T11:00:00+03:00')
        ->not->toContain(
            '2026-11-04T09:45:00+03:00',
            '2026-11-04T10:00:00+03:00',
            '2026-11-04T10:15:00+03:00',
            '2026-11-04T10:30:00+03:00',
            '2026-11-04T10:45:00+03:00',
        )
        ->toHaveCount(30);
});

test('guest sees a fully booked date with no slots', function () {
    $meetingType = MeetingType::factory()->create(['duration_minutes' => 15]);
    $longMeetingType = MeetingType::factory()->create(['duration_minutes' => 240]);
    $shortMeetingType = MeetingType::factory()->create(['duration_minutes' => 60]);
    Meeting::factory()->for($longMeetingType)->startingAt('2026-11-05 09:00')->create();
    Meeting::factory()->for($longMeetingType)->startingAt('2026-11-05 13:00')->create();
    Meeting::factory()->for($shortMeetingType)->startingAt('2026-11-05 17:00')->create();

    $days = $this->getJson("/api/guest/v1/slots?meetingTypeId={$meetingType->id}")
        ->assertOk()
        ->json('data.days');

    expect($days)->toHaveCount(14)
        ->and($days[1])->toBe(['date' => '2026-11-05', 'slots' => []])
        ->and($days[0]['slots'])->toHaveCount(36)
        ->and($days[2]['slots'])->toHaveCount(36);
});

test('guest window starts tomorrow by the owner date, not the UTC date', function () {
    $this->travelTo(CarbonImmutable::parse('2026-11-03 22:30', 'UTC'));
    $meetingType = MeetingType::factory()->create(['duration_minutes' => 30]);

    $dates = $this->getJson("/api/guest/v1/slots?meetingTypeId={$meetingType->id}")
        ->assertOk()
        ->json('data.days.*.date');

    expect($dates)->toHaveCount(14)
        ->and($dates[0])->toBe('2026-11-05')
        ->and($dates[13])->toBe('2026-11-18');
});

test('guest gets 404 for an unknown meeting type', function () {
    $this->getJson('/api/guest/v1/slots?meetingTypeId=999')
        ->assertNotFound()
        ->assertJsonStructure(['message']);
});

test('guest gets a validation error for a missing or non-integer meeting type id', function (string $query) {
    $this->withoutRequestValidation()
        ->getJson('/api/guest/v1/slots'.$query)
        ->assertUnprocessable()
        ->assertOnlyJsonValidationErrors(['meetingTypeId']);
})->with([
    'missing' => [''],
    'empty' => ['?meetingTypeId='],
    'not a number' => ['?meetingTypeId=abc'],
    'fractional' => ['?meetingTypeId=1.5'],
    'beyond int32' => ['?meetingTypeId=2147483648'],
]);
