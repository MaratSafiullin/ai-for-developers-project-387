<?php

test('owner creates a meeting type that appears in both catalogs', function () {
    $response = $this->postJson('/api/owner/v1/meeting-types', [
        'name' => '  Знакомство  ',
        'description' => 'Короткий созвон, чтобы познакомиться и обсудить задачу',
        'durationMinutes' => 30,
    ]);

    $createdMeetingType = [
        'id' => $response->json('data.id'),
        'name' => 'Знакомство',
        'description' => 'Короткий созвон, чтобы познакомиться и обсудить задачу',
        'durationMinutes' => 30,
    ];

    $response->assertCreated()
        ->assertExactJson(['data' => $createdMeetingType]);

    $this->getJson('/api/owner/v1/meeting-types')
        ->assertExactJson(['data' => [$createdMeetingType]]);

    $this->getJson('/api/guest/v1/meeting-types')
        ->assertExactJson(['data' => [$createdMeetingType]]);
});

test('owner creates a meeting type with an empty description stored as null', function (array $descriptionField) {
    $this->postJson('/api/owner/v1/meeting-types', [
        'name' => 'Консультация',
        'durationMinutes' => 60,
        ...$descriptionField,
    ])
        ->assertCreated()
        ->assertJsonPath('data.description', null);

    $this->getJson('/api/owner/v1/meeting-types')
        ->assertJsonPath('data.0.description', null);
})->with([
    'empty' => [['description' => '']],
    'whitespace only' => [['description' => '   ']],
    'null' => [['description' => null]],
    'missing' => [[]],
]);

test('owner gets a validation error for an invalid field', function (array $invalidFields, string $invalidField) {
    $this->withoutRequestValidation()
        ->postJson('/api/owner/v1/meeting-types', [
            'name' => 'Знакомство',
            'description' => 'Короткий созвон',
            'durationMinutes' => 30,
            ...$invalidFields,
        ])
        ->assertUnprocessable()
        ->assertOnlyJsonValidationErrors([$invalidField]);

    $this->getJson('/api/owner/v1/meeting-types')
        ->assertExactJson(['data' => []]);
})->with([
    'name empty' => [['name' => ''], 'name'],
    'name whitespace only' => [['name' => '   '], 'name'],
    'name missing' => [['name' => null], 'name'],
    'name not a string' => [['name' => ['Знакомство']], 'name'],
    'name 101 characters' => [['name' => str_repeat('я', 101)], 'name'],
    'description 1001 characters' => [['description' => str_repeat('я', 1001)], 'description'],
    'description not a string' => [['description' => 42], 'description'],
    'duration empty' => [['durationMinutes' => ''], 'durationMinutes'],
    'duration missing' => [['durationMinutes' => null], 'durationMinutes'],
    'duration 0' => [['durationMinutes' => 0], 'durationMinutes'],
    'duration 255' => [['durationMinutes' => 255], 'durationMinutes'],
    'duration not a multiple of 15' => [['durationMinutes' => 20], 'durationMinutes'],
    'duration not an integer' => [['durationMinutes' => 22.5], 'durationMinutes'],
]);

test('owner creates meeting types at the field limits', function () {
    $this->postJson('/api/owner/v1/meeting-types', [
        'name' => str_repeat('я', 100),
        'description' => str_repeat('я', 1000),
        'durationMinutes' => 240,
    ])->assertCreated();

    $this->postJson('/api/owner/v1/meeting-types', [
        'name' => 'я',
        'durationMinutes' => 15,
    ])->assertCreated();
});

test('owner may create meeting types with duplicate names', function () {
    $meetingType = ['name' => 'Знакомство', 'durationMinutes' => 30];

    $this->postJson('/api/owner/v1/meeting-types', $meetingType)->assertCreated();
    $this->postJson('/api/owner/v1/meeting-types', $meetingType)->assertCreated();

    $this->getJson('/api/owner/v1/meeting-types')
        ->assertJsonCount(2, 'data');
});
