<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Decision;
use App\Models\District;
use App\Models\Petition;
use App\Models\Seat;
use App\Models\SeatUser;
use App\Models\Unit;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PetitionUnitSearchTest extends TestCase
{
    use RefreshDatabase;

    protected User $cpspUser;
    protected Seat $cpspSeat;
    protected User $inwardUser;
    protected Seat $inwardSeat;
    protected User $adminUser;
    protected Seat $adminSeat;
    protected Unit $unit;
    protected District $district;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        $cpspRole = Role::findByName('CPSP', 'web');
        $inwardRole = Role::findByName('Inward', 'web');
        $adminRole = Role::findByName('Super Admin', 'web');

        $this->cpspSeat = Seat::factory()->create(['seat_name' => 'CPSP 1', 'is_active' => true]);
        $this->cpspSeat->assignRole($cpspRole);

        $this->cpspUser = User::factory()->create(['name' => 'CPSP Search Officer']);
        $this->cpspUser->assignRole($cpspRole);
        SeatUser::factory()->create([
            'user_id' => $this->cpspUser->user_id,
            'seat_id' => $this->cpspSeat->seat_id,
            'is_active' => true,
        ]);

        $this->inwardSeat = Seat::factory()->create(['seat_name' => 'Inward 1', 'is_active' => true]);
        $this->inwardSeat->assignRole($inwardRole);

        $this->inwardUser = User::factory()->create(['name' => 'Inward Search Officer']);
        $this->inwardUser->assignRole($inwardRole);
        SeatUser::factory()->create([
            'user_id' => $this->inwardUser->user_id,
            'seat_id' => $this->inwardSeat->seat_id,
            'is_active' => true,
        ]);

        $this->adminSeat = Seat::factory()->create(['seat_name' => 'Admin Desk', 'is_active' => true]);
        $this->adminSeat->assignRole($adminRole);

        $this->adminUser = User::factory()->create(['name' => 'Super Admin User']);
        $this->adminUser->assignRole($adminRole);
        $viewTrashPerm = Permission::firstOrCreate(['name' => 'view recycle bin', 'guard_name' => 'web']);
        $this->adminUser->givePermissionTo($viewTrashPerm);
        SeatUser::factory()->create([
            'user_id' => $this->adminUser->user_id,
            'seat_id' => $this->adminSeat->seat_id,
            'is_active' => true,
        ]);

        $this->unit = Unit::factory()->create(['unit_name' => 'SIU-II', 'unit_code' => 'SIU2']);
        $this->district = District::firstOrCreate(['district_name' => 'Thiruvananthapuram', 'district_code' => 'TVM']);
    }

    public function test_submitted_petitions_live_search_finds_full_and_partial_petition_number_at_unit(): void
    {
        $match = Petition::factory()->create([
            'receipt_no' => 'CPSP/2026/REC-101',
            'date_of_petition_received' => now()->toDateString(),
            'user_id' => $this->cpspUser->user_id,
            'seat_id' => $this->cpspSeat->seat_id,
            'concerned_district_id' => $this->district->district_id,
            'mode_of_petition_received' => 'Unit',
            'unit_id' => $this->unit->unit_id,
            'petition_number_at_unit' => 'SIU2-PET/2026/8844',
            'date_of_petition_received_at_unit' => now()->toDateString(),
            'is_cpsp_processed' => true,
        ]);

        $other = Petition::factory()->create([
            'receipt_no' => 'CPSP/2026/REC-202',
            'user_id' => $this->cpspUser->user_id,
            'seat_id' => $this->cpspSeat->seat_id,
            'concerned_district_id' => $this->district->district_id,
            'mode_of_petition_received' => 'Direct',
            'petition_number_at_unit' => null,
            'is_cpsp_processed' => true,
        ]);

        // Full match via AJAX live search
        $fullResponse = $this->actingAs($this->cpspUser)
            ->withSession(['current_seat_id' => $this->cpspSeat->seat_id])
            ->get(route('petitions.index', [
                'tab' => 'all',
                'search' => 'SIU2-PET/2026/8844',
            ]), ['X-Requested-With' => 'XMLHttpRequest']);

        $fullResponse->assertStatus(200);
        $fullResponse->assertSee($match->receipt_no);
        $fullResponse->assertSee('SIU2-PET/2026/8844');
        $fullResponse->assertDontSee($other->receipt_no);

        // Partial match alongside date filter
        $partialResponse = $this->actingAs($this->cpspUser)
            ->withSession(['current_seat_id' => $this->cpspSeat->seat_id])
            ->get(route('petitions.index', [
                'tab' => 'all',
                'search' => '8844',
                'date_from' => now()->subDays(2)->toDateString(),
            ]), ['X-Requested-With' => 'XMLHttpRequest']);

        $partialResponse->assertStatus(200);
        $partialResponse->assertSee($match->receipt_no);
        $partialResponse->assertDontSee($other->receipt_no);
    }

    public function test_reports_search_supports_all_petitions_and_dedicated_unit_petition_no_type(): void
    {
        $unitMatch = Petition::factory()->create([
            'receipt_no' => 'RPT/2026/A1',
            'user_id' => $this->cpspUser->user_id,
            'seat_id' => $this->cpspSeat->seat_id,
            'mode_of_petition_received' => 'Unit',
            'unit_id' => $this->unit->unit_id,
            'petition_number_at_unit' => 'UNIT-ONLY-9955',
            'date_of_petition_received_at_unit' => now()->toDateString(),
            'is_cpsp_processed' => true,
        ]);

        $phoneMatch = Petition::factory()->create([
            'receipt_no' => 'RPT/2026/B2',
            'user_id' => $this->cpspUser->user_id,
            'seat_id' => $this->cpspSeat->seat_id,
            'mode_of_petition_received' => 'Direct',
            'is_cpsp_processed' => true,
        ]);
        Address::factory()->create([
            'petition_id' => $phoneMatch->petition_id,
            'person_type' => 'Complainant',
            'person_name' => 'Phone Holder',
            'phone' => '9876599550',
        ]);

        // 1. Default All Petitions search finds unitMatch via partial unit petition number
        $allSearch = $this->actingAs($this->adminUser)
            ->withSession(['current_seat_id' => $this->adminSeat->seat_id])
            ->get(route('petitions.reports', ['search' => '9955']));

        $allSearch->assertStatus(200);
        $allSearch->assertSee($unitMatch->receipt_no);
        $allSearch->assertSee($phoneMatch->receipt_no);

        // 2. Targeted search_type = unit_petition_no matches ONLY petition_number_at_unit
        $targetedSearch = $this->actingAs($this->adminUser)
            ->withSession(['current_seat_id' => $this->adminSeat->seat_id])
            ->get(route('petitions.reports', [
                'search_type' => 'unit_petition_no',
                'search' => '9955',
            ]));

        $targetedSearch->assertStatus(200);
        $targetedSearch->assertSee($unitMatch->receipt_no);
        $targetedSearch->assertDontSee($phoneMatch->receipt_no);
    }

    public function test_inward_live_search_finds_petition_by_partial_and_full_unit_number(): void
    {
        $inwardUnit = Petition::factory()->create([
            'receipt_no' => 'INW/2026/U-500',
            'user_id' => $this->inwardUser->user_id,
            'seat_id' => $this->cpspSeat->seat_id,
            'created_by_seat_id' => $this->inwardSeat->seat_id,
            'mode_of_petition_received' => 'Unit',
            'unit_id' => $this->unit->unit_id,
            'petition_number_at_unit' => 'INW-UNIT-7733',
            'date_of_petition_received_at_unit' => now()->toDateString(),
            'is_cpsp_processed' => false,
        ]);

        $inwardOther = Petition::factory()->create([
            'receipt_no' => 'INW/2026/D-501',
            'user_id' => $this->inwardUser->user_id,
            'seat_id' => $this->cpspSeat->seat_id,
            'created_by_seat_id' => $this->inwardSeat->seat_id,
            'mode_of_petition_received' => 'Direct',
            'is_cpsp_processed' => false,
        ]);

        $response = $this->actingAs($this->inwardUser)
            ->withSession(['current_seat_id' => $this->inwardSeat->seat_id])
            ->get(route('inward.enter_petition', ['search' => '7733']), [
                'X-Requested-With' => 'XMLHttpRequest',
            ]);

        $response->assertStatus(200);
        $response->assertSee($inwardUnit->receipt_no);
        $response->assertSee('INW-UNIT-7733');
        $response->assertDontSee($inwardOther->receipt_no);
    }

    public function test_duplicate_link_live_search_includes_petition_number_at_unit(): void
    {
        $target = Petition::factory()->create([
            'receipt_no' => 'DUP/2026/TARGET-1',
            'user_id' => $this->cpspUser->user_id,
            'seat_id' => $this->cpspSeat->seat_id,
            'mode_of_petition_received' => 'Unit',
            'unit_id' => $this->unit->unit_id,
            'petition_number_at_unit' => 'LINK-UNIT-4422',
            'date_of_petition_received_at_unit' => now()->toDateString(),
            'is_cpsp_processed' => true,
        ]);

        $response = $this->actingAs($this->cpspUser)
            ->withSession(['current_seat_id' => $this->cpspSeat->seat_id])
            ->getJson(route('petitions.searchDuplicates', ['q' => '4422']));

        $response->assertStatus(200)
            ->assertJsonFragment([
                'receipt_no' => $target->receipt_no,
                'petition_number_at_unit' => 'LINK-UNIT-4422',
            ]);
    }

    public function test_recycle_bin_and_compliance_search_include_petition_number_at_unit(): void
    {
        $trashed = Petition::factory()->create([
            'receipt_no' => 'TRASH/2026/UNIT-1',
            'user_id' => $this->cpspUser->user_id,
            'seat_id' => $this->cpspSeat->seat_id,
            'mode_of_petition_received' => 'Unit',
            'unit_id' => $this->unit->unit_id,
            'petition_number_at_unit' => 'TRASH-UNIT-6611',
            'is_cpsp_processed' => true,
        ]);
        $trashed->delete();

        $trashResponse = $this->actingAs($this->adminUser)
            ->withSession(['current_seat_id' => $this->adminSeat->seat_id])
            ->get(route('admin.trash.index', ['search' => '6611']));

        $trashResponse->assertStatus(200);
        $trashResponse->assertSee($trashed->receipt_no);

        $compliancePetition = Petition::factory()->create([
            'receipt_no' => 'COMP/2026/UNIT-1',
            'user_id' => $this->cpspUser->user_id,
            'seat_id' => $this->cpspSeat->seat_id,
            'mode_of_petition_received' => 'Unit',
            'unit_id' => $this->unit->unit_id,
            'petition_number_at_unit' => 'COMP-UNIT-5599',
            'status' => Petition::STATUS_INTERNAL_VIGILANCE,
            'is_cpsp_processed' => true,
        ]);
        Decision::factory()->create([
            'petition_id' => $compliancePetition->petition_id,
            'final_decision' => 'VE',
            'directorate_order_number' => 'ORD/2026/100',
            'decision_date' => now()->toDateString(),
        ]);

        $compResponse = $this->actingAs($this->adminUser)
            ->withSession(['current_seat_id' => $this->adminSeat->seat_id])
            ->get(route('compliances.index', ['search' => '5599']));

        $compResponse->assertStatus(200);
        $compResponse->assertSee('ORD/2026/100');
    }
}
