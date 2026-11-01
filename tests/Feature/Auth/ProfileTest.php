<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->simpleUser()->create();

        $this->actingAs($user)->get('/profil')->assertOk()->assertSee($user->email);
    }

    public function test_name_update_keeps_verification(): void
    {
        $user = User::factory()->simpleUser()->create();

        $this->actingAs($user)->put('/profil', ['name' => 'Nouveau Nom', 'email' => $user->email])->assertSessionHasNoErrors();

        $this->assertSame('Nouveau Nom', $user->fresh()->name);
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }

    public function test_email_change_requires_reverification(): void
    {
        Notification::fake();
        $user = User::factory()->simpleUser()->create();

        $this->actingAs($user)->put('/profil', ['name' => $user->name, 'email' => 'autre@example.test'])
            ->assertRedirect(route('verification.notice'));

        $this->assertNull($user->fresh()->email_verified_at);
        Notification::assertSentTo($user->fresh(), VerifyEmail::class);
    }

    public function test_password_change_requires_current_password(): void
    {
        $user = User::factory()->create(['password' => 'Ancien#12345']);

        $this->actingAs($user)->put('/profil/mot-de-passe', [
            'current_password' => 'mauvais', 'password' => 'Nouveau#12345', 'password_confirmation' => 'Nouveau#12345',
        ])->assertSessionHasErrors('current_password');

        $this->actingAs($user)->put('/profil/mot-de-passe', [
            'current_password' => 'Ancien#12345', 'password' => 'Nouveau#12345', 'password_confirmation' => 'Nouveau#12345',
        ])->assertSessionHasNoErrors();

        $this->assertTrue(\Hash::check('Nouveau#12345', $user->fresh()->password));
    }

    public function test_avatar_upload_validation_and_deletion(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $this->actingAs($user)->post('/profil/avatar', ['avatar' => UploadedFile::fake()->create('doc.pdf', 10, 'application/pdf')])
            ->assertSessionHasErrors('avatar');
        $this->actingAs($user)->post('/profil/avatar', ['avatar' => UploadedFile::fake()->image('big.png')->size(3000)])
            ->assertSessionHasErrors('avatar');

        $this->actingAs($user)->post('/profil/avatar', ['avatar' => UploadedFile::fake()->image('moi.png', 200, 200)])
            ->assertSessionHasNoErrors();
        $path = $user->fresh()->avatar_path;
        Storage::disk('public')->assertExists($path);

        $this->actingAs($user)->delete('/profil/avatar');
        Storage::disk('public')->assertMissing($path);
        $this->assertNull($user->fresh()->avatar_path);
    }

    public function test_initials_are_used_without_avatar(): void
    {
        $user = User::factory()->make(['name' => 'Aziz Douagi']);
        $this->assertSame('AD', $user->initiales());
    }

    public function test_account_deletion_requires_password(): void
    {
        $user = User::factory()->simpleUser()->create(['password' => 'Secret#12345']);

        $this->actingAs($user)->delete('/profil', ['password' => 'faux'])->assertSessionHasErrors('password');
        $this->assertModelExists($user);

        $this->actingAs($user)->delete('/profil', ['password' => 'Secret#12345'])->assertRedirect(route('front.home'));
        $this->assertModelMissing($user);
        $this->assertGuest();
    }

    public function test_last_admin_cannot_delete_account(): void
    {
        $admin = User::factory()->admin()->create(['password' => 'Secret#12345']);

        $this->actingAs($admin)->delete('/profil', ['password' => 'Secret#12345'])->assertSessionHasErrors('password');
        $this->assertModelExists($admin);
    }
}
