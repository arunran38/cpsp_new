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

class DepartmentAnalysisTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $cpspUser;
    protected Seat $cpspSeat;
    protected Seat $tSectionSeat;
    protected Department $deptPolice;
    protected Department $deptRevenue;

    protected function setUp(): void
    {
        parent::setUp();

        $superAdminRole = Role::firstOrCreate(['name' => 'super admin', 'guard_name' => 'web']);
        $cpspRole = Role::firstOrCreate(['name' => 'CPSP', 'guard_name' => 'web']);
        $tSectionRole = Role::firstOrCreate(['name' => 'T Section', 'guard_name' => 'web']);

        $perms = [
            'view seat diagnostics',
            'view t-section diagnostics',
            'view t-section in reports',
            'export statistics',
            'view petitions',
            'view all petitions',
            'access admin dashboard',
        ];
        foreach ($perms as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

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

        // Admin User with full clearances
        $this->adminUser = User::factory()->create(['name' => 'Admin Officer']);
        $this->adminUser->assignRole($superAdminRole);
        $this->adminUser->givePermissionTo($perms);

        // Give permissions to CPSP role so seats with CPSP role inherit them
        $cpspRole->givePermissionTo(['view seat diagnostics', 'view petitions']);

        // CPSP Operational User (no T-Section clearance)
        $this->cpspUser = User::factory()->create(['name' => 'CPSP Desk Officer']);
        $this->cpspUser->assignRole($cpspRole);
        $this->cpspUser->givePermissionTo(['view seat diagnostics', 'view petitions']);
        SeatUser::factory()->create([
            'user_id' => $this->cpspUser->user_id,
            'seat_id' => $this->cpspSeat->seat_id,
            'is_active' => true,
        ]);

        // Departments
        $this->deptPolice = Department::create(['department_name' => 'Police Department']);
        $this->deptRevenue = Department::create(['department_name' => 'Revenue Department']);

        // CPSP Petition -> Police Department
        $cpspPetition = Petition::factory()->create([
            'seat_id' => $this->cpspSeat->seat_id,
            'user_id' => $this->cpspUser->user_id,
            'receipt_no' => 'CPSP-POL-001',
            'date_of_petition_received' => '2026-05-10',
        ]);
        Address::factory()->create([
            'petition_id' => $cpspPetition->petition_id,
            'department_id' => $this->deptPolice->id,
            'person_type' => 'Accused',
            'person_name' => 'Officer A',
        ]);

        // T-Section Petition -> Revenue Department
        $tPetition = Petition::factory()->create([
            'seat_id' => $this->tSectionSeat->seat_id,
            'user_id' => $this->adminUser->user_id,
            'receipt_no' => 'TSEC-REV-001',
            'date_of_petition_received' => '2026-05-15',
        ]);
        Address::factory()->create([
            'petition_id' => $tPetition->petition_id,
            'department_id' => $this->deptRevenue->id,
            'person_type' => 'Accused',
            'person_name' => 'Officer B',
        ]);
    }

    public function test_section_filter_dropdown_displays_options_for_authorized_user(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('admin.departments.analysis'));

        $response->assertOk();
        $response->assertSee('name="section"', false);
        $response->assertSee('<option value="all"', false);
        $response->assertSee('<option value="cpsp"', false);
        $response->assertSee('<option value="t_section"', false);
        $response->assertSee('All Sections');
        $response->assertSee('T-Section');
        $response->assertViewHas('availableSections', [
            'all' => 'All Sections',
            'cpsp' => 'CPSP',
            't_section' => 'T-Section',
        ]);
        $response->assertViewHas('section', 'cpsp');
    }

    public function test_section_filter_dropdown_for_restricted_user_only_contains_cpsp(): void
    {
        $response = $this->actingAs($this->cpspUser)->get(route('admin.departments.analysis'));

        $response->assertOk();
        $response->assertSee('name="section"', false);
        $response->assertSee('<option value="cpsp"', false);
        $response->assertDontSee('<option value="t_section"', false);
        $response->assertViewHas('availableSections', [
            'cpsp' => 'CPSP',
        ]);
    }

    public function test_selecting_cpsp_section_filters_only_cpsp_petitions(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('admin.departments.analysis', ['section' => 'cpsp']));

        $response->assertOk();
        $response->assertViewHas('total_sum', 1);
        $response->assertSee('Police Department');
        $response->assertDontSee('Revenue Department');
    }

    public function test_selecting_t_section_filters_only_t_section_petitions(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('admin.departments.analysis', ['section' => 't_section']));

        $response->assertOk();
        $response->assertViewHas('total_sum', 1);
        $response->assertSee('Revenue Department');
        $response->assertDontSee('Police Department');
    }

    public function test_selecting_all_sections_shows_both_sections(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('admin.departments.analysis', ['section' => 'all']));

        $response->assertOk();
        $response->assertViewHas('total_sum', 2);
        $response->assertSee('Police Department');
        $response->assertSee('Revenue Department');
    }

    public function test_ajax_search_with_section_filter_returns_table_partial(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('admin.departments.analysis', [
            'section' => 't_section',
            'search' => 'Revenue'
        ]), ['X-Requested-With' => 'XMLHttpRequest']);

        $response->assertOk();
        $response->assertSee('Revenue Department');
        $response->assertDontSee('Police Department');
    }

    public function test_excel_export_respects_section_filter_and_includes_section_suffix(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('admin.departments.analysis.export', ['section' => 't_section']));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/vnd.ms-excel');
        $this->assertStringContainsString('department_analysis_t_section_', $response->headers->get('Content-Disposition'));
    }
}
