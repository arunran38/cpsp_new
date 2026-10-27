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
            'inward form',
            'create inward petitions',
            'view file transfer',
            'filter transfer',

            // Reports & Operational Statistics
            'view master reports',
            'inward statistics',
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
        $developer = Role::firstOrCreate(['name' => 'Developer']);
        $developer->syncPermissions(Permission::all());

        // 2. Super Admin: General administrative oversight (excluding confidential T-section permissions unless explicitly granted)
        $superAdmin = Role::firstOrCreate(['name' => 'super admin']);
        if ($superAdmin->wasRecentlyCreated) {
            $superAdminPermissions = Permission::whereNotIn('name', TSectionPermissionRegistry::all())->get();
            $superAdmin->syncPermissions($superAdminPermissions);
        }

        // Cleanup legacy capitalized 'Super Admin' if it exists as a separate record
        $legacySuperAdmin = Role::where('name', 'Super Admin')->first();
        if ($legacySuperAdmin && $legacySuperAdmin->id !== $superAdmin->id) {
            \Illuminate\Support\Facades\DB::table('model_has_roles')
                ->where('role_id', $legacySuperAdmin->id)
                ->update(['role_id' => $superAdmin->id]);
            $legacySuperAdmin->syncPermissions([]);
            $legacySuperAdmin->delete();
        }

        // 3. Admin: System administrator (operations, masters, reports, charts, dashboard - strictly excluding secret T-section perms)
        $admin = Role::firstOrCreate(['name' => 'admin']);
        if ($admin->wasRecentlyCreated || $admin->permissions()->count() === 0) {
            $adminPermissions = Permission::whereNotIn('name', array_merge([
                'delete roles',
                'delete permissions',
            ], TSectionPermissionRegistry::all()))->get();
            $admin->syncPermissions($adminPermissions);
        }

        // 4. Inward Desk: Reception, petition registration, transfers & statistics
        $inward = Role::firstOrCreate(['name' => 'Inward']);
        if ($inward->wasRecentlyCreated || $inward->permissions()->count() === 0) {
            $inward->syncPermissions([
                'view file transfer', 
                'inward form',
                'inward statistics',
                'export statistics',
                'filter transfer',
                'create inward petitions',
                'create petitions',
                'view petitions',
                'view own petitions',
                'view recent petitions',
            ]);
        }

        // 5. CPSP Desk: Grievance management, forwarding, compliances, duplicate linking, transfers
        $cpsp = Role::firstOrCreate(['name' => 'CPSP']);
        if ($cpsp->wasRecentlyCreated || $cpsp->permissions()->count() === 0) {
            $cpsp->syncPermissions([
                'view petitions',
                'view all petitions',
                'view recent petitions',
                'create petitions',
                'update petitions',
                'view file transfer',
                'filter transfer',
                'view compliances',
                'create compliances',
                'update compliances',
                'view duplicate warnings',
                'link petitions',
                'view petition trends chart',
                'view petition nature chart',
            ]);
        }

        // 6. Petition Compliances: Petitions oversight and compliances
        $petitionCompliances = Role::where('name', 'Petition Compliances')->first()
            ?? Role::where('name', 'IOP HQ')->first();
        if ($petitionCompliances) {
            $petitionCompliances->update(['name' => 'Petition Compliances']);
        } else {
            $petitionCompliances = Role::firstOrCreate(['name' => 'Petition Compliances', 'guard_name' => 'web']);
        }
        if ($petitionCompliances->wasRecentlyCreated || $petitionCompliances->permissions()->count() === 0) {
            $petitionCompliances->syncPermissions([
                'view petitions',
                'view all petitions',
                'view recent petitions',
                'view master reports',
                'view seat distribution chart',
                'view petition status chart',
                'view compliances',
                'create compliances',
                'update compliances',
                'delete compliances',
            ]);
        }

        // 7. T-Section Role: Confidential / Secret Section (Isolated CRUD, Strict NO Delete)
        $tSection = Role::firstOrCreate(['name' => 'T Section']);
        $tSection->syncPermissions([
            'view petitions',
            'view own petitions',
            'create petitions',
            'update petitions',
            'view recent petitions',
            'access t-section dashboard',
            'forward t-section petitions',
            TSectionPermissionRegistry::VIEW_DIAGNOSTICS,
            TSectionPermissionRegistry::VIEW_PENDING_DETAILS,
        ]);

        // Ensure T Section Seat exists and is mapped to T Section Role & Unit 8 (Vigilance Directorate)
        $tSectionSeat = \App\Models\Seat::firstOrCreate(
            ['seat_name' => 'T Section'],
            ['is_active' => true]
        );
        $tSectionSeat->syncRoles(['T Section']);
        $directorateUnit = \App\Models\Unit::find(8);
        if ($directorateUnit && !$tSectionSeat->units()->where('units.unit_id', 8)->exists()) {
            $tSectionSeat->units()->attach(8);
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
        $adminUser = \App\Models\User::where('pen', '100001')->orWhere('name', 'Admin')->first();
        if ($adminUser) {
            $adminUser->assignRole('super admin');
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
