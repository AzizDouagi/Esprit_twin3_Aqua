<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_unverified_user_is_redirected_to_notice(): void
    {
        $user = User::factory()->simpleUser()->unverified()->create();

        $this->actingAs($user)->get('/mon-espace')->assertRedirect(route('verification.notice'));
        $this->actingAs($user)->get('/email/verify')->assertOk()->assertSee('Vérifiez votre adresse email');
    }

    public function test_unverified_admin_cannot_access_backoffice(): void
    {
        $user = User::factory()->admin()->unverified()->create();

        $this->actingAs($user)->get('/admin')->assertRedirect(route('verification.notice'));
    }

    public function test_email_can_be_verified_with_signed_link(): void
    {
        Event::fake([Verified::class]);
        $user = User::factory()->simpleUser()->unverified()->create();

        $url = URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), [
            'id' => $user->id, 'hash' => sha1($user->email),
        ]);

        $this->actingAs($user)->get($url)->assertRedirect(route('espace.index').'?verified=1');
        Event::assertDispatched(Verified::class);
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }

    public function test_invalid_or_expired_link_is_rejected(): void
    {
        $user = User::factory()->unverified()->create();

        $expired = URL::temporarySignedRoute('verification.verify', now()->subMinute(), [
            'id' => $user->id, 'hash' => sha1($user->email),
        ]);

        $this->actingAs($user)->get($expired)->assertForbidden();
        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }

    public function test_verification_link_can_be_resent(): void
    {
        Notification::fake();
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)->post('/email/verification-notification')->assertSessionHas('status');
        Notification::assertSentTo($user, VerifyEmail::class);
    }
}
