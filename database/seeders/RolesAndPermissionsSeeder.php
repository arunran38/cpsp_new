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
        $viewFileTransfer = Permission::firstOrCreate(['name' => 'view_file_transfer']);
        Permission::firstOrCreate(['name' => 'view file transfer']);
        $inwardFormPermission = Permission::firstOrCreate(['name' => 'inward_form']);
        Permission::firstOrCreate(['name' => 'inward form']);

        $inwardStats = Permission::firstOrCreate(['name' => 'inward_statistics']);
        $inwardStatsSpaced = Permission::firstOrCreate(['name' => 'inward statistics']);
        $exportStats = Permission::firstOrCreate(['name' => 'export_statistics']);
        $exportStatsSpaced = Permission::firstOrCreate(['name' => 'export statistics']);
        $filterTransfer = Permission::firstOrCreate(['name' => 'filter_transfer']);
        $filterTransferSpaced = Permission::firstOrCreate(['name' => 'filter transfer']);
        
        $viewProcessedStats = Permission::firstOrCreate(['name' => 'view_processed_statistics']);
        $viewProcessedStatsSpaced = Permission::firstOrCreate(['name' => 'view processed statistics']);

        $createInwardPetitions = Permission::firstOrCreate(['name' => 'create_inward_petitions']);
        $createInwardPetitionsSpaced = Permission::firstOrCreate(['name' => 'create inward petitions']);

        // റോളുകൾ ഉണ്ടാക്കുന്നു (Create Roles) & Assign Permissions
        $superAdminRole = Role::firstOrCreate(['name' => 'super admin']);
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $cpspRole = Role::firstOrCreate(['name' => 'CPSP']);
        $inwardRole = Role::firstOrCreate(['name' => 'Inward']);

        $inwardRole->givePermissionTo([
            $viewFileTransfer, 
            $inwardFormPermission,
            $inwardStats,
            $exportStats,
            $filterTransfer,
            $filterTransferSpaced,
        ]);
        $cpspRole->givePermissionTo([$viewFileTransfer, $inwardFormPermission]);
        $adminRole->givePermissionTo([
            $viewFileTransfer, 
            $inwardFormPermission,
            $inwardStats,
            $exportStats,
            $filterTransfer,
            $filterTransferSpaced,
        ]);
        $superAdminRole->givePermissionTo([
            $viewFileTransfer, 
            $inwardFormPermission,
            $inwardStats,
            $exportStats,
            $filterTransfer,
            $filterTransferSpaced,
        ]);
        // റോളുകൾ ഉണ്ടാക്കുന്നു (Create Roles)
        $superAdmin = Role::firstOrCreate(['name' => 'super admin']);
        $superAdmin->syncPermissions(Permission::all());

        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'CPSP']);
        $iopHq = Role::firstOrCreate(['name' => 'IOP HQ']);

        // IOP HQ-നുള്ള പെർമിഷനുകൾ
        $iopHq->givePermissionTo([
            'view petitions',
            'view all petitions',
            'view compliances',
            'create compliances',
            'update compliances'
        ]);
    }
}
