<?php

namespace Tests\Feature;

use App\Models\Petition;
use App\Models\Seat;
use App\Models\SeatUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class InwardStatisticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_creator_filters_include_ss_cpsp_petitions_without_bypassing_other_user_scope(): void
    {
        $inwardRole = Role::firstOrCreate(['name' => 'Inward', 'guard_name' => 'web']);
        $cpspRole = Role::firstOrCreate(['name' => 'CPSP', 'guard_name' => 'web']);
        $superAdminRole = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);

        $ssCpspSeat = Seat::factory()->create(['seat_name' => 'SS CPSP']);
        $ssCpspSeat->assignRole($inwardRole, $cpspRole);

        $cpspSeat = Seat::factory()->create(['seat_name' => 'CPSP 1']);
        $cpspSeat->assignRole($cpspRole);

        $cpspSeat2 = Seat::factory()->create(['seat_name' => 'CPSP 2']);
        $cpspSeat2->assignRole($cpspRole);

        $superAdmin = User::factory()->create(['name' => 'Super Admin User']);
        $superAdmin->assignRole($superAdminRole);
        SeatUser::factory()->create([
            'user_id' => $superAdmin->user_id,
            'seat_id' => $ssCpspSeat->seat_id,
            'is_active' => true,
        ]);
        session(['current_seat_id' => $ssCpspSeat->seat_id]);

        $inwardSeat = Seat::factory()->create(['seat_name' => 'Inward 1']);
        $inwardSeat->assignRole($inwardRole);

        $inwardCreator = User::factory()->create(['name' => 'Abhilash M. S']);
        SeatUser::factory()->create([
            'user_id' => $inwardCreator->user_id,
            'seat_id' => $inwardSeat->seat_id,
            'is_active' => true,
        ]);

        Petition::factory()->create([
            'user_id' => $superAdmin->user_id,
            'seat_id' => $cpspSeat2->seat_id,
        ]);
        Petition::factory()->create([
            'user_id' => $inwardCreator->user_id,
            'seat_id' => $cpspSeat->seat_id,
        ]);

        $this->actingAs($superAdmin)
            ->get(route('inward.statistics', ['creator_id' => 'all']))
            ->assertOk()
            ->assertSee('All Petitions')
            ->assertSee('Abhilash M. S (INWARD 1)')
            ->assertViewHas('summaryTotals', fn (array $totals) => $totals['total'] === 2);

        $this->get(route('inward.statistics', ['creator_id' => 'my_petitions']))
            ->assertOk()
            ->assertViewHas('summaryTotals', fn (array $totals) => $totals['total'] === 1);

        $this->get(route('inward.statistics', ['creator_id' => $inwardCreator->user_id]))
            ->assertOk()
            ->assertViewHas('summaryTotals', fn (array $totals) => $totals['total'] === 1);

        $this->get(route('inward.statistics', ['creator_id' => PHP_INT_MAX]))
            ->assertOk()
            ->assertViewHas('selectedCreator', 'my_petitions')
            ->assertViewHas('summaryTotals', fn (array $totals) => $totals['total'] === 1);

        $ordinaryInwardUser = User::factory()->create();
        SeatUser::factory()->create([
            'user_id' => $ordinaryInwardUser->user_id,
            'seat_id' => $inwardSeat->seat_id,
            'is_active' => true,
        ]);
        session(['current_seat_id' => $inwardSeat->seat_id]);

        $this->actingAs($ordinaryInwardUser)
            ->get(route('inward.statistics', ['creator_id' => $superAdmin->user_id]))
            ->assertOk()
            ->assertViewHas('selectedCreator', 'my_petitions')
            ->assertViewHas('summaryTotals', fn (array $totals) => $totals['total'] === 0);
    }

    public function test_user_without_inward_creation_provision_hides_my_petitions_and_defaults_to_all(): void
    {
        $adminRole = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'view all inward statistics', 'guard_name' => 'web']);

        $cpspSeat = Seat::factory()->create(['seat_name' => 'CPSP Office']);
        $adminUser = User::factory()->create(['name' => 'Executive Officer']);
        $adminUser->assignRole($adminRole);
        $adminUser->givePermissionTo('view all inward statistics');

        SeatUser::factory()->create([
            'user_id' => $adminUser->user_id,
            'seat_id' => $cpspSeat->seat_id,
            'is_active' => true,
        ]);
        session(['current_seat_id' => $cpspSeat->seat_id]);

        // When accessing without parameters, default should be 'all'
        $this->actingAs($adminUser)
            ->get(route('inward.statistics'))
            ->assertOk()
            ->assertViewHas('canCreateInward', false)
            ->assertViewHas('selectedCreator', 'all')
            ->assertSee('All Petitions')
            ->assertDontSee('My Petitions');

        // When attempting to request 'my_petitions', backend must normalize to 'all'
        $this->get(route('inward.statistics', ['creator_id' => 'my_petitions']))
            ->assertOk()
            ->assertViewHas('canCreateInward', false)
            ->assertViewHas('selectedCreator', 'all')
            ->assertSee('All Petitions')
            ->assertDontSee('My Petitions');
    }

    public function test_viewing_inward_statistics_does_not_trigger_or_show_statistical_reports_dropdown_or_pendency_details(): void
    {
        $inwardRole = Role::firstOrCreate(['name' => 'Inward', 'guard_name' => 'web']);
        $superAdminRole = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'inward statistics', 'guard_name' => 'web']);
        \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'create inward petitions', 'guard_name' => 'web']);
        \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'view seat diagnostics', 'guard_name' => 'web']);
        \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'access admin dashboard', 'guard_name' => 'web']);

        $inwardSeat = Seat::factory()->create(['seat_name' => 'INWARD 1']);
        $inwardSeat->assignRole($inwardRole);

        $inwardUser = User::factory()->create(['name' => 'Inward Desk Officer']);
        $inwardUser->assignRole($inwardRole);
        $inwardUser->givePermissionTo('inward statistics', 'create inward petitions');

        SeatUser::factory()->create([
            'user_id' => $inwardUser->user_id,
            'seat_id' => $inwardSeat->seat_id,
            'is_active' => true,
        ]);
        session(['current_seat_id' => $inwardSeat->seat_id]);

        // 1. Inward Desk Officer visiting Inward Statistics
        $response = $this->actingAs($inwardUser)->get(route('inward.statistics'));
        $response->assertOk();
        $response->assertSee('Inward Statistics');
        // Unrelated Statistical Reports and Pendency Details must NOT be rendered
        $response->assertDontSee('Statistical Reports');
        $response->assertDontSee('Pendency Details');
        $response->assertDontSee('Seat Diagnostics');
        $response->assertDontSee('reportsOpen: true', false);

        // 2. Super Admin (who has view seat diagnostics) visiting Inward Statistics
        $adminSeat = Seat::factory()->create(['seat_name' => 'Director Admin']);
        $adminSeat->assignRole($superAdminRole);
        $superAdminRole->givePermissionTo('access admin dashboard', 'view seat diagnostics', 'inward statistics');

        $superAdmin = User::factory()->create(['name' => 'System Super Admin']);
        $superAdmin->assignRole($superAdminRole);

        SeatUser::factory()->create([
            'user_id' => $superAdmin->user_id,
            'seat_id' => $adminSeat->seat_id,
            'is_active' => true,
        ]);
        session(['current_seat_id' => $adminSeat->seat_id]);

        $adminResponse = $this->actingAs($superAdmin)->get(route('inward.statistics'));
        $adminResponse->assertOk();
        // Statistical Reports should exist in the sidebar for admin, but must NOT be triggered open by inward statistics
        $adminResponse->assertSee('reportsOpen: false', false);
        $adminResponse->assertDontSee('reportsOpen: true', false);
        // Pendency Details duplicate link pointing to inward.statistics must NOT be rendered
        $adminResponse->assertDontSee('Pendency Details');
    }
}
