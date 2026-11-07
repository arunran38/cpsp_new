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
        Permission::firstOrCreate(['name' => 'view inward statistics', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'view own inward statistics', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'export statistics', 'guard_name' => 'web']);
    }

    public function test_user_without_permission_cannot_access_pendency_details_regardless_of_role(): void
    {
        $inwardRole = Role::firstOrCreate(['name' => 'Inward', 'guard_name' => 'web']);
        $inwardRole->syncPermissions(['view inward statistics']);

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
            'view inward statistics',
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

    public function test_user_with_access_to_both_cpsp_and_t_section_defaults_to_all_on_pendency_details(): void
    {
        Permission::firstOrCreate(['name' => 'access cpsp section', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'access t-section', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'view t-section pending details', 'guard_name' => 'web']);

        $role = Role::firstOrCreate(['name' => 'Admin Clearances', 'guard_name' => 'web']);
        $role->syncPermissions([
            'view pendency details',
            'access cpsp section',
            'access t-section',
            'view t-section pending details',
        ]);

        $user = User::factory()->create(['name' => 'Full Clearance Officer']);
        $user->assignRole($role);

        // Visiting Pendency Details without explicit section parameter should default to 'all'
        $response = $this->actingAs($user)->get(route('admin.reports.pendency'));
        $response->assertOk();
        $response->assertViewHas('section', 'all');
        $response->assertViewHas('defaultSection', 'all');

        // Export should also default to 'all'
        $exportResponse = $this->actingAs($user)->get(route('admin.reports.pendency.export'));
        $exportResponse->assertOk();
    }

    public function test_user_with_access_to_only_cpsp_defaults_to_cpsp_on_pendency_details(): void
    {
        Permission::firstOrCreate(['name' => 'access cpsp section', 'guard_name' => 'web']);

        $cpspRole = Role::firstOrCreate(['name' => 'CPSP Only', 'guard_name' => 'web']);
        $cpspRole->syncPermissions([
            'view pendency details',
            'access cpsp section',
        ]);

        $cpspSeat = Seat::factory()->create(['seat_name' => 'CPSP Counter 1', 'is_active' => true]);
        $cpspSeat->assignRole($cpspRole);

        $user = User::factory()->create(['name' => 'CPSP Only User']);
        $user->assignRole($cpspRole);
        SeatUser::factory()->create([
            'user_id' => $user->user_id,
            'seat_id' => $cpspSeat->seat_id,
            'is_active' => true,
        ]);
        session(['current_seat_id' => $cpspSeat->seat_id]);

        $response = $this->actingAs($user)->get(route('admin.reports.pendency'));
        $response->assertOk();
        $response->assertViewHas('section', 'cpsp');
        $response->assertViewHas('defaultSection', 'cpsp');
    }

    public function test_user_with_access_to_only_t_section_defaults_to_t_section_on_pendency_details(): void
    {
        Permission::firstOrCreate(['name' => 'access t-section', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'view t-section pending details', 'guard_name' => 'web']);

        $tRole = Role::firstOrCreate(['name' => 'T-Section', 'guard_name' => 'web']);
        $tRole->syncPermissions([
            'view pendency details',
            'access t-section',
            'view t-section pending details',
        ]);

        $tSeat = Seat::factory()->create(['seat_name' => 'T Section 1', 'is_active' => true]);
        $tSeat->assignRole($tRole);

        $user = User::factory()->create(['name' => 'T Section Officer']);
        $user->assignRole($tRole);
        SeatUser::factory()->create([
            'user_id' => $user->user_id,
            'seat_id' => $tSeat->seat_id,
            'is_active' => true,
        ]);
        session(['current_seat_id' => $tSeat->seat_id]);

        $response = $this->actingAs($user)->get(route('admin.reports.pendency'));
        $response->assertOk();
        $response->assertViewHas('section', 't_section');
        $response->assertViewHas('defaultSection', 't_section');
    }

    public function test_pendency_details_filter_by_section_explicitly_persists_selection(): void
    {
        Permission::firstOrCreate(['name' => 'access cpsp section', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'access t-section', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'view t-section pending details', 'guard_name' => 'web']);

        $role = Role::firstOrCreate(['name' => 'Admin Dual', 'guard_name' => 'web']);
        $role->syncPermissions([
            'view pendency details',
            'access cpsp section',
            'access t-section',
            'view t-section pending details',
        ]);

        $user = User::factory()->create(['name' => 'Dual User']);
        $user->assignRole($role);

        // Filter explicitly by cpsp
        $cpspResponse = $this->actingAs($user)->get(route('admin.reports.pendency', ['section' => 'cpsp']));
        $cpspResponse->assertOk();
        $cpspResponse->assertViewHas('section', 'cpsp');

        // Filter explicitly by t_section
        $tResponse = $this->actingAs($user)->get(route('admin.reports.pendency', ['section' => 't_section']));
        $tResponse->assertOk();
        $tResponse->assertViewHas('section', 't_section');

        // Filter explicitly by all
        $allResponse = $this->actingAs($user)->get(route('admin.reports.pendency', ['section' => 'all']));
        $allResponse->assertOk();
        $allResponse->assertViewHas('section', 'all');
    }
}
