<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Redundant / non-existent CRUD permissions to remove
        $permissionsToDelete = [
            'create master reports',
            'update master reports',
            'delete master reports',
            'create seat diagnostics',
            'update seat diagnostics',
            'delete seat diagnostics',
            'create recycle bin',
            'create seat distribution chart',
            'update seat distribution chart',
            'delete seat distribution chart',
            'create petition status chart',
            'update petition status chart',
            'delete petition status chart',
            'create petition trends chart',
            'update petition trends chart',
            'delete petition trends chart',
            'create petition nature chart',
            'update petition nature chart',
            'delete petition nature chart',
        ];

        foreach ($permissionsToDelete as $permName) {
            $permission = Permission::where('name', $permName)->first();
            if ($permission) {
                DB::table('role_has_permissions')->where('permission_id', $permission->id)->delete();
                DB::table('model_has_permissions')->where('permission_id', $permission->id)->delete();
                $permission->delete();
            }
        }

        // 2. Essential missing permissions to create
        $permissionsToAdd = [
            'view duplicate warnings',
            'link petitions',
            'restore recycle bin',
            'update recycle bin',
        ];

        foreach ($permissionsToAdd as $permName) {
            Permission::firstOrCreate(['name' => $permName, 'guard_name' => 'web']);
        }

        // 3. Sync Roles
        $superAdmin = Role::whereIn('name', ['super admin', 'Super Admin'])->first();
        if ($superAdmin) {
            $superAdmin->syncPermissions(Permission::all());
        }

        $admin = Role::whereIn('name', ['admin', 'Admin'])->first();
        if ($admin) {
            $adminPermissions = Permission::whereNotIn('name', [
                'delete roles',
                'delete permissions',
            ])->get();
            $admin->syncPermissions($adminPermissions);
        }

        $cpsp = Role::where('name', 'CPSP')->first();
        if ($cpsp) {
            $cpsp->givePermissionTo([
                'view duplicate warnings',
                'link petitions',
            ]);
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
