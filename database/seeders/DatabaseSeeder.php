<?php

namespace Database\Seeders;

use App\Models\Incident;
use App\Models\Infrastructure;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            UserSeeder::class,
        ]);

        $infrastructures = Infrastructure::factory(12)->create();

        Incident::factory(36)
            ->recycle($infrastructures)
            ->create();

        Incident::factory()
            ->recycle($infrastructures)
            ->create([
                'titre' => 'Contamination critique détectée au réservoir principal',
                'type' => 'Contamination',
                'gravite' => 'Critique',
                'statut' => 'Ouvert',
                'date_incident' => now(),
            ]);
    }
}
