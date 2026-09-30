<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$role = Spatie\Permission\Models\Role::where('name', 'admin')->first();
$permissions = Spatie\Permission\Models\Permission::all();
$role->syncPermissions($permissions);
echo "Permissions synced to admin role.\n";
