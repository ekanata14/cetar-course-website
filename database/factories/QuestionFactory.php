<?php

namespace Database\Factories;

use App\Models\Quiz;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Question>
 */
class QuestionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'quiz_id' => Quiz::factory(),
            'section' => fake()->randomElement(['TWK', 'TIU', 'TKP']),
            'passage' => null,
            'text' => fake()->sentence().'?',
            'option_a' => fake()->sentence(4),
            'option_b' => fake()->sentence(4),
            'option_c' => fake()->sentence(4),
            'option_d' => fake()->sentence(4),
            'option_e' => fake()->sentence(4),
            'correct_answer' => fake()->randomElement(['A', 'B', 'C', 'D', 'E']),
            'points' => 5,
            'explanation' => fake()->paragraph(),
        ];
    }
}
