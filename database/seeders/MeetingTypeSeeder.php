<?php

namespace Database\Seeders;

use App\Models\MeetingType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MeetingTypeSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the demo meeting type catalog.
     */
    public function run(): void
    {
        MeetingType::query()->create([
            'name' => 'Знакомство',
            'description' => 'Короткий созвон, чтобы познакомиться и обсудить задачу',
            'duration_minutes' => 30,
        ]);

        MeetingType::query()->create([
            'name' => 'Консультация',
            'description' => 'Разбор вашего вопроса с конкретными рекомендациями',
            'duration_minutes' => 60,
        ]);

        MeetingType::query()->create([
            'name' => 'Быстрый вопрос',
            'description' => null,
            'duration_minutes' => 15,
        ]);
    }
}
