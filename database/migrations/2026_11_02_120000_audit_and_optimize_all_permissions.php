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

        // 1. Helper function to safely merge permission A into permission B
        $mergePermissions = function (string $sourceName, string $targetName) {
            $source = Permission::where('name', $sourceName)->first();
            $target = Permission::firstOrCreate(['name' => $targetName, 'guard_name' => 'web']);

            if ($source) {
                // Migrate role assignments
                $roleAssignments = DB::table('role_has_permissions')
                    ->where('permission_id', $source->id)
                    ->get(['role_id'])
                    ->map(fn($row) => [
                        'permission_id' => $target->id,
                        'role_id' => $row->role_id,
                    ])
                    ->all();

                if (!empty($roleAssignments)) {
                    DB::table('role_has_permissions')->insertOrIgnore($roleAssignments);
                }

                // Migrate model assignments (users/seats)
                $modelAssignments = DB::table('model_has_permissions')
                    ->where('permission_id', $source->id)
                    ->get(['model_type', 'model_id'])
                    ->map(fn($row) => [
                        'permission_id' => $target->id,
                        'model_type' => $row->model_type,
                        'model_id' => $row->model_id,
                    ])
                    ->all();

                if (!empty($modelAssignments)) {
                    DB::table('model_has_permissions')->insertOrIgnore($modelAssignments);
                }

                // Clean up source permission
                DB::table('role_has_permissions')->where('permission_id', $source->id)->delete();
                DB::table('model_has_permissions')->where('permission_id', $source->id)->delete();
                $source->delete();
            }
        };

        // 2. Merge duplicate underscore permissions into canonical space-separated permissions
        $mergePermissions('view_own_inward_statistics', 'view own inward statistics');
        $mergePermissions('view_other_inward_statistics', 'view other inward statistics');
        $mergePermissions('view_all_inward_statistics', 'view all inward statistics');

        // 3. Merge redundant 'inward form' into 'create inward petitions'
        $mergePermissions('inward form', 'create inward petitions');

        // 4. Create missing granular permissions
        $newPermissions = [
            'delete inward petitions',
            'reassign inward petitions',
        ];

        foreach ($newPermissions as $permName) {
            Permission::firstOrCreate(['name' => $permName, 'guard_name' => 'web']);
        }

        // 5. Assign new granular permissions to authorized roles
        $authorizedRoles = ['Developer', 'developer', 'super admin', 'Super Admin', 'admin', 'Admin', 'Inward', 'inward'];
        $roles = Role::whereIn('name', $authorizedRoles)->get();
        foreach ($roles as $role) {
            $role->givePermissionTo($newPermissions);
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Recreate 'inward form'
        Permission::firstOrCreate(['name' => 'inward form', 'guard_name' => 'web']);

        // Remove new permissions
        Permission::whereIn('name', [
            'delete inward petitions',
            'reassign inward petitions',
        ])->delete();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
};

