<?php

namespace Tests\Feature;

use App\Models\Incident;
use App\Models\Infrastructure;
use App\Models\Mesure;
use App\Models\User;
use App\Services\DashboardStats;
use App\Services\RiskScoreService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_mttr_is_average_resolution_time_in_hours(): void
    {
        $a = Incident::factory()->create(['statut' => 'Ouvert']);
        $a->forceFill(['created_at' => now()->subHours(10), 'resolu_le' => now()->subHours(6)])->saveQuietly();
        $b = Incident::factory()->create(['statut' => 'Ouvert']);
        $b->forceFill(['created_at' => now()->subHours(10), 'resolu_le' => now()->subHours(2)])->saveQuietly();

        $this->assertSame(6.0, app(DashboardStats::class)->mttrHeures());
    }

    public function test_risk_score_rises_with_anomalies_and_incidents(): void
    {
        $sain = Infrastructure::factory()->create(['etat' => 'Fonctionnelle', 'date_installation' => now()]);
        Mesure::factory()->count(5)->create(['infrastructure_id' => $sain->id, 'mesure_le' => now()->subDay()]);

        $risque = Infrastructure::factory()->create(['etat' => 'Hors service', 'date_installation' => now()->subYears(12)]);
        Mesure::factory()->count(5)->anormale()->create(['infrastructure_id' => $risque->id, 'mesure_le' => now()->subDay()]);
        Incident::factory()->count(3)->create(['infrastructure_id' => $risque->id, 'gravite' => 'Critique', 'statut' => 'Ouvert', 'date_incident' => now()]);

        $service = app(RiskScoreService::class);
        $this->assertLessThan(20, $service->evaluer($sain)['score']);
        $this->assertGreaterThan(70, $service->evaluer($risque)['score']);
        $this->assertSame($risque->id, $service->classement(1)->first()->id);
    }

    public function test_dashboard_renders_for_admin(): void
    {
        Incident::factory()->count(3)->create();
        Infrastructure::factory()->count(2)->create();

        $this->actingAs(User::factory()->admin()->create())->get(route('dashboard'))
            ->assertOk()->assertSee('MTTR')->assertSee('Top infrastructures à risque');
    }
}
