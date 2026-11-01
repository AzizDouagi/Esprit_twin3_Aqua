<?php

namespace Tests\Feature;

use App\Models\Infrastructure;
use App\Models\Maintenance;
use App\Models\Mesure;
use App\Models\User;
use App\Notifications\MaintenanceRappelNotification;
use App\Services\MesureService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class InfrastructureAdvancedTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    public function test_show_page_displays_health_indicators_and_charts(): void
    {
        $infra = Infrastructure::factory()->create();
        Mesure::factory(5)->create(['infrastructure_id' => $infra->id, 'mesure_le' => now()->subHours(2)]);

        $this->actingAs($this->admin())->get(route('infrastructures.show', $infra))
            ->assertOk()
            ->assertSee('Indicateurs de santé')
            ->assertSee('chart-turbidite', false);
    }

    public function test_mesure_service_flags_out_of_threshold_values(): void
    {
        $infra = Infrastructure::factory()->create();
        Mesure::factory()->anormale()->create(['infrastructure_id' => $infra->id, 'mesure_le' => now()]);

        $indicateurs = collect(app(MesureService::class)->indicateurs($infra))->keyBy('cle');

        $this->assertSame('danger', $indicateurs['turbidite']['statut']);
        $this->assertSame('danger', $indicateurs['pression']['statut']);
    }

    public function test_csv_and_pdf_exports(): void
    {
        $infra = Infrastructure::factory()->create(['nom' => 'Réservoir Export']);
        $admin = $this->admin();

        $csv = $this->actingAs($admin)->get(route('infrastructures.export-csv'));
        $csv->assertOk()->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('Réservoir Export', $csv->streamedContent());

        $this->actingAs($admin)->get(route('infrastructures.export-pdf'))
            ->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->actingAs($admin)->get(route('infrastructures.fiche-pdf', $infra))
            ->assertOk()->assertHeader('content-type', 'application/pdf');
    }

    public function test_soft_delete_trash_and_restore(): void
    {
        $infra = Infrastructure::factory()->create();
        $admin = $this->admin();

        $this->actingAs($admin)->delete(route('infrastructures.destroy', $infra));
        $this->assertSoftDeleted($infra);
        $this->actingAs($admin)->get(route('infrastructures.corbeille'))->assertOk()->assertSee($infra->nom);

        $this->actingAs($admin)->post(route('infrastructures.restore', $infra->id));
        $this->assertNotSoftDeleted($infra);

        $this->actingAs($admin)->delete(route('infrastructures.destroy', $infra));
        $this->actingAs($admin)->delete(route('infrastructures.force-delete', $infra->id));
        $this->assertDatabaseMissing('infrastructures', ['id' => $infra->id]);
    }

    public function test_technicien_cannot_use_trash(): void
    {
        $tech = User::factory()->technicien()->create();
        $infra = Infrastructure::factory()->create();
        $infra->delete();

        $this->actingAs($tech)->get(route('infrastructures.corbeille'))->assertForbidden();
        $this->actingAs($tech)->post(route('infrastructures.restore', $infra->id))->assertForbidden();
    }

    public function test_map_page_lists_geolocated_infrastructures(): void
    {
        Infrastructure::factory()->create(['nom' => 'Point Carte', 'latitude' => 36.8, 'longitude' => 10.1]);

        $this->get(route('carte'))->assertOk()->assertSee('Point Carte')->assertSee('leaflet', false);
    }

    public function test_maintenance_planning_and_completion(): void
    {
        $infra = Infrastructure::factory()->create();
        $tech = User::factory()->technicien()->create();

        $this->actingAs($tech)->post(route('maintenances.store'), [
            'infrastructure_id' => $infra->id, 'technicien_id' => $tech->id, 'titre' => 'Contrôle pompes', 'date_prevue' => now()->addDays(2)->toDateString(),
        ])->assertSessionHasNoErrors();

        $maintenance = Maintenance::firstOrFail();
        $this->actingAs($tech)->get(route('maintenances.index', ['statut' => 'Planifiée']))->assertOk()->assertSee('Contrôle pompes');

        $this->actingAs($tech)->put(route('maintenances.update', $maintenance), ['statut' => 'Réalisée', 'cout' => 320.5]);
        $this->assertSame('Réalisée', $maintenance->fresh()->statut);
        $this->assertEquals(320.5, $maintenance->fresh()->cout);
    }

    public function test_simple_user_cannot_access_maintenance(): void
    {
        $user = User::factory()->simpleUser()->create();

        $this->actingAs($user)->get(route('maintenances.index'))->assertForbidden();
    }

    public function test_maintenance_reminder_command_notifies_once(): void
    {
        Notification::fake();
        $tech = User::factory()->technicien()->create();
        Maintenance::factory()->create(['technicien_id' => $tech->id, 'date_prevue' => now()->addDay()]);
        Maintenance::factory()->create(['date_prevue' => now()->addDays(30)]);

        $this->artisan('maintenances:rappels')->assertSuccessful();
        $this->artisan('maintenances:rappels')->assertSuccessful();

        Notification::assertSentToTimes($tech, MaintenanceRappelNotification::class, 1);
    }
}
