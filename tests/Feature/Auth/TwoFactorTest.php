<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class TwoFactorTest extends TestCase
{
    use RefreshDatabase;

    private function enableAndConfirm(User $user): string
    {
        $this->actingAs($user)->post('/user/two-factor-authentication')->assertRedirect();
        $user->refresh();
        $this->assertNotNull($user->two_factor_secret);

        $secret = decrypt($user->two_factor_secret);
        $code = app(Google2FA::class)->getCurrentOtp($secret);

        $this->actingAs($user)->post('/user/confirmed-two-factor-authentication', ['code' => $code])->assertRedirect();
        $this->assertNotNull($user->fresh()->two_factor_confirmed_at);

        return $secret;
    }

    public function test_security_page_displays_qr_code_and_recovery_codes(): void
    {
        $user = User::factory()->simpleUser()->create();

        $this->actingAs($user)->get('/profil/securite')->assertOk()->assertSee('Activer la 2FA');

        $this->actingAs($user)->post('/user/two-factor-authentication');
        $this->actingAs($user)->get('/profil/securite')->assertOk()->assertSee('<svg', false);

        $this->enableAndConfirm($user->fresh());
        $this->actingAs($user->fresh())->get('/profil/securite')->assertOk()->assertSee('Codes de récupération');
        $this->assertCount(8, $user->fresh()->recoveryCodes());
    }

    public function test_login_requires_two_factor_challenge_with_code(): void
    {
        $user = User::factory()->simpleUser()->create(['password' => 'Secret#12345']);
        $secret = $this->enableAndConfirm($user);
        auth()->logout();

        $this->post('/login', ['email' => $user->email, 'password' => 'Secret#12345'])
            ->assertRedirect('/two-factor-challenge');
        $this->assertGuest();

        $this->get('/two-factor-challenge')->assertOk()->assertSee('Double authentification');

        $google2fa = app(Google2FA::class);
        $nextStepCode = $google2fa->oathTotp($secret, $google2fa->getTimestamp() + 1);

        $this->post('/two-factor-challenge', ['code' => $nextStepCode])
            ->assertRedirect(route('espace.index'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_recovery_code_works_and_wrong_code_fails(): void
    {
        $user = User::factory()->simpleUser()->create(['password' => 'Secret#12345']);
        $this->enableAndConfirm($user);
        $recovery = $user->fresh()->recoveryCodes()[0];
        auth()->logout();

        $this->post('/login', ['email' => $user->email, 'password' => 'Secret#12345']);
        $this->from('/two-factor-challenge')->post('/two-factor-challenge', ['code' => '000000'])
            ->assertSessionHasErrors('code');
        $this->assertGuest();

        $this->post('/two-factor-challenge', ['recovery_code' => $recovery])->assertRedirect();
        $this->assertAuthenticatedAs($user);
    }

    public function test_recovery_codes_can_be_regenerated_and_2fa_disabled(): void
    {
        $user = User::factory()->simpleUser()->create();
        $this->enableAndConfirm($user);
        $old = $user->fresh()->recoveryCodes();

        $this->actingAs($user)->post('/user/two-factor-recovery-codes')->assertRedirect();
        $this->assertNotEquals($old, $user->fresh()->recoveryCodes());

        $this->actingAs($user)->delete('/user/two-factor-authentication')->assertRedirect();
        $this->assertNull($user->fresh()->two_factor_secret);
        $this->assertNull($user->fresh()->two_factor_confirmed_at);
    }
}
