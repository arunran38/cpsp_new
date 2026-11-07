<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $duplicateRestorePermissions = Permission::where('name', 'update recycle bin')->get();
        foreach ($duplicateRestorePermissions as $duplicate) {
            $restorePermission = Permission::firstOrCreate([
                'name' => 'restore recycle bin',
                'guard_name' => $duplicate->guard_name,
            ]);
            $roleAssignments = DB::table('role_has_permissions')
                ->where('permission_id', $duplicate->id)
                ->get(['role_id'])
                ->map(fn($assignment) => [
                    'permission_id' => $restorePermission->id,
                    'role_id' => $assignment->role_id,
                ])
                ->all();
            if ($roleAssignments) {
                DB::table('role_has_permissions')->insertOrIgnore($roleAssignments);
            }

            $userAssignments = DB::table('model_has_permissions')
                ->where('permission_id', $duplicate->id)
                ->get(['model_type', 'model_id'])
                ->map(fn($assignment) => [
                    'permission_id' => $restorePermission->id,
                    'model_type' => $assignment->model_type,
                    'model_id' => $assignment->model_id,
                ])
                ->all();
            if ($userAssignments) {
                DB::table('model_has_permissions')->insertOrIgnore($userAssignments);
            }

            DB::table('role_has_permissions')->where('permission_id', $duplicate->id)->delete();
            DB::table('model_has_permissions')->where('permission_id', $duplicate->id)->delete();
            $duplicate->delete();
        }

        $unusedPermission = Permission::where('name', 'default compliances home')->get();
        foreach ($unusedPermission as $permission) {
            DB::table('role_has_permissions')->where('permission_id', $permission->id)->delete();
            DB::table('model_has_permissions')->where('permission_id', $permission->id)->delete();
            $permission->delete();
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function down(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        Permission::firstOrCreate(['name' => 'update recycle bin', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'default compliances home', 'guard_name' => 'web']);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
