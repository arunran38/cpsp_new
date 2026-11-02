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
        $developer->syncPermissions(Permission::all());

        // 2. Super Admin: General administrative oversight (including T-section report filtering, excluding secret internal diagnostics unless explicitly granted)
        $superAdmin = Role::firstOrCreate(['name' => 'super admin', 'guard_name' => 'web']);
        $superAdminPermissions = Permission::whereNotIn('name', array_diff(TSectionPermissionRegistry::all(), [
            TSectionPermissionRegistry::VIEW_IN_REPORTS,
        ]))->get();
        $superAdmin->syncPermissions($superAdminPermissions);

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

        // T Section Role (granted permission to view T-Section in reports & filters)
        $tSection = Role::firstOrCreate(['name' => 'T Section', 'guard_name' => 'web']);
        $tSection->syncPermissions([
            TSectionPermissionRegistry::VIEW_IN_REPORTS,
        ]);

        // Remove all existing permission assignments from roles (ensure NO permissions for any role except Developer, super admin, and T Section)
        $rolesToClear = Role::whereNotIn('name', ['Developer', 'developer', 'super admin', 'Super Admin', 'T Section', 't section', 'T-Section', 't-section'])->get();
        foreach ($rolesToClear as $role) {
            $role->syncPermissions([]);
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
