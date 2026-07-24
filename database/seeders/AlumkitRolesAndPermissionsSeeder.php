<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class AlumkitRolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $defaultPermissions = config('permission.alumkit.default_permissions', [
            'manage roles',
            'manage permissions',
            'manage members',
            'view dashboard',
        ]);

        foreach ($defaultPermissions as $permission) {
            Permission::findOrCreate($permission);
        }

        $defaultRoles = config('permission.alumkit.default_roles', ['admin', 'moderator', 'member']);

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
