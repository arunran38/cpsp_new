<?php

use Illuminate\Database\Migrations\Migration;
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
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'view own inward statistics',
            'view other inward statistics',
            'view all inward statistics',
            'view_own_inward_statistics',
            'view_other_inward_statistics',
            'view_all_inward_statistics',
        ];

        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate(['name' => $permissionName, 'guard_name' => 'web']);
        }

        // Assign full permissions to Super Admin and Developer
        $superAdminRoles = Role::whereIn('name', ['super admin', 'Super Admin', 'Developer', 'developer'])->get();
        foreach ($superAdminRoles as $role) {
            $role->givePermissionTo($permissions);
        }

        // Assign all permissions to Admin
        $adminRoles = Role::whereIn('name', ['admin', 'Admin'])->get();
        foreach ($adminRoles as $role) {
            $role->givePermissionTo($permissions);
        }

        // Assign own statistics view to Inward and CPSP roles
        $inwardRoles = Role::whereIn('name', ['inward', 'Inward'])->get();
        foreach ($inwardRoles as $role) {
            $role->givePermissionTo([
                'view own inward statistics',
                'view_own_inward_statistics',
            ]);
        }

        $cpspRoles = Role::whereIn('name', ['cpsp', 'CPSP'])->get();
        foreach ($cpspRoles as $role) {
            $role->givePermissionTo([
                'view own inward statistics',
                'view_own_inward_statistics',
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

        $permissions = [
            'view own inward statistics',
            'view other inward statistics',
            'view all inward statistics',
            'view_own_inward_statistics',
            'view_other_inward_statistics',
            'view_all_inward_statistics',
        ];

        Permission::whereIn('name', $permissions)->delete();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
};

