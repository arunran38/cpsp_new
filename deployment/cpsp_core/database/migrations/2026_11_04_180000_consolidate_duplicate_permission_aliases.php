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

        $permissionAliases = [
            'inward statistics' => 'view inward statistics',
            'inward_statistics' => 'view inward statistics',
            'view_inward_statistics' => 'view inward statistics',
            'view_own_inward_statistics' => 'view own inward statistics',
            'view_other_inward_statistics' => 'view other inward statistics',
            'view_all_inward_statistics' => 'view all inward statistics',
            'pendency details' => 'view pendency details',
            'pendency_details' => 'view pendency details',
            'view_pendency_details' => 'view pendency details',
            'view department wise report' => 'view department-wise report',
            'view_department_wise_report' => 'view department-wise report',
            'edit petitions' => 'update petitions',
            'edit_petitions' => 'update petitions',
            'inward form' => 'create inward petitions',
            'inward_form' => 'create inward petitions',
            'access cpsp' => 'access cpsp section',
            'access_cpsp' => 'access cpsp section',
            'access_cpsp_section' => 'access cpsp section',
            'access t section' => 'access t-section',
            'access_t_section' => 'access t-section',
            'access_t_section_dashboard' => 'access t-section dashboard',
            'create_inward_petitions' => 'create inward petitions',
            'view_inward_registration' => 'view inward registration',
            'export_petitions' => 'export petitions',
            'export_statistics' => 'export statistics',
            'update recycle bin' => 'restore recycle bin',
            'update_recycle_bin' => 'restore recycle bin',
        ];

        foreach ($permissionAliases as $alias => $canonicalName) {
            $aliases = Permission::where('name', $alias)->get();

            foreach ($aliases as $permission) {
                $canonical = Permission::firstOrCreate([
                    'name' => $canonicalName,
                    'guard_name' => $permission->guard_name,
                ]);

                $roleAssignments = DB::table('role_has_permissions')
                    ->where('permission_id', $permission->id)
                    ->get(['role_id'])
                    ->map(fn ($assignment) => [
                        'permission_id' => $canonical->id,
                        'role_id' => $assignment->role_id,
                    ])
                    ->all();
                if ($roleAssignments) {
                    DB::table('role_has_permissions')->insertOrIgnore($roleAssignments);
                }

                $modelAssignments = DB::table('model_has_permissions')
                    ->where('permission_id', $permission->id)
                    ->get(['model_type', 'model_id'])
                    ->map(fn ($assignment) => [
                        'permission_id' => $canonical->id,
                        'model_type' => $assignment->model_type,
                        'model_id' => $assignment->model_id,
                    ])
                    ->all();
                if ($modelAssignments) {
                    DB::table('model_has_permissions')->insertOrIgnore($modelAssignments);
                }

                DB::table('role_has_permissions')->where('permission_id', $permission->id)->delete();
                DB::table('model_has_permissions')->where('permission_id', $permission->id)->delete();
                $permission->delete();
            }
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function down(): void
    {
    }
};
