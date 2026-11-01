<?php

namespace Database\Factories;

use App\Models\Infrastructure;
use App\Models\Mesure;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Mesure>
 */
class MesureFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'infrastructure_id' => Infrastructure::factory(),
            'debit' => fake()->randomFloat(2, 120, 180),
            'pression' => fake()->randomFloat(2, 3.2, 4.8),
            'niveau' => fake()->randomFloat(2, 55, 85),
            'turbidite' => fake()->randomFloat(2, 0.2, 0.9),
            'ph' => fake()->randomFloat(2, 7.1, 7.6),
            'temperature' => fake()->randomFloat(2, 14, 20),
            'anomalie' => false,
            'mesure_le' => fake()->dateTimeBetween('-30 days', 'now'),
        ];
    }

    /** Relevé aberrant (pic de turbidité, chute de pression…). */
    public function anormale(): static
    {
        return $this->state(fn () => [
            'turbidite' => fake()->randomFloat(2, 6, 12),
            'pression' => fake()->randomFloat(2, 0.4, 1.2),
            'ph' => fake()->randomFloat(2, 5.2, 5.9),
        ]);
    }
}
