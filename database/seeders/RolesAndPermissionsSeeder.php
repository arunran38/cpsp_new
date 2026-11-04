<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use App\Services\TSectionPermissionRegistry;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 0. Consolidate & Clean Up Legacy/Redundant Duplicate Permissions
        $redundantPermissionMap = [
            'inward statistics' => 'view inward statistics',
            'inward_statistics' => 'view inward statistics',
            'view_inward_statistics' => 'view inward statistics',
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
            'view_own_inward_statistics' => 'view own inward statistics',
            'view_other_inward_statistics' => 'view other inward statistics',
            'view_all_inward_statistics' => 'view all inward statistics',
            'update recycle bin' => 'restore recycle bin',
            'update_recycle_bin' => 'restore recycle bin',
        ];

        foreach ($redundantPermissionMap as $redundantName => $canonicalName) {
            $redundantPerms = Permission::where('name', $redundantName)->get();

            foreach ($redundantPerms as $redundantPerm) {
                $canonicalPerm = Permission::firstOrCreate([
                    'name' => $canonicalName,
                    'guard_name' => $redundantPerm->guard_name,
                ]);

                // Re-link role assignments to canonical permission
                $roleIds = \Illuminate\Support\Facades\DB::table('role_has_permissions')
                    ->where('permission_id', $redundantPerm->id)
                    ->pluck('role_id');
                foreach ($roleIds as $roleId) {
                    \Illuminate\Support\Facades\DB::table('role_has_permissions')->insertOrIgnore([
                        'permission_id' => $canonicalPerm->id,
                        'role_id' => $roleId,
                    ]);
                }

                // Re-link direct model/user assignments to canonical permission
                $userAssignments = \Illuminate\Support\Facades\DB::table('model_has_permissions')
                    ->where('permission_id', $redundantPerm->id)
                    ->get();
                foreach ($userAssignments as $assignment) {
                    \Illuminate\Support\Facades\DB::table('model_has_permissions')->insertOrIgnore([
                        'permission_id' => $canonicalPerm->id,
                        'model_type' => $assignment->model_type,
                        'model_id' => $assignment->model_id,
                    ]);
                }

                \Illuminate\Support\Facades\DB::table('role_has_permissions')->where('permission_id', $redundantPerm->id)->delete();
                \Illuminate\Support\Facades\DB::table('model_has_permissions')->where('permission_id', $redundantPerm->id)->delete();
                $redundantPerm->delete();
            }
        }

        // 1. Standard CRUD Entities
        $crudEntities = [
            'users',
            'units',
            'departments',
            'designations',
            'seats',
            'petitions',
            'roles',
            'permissions',
            'compliances',
        ];

        $actions = ['view', 'create', 'update', 'delete'];

        foreach ($crudEntities as $entity) {
            foreach ($actions as $action) {
                Permission::firstOrCreate(['name' => "{$action} {$entity}", 'guard_name' => 'web']);
            }
        }

        // 2. Specialized Functional Permissions
        $specialPermissions = [
            // Section-Level Clearances
            'access cpsp section',
            'access t-section',

            // Scopes & Dashboard
            'access admin dashboard',
            'view own petitions',
            'view all petitions',
            'view recent petitions',

            // Duplicate Detection & Linking
            'view duplicate warnings',
            'link petitions',

            // Inward Desk & Transfers
            'view inward registration',
            'create inward petitions',
            'delete inward petitions',
            'reassign inward petitions',
            'view file transfer',
            'filter transfer',

            // Petition Operations (Granular PBAC)
            'forward petitions',
            'record decisions',
            'return petitions to inward',
            'pullback forwardings',
            'pullback decisions',
            'manage petition drafts',
            'export petitions',

            // Reports & Operational Statistics
            'view master reports',
            'view inward statistics',
            'view own inward statistics',
            'view other inward statistics',
            'view all inward statistics',
            'view pendency details',
            'view department-wise report',
            'export statistics',
            'view processed statistics',

            // Visual Analytics Charts (View-only)
            'view seat distribution chart',
            'view petition status chart',
            'view petition trends chart',
            'view petition nature chart',

            // System Health & Recovery
            'view seat diagnostics',
            'view recycle bin',
            'restore recycle bin',
            'delete recycle bin', // Permanent purge
            'view log', // System Audit Trail Clearance

            // T-Section (Confidential / Secret Section) Permissions
            ...TSectionPermissionRegistry::all(),
        ];

        foreach ($specialPermissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // -------------------------------------------------------------
        // Roles Creation & Permissions Assignment
        // -------------------------------------------------------------

        // 1. Developer: Highest level access with all permissions
        $developer = Role::firstOrCreate(['name' => 'Developer', 'guard_name' => 'web']);

        // 2. Super Admin: General administrative oversight
        $superAdmin = Role::firstOrCreate(['name' => 'super admin', 'guard_name' => 'web']);

        // Cleanup legacy capitalized 'Super Admin' if it exists as a separate record
        $legacySuperAdmin = Role::where('name', 'Super Admin')->first();
        if ($legacySuperAdmin && $legacySuperAdmin->id !== $superAdmin->id) {
            \Illuminate\Support\Facades\DB::table('model_has_roles')
                ->where('role_id', $legacySuperAdmin->id)
                ->update(['role_id' => $superAdmin->id]);
            $legacySuperAdmin->syncPermissions([]);
            $legacySuperAdmin->delete();
        }

        // Standard System Roles
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Inward', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'CPSP', 'guard_name' => 'web']);

        $petitionCompliances = Role::where('name', 'Petition Compliances')->first()
            ?? Role::where('name', 'IOP HQ')->first();
        if ($petitionCompliances) {
            $petitionCompliances->update(['name' => 'Petition Compliances']);
        } else {
            Role::firstOrCreate(['name' => 'Petition Compliances', 'guard_name' => 'web']);
        }

        Role::firstOrCreate(['name' => 'T Section', 'guard_name' => 'web']);

        $allPermissions = Permission::pluck('name')->toArray();

        // Exact verified role-permission mappings from Role Management
        $rolePermissions = [
            'Developer' => $allPermissions,
            'super admin' => $allPermissions,

            'admin' => [
                'access admin dashboard',
                'access cpsp section',
                'access t-section',
                'access t-section dashboard',
                'create compliances',
                'create departments',
                'create designations',
                'create inward petitions',
                'create permissions',
                'create petitions',
                'create roles',
                'create seats',
                'create units',
                'create users',
                'delete compliances',
                'delete departments',
                'delete designations',
                'delete inward petitions',
                'delete permissions',
                'delete petitions',
                'delete recycle bin',
                'delete roles',
                'delete seats',
                'delete units',
                'delete users',
                'export petitions',
                'export statistics',
                'export t-section petitions',
                'filter transfer',
                'forward petitions',
                'forward t-section petitions',
                'link petitions',
                'manage petition drafts',
                'pullback decisions',
                'pullback forwardings',
                'reassign inward petitions',
                'record decisions',
                'restore recycle bin',
                'return petitions to inward',
                'update compliances',
                'update departments',
                'update designations',
                'update permissions',
                'update petitions',
                'update roles',
                'update seats',
                'update units',
                'update users',
                'view all inward statistics',
                'view all petitions',
                'view compliances',
                'view department-wise report',
                'view departments',
                'view designations',
                'view duplicate warnings',
                'view file transfer',
                'view inward registration',
                'view inward statistics',
                'view log',
                'view master reports',
                'view other inward statistics',
                'view own inward statistics',
                'view own petitions',
                'view pendency details',
                'view permissions',
                'view petition nature chart',
                'view petition status chart',
                'view petition trends chart',
                'view petitions',
                'view processed statistics',
                'view recent petitions',
                'view recycle bin',
                'view roles',
                'view seat diagnostics',
                'view seat distribution chart',
                'view seats',
                'view t-section diagnostics',
                'view t-section in reports',
                'view t-section pending details',
                'view units',
                'view users',
            ],

            'CPSP' => [
                'access cpsp section',
                'create petitions',
                'export petitions',
                'export statistics',
                'forward petitions',
                'link petitions',
                'manage petition drafts',
                'pullback decisions',
                'pullback forwardings',
                'record decisions',
                'return petitions to inward',
                'update petitions',
                'view duplicate warnings',
                'view master reports',
                'view own petitions',
                'view petition nature chart',
                'view petition trends chart',
                'view petitions',
                'view recent petitions',
            ],

            'Inward' => [
                'access cpsp section',
                'create inward petitions',
                'delete inward petitions',
                'export petitions',
                'export statistics',
                'filter transfer',
                'reassign inward petitions',
                'view file transfer',
                'view inward registration',
                'view inward statistics',
                'view own inward statistics',
                'view petition nature chart',
                'view petition trends chart',
                'view processed statistics',
            ],

            'Petition Compliances' => [
                'access admin dashboard',
                'access cpsp section',
                'create compliances',
                'delete compliances',
                'export statistics',
                'update compliances',
                'view all inward statistics',
                'view all petitions',
                'view compliances',
                'view department-wise report',
                'view inward statistics',
                'view master reports',
                'view pendency details',
                'view petition status chart',
                'view petitions',
                'view processed statistics',
                'view seat diagnostics',
                'view seat distribution chart',
            ],

            'T Section' => [
                'access t-section',
                'access t-section dashboard',
                'create petitions',
                'delete petitions',
                'export petitions',
                'export statistics',
                'export t-section petitions',
                'forward t-section petitions',
                'link petitions',
                'manage petition drafts',
                'update petitions',
                'view duplicate warnings',
                'view master reports',
                'view own petitions',
                'view petition nature chart',
                'view petition trends chart',
                'view petitions',
                'view recent petitions',
                'view t-section diagnostics',
                'view t-section in reports',
                'view t-section pending details',
            ],

            'CPSP Admin' => [
                'access admin dashboard',
                'access cpsp section',
                'delete petitions',
                'delete recycle bin',
                'export petitions',
                'export statistics',
                'restore recycle bin',
                'view all inward statistics',
                'view all petitions',
                'view department-wise report',
                'view master reports',
                'view other inward statistics',
                'view pendency details',
                'view petition status chart',
                'view petitions',
                'view processed statistics',
                'view recent petitions',
                'view recycle bin',
                'view seat diagnostics',
                'view seat distribution chart',
            ],
        ];

        // Synchronize permissions for each role safely and idempotently
        foreach ($rolePermissions as $roleName => $permissions) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $role->syncPermissions(array_values(array_unique($permissions)));
            }
        }

        // Ensure deprecated Director role is removed if present
        $deprecatedDirector = Role::where('name', 'Director')->first();
        if ($deprecatedDirector) {
            $deprecatedDirector->syncPermissions([]);
            $deprecatedDirector->delete();
        }

        // Ensure Director seat has super admin role
        $directorSeat = \App\Models\Seat::where('seat_name', 'like', '%Director%')->first();
        if ($directorSeat) {
            $directorSeat->assignRole('super admin');
        }

        // Ensure Admin user has super admin role
        $adminUser = \App\Models\User::where('pen', '100001')->first();
        if ($adminUser) {
            $adminUser->assignRole('super admin');
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
