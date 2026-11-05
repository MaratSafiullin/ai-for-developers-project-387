<?php

namespace Database\Factories;

use App\Models\MeetingType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MeetingType>
 */
class MeetingTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'description' => fake()->sentence(),
            'duration_minutes' => fake()->numberBetween(1, 16) * 15,
        ];
    }

    /**
     * Indicate that the meeting type has no description.
     */
    public function withoutDescription(): static
    {
        return $this->state(fn (array $attributes) => [
            'description' => null,
        ]);
    }
}
