<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Exceptions\RoleDoesNotExist;
use Spatie\Permission\Models\Role;

class AlumkitUserSeeder extends Seeder
{
    public function run(): void
    {
        $userModel = config('alumkit.auth.user_model', 'App\\Models\\User');

        if (! class_exists($userModel)) {
            return;
        }

        $defaultRoles = config('permission.alumkit.default_roles', ['admin', 'moderator', 'member']);
        $adminRole = $defaultRoles[0] ?? 'admin';

        try {
            $role = Role::findByName($adminRole);
        } catch (RoleDoesNotExist) {
            return;
        }

        $user = $userModel::firstOrCreate(
            ['email' => config('alumkit.seeder.admin_email', 'admin@example.com')],
            [
                'password' => bcrypt(config('alumkit.seeder.admin_password', 'password')),
                'email_verified_at' => now(),
            ],
        );

        if (! $user->hasRole($role)) {
            $user->assignRole($role);
        }
    }
}
