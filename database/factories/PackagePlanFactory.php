<?php

namespace Database\Factories;

use App\Models\Package;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PackagePlan>
 */
class PackagePlanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'package_id' => Package::factory(),
            'name' => '1 Bulan',
            'duration_days' => 30,
            'price' => fake()->randomElement([49000, 99000, 149000, 199000]),
        ];
    }

    /**
     * Plan tahunan (12 bulan).
     */
    public function yearly(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => '1 Tahun',
            'duration_days' => 365,
            'price' => fake()->randomElement([399000, 499000, 599000]),
        ]);
    }
}
