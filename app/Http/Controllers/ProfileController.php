<?php

namespace App\Http\Controllers;

use App\Actions\Fortify\PasswordValidationRules;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    use PasswordValidationRules;

    public function edit(Request $request): View
    {
        return view('profile.edit', ['user' => $request->user()]);
    }

    public function security(Request $request): View
    {
        $user = $request->user();

        return view('profile.security', [
            'user' => $user,
            'enabled' => filled($user->two_factor_secret),
            'confirmed' => filled($user->two_factor_confirmed_at),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
        ]);

        $emailChanged = strtolower($data['email']) !== strtolower($user->email);

        $user->fill($data);

        if ($emailChanged) {
            $user->email_verified_at = null;
        }

        $user->save();

        if ($emailChanged) {
            $user->sendEmailVerificationNotification();

            return redirect()->route('verification.notice')
                ->with('success', 'Email modifié : un nouveau lien de vérification vous a été envoyé.');
        }

        return back()->with('success', 'Profil mis à jour.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => $this->passwordRules(),
        ]);

        $request->user()->update(['password' => $request->input('password')]);

        return back()->with('success', 'Mot de passe modifié.');
    }

    public function updateAvatar(Request $request): RedirectResponse
    {
        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'avatar.max' => 'La photo ne doit pas dépasser 2 Mo.',
            'avatar.image' => 'Le fichier doit être une image (jpg, png ou webp).',
        ]);

        $user = $request->user();

        if ($user->avatar_path) {
            Storage::disk('public')->delete($user->avatar_path);
        }

        $user->update(['avatar_path' => $request->file('avatar')->store('avatars', 'public')]);

        return back()->with('success', 'Photo de profil mise à jour.');
    }

    public function destroyAvatar(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->avatar_path) {
            Storage::disk('public')->delete($user->avatar_path);
            $user->update(['avatar_path' => null]);
        }

        return back()->with('success', 'Photo de profil supprimée.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validate(['password' => ['required', 'current_password']]);

        $user = $request->user();

        if ($user->hasRole('admin') && User::role('admin')->count() <= 1) {
            return back()->withErrors(['password' => 'Vous êtes le dernier administrateur : suppression impossible.']);
        }

        Auth::logout();

        if ($user->avatar_path) {
            Storage::disk('public')->delete($user->avatar_path);
        }

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('front.home')->with('success', 'Votre compte a été supprimé.');
    }
}
