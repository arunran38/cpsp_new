<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'view petition trends chart',
            'view petition nature chart',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $cpsp = Role::where('name', 'CPSP')->first();
        if ($cpsp) {
            $cpsp->givePermissionTo($permissions);
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function down(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $cpsp = Role::where('name', 'CPSP')->first();
        if ($cpsp) {
            $cpsp->revokePermissionTo([
                'view petition trends chart',
                'view petition nature chart',
            ]);
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
