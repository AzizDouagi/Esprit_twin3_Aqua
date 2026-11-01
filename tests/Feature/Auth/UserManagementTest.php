<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_change_role_toggle_and_delete(): void
    {
        $admin = User::factory()->admin()->create();
        $target = User::factory()->simpleUser()->create();

        $this->actingAs($admin)->put("/admin/utilisateurs/{$target->id}/role", ['role' => 'technicien'])->assertSessionHasNoErrors();
        $this->assertTrue($target->fresh()->hasRole('technicien'));

        $this->actingAs($admin)->put("/admin/utilisateurs/{$target->id}/actif");
        $this->assertFalse($target->fresh()->actif);

        $this->actingAs($admin)->delete("/admin/utilisateurs/{$target->id}");
        $this->assertModelMissing($target);
    }

    public function test_admin_cannot_demote_disable_or_delete_self(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->put("/admin/utilisateurs/{$admin->id}/role", ['role' => 'user'])->assertSessionHasErrors('role');
        $this->actingAs($admin)->put("/admin/utilisateurs/{$admin->id}/actif")->assertSessionHasErrors('actif');
        $this->actingAs($admin)->delete("/admin/utilisateurs/{$admin->id}")->assertSessionHasErrors('user');

        $this->assertTrue($admin->fresh()->hasRole('admin'));
        $this->assertTrue($admin->fresh()->actif);
        $this->assertModelExists($admin);
    }

    public function test_invalid_role_is_rejected(): void
    {
        $admin = User::factory()->admin()->create();
        $target = User::factory()->simpleUser()->create();

        $this->actingAs($admin)->put("/admin/utilisateurs/{$target->id}/role", ['role' => 'root'])->assertSessionHasErrors('role');
    }
}
