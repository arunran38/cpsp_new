<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Department;
use App\Models\Petition;
use App\Models\Seat;
use App\Models\SeatUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class TSectionConfidentialityExclusionTest extends TestCase
{
    use RefreshDatabase;

    protected User $multiRoleUser;
    protected User $cpspUser;
    protected Seat $cpspSeat;
    protected Seat $tSectionSeat;
    protected Department $department;
    protected Petition $cpspPetition;
    protected Petition $tSectionPetition;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure roles & permissions exist
        $superAdminRole = Role::firstOrCreate(['name' => 'super admin', 'guard_name' => 'web']);
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $tSectionRole = Role::firstOrCreate(['name' => 'T Section', 'guard_name' => 'web']);
        $cpspRole = Role::firstOrCreate(['name' => 'CPSP', 'guard_name' => 'web']);
        $inwardRole = Role::firstOrCreate(['name' => 'Inward', 'guard_name' => 'web']);

        $perms = [
            'view seat diagnostics',
            'view department-wise report',
            'access cpsp section',
            'access t-section',
            'view t-section diagnostics',
            'view t-section pending details',
            'view t-section in reports',
            'access t-section dashboard',
            'inward statistics',
            'view all inward statistics',
            'view other inward statistics',
            'view own inward statistics',
            'view petitions',
            'view all petitions',
            'access admin dashboard',
            'export statistics',
        ];
        foreach ($perms as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        // Create seats
        $this->cpspSeat = Seat::factory()->create([
            'seat_name' => 'CPSP 1',
            'is_active' => true,
        ]);
        $this->cpspSeat->assignRole($cpspRole);

        $this->tSectionSeat = Seat::factory()->create([
            'seat_name' => 'T Section',
            'is_active' => true,
        ]);
        $this->tSectionSeat->assignRole($tSectionRole);

        // Create Multi-Role User (Holds Admin, Inward, and T Section clearances)
        $this->multiRoleUser = User::factory()->create([
            'name' => 'Multi-Role Clearance Officer',
        ]);
        $this->multiRoleUser->assignRole([$adminRole, $inwardRole, $tSectionRole]);
        $this->multiRoleUser->givePermissionTo($perms);

        // Give permissions to CPSP role so seats with CPSP role inherit them
        $cpspRole->givePermissionTo(['view seat diagnostics', 'view department-wise report', 'access cpsp section', 'view own inward statistics', 'inward statistics', 'view petitions']);

        // Create CPSP User (Restricted to CPSP 1)
        $this->cpspUser = User::factory()->create([
            'name' => 'Operational CPSP Officer',
        ]);
        $this->cpspUser->assignRole($cpspRole);
        SeatUser::factory()->create([
            'user_id' => $this->cpspUser->user_id,
            'seat_id' => $this->cpspSeat->seat_id,
            'is_active' => true,
        ]);

        // Create Department
        $this->department = Department::create([
            'department_name' => 'Health & Family Welfare',
        ]);

        // Create CPSP Petition in this Department
        $this->cpspPetition = Petition::factory()->create([
            'seat_id' => $this->cpspSeat->seat_id,
            'user_id' => $this->cpspUser->user_id,
            'receipt_no' => 'CPSP-2026-001',
            'date_of_petition_received' => now()->toDateString(),
        ]);
        Address::factory()->create([
            'petition_id' => $this->cpspPetition->petition_id,
            'department_id' => $this->department->id,
            'person_type' => 'Accused',
            'person_name' => 'Public Health Official',
        ]);

        // Create Confidential T-Section Petition in the same Department
        $this->tSectionPetition = Petition::factory()->create([
            'seat_id' => $this->tSectionSeat->seat_id,
            'user_id' => $this->multiRoleUser->user_id,
            'receipt_no' => 'T-SEC-CONF-999',
            'date_of_petition_received' => now()->toDateString(),
        ]);
        Address::factory()->create([
            'petition_id' => $this->tSectionPetition->petition_id,
            'department_id' => $this->department->id,
            'person_type' => 'Accused',
            'person_name' => 'Confidential Intelligence Subject',
        ]);
    }

    public function test_department_analysis_defaults_to_cpsp_and_excludes_t_section_by_default(): void
    {
        // Multi-role user has full clearances but visiting without section query defaults to CPSP
        $response = $this->actingAs($this->multiRoleUser)->get(route('admin.departments.analysis'));

        $response->assertOk();
        $response->assertViewHas('section', 'cpsp');
        $response->assertViewHas('availableSections', [
            'all' => 'All Sections',
            'cpsp' => 'CPSP',
            't_section' => 'T-Section',
        ]);
        // The total count for Health & Family Welfare must be exactly 1 (only CPSP petition, excluding T-Section)
        $response->assertViewHas('total_sum', 1);
        $response->assertSee('Health & Family Welfare');
        $response->assertSee('CPSP Section');
    }

    public function test_department_analysis_filters_by_section_for_authorized_user(): void
    {
        // Filtering by T-Section
        $tResponse = $this->actingAs($this->multiRoleUser)->get(route('admin.departments.analysis', ['section' => 't_section']));
        $tResponse->assertOk();
        $tResponse->assertViewHas('section', 't_section');
        $tResponse->assertViewHas('total_sum', 1);
        $tResponse->assertSee('T-Section');

        // Filtering by All Sections
        $allResponse = $this->actingAs($this->multiRoleUser)->get(route('admin.departments.analysis', ['section' => 'all']));
        $allResponse->assertOk();
        $allResponse->assertViewHas('section', 'all');
        $allResponse->assertViewHas('total_sum', 2);
        $allResponse->assertSee('All Sections');
    }

    public function test_department_analysis_rejects_t_section_for_unauthorized_user(): void
    {
        // CPSP user without T-Section clearance should only see CPSP section option
        $cpspResponse = $this->actingAs($this->cpspUser)->get(route('admin.departments.analysis'));
        $cpspResponse->assertOk();
        $cpspResponse->assertViewHas('availableSections', [
            'cpsp' => 'CPSP',
        ]);

        // Attempting to pass ?section=t_section must be forbidden
        $this->actingAs($this->cpspUser)
            ->get(route('admin.departments.analysis', ['section' => 't_section']))
            ->assertForbidden();

        // Attempting to pass ?section=all falls back to cpsp
        $allResponse = $this->actingAs($this->cpspUser)->get(route('admin.departments.analysis', ['section' => 'all']));
        $allResponse->assertOk();
        $allResponse->assertViewHas('section', 'cpsp');
        $allResponse->assertViewHas('total_sum', 1);
    }

    public function test_inward_statistics_defaults_to_cpsp_and_offers_t_section_with_clearance(): void
    {
        $response = $this->actingAs($this->multiRoleUser)->get(route('inward.statistics'));

        $response->assertOk();
        // CPSP 1 seat must be visible
        $response->assertSee('CPSP 1');
        $response->assertSee('T-Section');
        $response->assertViewHas('seatStats', function ($stats) {
            $seatNames = collect($stats)->pluck('seat_name')->toArray();
            return in_array('CPSP 1', $seatNames) && !in_array('T Section', $seatNames);
        });
    }

    public function test_authorized_inward_statistics_can_filter_t_section_and_all_sections(): void
    {
        $response = $this->actingAs($this->multiRoleUser)->get(route('inward.statistics', ['section' => 't_section']));

        $response->assertOk();
        $response->assertSee('T Section');
        $response->assertViewHas('section', 't_section');
        $response->assertViewHas('seatStats', function ($stats) {
            $seatNames = collect($stats)->pluck('seat_name')->toArray();
            return in_array('T Section', $seatNames);
        });
        $response->assertViewHas('summaryTotals', fn ($totals) => $totals['total'] === 1);

        $responseAll = $this->actingAs($this->multiRoleUser)->get(route('inward.statistics', ['section' => 'all']));

        $responseAll->assertOk();
        $responseAll->assertSee('T Section');
        $responseAll->assertViewHas('seatStats', function ($stats) {
            $seatNames = collect($stats)->pluck('seat_name')->toArray();
            return in_array('T Section', $seatNames) && in_array('CPSP 1', $seatNames);
        });
        $responseAll->assertViewHas('summaryTotals', fn ($totals) => $totals['total'] === 2);
    }

    public function test_inward_role_with_t_section_pending_permission_can_view_t_section_statistics(): void
    {
        $inwardSeat = Seat::factory()->create([
            'seat_name' => 'INWARD I',
            'is_active' => true,
        ]);
        $inwardRole = Role::findByName('Inward', 'web');
        $inwardSeat->assignRole($inwardRole);
        $inwardRole->givePermissionTo('inward statistics', 'view t-section pending details');

        $inwardOfficer = User::factory()->create(['name' => 'Inward Statistics Officer']);
        $inwardOfficer->assignRole($inwardRole);
        SeatUser::factory()->create([
            'user_id' => $inwardOfficer->user_id,
            'seat_id' => $inwardSeat->seat_id,
            'is_active' => true,
        ]);
        session(['current_seat_id' => $inwardSeat->seat_id]);

        $response = $this->actingAs($inwardOfficer)
            ->get(route('inward.statistics', ['section' => 't_section']));

        $response->assertOk();
        $response->assertSee('T Section');
        $response->assertViewHas('section', 't_section');
    }

    public function test_inward_statistics_rejects_t_section_without_clearance(): void
    {
        $this->actingAs($this->cpspUser)
            ->get(route('inward.statistics', ['section' => 't_section']))
            ->assertForbidden();
    }

    public function test_cpsp_user_views_only_authorized_data(): void
    {
        // Create another CPSP seat with a petition
        $otherCpspSeat = Seat::factory()->create([
            'seat_name' => 'CPSP 2',
            'is_active' => true,
        ]);
        $otherCpspSeat->assignRole('CPSP');

        $otherPetition = Petition::factory()->create([
            'seat_id' => $otherCpspSeat->seat_id,
            'receipt_no' => 'CPSP-2026-002',
            'date_of_petition_received' => now()->toDateString(),
        ]);
        Address::factory()->create([
            'petition_id' => $otherPetition->petition_id,
            'department_id' => $this->department->id,
            'person_type' => 'Accused',
            'person_name' => 'Other Officer',
        ]);

        // CPSP user restricted to CPSP 1 (no 'view all petitions' or 'view other inward statistics')
        session(['current_seat_id' => $this->cpspSeat->seat_id]);

        // Inward statistics for restricted CPSP user only includes their seat
        $inwardResponse = $this->actingAs($this->cpspUser)->get(route('inward.statistics'));
        $inwardResponse->assertOk();
        $inwardResponse->assertSee('CPSP 1');
        $inwardResponse->assertDontSee('CPSP 2');
        $inwardResponse->assertDontSee('T Section');

        // Department analysis for restricted CPSP user only aggregates their own authorized petitions
        $deptResponse = $this->actingAs($this->cpspUser)->get(route('admin.departments.analysis'));
        $deptResponse->assertOk();
        $deptResponse->assertViewHas('total_sum', 1); // Only CPSP 1 petition, excluding CPSP 2 and T Section
    }

    public function test_inward_desk_user_views_cpsp_seats_statistics_without_t_section(): void
    {
        // An Inward desk officer assigned to an INWARD seat (like Anudev S in INWARD I)
        $inwardSeat = Seat::factory()->create([
            'seat_name' => 'INWARD I',
            'is_active' => true,
        ]);
        $inwardRole = Role::firstOrCreate(['name' => 'Inward', 'guard_name' => 'web']);
        $inwardSeat->assignRole($inwardRole);
        $inwardRole->givePermissionTo(['create inward petitions', 'inward statistics']);

        $inwardOfficer = User::factory()->create(['name' => 'Anudev S']);
        $inwardOfficer->assignRole($inwardRole);
        SeatUser::factory()->create([
            'user_id' => $inwardOfficer->user_id,
            'seat_id' => $inwardSeat->seat_id,
            'is_active' => true,
        ]);
        session(['current_seat_id' => $inwardSeat->seat_id]);

        // Inward officer registered a petition sent to CPSP 1
        Petition::factory()->create([
            'user_id' => $inwardOfficer->user_id,
            'seat_id' => $this->cpspSeat->seat_id,
            'receipt_no' => 'INW-CPSP-2026',
        ]);

        $response = $this->actingAs($inwardOfficer)->get(route('inward.statistics'));

        $response->assertOk();
        $response->assertSee('CPSP 1');
        $response->assertDontSee('T Section');
        $response->assertViewHas('seatStats', function ($stats) {
            $seatNames = collect($stats)->pluck('seat_name')->toArray();
            return in_array('CPSP 1', $seatNames) && !in_array('T Section', $seatNames);
        });
        $response->assertViewHas('summaryTotals', fn ($totals) => $totals['total'] >= 1);
    }

    public function test_t_section_data_remains_accessible_in_dedicated_diagnostics(): void
    {
        // When user has explicit 'view t-section diagnostics' clearance,
        // they can access T-Section data in the dedicated seat diagnostics module (/seats/statistics)
        $response = $this->actingAs($this->multiRoleUser)->get(route('admin.seats.statistics', ['section' => 't_section']));

        $response->assertOk();
        $response->assertSee('T Section');
    }
}
