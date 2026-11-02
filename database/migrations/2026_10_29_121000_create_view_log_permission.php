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
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Create 'view log' permission
        $viewLogPerm = Permission::firstOrCreate([
            'name' => 'view log',
            'guard_name' => 'web',
        ]);

        // 2. Assign 'view log' to Developer, Super Admin, and Admin roles
        $targetRoles = Role::whereIn('name', [
            'developer',
            'Developer',
            'super admin',
            'Super Admin',
            'admin',
            'Admin',
        ])->get();

        foreach ($targetRoles as $role) {
            if (!$role->hasPermissionTo($viewLogPerm)) {
                $role->givePermissionTo($viewLogPerm);
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

        $viewLogPerm = Permission::where('name', 'view log')->where('guard_name', 'web')->first();
        if ($viewLogPerm) {
            $viewLogPerm->roles()->detach();
            $viewLogPerm->delete();
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
};

