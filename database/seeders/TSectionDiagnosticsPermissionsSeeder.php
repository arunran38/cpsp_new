<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use App\Services\TSectionPermissionRegistry;

class TSectionDiagnosticsPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds to register T-Section diagnostics and pending details permissions.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Create the new granular permissions
        $permissions = [
            TSectionPermissionRegistry::VIEW_DIAGNOSTICS,
            TSectionPermissionRegistry::VIEW_PENDING_DETAILS,
        ];

        foreach ($permissions as $permName) {
            Permission::firstOrCreate([
                'name' => $permName,
                'guard_name' => 'web',
            ]);
        }

        // 2. Grant to Developer and super admin roles
        foreach (['Developer', 'super admin'] as $roleName) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $role->givePermissionTo($permissions);
            }
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
