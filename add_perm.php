<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'access admin dashboard']);
$role = Spatie\Permission\Models\Role::where('name', 'admin')->first();
$role->givePermissionTo('access admin dashboard');
echo "Created 'access admin dashboard' and gave to admin.\n";
