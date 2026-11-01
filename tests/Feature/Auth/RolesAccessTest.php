<?php

namespace Tests\Feature\Auth;

use App\Models\Infrastructure;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RolesAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_simple_user_gets_403_on_backoffice(): void
    {
        $user = User::factory()->simpleUser()->create();

        $this->actingAs($user)->get('/admin')->assertForbidden();
        $this->actingAs($user)->get('/admin/infrastructures')->assertForbidden();
        $this->actingAs($user)->get('/admin/incidents')->assertForbidden();
        $this->actingAs($user)->get('/admin/utilisateurs')->assertForbidden();
    }

    public function test_simple_user_cannot_write_via_direct_requests(): void
    {
        $user = User::factory()->simpleUser()->create();
        $infra = Infrastructure::factory()->create();

        $this->actingAs($user)->post('/admin/infrastructures', [])->assertForbidden();
        $this->actingAs($user)->delete("/admin/infrastructures/{$infra->id}")->assertForbidden();
        $this->assertDatabaseHas('infrastructures', ['id' => $infra->id]);
    }

    public function test_admin_can_access_everything(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get('/admin')->assertOk();
        $this->actingAs($admin)->get('/admin/utilisateurs')->assertOk();
        $this->actingAs($admin)->get('/admin/infrastructures/create')->assertOk();
    }

    public function test_technicien_manages_incidents_but_not_infrastructures_or_users(): void
    {
        $tech = User::factory()->technicien()->create();
        $infra = Infrastructure::factory()->create();

        $this->actingAs($tech)->get('/admin')->assertOk();
        $this->actingAs($tech)->get('/admin/incidents')->assertOk();
        $this->actingAs($tech)->get('/admin/infrastructures')->assertOk();
        $this->actingAs($tech)->get('/admin/infrastructures/create')->assertForbidden();
        $this->actingAs($tech)->delete("/admin/infrastructures/{$infra->id}")->assertForbidden();
        $this->actingAs($tech)->get('/admin/utilisateurs')->assertForbidden();
    }

    public function test_front_office_is_public(): void
    {
        $this->get('/')->assertOk();
        $this->get('/infrastructures')->assertOk();
    }

    public function test_custom_error_pages(): void
    {
        $this->get('/page-inexistante')->assertNotFound()->assertSee('Page introuvable');
        $user = User::factory()->simpleUser()->create();
        $this->actingAs($user)->get('/admin')->assertForbidden()->assertSee('Accès refusé');
    }
}
