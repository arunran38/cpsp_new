<?php

namespace Tests\Feature;

use App\Models\Seat;
use App\Models\SeatUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PendencyDetailsPermissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Permission::firstOrCreate(['name' => 'view pendency details', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'inward statistics', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'view own inward statistics', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'export statistics', 'guard_name' => 'web']);
    }

    public function test_user_without_permission_cannot_access_pendency_details_regardless_of_role(): void
    {
        $inwardRole = Role::firstOrCreate(['name' => 'Inward', 'guard_name' => 'web']);
        $inwardRole->syncPermissions(['inward statistics']);

        $seat = Seat::factory()->create(['seat_name' => 'Inward Counter 1']);
        $seat->assignRole($inwardRole);

        $user = User::factory()->create(['name' => 'Inward Staff']);
        $user->assignRole($inwardRole);
        SeatUser::factory()->create([
            'user_id' => $user->user_id,
            'seat_id' => $seat->seat_id,
            'is_active' => true,
        ]);
        session(['current_seat_id' => $seat->seat_id]);

        // Menu items must NOT show Pendency Details
        $viewResponse = $this->actingAs($user)->get(route('inward.statistics'));
        $viewResponse->assertOk();
        $viewResponse->assertDontSee('Pendency Details');

        // Direct URL access must be 403 Forbidden
        $this->actingAs($user)
            ->get(route('admin.reports.pendency'))
            ->assertForbidden();

        // Direct Export URL access must be 403 Forbidden
        $this->actingAs($user)
            ->get(route('admin.reports.pendency.export'))
            ->assertForbidden();
    }

    public function test_inward_user_with_permission_can_access_pendency_details(): void
    {
        $inwardRole = Role::firstOrCreate(['name' => 'Inward', 'guard_name' => 'web']);
        // Assign view pendency details to Inward role
        $inwardRole->syncPermissions([
            'inward statistics',
            'view pendency details',
        ]);

        $seat = Seat::factory()->create(['seat_name' => 'Inward Counter 2']);
        $seat->assignRole($inwardRole);

        $user = User::factory()->create(['name' => 'Authorized Inward Officer']);
        $user->assignRole($inwardRole);
        SeatUser::factory()->create([
            'user_id' => $user->user_id,
            'seat_id' => $seat->seat_id,
            'is_active' => true,
        ]);
        session(['current_seat_id' => $seat->seat_id]);

        // Menu item must now be visible
        $userLayoutResponse = $this->actingAs($user)->get(route('inward.statistics'));
        $userLayoutResponse->assertOk();
        $userLayoutResponse->assertSee('Pendency Details');
        $userLayoutResponse->assertSee(route('admin.reports.pendency'));

        // Direct URL access must succeed (200 OK)
        $pendencyResponse = $this->actingAs($user)->get(route('admin.reports.pendency'));
        $pendencyResponse->assertOk();
        $pendencyResponse->assertSee('Pendency Details');

        // Direct Export URL access must succeed (200 OK / binary download)
        $exportResponse = $this->actingAs($user)->get(route('admin.reports.pendency.export'));
        $exportResponse->assertOk();
    }

    public function test_user_with_direct_permission_can_access_pendency_details(): void
    {
        $regularRole = Role::firstOrCreate(['name' => 'Regular', 'guard_name' => 'web']);
        $regularRole->syncPermissions([]);

        $seat = Seat::factory()->create(['seat_name' => 'Desk 1']);
        $seat->assignRole($regularRole);

        $user = User::factory()->create(['name' => 'Direct Perm User']);
        $user->assignRole($regularRole);
        // Grant direct permission to user
        $user->givePermissionTo('view pendency details');

        SeatUser::factory()->create([
            'user_id' => $user->user_id,
            'seat_id' => $seat->seat_id,
            'is_active' => true,
        ]);
        session(['current_seat_id' => $seat->seat_id]);

        $this->actingAs($user)
            ->get(route('admin.reports.pendency'))
            ->assertOk()
            ->assertSee('Pendency Details');

        $this->actingAs($user)
            ->get(route('admin.reports.pendency.export'))
            ->assertOk();
    }
}

