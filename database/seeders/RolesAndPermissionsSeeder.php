<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        $admin = Role::firstOrCreate(
            ['name' => 'admin', 'guard_name' => 'sanctum']
        );

        $user = Role::firstOrCreate(
            ['name' => 'user', 'guard_name' => 'sanctum']
        );

        $permissions = Permission::all();
        $admin->syncPermissions($permissions);
    }
}

// php artisan migrate:fresh --seed