<?php

namespace Database\Factories;

use App\Models\Infrastructure;
use App\Models\Maintenance;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Maintenance>
 */
class MaintenanceFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'infrastructure_id' => Infrastructure::factory(),
            'technicien_id' => null,
            'titre' => fake()->randomElement(['Contrôle des pompes', 'Nettoyage du réservoir', 'Remplacement de joints', 'Étalonnage des capteurs', 'Inspection de canalisation']),
            'notes' => fake()->sentence(),
            'statut' => 'Planifiée',
            'date_prevue' => fake()->dateTimeBetween('now', '+45 days'),
            'date_realisee' => null,
            'cout' => null,
        ];
    }

    public function realisee(): static
    {
        return $this->state(function () {
            $date = fake()->dateTimeBetween('-60 days', '-1 day');

            return [
                'statut' => 'Réalisée',
                'date_prevue' => $date,
                'date_realisee' => $date,
                'cout' => fake()->randomFloat(2, 150, 4200),
            ];
        });
    }
}
