<?php

use App\Models\MeetingType;

test('guest catalog lists meeting types ordered by id', function () {
    MeetingType::factory()->withoutDescription()->create([
        'id' => 2,
        'name' => 'Консультация',
        'duration_minutes' => 60,
    ]);
    MeetingType::factory()->create([
        'id' => 1,
        'name' => 'Знакомство',
        'description' => 'Короткий созвон, чтобы познакомиться и обсудить задачу',
        'duration_minutes' => 30,
    ]);

    $this->getJson('/api/guest/v1/meeting-types')
        ->assertOk()
        ->assertExactJson([
            'data' => [
                [
                    'id' => 1,
                    'name' => 'Знакомство',
                    'description' => 'Короткий созвон, чтобы познакомиться и обсудить задачу',
                    'durationMinutes' => 30,
                ],
                [
                    'id' => 2,
                    'name' => 'Консультация',
                    'description' => null,
                    'durationMinutes' => 60,
                ],
            ],
        ]);
});

test('guest catalog is empty when there are no meeting types', function () {
    $this->getJson('/api/guest/v1/meeting-types')
        ->assertOk()
        ->assertExactJson(['data' => []]);
});
