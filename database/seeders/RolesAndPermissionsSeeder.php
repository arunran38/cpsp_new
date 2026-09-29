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
            'recycle bin'
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

        // റോളുകൾ ഉണ്ടാക്കുന്നു (Create Roles)
        Role::firstOrCreate(['name' => 'super admin']);
        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'CPSP']);
    }
}
