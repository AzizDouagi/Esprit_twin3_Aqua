<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_link_is_sent_and_password_can_be_reset(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email])->assertSessionHas('status');
        Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use ($user) {
            $this->get('/reset-password/'.$notification->token.'?email='.urlencode($user->email))
                ->assertOk()->assertSee('Nouveau mot de passe');

            $this->post('/reset-password', [
                'token' => $notification->token,
                'email' => $user->email,
                'password' => 'NouveauPass#2026',
                'password_confirmation' => 'NouveauPass#2026',
            ])->assertRedirect('/login');

            return true;
        });

        $this->post('/login', ['email' => $user->email, 'password' => 'NouveauPass#2026']);
        $this->assertAuthenticatedAs($user);
    }

    public function test_expired_token_is_rejected(): void
    {
        $user = User::factory()->create();
        $token = Password::createToken($user);

        $this->travel(config('auth.passwords.users.expire') + 1)->minutes();

        $this->from('/forgot-password')->post('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'NouveauPass#2026',
            'password_confirmation' => 'NouveauPass#2026',
        ])->assertSessionHasErrors('email');
    }

    public function test_expiration_is_configured(): void
    {
        $this->assertGreaterThan(0, config('auth.passwords.users.expire'));
    }
}
