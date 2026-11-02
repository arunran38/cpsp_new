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

        // 1. Create the new permission
        $permission = Permission::firstOrCreate([
            'name' => TSectionPermissionRegistry::VIEW_IN_REPORTS,
            'guard_name' => 'web',
        ]);

        // 2. Assign to Developer, Super Admin, and T Section roles
        $rolesToAssign = Role::whereIn('name', [
            'Developer',
            'developer',
            'super admin',
            'Super Admin',
            'T Section',
            't section',
            'T-Section',
            't-section',
        ])->get();

        foreach ($rolesToAssign as $role) {
            $role->givePermissionTo($permission);
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permission = Permission::where('name', TSectionPermissionRegistry::VIEW_IN_REPORTS)->first();
        if ($permission) {
            $permission->delete();
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
};

