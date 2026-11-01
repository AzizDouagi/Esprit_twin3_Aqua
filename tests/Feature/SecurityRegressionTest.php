<?php

namespace Tests\Feature;

use App\Models\Incident;
use App\Models\Infrastructure;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Sanctum;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class SecurityRegressionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_disabled_accounts_cannot_reuse_sessions_or_api_tokens(): void
    {
        $user = User::factory()->admin()->create(['actif' => false]);
        $this->actingAs($user)->get('/admin')->assertForbidden();
        Sanctum::actingAs($user);
        $this->getJson('/api/v1/me')->assertForbidden();
    }

    public function test_api_requires_verified_email_and_two_factor_when_enabled(): void
    {
        $user = User::factory()->admin()->create(['password' => 'Strong#12345', 'email_verified_at' => null]);
        $payload = ['email' => $user->email, 'password' => 'Strong#12345'];
        $this->postJson('/api/v1/auth/token', $payload)->assertUnprocessable();
        $secret = app(Google2FA::class)->generateSecretKey();
        $user->forceFill(['email_verified_at' => now(), 'two_factor_secret' => encrypt($secret), 'two_factor_confirmed_at' => now()])->save();
        $this->postJson('/api/v1/auth/token', $payload)->assertUnprocessable()->assertJsonValidationErrors('code');
        $this->postJson('/api/v1/auth/token', $payload + ['code' => app(Google2FA::class)->getCurrentOtp($secret)])->assertOk()->assertJsonStructure(['token']);
    }

    public function test_deactivation_revokes_existing_tokens(): void
    {
        $other = User::factory()->technicien()->create();
        $other->createToken('existing');
        $this->actingAs(User::factory()->admin()->create())->put(route('admin.users.actif', $other))->assertRedirect();
        $this->assertSame(0, $other->tokens()->count());
    }

    public function test_incident_assignment_rejects_non_staff_and_inactive_technician(): void
    {
        $incident = Incident::factory()->create();
        $user = User::factory()->simpleUser()->create();
        $disabled = User::factory()->technicien()->create(['actif' => false]);
        $this->actingAs(User::factory()->admin()->create());
        foreach ([$user, $disabled] as $invalid) {
            $this->patchJson(route('incidents.assigner', $incident), ['technicien_id' => $invalid->id])->assertUnprocessable()->assertJsonValidationErrors('technicien_id');
        }
        $this->assertNull($incident->fresh()->technicien_id);
    }

    public function test_api_update_cannot_skip_workflow(): void
    {
        $incident = Incident::factory()->create(['statut' => 'Ouvert']);
        Sanctum::actingAs(User::factory()->technicien()->create());
        $payload = $incident->only(['titre', 'description', 'type', 'gravite', 'infrastructure_id']);
        $payload['date_incident'] = $incident->date_incident->format('Y-m-d');
        $this->putJson('/api/v1/incidents/'.$incident->id, $payload + ['statut' => 'Résolu'])->assertUnprocessable()->assertJsonValidationErrors('statut');
        $this->putJson('/api/v1/incidents/'.$incident->id, $payload + ['statut' => 'En cours'])->assertOk();
        $this->putJson('/api/v1/incidents/'.$incident->id, $payload + ['statut' => 'Résolu'])->assertOk();
        $this->assertNotNull($incident->fresh()->resolu_le);
    }

    public function test_reporter_api_cannot_assign_or_close_an_incident(): void
    {
        Sanctum::actingAs(User::factory()->simpleUser()->create());
        $infra = Infrastructure::factory()->create();
        $payload = ['titre' => 'Petite fuite', 'description' => 'Sur la canalisation principale', 'type' => 'Fuite', 'gravite' => 'Faible', 'date_incident' => now()->toDateString(), 'infrastructure_id' => $infra->id];
        $tech = User::factory()->technicien()->create();
        $this->postJson('/api/v1/incidents', $payload + ['statut' => 'Résolu', 'technicien_id' => $tech->id])->assertUnprocessable()->assertJsonValidationErrors(['statut', 'technicien_id']);
        $this->postJson('/api/v1/incidents', $payload + ['statut' => 'Ouvert'])->assertCreated();
    }

    public function test_incident_filters_reject_malformed_ranges_arrays_and_sort_columns(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        foreach (['du=2026-11-02&au=2026-11-01', 'search[]=x', 'tri=password', 'sens=random', 'technicien_id[]=1'] as $query) {
            $this->getJson('/admin/incidents?'.$query)->assertUnprocessable();
            $this->getJson('/api/v1/incidents?'.$query)->assertUnprocessable();
        }
        $this->getJson('/admin/incidents?au=2026-11-01')->assertOk();
        $this->getJson('/admin/incidents/export/csv?du=invalid')->assertUnprocessable();
    }

    public function test_error_page_is_french_and_does_not_claim_an_alert_was_sent(): void
    {
        config(['app.debug' => false]);
        Route::get('/__test-error', fn () => abort(500));
        $this->get('/__test-error')->assertStatus(500)->assertSee('Erreur serveur')->assertDontSee('Notre équipe a été informée');
    }
}
