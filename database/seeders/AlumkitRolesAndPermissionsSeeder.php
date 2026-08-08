<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Database\Seeders;

use Alumkit\Alumkit\Alumkit;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class AlumkitRolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Package permissions — always seeded, cannot be removed by the consumer app.
        foreach (Alumkit::PERMISSIONS as $permission) {
            Permission::findOrCreate($permission);
        }

        // Consumer app extensions via config('alumkit.permission.permissions').
        foreach ((array) config('alumkit.permission.permissions', []) as $permission) {
            Permission::findOrCreate($permission);
        }

        $defaultRoles = config('alumkit.permission.default_roles', ['admin', 'moderator', 'member']);

        foreach ($defaultRoles as $roleName) {
            Role::findOrCreate($roleName);
        }

        $adminRole = Role::findByName($defaultRoles[0] ?? 'admin');
        $adminRole->givePermissionTo(Permission::all());

        if (isset($defaultRoles[1])) {
            $moderatorRole = Role::findByName($defaultRoles[1]);
            $moderatorRole->givePermissionTo(['manage members', 'view dashboard']);
        }

        if (isset($defaultRoles[2])) {
            Role::findByName($defaultRoles[2]);
        }
    }
}
