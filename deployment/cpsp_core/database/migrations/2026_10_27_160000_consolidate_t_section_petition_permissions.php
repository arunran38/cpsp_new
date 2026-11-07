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

        $mapping = [
            'view t-section petitions' => 'view petitions',
            'create t-section petitions' => 'create petitions',
            'update t-section petitions' => 'update petitions',
            'delete t-section petitions' => 'delete petitions',
        ];

        foreach ($mapping as $legacyPermName => $sharedPermName) {
            $sharedPerm = Permission::firstOrCreate(['name' => $sharedPermName, 'guard_name' => 'web']);
            $legacyPerm = Permission::where('name', $legacyPermName)->where('guard_name', 'web')->first();

            if ($legacyPerm) {
                // Ensure all roles with the legacy permission have the shared permission
                foreach ($legacyPerm->roles as $role) {
                    if (!$role->hasPermissionTo($sharedPermName)) {
                        $role->givePermissionTo($sharedPerm);
                    }
                }

                // Delete the redundant permission
                $legacyPerm->delete();
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

        $legacyPerms = [
            'view t-section petitions',
            'create t-section petitions',
            'update t-section petitions',
            'delete t-section petitions',
        ];

        foreach ($legacyPerms as $permName) {
            Permission::firstOrCreate(['name' => $permName, 'guard_name' => 'web']);
        }

        // Reassign to T Section role if it exists
        $tSection = Role::where('name', 'T Section')->where('guard_name', 'web')->first();
        if ($tSection) {
            $tSection->givePermissionTo([
                'view t-section petitions',
                'create t-section petitions',
                'update t-section petitions',
            ]);
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
