<?php

use App\Models\MeetingType;

test('database seeder fills the demo meeting type catalog', function () {
    $this->seed();

    expect(MeetingType::query()->pluck('name')->all())
        ->toBe(['Знакомство', 'Консультация', 'Быстрый вопрос']);
});
