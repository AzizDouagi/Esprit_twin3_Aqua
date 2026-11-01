<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Comptes de démonstration (déjà vérifiés). Mot de passe de test : Password#2026
     */
    public const DEMO_PASSWORD = 'Password#2026';

    public function run(): void
    {
        $comptes = [
            ['Administrateur AquaSecure', 'admin@aquasecure.test', User::ROLE_ADMIN],
            ['Karim Technicien', 'technicien@aquasecure.test', User::ROLE_TECHNICIEN],
            ['Sonia Utilisatrice', 'user@aquasecure.test', User::ROLE_USER],
        ];

        foreach ($comptes as [$name, $email, $role]) {
            $user = User::updateOrCreate(
                ['email' => $email],
                ['name' => $name, 'password' => self::DEMO_PASSWORD, 'actif' => true],
            );
            $user->forceFill(['email_verified_at' => now()])->save();
            $user->syncRoles($role);
        }
    }
}
