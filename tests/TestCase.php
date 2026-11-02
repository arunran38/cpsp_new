<?php

namespace Tests;

use App\Models\Seat;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Spatie\Permission\Models\Role;

abstract class TestCase extends BaseTestCase
{
    protected function createSuperAdminUser(?Seat $seat = null): User
    {
        $role = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $user = User::factory()->create();
        $user->assignRole($role);

        if ($seat) {
            $seat->assignRole($role);
        }

        return $user;
    }
}
