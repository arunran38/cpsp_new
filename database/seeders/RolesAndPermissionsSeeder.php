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
                \Spatie\Permission\Models\Permission::firstOrCreate(['name' => $permissionName]);
            }
        }

        // പെർമിഷനുകൾ ഉണ്ടാക്കുന്നു (Scoped Permissions)
        \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'view own petitions']);
        \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'view all petitions']);

        // റോളുകൾ ഉണ്ടാക്കുന്നു (Create Roles)
        $superAdmin = Role::firstOrCreate(['name' => 'super admin']);
        $superAdmin->syncPermissions(\Spatie\Permission\Models\Permission::all());

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
