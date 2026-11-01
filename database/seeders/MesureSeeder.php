<?php

namespace Database\Seeders;

use App\Models\Infrastructure;
use App\Models\Mesure;
use Illuminate\Database\Seeder;

class MesureSeeder extends Seeder
{
    /** Pas de temps entre deux relevés (heures) et durée de la série (jours). */
    private const PAS_HEURES = 3;

    private const JOURS = 30;

    public function run(): void
    {
        $maintenant = now()->startOfHour();
        $lignes = [];

        foreach (Infrastructure::all() as $infrastructure) {
            $base = [
                'debit' => fake()->randomFloat(2, 110, 190),
                'pression' => fake()->randomFloat(2, 3.4, 4.6),
                'niveau' => fake()->randomFloat(2, 60, 80),
            ];

            for ($h = self::JOURS * 24; $h >= 0; $h -= self::PAS_HEURES) {
                $moment = $maintenant->copy()->subHours($h);
                $cycle = sin(($moment->hour / 24) * 2 * M_PI); // consommation journalière

                $ligne = [
                    'infrastructure_id' => $infrastructure->id,
                    'debit' => round($base['debit'] + 25 * $cycle + fake()->randomFloat(2, -6, 6), 2),
                    'pression' => round($base['pression'] - 0.3 * $cycle + fake()->randomFloat(2, -0.15, 0.15), 2),
                    'niveau' => round($base['niveau'] + 8 * $cycle + fake()->randomFloat(2, -2, 2), 2),
                    'turbidite' => round(fake()->randomFloat(2, 0.25, 0.85), 2),
                    'ph' => round(fake()->randomFloat(2, 7.15, 7.55), 2),
                    'temperature' => round(16 + 3 * $cycle + fake()->randomFloat(2, -1, 1), 2),
                    'anomalie' => false,
                    'mesure_le' => $moment->toDateTimeString(),
                    'created_at' => $maintenant->toDateTimeString(),
                    'updated_at' => $maintenant->toDateTimeString(),
                ];

                // ~2 % de relevés anormaux injectés (détectés ensuite par mesures:detect-anomalies)
                if (fake()->boolean(2)) {
                    $ligne['turbidite'] = fake()->randomFloat(2, 6, 11);
                    $ligne['pression'] = fake()->randomFloat(2, 0.5, 1.4);
                    $ligne['ph'] = fake()->randomFloat(2, 5.3, 5.9);
                }

                $lignes[] = $ligne;
            }
        }

        foreach (array_chunk($lignes, 500) as $lot) {
            Mesure::insert($lot);
        }
    }
}
