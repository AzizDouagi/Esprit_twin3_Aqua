<?php

namespace Tests\Feature\Api;

use App\Models\Incident;
use App\Models\Infrastructure;
use App\Models\Mesure;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiV1Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_endpoints_require_authentication(): void
    {
        $this->getJson('/api/v1/infrastructures')->assertUnauthorized();
        $this->getJson('/api/v1/incidents')->assertUnauthorized();
    }

    public function test_token_can_be_issued_and_used(): void
    {
        $user = User::factory()->admin()->create(['password' => 'secret-pass-1']);

        $this->postJson('/api/v1/auth/token', ['email' => $user->email, 'password' => 'bad'])
            ->assertUnprocessable()->assertJsonValidationErrors('email');

        $token = $this->postJson('/api/v1/auth/token', ['email' => $user->email, 'password' => 'secret-pass-1'])
            ->assertOk()->json('token');

        $this->withToken($token)->getJson('/api/v1/me')
            ->assertOk()->assertJsonPath('data.email', $user->email)->assertJsonPath('data.roles.0', 'admin');
    }

    public function test_infrastructure_crud_is_paginated_validated_and_authorized(): void
    {
        Infrastructure::factory()->count(20)->create();

        Sanctum::actingAs(User::factory()->admin()->create());
        $this->getJson('/api/v1/infrastructures?per_page=5')
            ->assertOk()->assertJsonCount(5, 'data')->assertJsonPath('meta.total', 20);

        $this->postJson('/api/v1/infrastructures', [])->assertUnprocessable()->assertJsonValidationErrors(['nom', 'type', 'etat']);

        $id = $this->postJson('/api/v1/infrastructures', [
            'nom' => 'Réservoir Nord', 'type' => 'Réservoir', 'localisation' => 'Tunis', 'etat' => 'Fonctionnelle',
        ])->assertCreated()->json('data.id');

        $this->putJson("/api/v1/infrastructures/{$id}", [
            'nom' => 'Réservoir Nord 2', 'type' => 'Réservoir', 'localisation' => 'Tunis', 'etat' => 'Maintenance',
        ])->assertOk()->assertJsonPath('data.etat', 'Maintenance');

        $this->deleteJson("/api/v1/infrastructures/{$id}")->assertNoContent();
        $this->assertSoftDeleted('infrastructures', ['id' => $id]);
    }

    public function test_simple_user_is_forbidden_on_infrastructures_and_incident_list(): void
    {
        Sanctum::actingAs(User::factory()->simpleUser()->create());

        $this->getJson('/api/v1/infrastructures')->assertForbidden();
        $this->getJson('/api/v1/incidents')->assertForbidden();
    }

    public function test_incidents_can_be_filtered_created_and_deleted_by_admin_only(): void
    {
        $infra = Infrastructure::factory()->create();
        Incident::factory()->count(3)->create(['gravite' => 'Faible']);
        Incident::factory()->create(['gravite' => 'Critique']);

        $tech = User::factory()->technicien()->create();
        Sanctum::actingAs($tech);

        $this->getJson('/api/v1/incidents?gravite=Critique')->assertOk()->assertJsonCount(1, 'data');
        $this->getJson('/api/v1/incidents?gravite=Nope')->assertUnprocessable();

        $id = $this->postJson('/api/v1/incidents', [
            'titre' => 'Fuite', 'description' => 'Fuite visible', 'type' => 'Fuite', 'gravite' => 'Moyenne',
            'statut' => 'Ouvert', 'date_incident' => now()->toDateString(), 'infrastructure_id' => $infra->id,
        ])->assertCreated()->json('data.id');

        $this->assertDatabaseHas('incidents', ['id' => $id, 'declarant_id' => $tech->id]);
        $this->deleteJson("/api/v1/incidents/{$id}")->assertForbidden();

        Sanctum::actingAs(User::factory()->admin()->create());
        $this->deleteJson("/api/v1/incidents/{$id}")->assertNoContent();
    }

    public function test_measures_listing_and_ingestion(): void
    {
        $infra = Infrastructure::factory()->create();
        Mesure::factory()->count(4)->create(['infrastructure_id' => $infra->id]);
        Mesure::factory()->anormale()->create(['infrastructure_id' => $infra->id, 'anomalie' => true]);

        Sanctum::actingAs(User::factory()->technicien()->create());
        $this->getJson("/api/v1/infrastructures/{$infra->id}/mesures")->assertOk()->assertJsonPath('meta.total', 5);
        $this->getJson("/api/v1/infrastructures/{$infra->id}/mesures?anomalie=1")->assertOk()->assertJsonPath('meta.total', 1);

        $this->postJson("/api/v1/infrastructures/{$infra->id}/mesures", ['ph' => 20])->assertUnprocessable()->assertJsonValidationErrors('ph');
        $id = $this->postJson("/api/v1/infrastructures/{$infra->id}/mesures", ['ph' => 7.2, 'pression' => 4.1])
            ->assertCreated()->json('data.id');
        $this->getJson("/api/v1/mesures/{$id}")->assertOk()->assertJsonPath('data.ph', 7.2);
    }

    public function test_revoked_token_is_rejected_and_reporter_sees_only_own_incident(): void
    {
        $user = User::factory()->create(['password' => 'secret-pass-1']);
        $token = $this->postJson('/api/v1/auth/token', ['email' => $user->email, 'password' => 'secret-pass-1'])->json('token');

        $own = Incident::factory()->create(['declarant_id' => $user->id]);
        $foreign = Incident::factory()->create(['declarant_id' => User::factory()->create()->id]);

        $this->withToken($token)->getJson("/api/v1/incidents/{$own->id}")->assertOk();
        $this->withToken($token)->getJson("/api/v1/incidents/{$foreign->id}")->assertForbidden();

        $this->withToken($token)->deleteJson('/api/v1/auth/token')->assertOk();
        $this->app['auth']->forgetGuards();
        $this->withToken($token)->getJson('/api/v1/me')->assertUnauthorized();
    }

    public function test_unknown_resource_returns_json_404(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());

        $this->getJson('/api/v1/incidents/999999')->assertNotFound()->assertJsonStructure(['message']);
    }
}
