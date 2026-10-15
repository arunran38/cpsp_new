<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;

$users = User::all();
foreach ($users as $u) {
    $canAdmin = $u->canAccess('access admin dashboard');
    if ($canAdmin || str_contains(strtolower($u->name), 'arun') || str_contains(strtolower($u->name), 'director')) {
        echo "USER: {$u->name} (ID: {$u->user_id}, PEN: {$u->pen})\n";
        echo "  User Roles: " . $u->roles->pluck('name')->implode(', ') . "\n";
        echo "  User Direct Perms: " . $u->permissions->pluck('name')->implode(', ') . "\n";
        echo "  canAccess('access admin dashboard'): " . ($canAdmin ? 'YES' : 'NO') . "\n";
        foreach ($u->seatUsers as $su) {
            $seat = $su->seat;
            if ($seat) {
                echo "    Seat: {$seat->seat_name} (ID: {$seat->seat_id})\n";
                echo "      Seat Roles: " . $seat->roles->pluck('name')->implode(', ') . "\n";
                echo "      Seat Direct Perms: " . $seat->permissions->pluck('name')->implode(', ') . "\n";
                echo "      Seat has 'access admin dashboard': " . ($seat->hasPermissionTo('access admin dashboard') ? 'YES' : 'NO') . "\n";
            }
        }
    }
}
