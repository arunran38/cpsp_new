<?php

namespace Tests\Feature;

use App\Models\District;
use App\Models\Petition;
use App\Models\Seat;
use App\Models\SeatUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DistrictAnalysisTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $cpspUser;
    protected Seat $cpspSeat;
    protected District $tvm;
    protected District $klm;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $cpspRole = Role::firstOrCreate(['name' => 'CPSP', 'guard_name' => 'web']);

        $perms = [
            'view district analysis report',
            'view district analysis chart',
            'export district analysis',
            'filter district in master reports',
            'view master reports',
            'view petitions',
            'view all petitions',
            'access admin dashboard',
        ];

        foreach ($perms as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        // Seed standard 15 districts in database order
        $this->seed(\Database\Seeders\DistrictSeeder::class);
        $this->tvm = District::where('district_code', 'TVM')->first();
        $this->klm = District::where('district_code', 'KLM')->first();

        $this->cpspSeat = Seat::factory()->create([
            'seat_name' => 'CPSP Desk 1',
            'is_active' => true,
        ]);
        $this->cpspSeat->assignRole($cpspRole);

        // Admin User with full permissions
        $this->adminUser = User::factory()->create(['name' => 'Admin User']);
        $this->adminUser->assignRole($adminRole);
        $this->adminUser->givePermissionTo($perms);
        SeatUser::factory()->create([
            'user_id' => $this->adminUser->user_id,
            'seat_id' => $this->cpspSeat->seat_id,
            'is_active' => true,
        ]);

        // CPSP User without district analysis permissions
        $this->cpspUser = User::factory()->create(['name' => 'CPSP Officer']);
        $this->cpspUser->assignRole($cpspRole);
        $this->cpspUser->givePermissionTo(['view petitions', 'view master reports']);
        SeatUser::factory()->create([
            'user_id' => $this->cpspUser->user_id,
            'seat_id' => $this->cpspSeat->seat_id,
            'is_active' => true,
        ]);
    }

    public function test_admin_can_access_district_analysis_report_without_chart(): void
    {
        // Create a petition in TVM only; KLM has 0 petitions
        Petition::factory()->create([
            'concerned_district_id' => $this->tvm->district_id,
            'status' => Petition::STATUS_RECEIVED,
            'date_of_petition_received' => now()->format('Y-m-d'),
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.district-analysis'));

        $response->assertStatus(200);
        $response->assertSee('District Wise Analysis');
        // TVM has petitions > 0 so it should appear
        $response->assertSee('Thiruvananthapuram');
        $response->assertSee('TVM');
        // KLM has 0 petitions, so only districts with petitions > 0 should appear
        $response->assertDontSee('Kollam');
        $response->assertDontSee('KLM');
        // The chart should NOT appear in District Wise Analysis page
        $response->assertDontSee('District Trend Visualisation');
    }

    public function test_district_analysis_shows_empty_state_when_no_petitions_exist(): void
    {
        // No petitions created
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.district-analysis'));

        $response->assertStatus(200);
        $response->assertSee('No district data available');
        $response->assertSee('There are no petitions found for the selected filter criteria.');
    }

    public function test_cpsp_officer_without_report_permission_receives_403(): void
    {
        $response = $this->actingAs($this->cpspUser)
            ->get(route('admin.reports.district-analysis'));

        $response->assertStatus(403);
    }

    public function test_district_analysis_ajax_json_returns_table_and_total(): void
    {
        // Create test petition in TVM
        Petition::factory()->create([
            'concerned_district_id' => $this->tvm->district_id,
            'status' => Petition::STATUS_RECEIVED,
            'date_of_petition_received' => now()->format('Y-m-d'),
        ]);

        $response = $this->actingAs($this->adminUser)
            ->json('GET', route('admin.reports.district-analysis'));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'tableHtml',
            'total',
        ]);
        $this->assertGreaterThanOrEqual(1, $response->json('total'));
    }

    public function test_export_district_analysis_respects_permissions(): void
    {
        // Admin can export
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.district-analysis.export'));

        $response->assertStatus(200);
        $response->assertHeader('content-disposition');

        // CPSP User without export permission cannot export
        $responseCpsp = $this->actingAs($this->cpspUser)
            ->get(route('admin.reports.district-analysis.export'));

        $responseCpsp->assertStatus(403);
    }

    public function test_dashboard_displays_district_chart_for_authorized_users(): void
    {
        Petition::factory()->create([
            'concerned_district_id' => $this->tvm->district_id,
            'status' => Petition::STATUS_RECEIVED,
            'date_of_petition_received' => '2026-05-01',
        ]);

        // Admin has 'view district analysis chart'
        $responseAdmin = $this->actingAs($this->adminUser)
            ->get(route('dashboard'));

        $responseAdmin->assertStatus(200);
        $responseAdmin->assertSee('District Trend Visualisation');
        $responseAdmin->assertSee('id="districtTrendChart"', false);
        $responseAdmin->assertSee('name="district_from_date"', false);
        $responseAdmin->assertSee('name="district_to_date"', false);

        // CPSP User without 'view district analysis chart' does not see the chart
        $responseCpsp = $this->actingAs($this->cpspUser)
            ->get(route('dashboard'));

        $responseCpsp->assertStatus(200);
        $responseCpsp->assertDontSee('District Trend Visualisation');
        $responseCpsp->assertDontSee('id="districtTrendChart"', false);
    }

    public function test_dashboard_district_chart_date_filter_works(): void
    {
        Petition::factory()->create([
            'concerned_district_id' => $this->tvm->district_id,
            'status' => Petition::STATUS_RECEIVED,
            'date_of_petition_received' => '2026-05-10',
        ]);

        Petition::factory()->create([
            'concerned_district_id' => $this->klm->district_id,
            'status' => Petition::STATUS_RECEIVED,
            'date_of_petition_received' => '2026-06-15',
        ]);

        // Filter for May only
        $response = $this->actingAs($this->adminUser)
            ->get(route('dashboard', [
                'district_from_date' => '2026-05-01',
                'district_to_date' => '2026-05-31',
            ]));

        $response->assertStatus(200);
        $response->assertSee('District Trend Visualisation');
    }

    public function test_dashboard_district_chart_excludes_unassigned_and_has_no_duplicate_oth(): void
    {
        // 1. Create a petition with null concerned_district_id
        Petition::factory()->create([
            'concerned_district_id' => null,
            'status' => Petition::STATUS_RECEIVED,
            'date_of_petition_received' => '2026-05-15',
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('dashboard'));
        $response->assertStatus(200);

        $content = $response->getContent();
        // Extract the labels array from JavaScript districtTrendChart
        if (preg_match('/labels:\s*(\[[^\]]+\])/', $content, $matches)) {
            $labels = json_decode($matches[1], true);
            $othCount = count(array_keys($labels, 'OTH'));
            $this->assertEquals(1, $othCount, '"OTH" should appear exactly once in the district chart labels.');
            $this->assertFalse(in_array('Unassigned', $labels), '"Unassigned" should not be in the district chart labels.');
        }
    }

    public function test_master_reports_shows_district_filter_only_for_authorized_users(): void
    {
        $responseAdmin = $this->actingAs($this->adminUser)
            ->get(route('petitions.reports'));

        $responseAdmin->assertStatus(200);
        $responseAdmin->assertSee('Concerned District');
        $responseAdmin->assertSee('concerned_district_id');

        $responseCpsp = $this->actingAs($this->cpspUser)
            ->get(route('petitions.reports'));

        $responseCpsp->assertStatus(200);
        $responseCpsp->assertDontSee('name="concerned_district_id"', false);
    }

    public function test_master_reports_filters_by_concerned_district_id(): void
    {
        $petTvm = Petition::factory()->create([
            'receipt_no' => 'TVM-101/2026',
            'concerned_district_id' => $this->tvm->district_id,
            'user_id' => $this->adminUser->user_id,
        ]);

        $petKlm = Petition::factory()->create([
            'receipt_no' => 'KLM-202/2026',
            'concerned_district_id' => $this->klm->district_id,
            'user_id' => $this->adminUser->user_id,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get(route('petitions.reports', [
                'concerned_district_id' => $this->tvm->district_id,
            ]));

        $response->assertStatus(200);
        $response->assertSee('TVM-101/2026');
        $response->assertDontSee('KLM-202/2026');
    }

    public function test_districts_dropdown_displays_in_exact_database_table_order(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('petitions.reports'));

        $response->assertStatus(200);

        $content = $response->getContent();
        $tvmPos = strpos($content, 'Thiruvananthapuram (TVM)');
        $klmPos = strpos($content, 'Kollam (KLM)');
        $ptaPos = strpos($content, 'Pathanamthitta (PTA)');
        $alpPos = strpos($content, 'Alappuzha (ALP)');
        $othPos = strpos($content, 'Other State (OTH)');

        $this->assertNotFalse($tvmPos);
        $this->assertNotFalse($klmPos);
        $this->assertNotFalse($ptaPos);
        $this->assertNotFalse($alpPos);
        $this->assertNotFalse($othPos);

        // Standard database table order: 1: TVM, 2: KLM, 3: PTA, 4: ALP, ... 15: OTH
        // In alphabetical order, ALP would be before TVM and KLM.
        // In database table order, TVM is before KLM, KLM is before PTA, PTA is before ALP, ALP is before OTH!
        $this->assertLessThan($klmPos, $tvmPos);
        $this->assertLessThan($ptaPos, $klmPos);
        $this->assertLessThan($alpPos, $ptaPos);
        $this->assertLessThan($othPos, $alpPos);
    }

    public function test_status_dropdown_displays_processed_instead_of_received(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('petitions.reports'));

        $response->assertStatus(200);
        // Under Petition Timeline, option should display 'Processed' instead of 'Received'
        $response->assertSee('Processed</option>', false);
        $response->assertDontSee('>Received</option>', false);
        // Valid references like 'Verification Report Received' should remain
        $response->assertSee('Verification Report Received');

        // Check in District Wise Analysis filter dropdown
        $distResponse = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.district-analysis'));

        $distResponse->assertStatus(200);
        $distResponse->assertSee('>Processed</option>', false);
        $distResponse->assertDontSee('>Received</option>', false);
    }

    public function test_filtering_by_processed_and_received_works(): void
    {
        Petition::factory()->create([
            'concerned_district_id' => $this->tvm->district_id,
            'status' => Petition::STATUS_RECEIVED,
            'is_cpsp_processed' => true,
            'seat_id' => $this->cpspSeat->seat_id,
            'date_of_petition_received' => now()->format('Y-m-d'),
        ]);

        // Filter by status=Received
        $resReceived = $this->actingAs($this->adminUser)
            ->get(route('petitions.reports', ['status' => 'Received']));
        $resReceived->assertStatus(200);
        $resReceived->assertSee('Thiruvananthapuram');

        // Filter by status=Processed
        $resProcessed = $this->actingAs($this->adminUser)
            ->get(route('petitions.reports', ['status' => 'Processed']));
        $resProcessed->assertStatus(200);
        $resProcessed->assertSee('Thiruvananthapuram');
    }
}
