<?php

namespace Database\Factories;

use App\Models\Incident;
use App\Models\Infrastructure;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Incident>
 */
class IncidentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement(Incident::TYPES);

        return [
            'titre' => $type.' signalée - '.fake()->streetName(),
            'description' => fake()->paragraph(3),
            'type' => $type,
            'gravite' => fake()->randomElement(Incident::GRAVITES),
            'statut' => fake()->randomElement(Incident::STATUTS),
            'date_incident' => fake()->dateTimeBetween('-90 days', 'now'),
            'infrastructure_id' => Infrastructure::factory(),
        ];
    }
}
