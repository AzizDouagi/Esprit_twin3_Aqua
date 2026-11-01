<?php

namespace Tests\Feature;

use App\Models\Incident;
use App\Models\Infrastructure;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AquaSecureCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->admin()->create());
    }

    public function test_front_office_and_dashboard_are_accessible(): void
    {
        Infrastructure::factory(3)->create();
        Incident::factory(5)->create();

        $response = $this->get('/');
        $response->assertOk()->assertSee('AquaSecure');

        $this->get('/infrastructures')->assertOk();
        $this->get('/admin')->assertOk()->assertSee('Total infrastructures');
    }

    public function test_infrastructure_crud_flow(): void
    {
        $payload = [
            'nom' => 'Réservoir Central Test',
            'type' => 'Réservoir',
            'localisation' => 'Tunis Centre',
            'etat' => 'Fonctionnelle',
            'date_installation' => '2023-02-12',
            'description' => 'Infrastructure créée depuis le test fonctionnel.',
        ];

        $store = $this->post('/admin/infrastructures', $payload);
        $infrastructure = Infrastructure::firstOrFail();
        $store->assertRedirect(route('infrastructures.show', $infrastructure));

        $this->get(route('infrastructures.show', $infrastructure))
            ->assertOk()
            ->assertSee('Réservoir Central Test');

        $this->put(route('infrastructures.update', $infrastructure), [
            ...$payload,
            'etat' => 'Maintenance',
        ])->assertRedirect(route('infrastructures.show', $infrastructure));

        $this->assertDatabaseHas('infrastructures', [
            'id' => $infrastructure->id,
            'etat' => 'Maintenance',
        ]);

        $this->delete(route('infrastructures.destroy', $infrastructure))
            ->assertRedirect(route('infrastructures.index'));

        $this->assertSoftDeleted('infrastructures', ['id' => $infrastructure->id]);
    }

    public function test_incident_crud_flow_and_relation(): void
    {
        $infrastructure = Infrastructure::factory()->create(['nom' => 'Station Nord Test']);

        $payload = [
            'titre' => 'Fuite test sur canalisation',
            'description' => 'Un incident de test lié à une infrastructure existante.',
            'type' => 'Fuite',
            'gravite' => 'Critique',
            'statut' => 'Ouvert',
            'date_incident' => '2026-10-04',
            'infrastructure_id' => $infrastructure->id,
        ];

        $store = $this->post('/admin/incidents', $payload);
        $incident = Incident::with('infrastructure')->firstOrFail();
        $store->assertRedirect(route('incidents.show', $incident));

        $this->assertSame('Station Nord Test', $incident->infrastructure->nom);

        $this->get(route('incidents.index', ['search' => 'Station Nord']))
            ->assertOk()
            ->assertSee('Fuite test sur canalisation')
            ->assertSee('Station Nord Test');

        $this->put(route('incidents.update', $incident), [
            ...$payload,
            'statut' => 'Résolu',
        ])->assertRedirect(route('incidents.show', $incident));

        $this->assertDatabaseHas('incidents', [
            'id' => $incident->id,
            'statut' => 'Résolu',
        ]);

        $this->delete(route('incidents.destroy', $incident))
            ->assertRedirect(route('incidents.index'));

        $this->assertDatabaseMissing('incidents', ['id' => $incident->id]);
    }

    public function test_validation_rejects_invalid_incident(): void
    {
        $response = $this->from('/admin/incidents/create')->post('/admin/incidents', [
            'titre' => '',
            'description' => '',
            'type' => 'Invalide',
            'gravite' => 'Critique',
            'statut' => 'Ouvert',
            'date_incident' => 'not-a-date',
            'infrastructure_id' => 999,
        ]);

        $response
            ->assertRedirect('/admin/incidents/create')
            ->assertSessionHasErrors(['titre', 'description', 'type', 'date_incident', 'infrastructure_id']);
    }
}
