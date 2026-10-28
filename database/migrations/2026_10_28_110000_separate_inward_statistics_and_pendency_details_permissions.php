<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Create the separate 'view pendency details' permission
        $pendencyPerm = Permission::firstOrCreate([
            'name' => 'view pendency details',
            'guard_name' => 'web',
        ]);

        // 2. Ensure roles/models that currently have 'inward statistics' also receive 'view pendency details'
        $inwardStatsPerm = Permission::where('name', 'inward statistics')->where('guard_name', 'web')->first();
        if ($inwardStatsPerm) {
            $roleAssignments = DB::table('role_has_permissions')
                ->where('permission_id', $inwardStatsPerm->id)
                ->get(['role_id'])
                ->map(fn($assignment) => [
                    'permission_id' => $pendencyPerm->id,
                    'role_id' => $assignment->role_id,
                ])
                ->all();

            if (!empty($roleAssignments)) {
                DB::table('role_has_permissions')->insertOrIgnore($roleAssignments);
            }

            $userAssignments = DB::table('model_has_permissions')
                ->where('permission_id', $inwardStatsPerm->id)
                ->get(['model_type', 'model_id'])
                ->map(fn($assignment) => [
                    'permission_id' => $pendencyPerm->id,
                    'model_type' => $assignment->model_type,
                    'model_id' => $assignment->model_id,
                ])
                ->all();

            if (!empty($userAssignments)) {
                DB::table('model_has_permissions')->insertOrIgnore($userAssignments);
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

        $pendencyPerm = Permission::where('name', 'view pendency details')->first();
        if ($pendencyPerm) {
            DB::table('role_has_permissions')->where('permission_id', $pendencyPerm->id)->delete();
            DB::table('model_has_permissions')->where('permission_id', $pendencyPerm->id)->delete();
            $pendencyPerm->delete();
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
