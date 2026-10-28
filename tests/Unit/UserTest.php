<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Seat;
use App\Models\SeatUser;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_count_users(): void
    {
        $roleUser = Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);
        $roleAdmin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $users = User::factory()->count(3)->create();
        foreach ($users as $u) {
            $u->assignRole($roleUser);
        }
        $admin = User::factory()->create();
        $admin->assignRole($roleAdmin);

        $this->assertEquals(3, User::countUser());
    }

    public function test_user_current_seat_user_logic(): void
    {
        $user = User::factory()->create();
        $seat1 = Seat::factory()->create(['seat_name' => 'Seat 1']);
        $seat2 = Seat::factory()->create(['seat_name' => 'Seat 2']);

        $su1 = SeatUser::factory()->create([
            'user_id' => $user->user_id,
            'seat_id' => $seat1->seat_id,
            'is_active' => true
        ]);
        $su2 = SeatUser::factory()->create([
            'user_id' => $user->user_id,
            'seat_id' => $seat2->seat_id,
            'is_active' => true
        ]);

        // Default should be the first active one
        $this->assertEquals($su1->seat_id, $user->currentSeatUser()->seat_id);
        $this->assertEquals($su1->seat_id, session('current_seat_id'));

        // Test switching via session
        session(['current_seat_id' => $seat2->seat_id]);
        $user->flushCurrentSeatUserCache();
        $this->assertEquals($su2->seat_id, $user->currentSeatUser()->seat_id);
    }

    public function test_super_admin_requires_explicit_statistics_permissions(): void
    {
        $superAdmin = User::factory()->create();
        $role = Role::create(['name' => 'super admin', 'guard_name' => 'web']);
        $superAdmin->assignRole($role);

        $this->assertFalse($superAdmin->canAccess('view seat diagnostics'));
        $this->assertFalse($superAdmin->canAccess('inward_statistics'));
        $this->assertFalse($superAdmin->canAccessTSectionDiagnostics());
        $this->assertFalse($superAdmin->canAccessTSectionPending());

        $role->givePermissionTo(Permission::create([
            'name' => 'access t-section dashboard',
            'guard_name' => 'web',
        ]));
        $this->assertFalse($superAdmin->canAccessTSectionDiagnostics());
        $this->assertFalse($superAdmin->canAccessTSectionPending());

        $role->givePermissionTo(
            Permission::firstOrCreate(['name' => 'view seat diagnostics', 'guard_name' => 'web']),
            Permission::firstOrCreate(['name' => 'inward statistics', 'guard_name' => 'web']),
            Permission::firstOrCreate(['name' => 'view pendency details', 'guard_name' => 'web']),
            Permission::firstOrCreate(['name' => 'view t-section diagnostics', 'guard_name' => 'web']),
            Permission::firstOrCreate(['name' => 'view t-section pending details', 'guard_name' => 'web'])
        );

        $this->assertTrue($superAdmin->canAccess('view seat diagnostics'));
        $this->assertTrue($superAdmin->canAccess('inward_statistics'));
        $this->assertTrue($superAdmin->canAccess('view pendency details'));
        $this->assertTrue($superAdmin->canAccessTSectionDiagnostics());
        $this->assertTrue($superAdmin->canAccessTSectionPending());
    }
}
