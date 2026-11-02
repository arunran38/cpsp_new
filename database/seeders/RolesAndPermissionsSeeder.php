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
            // Scopes & Dashboard
            'access admin dashboard',
            'view own petitions',
            'view all petitions',
            'view recent petitions',

            // Duplicate Detection & Linking
            'view duplicate warnings',
            'link petitions',

            // Inward Desk & Transfers
            'create inward petitions',
            'delete inward petitions',
            'reassign inward petitions',
            'view file transfer',
            'filter transfer',

            // Reports & Operational Statistics
            'view master reports',
            'inward statistics',
            'view own inward statistics',
            'view other inward statistics',
            'view all inward statistics',
            'view pendency details',
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

        // Exact verified role-permission mappings from Role Management
        $rolePermissions = [
            'Developer' => [
                'access admin dashboard',
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
                'export statistics',
                'export t-section petitions',
                'filter transfer',
                'forward t-section petitions',
                'inward statistics',
                'link petitions',
                'reassign inward petitions',
                'restore recycle bin',
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
                'view departments',
                'view designations',
                'view duplicate warnings',
                'view file transfer',
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

            'super admin' => [
                'access admin dashboard',
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
                'export statistics',
                'filter transfer',
                'inward statistics',
                'link petitions',
                'reassign inward petitions',
                'restore recycle bin',
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
                'view departments',
                'view designations',
                'view duplicate warnings',
                'view file transfer',
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

            'admin' => [
                'access admin dashboard',
                'access t-section dashboard',
                'create seats',
                'create users',
                'delete recycle bin',
                'delete seats',
                'delete users',
                'export statistics',
                'export t-section petitions',
                'restore recycle bin',
                'update seats',
                'update users',
                'view all petitions',
                'view master reports',
                'view other inward statistics',
                'view pendency details',
                'view petition status chart',
                'view petitions',
                'view processed statistics',
                'view recycle bin',
                'view seat diagnostics',
                'view seat distribution chart',
                'view seats',
                'view t-section diagnostics',
                'view t-section in reports',
                'view t-section pending details',
                'view users',
            ],

            'CPSP' => [
                'create petitions',
                'export statistics',
                'link petitions',
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
                'create inward petitions',
                'delete inward petitions',
                'export statistics',
                'filter transfer',
                'reassign inward petitions',
                'view file transfer',
                'view own inward statistics',
                'view petition nature chart',
                'view petition trends chart',
                'view processed statistics',
                'view t-section pending details',
            ],

            'Petition Compliances' => [
                'access admin dashboard',
                'create compliances',
                'delete compliances',
                'export statistics',
                'inward statistics',
                'update compliances',
                'view all inward statistics',
                'view all petitions',
                'view compliances',
                'view master reports',
                'view pendency details',
                'view petition status chart',
                'view petitions',
                'view processed statistics',
                'view seat diagnostics',
                'view seat distribution chart',
            ],

            'T Section' => [
                'access t-section dashboard',
                'create petitions',
                'delete petitions',
                'export statistics',
                'export t-section petitions',
                'forward t-section petitions',
                'link petitions',
                'update petitions',
                'view duplicate warnings',
                'view master reports',
                'view own petitions',
                'view petition nature chart',
                'view petition trends chart',
                'view petitions',
                'view recent petitions',
                'view t-section diagnostics',
                'view t-section pending details',
            ],
        ];

        // Synchronize permissions for each role safely and idempotently
        foreach ($rolePermissions as $roleName => $permissions) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $role->syncPermissions($permissions);
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
