<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use App\Services\TSectionPermissionRegistry;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            TSectionPermissionRegistry::VIEW_CPSP_READONLY,
            TSectionPermissionRegistry::VIEW_ASSIGNED_INWARD,
            TSectionPermissionRegistry::LINK_RELATED,
            TSectionPermissionRegistry::VIEW_LINKED_REFERENCES,
        ];

        $createdPermissions = [];
        foreach ($permissions as $permName) {
            $createdPermissions[] = Permission::firstOrCreate([
                'name' => $permName,
                'guard_name' => 'web',
            ]);
        }

        $rolesToAssign = Role::whereIn('name', [
            'Developer',
            'developer',
            'super admin',
            'Super Admin',
            'admin',
            'Admin',
            'T Section',
            't section',
            'T-Section',
            't-section',
        ])->get();

        foreach ($rolesToAssign as $role) {
            foreach ($createdPermissions as $permission) {
                $role->givePermissionTo($permission);
            }
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
            TSectionPermissionRegistry::VIEW_CPSP_READONLY,
            TSectionPermissionRegistry::VIEW_ASSIGNED_INWARD,
            TSectionPermissionRegistry::LINK_RELATED,
            TSectionPermissionRegistry::VIEW_LINKED_REFERENCES,
        ];

        Permission::whereIn('name', $permissions)->delete();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
