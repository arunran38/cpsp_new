<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Services\TSectionPermissionRegistry;

class TSectionDiagnosticsPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds to register T-Section diagnostics and pending details permissions.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

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

        // 2. Grant to Developer role (full access)
        $developer = Role::where('name', 'Developer')->first();
        if ($developer) {
            $developer->givePermissionTo($permissions);
        }

        // 3. Grant to T Section role
        $tSectionRole = Role::whereIn('name', ['T Section', 't section', 'T-Section', 't-section'])->first();
        if ($tSectionRole) {
            $tSectionRole->givePermissionTo($permissions);
        }

        // 4. Grant to Super Admin role
        $superAdmin = Role::where('name', 'super admin')->orWhere('name', 'Super Admin')->first();
        if ($superAdmin) {
            $superAdmin->givePermissionTo($permissions);
        }
    }
}
