<?php

namespace Database\Factories;

use App\Models\Meeting;
use App\Models\MeetingType;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Creates meetings together with their cells, so that tests occupy time
 * exactly as a booking does. Times are stored in UTC.
 *
 * @extends Factory<Meeting>
 */
class MeetingFactory extends Factory
{
    /**
     * Define the model's default state: tomorrow at 10:00 in the owner timezone,
     * as long as its meeting type.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'meeting_type_id' => MeetingType::factory(),
            'guest_name' => fake()->name(),
            'starts_at' => fn (): CarbonImmutable => CarbonImmutable::now(config()->string('owner.timezone'))
                ->addDay()
                ->setTime(10, 0)
                ->utc(),
            'ends_at' => fn (array $attributes): CarbonImmutable => CarbonImmutable::parse($attributes['starts_at'])
                ->addMinutes(MeetingType::query()->findOrFail((int) $attributes['meeting_type_id'])->duration_minutes),
        ];
    }

    /**
     * Configure the factory to occupy the meeting's cells after creation.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (Meeting $meeting): void {
            $meeting->occupyCells();
        });
    }

    /**
     * Indicate that the meeting starts at the given local time of the owner timezone.
     */
    public function startingAt(string $ownerLocalTime): static
    {
        return $this->state(fn (array $attributes): array => [
            'starts_at' => CarbonImmutable::parse($ownerLocalTime, config()->string('owner.timezone'))->utc(),
        ]);
    }
}
