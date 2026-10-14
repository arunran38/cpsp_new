<?php



namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // വിഭാഗങ്ങൾ (Categories)
        $categories = [
            'users',
            'units',
            'departments',
            'designations',
            'seats',
            'master reports',
            'petitions',
            'seat diagnostics',
            'recycle bin',
            'seat distribution chart',
            'petition status chart',
            'petition trends chart',
            'petition nature chart',
            'roles',
            'permissions',
            'compliances'
        ];

        // പ്രവർത്തനങ്ങൾ (Actions)
        $actions = ['view', 'create', 'update', 'delete'];

        // പെർമിഷനുകൾ ഉണ്ടാക്കുന്നു (Create Permissions)
        foreach ($categories as $category) {
            foreach ($actions as $action) {
                $permissionName = "{$action} {$category}";
                Permission::firstOrCreate(['name' => $permissionName]);
            }
        }

        // പെർമിഷനുകൾ ഉണ്ടാക്കുന്നു (Scoped Permissions)
        Permission::firstOrCreate(['name' => 'view own petitions']);
        Permission::firstOrCreate(['name' => 'view all petitions']);
        Permission::firstOrCreate(['name' => 'access admin dashboard']);
        
        $viewFileTransfer = Permission::firstOrCreate(['name' => 'view file transfer']);
        $inwardFormPermission = Permission::firstOrCreate(['name' => 'inward form']);
        $inwardStats = Permission::firstOrCreate(['name' => 'inward statistics']);
        $exportStats = Permission::firstOrCreate(['name' => 'export statistics']);
        $filterTransfer = Permission::firstOrCreate(['name' => 'filter transfer']);
        $viewProcessedStats = Permission::firstOrCreate(['name' => 'view processed statistics']);
        $createInwardPetitions = Permission::firstOrCreate(['name' => 'create inward petitions']);

        // -------------------------------------------------------------
        // Roles Creation & Permissions Assignment
        // -------------------------------------------------------------

        // 1. Super Admin: Unrestricted full access
        $superAdmin = Role::firstOrCreate(['name' => 'super admin']);
        $superAdmin->syncPermissions(Permission::all());

        // 2. Admin: System administrator (operations, masters, reports, charts, dashboard)
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $adminPermissions = Permission::whereNotIn('name', [
            'delete roles',
            'delete permissions',
        ])->get();
        $admin->syncPermissions($adminPermissions);

        // 3. Inward Desk: Reception, petition registration, transfers & statistics
        $inward = Role::firstOrCreate(['name' => 'Inward']);
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
        ]);

        // 4. CPSP Desk: Grievance management, forwarding, compliances, transfers
        $cpsp = Role::firstOrCreate(['name' => 'CPSP']);
        $cpsp->syncPermissions([
            'view petitions',
            'view all petitions',
            'view own petitions',
            'create petitions',
            'update petitions',
            'view file transfer',
            'filter transfer',
            'view compliances',
            'create compliances',
            'update compliances',
        ]);

        // 5. IOP HQ: Petitions oversight and compliances
        $iopHq = Role::firstOrCreate(['name' => 'IOP HQ']);
        $iopHq->syncPermissions([
            'view petitions',
            'view all petitions',
            'view compliances',
            'create compliances',
            'update compliances',
            'view master reports',
        ]);
    }
}
