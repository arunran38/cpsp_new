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
            'seat_id' => $ssCpspSeat->seat_id,
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
}
