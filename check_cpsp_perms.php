<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::where('name', 'like', '%Aswathy%')->first();
echo "User: " . ($user ? $user->name . " (ID: " . $user->user_id . ", Email: " . $user->email . ")" : "Not found") . PHP_EOL;
echo "User roles: " . ($user ? $user->roles->pluck('name')->implode(', ') : '') . PHP_EOL;

if ($user) {
    $csu = $user->currentSeatUser();
    echo "Current Seat: " . ($csu ? $csu->seat->seat_name . " (ID: " . $csu->seat_id . ")" : "None") . PHP_EOL;
    if ($csu && $csu->seat) {
        echo "Seat Roles: " . $csu->seat->roles->pluck('name')->implode(', ') . PHP_EOL;
        echo "Seat Permissions: " . $csu->seat->getAllPermissions()->pluck('name')->implode(', ') . PHP_EOL;
    }
    $rows = \Illuminate\Support\Facades\DB::table('seat_users')->where('user_id', $user->user_id)->get();
    foreach ($rows as $r) {
        $seat = \App\Models\Seat::find($r->seat_id);
        echo "DB Row - seat_id: {$r->seat_id} (" . ($seat ? $seat->seat_name : 'null') . "), is_active: {$r->is_active}, is_additional: {$r->is_additional}\n";
    }
    auth()->login($user);
    $isExecutive = $user->canAccess('view all petitions') || $user->canAccess('access admin dashboard');
    $isInwardDesk = !$isExecutive && ($user->canAccess('view file transfer') || ($csu?->seat && stripos($csu->seat->seat_name, 'Inward') !== false));
    echo "isExecutive: " . ($isExecutive ? 'YES' : 'NO') . PHP_EOL;
    echo "isInwardDesk: " . ($isInwardDesk ? 'YES' : 'NO') . PHP_EOL;
    echo "canAccess('view file transfer'): " . ($user->canAccess('view file transfer') ? 'YES' : 'NO') . PHP_EOL;
    echo "canAccess('inward form'): " . ($user->canAccess('inward form') ? 'YES' : 'NO') . PHP_EOL;
    echo "canAccess('create inward petitions'): " . ($user->canAccess('create inward petitions') ? 'YES' : 'NO') . PHP_EOL;
    echo "canAccess('view all petitions'): " . ($user->canAccess('view all petitions') ? 'YES' : 'NO') . PHP_EOL;
    echo "canAccess('access admin dashboard'): " . ($user->canAccess('access admin dashboard') ? 'YES' : 'NO') . PHP_EOL;
}

echo "\n--- CPSP Role Permissions ---\n";
$cpspRole = \Spatie\Permission\Models\Role::where('name', 'cpsp')->orWhere('name', 'CPSP')->first();
if ($cpspRole) {
    echo "CPSP perms: " . $cpspRole->permissions->pluck('name')->implode(', ') . PHP_EOL;
}

echo "\n--- DB model_has_roles for user 12 ---\n";
$mhr = \Illuminate\Support\Facades\DB::table('model_has_roles')->where('model_id', 12)->get();
foreach ($mhr as $r) {
    $role = \Spatie\Permission\Models\Role::find($r->role_id);
    echo "Role: " . ($role ? $role->name : $r->role_id) . " (model_type: {$r->model_type})\n";
}

echo "\n--- DB model_has_permissions for user 12 ---\n";
$mhp = \Illuminate\Support\Facades\DB::table('model_has_permissions')->where('model_id', 12)->get();
foreach ($mhp as $p) {
    $perm = \Spatie\Permission\Models\Permission::find($p->permission_id);
    echo "Perm: " . ($perm ? $perm->name : $p->permission_id) . " (model_type: {$p->model_type})\n";
}

echo "\n--- DB model_has_roles for seat 2 ---\n";
$smhr = \Illuminate\Support\Facades\DB::table('model_has_roles')->where('model_id', 2)->get();
foreach ($smhr as $r) {
    $role = \Spatie\Permission\Models\Role::find($r->role_id);
    echo "Seat Role: " . ($role ? $role->name : $r->role_id) . " (model_type: {$r->model_type})\n";
}

echo "\n--- Rendering dashboard for Aswathy ---\n";
auth()->login($user);
$html = view('dashboard')->render();
if (str_contains($html, 'Inward desk operations')) {
    echo "RENDERED: Inward Desk Operations\n";
} elseif (str_contains($html, 'Active desk petition processing')) {
    echo "RENDERED: Active Desk Petition Processing (CPSP)\n";
} elseif (str_contains($html, 'State-wide executive petition intelligence')) {
    echo "RENDERED: Executive Dashboard\n";
} else {
    echo "RENDERED: Other\n";
}
