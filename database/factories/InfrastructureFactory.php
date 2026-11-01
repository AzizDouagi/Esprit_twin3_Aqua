<?php

namespace Database\Factories;

use App\Models\Infrastructure;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Infrastructure>
 */
class InfrastructureFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement(Infrastructure::TYPES);
        $zones = ['Ariana Nord', 'El Menzah', 'Lac 2', 'Sidi Bou Said', 'Marsa Plage', 'Centre Urbain Nord', 'Charguia', 'Ezzahra', 'Manouba', 'Ben Arous'];

        return [
            'nom' => $type.' '.fake()->unique()->bothify('AQ-###'),
            'type' => $type,
            'localisation' => fake()->randomElement($zones).', Grand Tunis',
            'latitude' => fake()->randomFloat(6, 36.74, 36.92),
            'longitude' => fake()->randomFloat(6, 10.02, 10.32),
            'etat' => fake()->randomElement(Infrastructure::ETATS),
            'date_installation' => fake()->dateTimeBetween('-18 years', '-3 months'),
            'description' => fake()->sentence(18),
        ];
    }
}
