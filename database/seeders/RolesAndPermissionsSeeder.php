<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

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
            'permissions'
        ];

        // പ്രവർത്തനങ്ങൾ (Actions)
        $actions = ['view', 'create', 'update', 'delete'];

        // പെർമിഷനുകൾ ഉണ്ടാക്കുന്നു (Create Permissions)
        foreach ($categories as $category) {
            foreach ($actions as $action) {
                $permissionName = "{$action} {$category}";
                \Spatie\Permission\Models\Permission::firstOrCreate(['name' => $permissionName]);
            }
        }

        // പെർമിഷനുകൾ ഉണ്ടാക്കുന്നു (Scoped Permissions)
        \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'view own petitions']);
        \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'view all petitions']);
        $viewFileTransfer = \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'view_file_transfer']);
        \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'view file transfer']);
        $inwardFormPermission = \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'inward_form']);
        \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'inward form']);

        // റോളുകൾ ഉണ്ടാക്കുന്നു (Create Roles) & Assign Permissions
        $superAdminRole = Role::firstOrCreate(['name' => 'super admin']);
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $cpspRole = Role::firstOrCreate(['name' => 'CPSP']);
        $inwardRole = Role::firstOrCreate(['name' => 'Inward']);

        $inwardRole->givePermissionTo([$viewFileTransfer, $inwardFormPermission]);
        $cpspRole->givePermissionTo([$viewFileTransfer, $inwardFormPermission]);
        $adminRole->givePermissionTo([$viewFileTransfer, $inwardFormPermission]);
        $superAdminRole->givePermissionTo([$viewFileTransfer, $inwardFormPermission]);
    }
}
