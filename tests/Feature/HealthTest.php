<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class HealthTest extends TestCase
{
    public function test_liveness_and_readiness_are_public(): void
    {
        $this->get('/up')->assertOk();
        $this->getJson('/health')->assertOk()->assertExactJson(['status' => 'ok', 'database' => 'ok']);
    }

    public function test_readiness_returns_503_without_database_credentials_on_failure(): void
    {
        DB::shouldReceive('select')->once()->with('SELECT 1')->andThrow(new \RuntimeException('sensitive database credentials'));
        $this->getJson('/health')->assertStatus(503)->assertExactJson(['status' => 'unavailable', 'database' => 'unavailable'])->assertDontSee('sensitive');
    }
}
