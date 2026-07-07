<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Quiz>
 */
class QuizFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => 'Try Out '.fake()->unique()->words(3, true),
            'description' => fake()->paragraph(),
            'duration_minutes' => fake()->randomElement([60, 90, 100, 120]),
        ];
    }
}
