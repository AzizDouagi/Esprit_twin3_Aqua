<?php

namespace Database\Factories;

use App\Models\Incident;
use App\Models\Infrastructure;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

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
        $statut = fake()->randomElement(Incident::STATUTS);
        $date = fake()->dateTimeBetween('-90 days', 'now');

        return [
            'titre' => $type.' signalée - '.fake()->streetName(),
            'description' => fake()->paragraph(3),
            'type' => $type,
            'gravite' => fake()->randomElement(Incident::GRAVITES),
            'statut' => $statut,
            'date_incident' => $date,
            'resolu_le' => $statut === 'Résolu'
                ? Carbon::instance($date)->addHours(fake()->numberBetween(2, 96))->min(now())
                : null,
            'infrastructure_id' => Infrastructure::factory(),
        ];
    }
}
