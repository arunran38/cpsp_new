<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class DeveloperRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Create or retrieve Developer role
        $developerRole = Role::firstOrCreate([
            'name' => 'Developer',
            'guard_name' => 'web'
        ]);

        // 2. Assign all permissions in the system to Developer
        $allPermissions = Permission::all();
        $developerRole->syncPermissions($allPermissions);

        // Also ensure Super Admin retains its configured permissions
        $superAdmin = Role::where('name', 'super admin')->first();
        if ($superAdmin) {
            $superAdminPermissions = Permission::whereNotIn('name', [
                'forward t-section petitions',
                'export t-section petitions',
            ])->get();
            $superAdmin->syncPermissions($superAdminPermissions);
        }

        // Reset cache again
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
