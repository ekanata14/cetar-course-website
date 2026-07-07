<?php

namespace Database\Factories;

use App\Models\Quiz;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserQuizAttempt>
 */
class UserQuizAttemptFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'quiz_id' => Quiz::factory(),
            'started_at' => now(),
            'completed_at' => null,
            'score' => 0,
            'status' => 'in_progress',
        ];
    }

    /**
     * Attempt yang sudah selesai dikerjakan.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'completed_at' => now(),
            'score' => fake()->numberBetween(100, 500),
            'status' => 'completed',
        ]);
    }
}
