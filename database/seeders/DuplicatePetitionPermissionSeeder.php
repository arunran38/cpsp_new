<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DuplicatePetitionPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ഡ്യൂപ്ലിക്കേറ്റ് കണ്ടെത്തുന്ന ഫീച്ചർ കാണാനുള്ള പെർമിഷൻ
        $permissions = [
            'view duplicate warnings',
            'link petitions',
            'mark petition as duplicate',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // admin അല്ലെങ്കിൽ ഉയർന്ന ഉദ്യോഗസ്ഥർക്ക് ഈ പെർമിഷനുകൾ കൊടുക്കാം (ഉദാഹരണത്തിന് 'super-admin' ഉണ്ടെങ്കിൽ)
        $role = Role::where('name', 'Super Admin')->first();
        if ($role) {
            $role->givePermissionTo($permissions);
        }
    }
}
