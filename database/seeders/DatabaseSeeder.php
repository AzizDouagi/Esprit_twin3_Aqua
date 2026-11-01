<?php

namespace Database\Seeders;

use App\Models\Incident;
use App\Models\Infrastructure;
use App\Models\Maintenance;
use App\Models\User;
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

        $techniciens = User::role(User::ROLE_TECHNICIEN)->get();
        $declarants = User::role(User::ROLE_USER)->get();

        $infrastructures = Infrastructure::factory(12)->create();

        Incident::factory(36)
            ->recycle($infrastructures)
            ->create()
            ->each(fn (Incident $incident) => $incident->update([
                'declarant_id' => $declarants->random()->id,
                'technicien_id' => fake()->boolean(70) ? $techniciens->random()->id : null,
            ]));

        Incident::factory()
            ->recycle($infrastructures)
            ->create([
                'titre' => 'Contamination critique détectée au réservoir principal',
                'description' => 'Forte turbidité et chute du pH constatées en sortie de réservoir. Risque sanitaire : eau non potable, consommation suspendue en attente d’analyses.',
                'type' => 'Contamination',
                'gravite' => 'Critique',
                'statut' => 'Ouvert',
                'date_incident' => now(),
                'resolu_le' => null,
                'declarant_id' => $declarants->first()?->id,
            ]);

        Maintenance::factory(6)->recycle($infrastructures)->create(['technicien_id' => $techniciens->first()?->id]);
        Maintenance::factory(8)->realisee()->recycle($infrastructures)->create(['technicien_id' => $techniciens->first()?->id]);

        $this->call(MesureSeeder::class);
    }
}
