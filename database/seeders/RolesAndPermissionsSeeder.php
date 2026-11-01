<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public const PERMISSIONS = [
        'admin.access',
        'infrastructures.create',
        'infrastructures.update',
        'infrastructures.delete',
        'incidents.manage',
        'incidents.report',
        'maintenances.manage',
        'users.manage',
        'ai.use',
    ];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (self::PERMISSIONS as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        // DatabaseSeeder désactive les événements de modèle : on vide le cache à la main.
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Role::findOrCreate(User::ROLE_ADMIN, 'web')->syncPermissions(self::PERMISSIONS);

        Role::findOrCreate(User::ROLE_TECHNICIEN, 'web')->syncPermissions([
            'admin.access', 'incidents.manage', 'incidents.report', 'maintenances.manage', 'ai.use',
        ]);

        Role::findOrCreate(User::ROLE_USER, 'web')->syncPermissions(['incidents.report', 'ai.use']);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
