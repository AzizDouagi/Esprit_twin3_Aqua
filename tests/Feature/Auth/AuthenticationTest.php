<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_creates_user_with_role_and_sends_verification(): void
    {
        Notification::fake();
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->post('/register', [
            'name' => 'Nouvel Utilisateur',
            'email' => 'nouveau@example.test',
            'password' => 'Secret#12345',
            'password_confirmation' => 'Secret#12345',
        ])->assertRedirect(route('verification.notice'));

        $user = User::where('email', 'nouveau@example.test')->firstOrFail();
        $this->assertTrue($user->hasRole('user'));
        $this->assertNull($user->email_verified_at);
        Notification::assertSentTo($user, VerifyEmail::class);
        $this->assertAuthenticatedAs($user);
    }

    public function test_registration_validation_errors_are_in_french(): void
    {
        $this->from('/register')->post('/register', ['name' => '', 'email' => 'invalide', 'password' => 'x'])
            ->assertRedirect('/register')
            ->assertSessionHasErrors(['name', 'email', 'password']);

        $this->get('/register')->assertOk()->assertSee('Créer un compte');
    }

    public function test_user_can_login_and_logout(): void
    {
        $user = User::factory()->simpleUser()->create(['password' => 'Secret#12345']);

        $this->post('/login', ['email' => $user->email, 'password' => 'Secret#12345'])
            ->assertRedirect(route('espace.index'));
        $this->assertAuthenticatedAs($user);

        $this->post('/logout')->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_admin_is_redirected_to_dashboard_after_login(): void
    {
        $admin = User::factory()->admin()->create(['password' => 'Secret#12345']);

        $this->post('/login', ['email' => $admin->email, 'password' => 'Secret#12345'])
            ->assertRedirect(route('dashboard'));
    }

    public function test_invalid_credentials_show_french_error(): void
    {
        $user = User::factory()->create();

        $this->from('/login')->post('/login', ['email' => $user->email, 'password' => 'faux'])
            ->assertRedirect('/login')
            ->assertSessionHasErrors('email');

        $this->get('/login')->assertSee('Ces identifiants ne correspondent à aucun compte.');
        $this->assertGuest();
    }

    public function test_deactivated_account_cannot_login(): void
    {
        $user = User::factory()->create(['password' => 'Secret#12345', 'actif' => false]);

        $this->post('/login', ['email' => $user->email, 'password' => 'Secret#12345'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_login_is_throttled(): void
    {
        $user = User::factory()->create();

        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['email' => $user->email, 'password' => 'faux']);
        }

        $this->post('/login', ['email' => $user->email, 'password' => 'faux'])->assertStatus(429);
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/admin')->assertRedirect('/login');
        $this->get('/admin/infrastructures')->assertRedirect('/login');
        $this->get('/mon-espace')->assertRedirect('/login');
        $this->get('/profil')->assertRedirect('/login');
    }
}
