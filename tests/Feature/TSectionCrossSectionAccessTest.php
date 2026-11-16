<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Decision;
use App\Models\Department;
use App\Models\Petition;
use App\Models\PetitionForwarding;
use App\Models\Seat;
use App\Models\SeatUser;
use App\Models\User;
use App\Services\HashidService;
use App\Services\TSectionPermissionRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TSectionCrossSectionAccessTest extends TestCase
{
    use RefreshDatabase;

    protected User $cpspUser;
    protected User $tSectionUser;
    protected Seat $cpspSeat;
    protected Seat $tSectionSeat;
    protected Department $department;
    protected Petition $cpspPetition;
    protected Petition $tSectionPetition;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Roles
        $cpspRole = Role::firstOrCreate(['name' => 'CPSP', 'guard_name' => 'web']);
        $tSectionRole = Role::firstOrCreate(['name' => 'T Section', 'guard_name' => 'web']);
        $inwardRole = Role::firstOrCreate(['name' => 'Inward', 'guard_name' => 'web']);

        // 2. Permissions
        $permissions = [
            'view petitions',
            'create petitions',
            'update petitions',
            'delete petitions',
            'link petitions',
            'access cpsp section',
            'access t-section',
            'view file transfer',
            'export petitions',
            'view master reports',
            TSectionPermissionRegistry::VIEW_CPSP_READONLY,
            TSectionPermissionRegistry::VIEW_ASSIGNED_INWARD,
            TSectionPermissionRegistry::LINK_RELATED,
            TSectionPermissionRegistry::VIEW_LINKED_REFERENCES,
        ];
        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // 3. Seats
        $this->cpspSeat = Seat::factory()->create([
            'seat_name' => 'CPSP 1',
            'is_active' => true,
        ]);
        $this->cpspSeat->assignRole($cpspRole);

        $this->tSectionSeat = Seat::factory()->create([
            'seat_name' => 'T Section 1',
            'is_active' => true,
        ]);
        $this->tSectionSeat->assignRole($tSectionRole);

        // 4. Users
        // CPSP Officer
        $this->cpspUser = User::factory()->create([
            'name' => 'CPSP User',
        ]);
        $this->cpspUser->assignRole($cpspRole);
        $this->cpspUser->givePermissionTo(['view petitions', 'create petitions', 'update petitions', 'delete petitions', 'link petitions', 'access cpsp section']);
        SeatUser::factory()->create([
            'user_id' => $this->cpspUser->user_id,
            'seat_id' => $this->cpspSeat->seat_id,
            'is_active' => true,
        ]);

        // T-Section Officer
        $this->tSectionUser = User::factory()->create([
            'name' => 'T-Section User',
        ]);
        $this->tSectionUser->assignRole($tSectionRole);
        $this->tSectionUser->givePermissionTo([
            'view petitions',
            'create petitions',
            'update petitions',
            'delete petitions',
            'link petitions',
            'access t-section',
            TSectionPermissionRegistry::VIEW_CPSP_READONLY,
            TSectionPermissionRegistry::VIEW_ASSIGNED_INWARD,
            TSectionPermissionRegistry::LINK_RELATED,
            TSectionPermissionRegistry::VIEW_LINKED_REFERENCES,
        ]);
        SeatUser::factory()->create([
            'user_id' => $this->tSectionUser->user_id,
            'seat_id' => $this->tSectionSeat->seat_id,
            'is_active' => true,
        ]);

        $this->department = Department::create([
            'department_name' => 'Revenue Department',
        ]);

        // 5. CPSP Petition with sensitive workflow (forwarding, decision)
        $this->cpspPetition = Petition::factory()->create([
            'seat_id' => $this->cpspSeat->seat_id,
            'user_id' => $this->cpspUser->user_id,
            'receipt_no' => 'CPSP/2026/101',
            'nature_of_petition' => 'Corruption Allegation',
            'description' => 'Confidential CPSP matter description',
            'status' => Petition::STATUS_INTERNAL_VIGILANCE,
            'is_cpsp_processed' => true,
            'date_of_petition_received' => now()->toDateString(),
        ]);
        Address::factory()->create([
            'petition_id' => $this->cpspPetition->petition_id,
            'department_id' => $this->department->id,
            'person_type' => 'Complainant',
            'person_name' => 'Ramesh Kumar',
        ]);
        Address::factory()->create([
            'petition_id' => $this->cpspPetition->petition_id,
            'department_id' => $this->department->id,
            'person_type' => 'Accused',
            'person_name' => 'Suresh Officer',
        ]);
        Decision::factory()->create([
            'petition_id' => $this->cpspPetition->petition_id,
            'final_decision' => 'Internal_Vigilance',
            'directorate_order_number' => 'DIR-CONF-9876',
            'final_remarks' => 'Highly confidential internal CPSP decision notes',
            'processed_by_user_id' => $this->cpspUser->user_id,
            'decided_by_seat_id' => $this->cpspSeat->seat_id,
            'decision_date' => now()->toDateString(),
        ]);

        // 6. T-Section Petition
        $this->tSectionPetition = Petition::factory()->create([
            'seat_id' => $this->tSectionSeat->seat_id,
            'user_id' => $this->tSectionUser->user_id,
            'receipt_no' => 'TSEC/2026/505',
            'nature_of_petition' => 'Special Intelligence Enquiry',
            'description' => 'Secret T-Section inquiry',
            'status' => Petition::STATUS_RECEIVED,
            'is_cpsp_processed' => true,
            'date_of_petition_received' => now()->toDateString(),
        ]);
        Address::factory()->create([
            'petition_id' => $this->tSectionPetition->petition_id,
            'department_id' => $this->department->id,
            'person_type' => 'Complainant',
            'person_name' => 'Ramesh Kumar',
        ]);
    }

    public function test_t_section_user_can_view_cpsp_petition_in_read_only_mode(): void
    {
        $response = $this->actingAs($this->tSectionUser)
            ->get(route('petitions.show', $this->cpspPetition->hashid));

        $response->assertOk();
        // Basic details visible
        $response->assertSee('CPSP/2026/101');
        $response->assertSee('Corruption Allegation');
        $response->assertSee('Ramesh Kumar');
        $response->assertSee('Suresh Officer');
        $response->assertSee('CPSP Reference (Read-Only Mode)');

        // Action buttons strictly absent for T-Section on CPSP petition
        $response->assertDontSee('Edit Details');
        $response->assertDontSee('Add Further Details');
        $response->assertDontSee('Return to Inward');

        // Sensitive workflow restricted banner shown, sensitive details hidden
        $response->assertSee('Workflow Details Restricted');
        $response->assertDontSee('DIR-CONF-9876');
        $response->assertDontSee('Highly confidential internal CPSP decision notes');
    }

    public function test_t_section_user_cannot_view_cpsp_workflow_details_policy(): void
    {
        $this->assertFalse($this->cpspPetition->canViewWorkflowDetails($this->tSectionUser));
        $this->assertFalse($this->tSectionUser->can('viewWorkflow', $this->cpspPetition));

        // CPSP user can view workflow of their own CPSP petition
        $this->assertTrue($this->cpspPetition->canViewWorkflowDetails($this->cpspUser));
        $this->assertTrue($this->cpspUser->can('viewWorkflow', $this->cpspPetition));
    }

    public function test_t_section_user_cannot_edit_update_or_delete_cpsp_petition(): void
    {
        // Edit screen forbidden
        $editResponse = $this->actingAs($this->tSectionUser)
            ->get(route('petitions.edit', $this->cpspPetition->hashid));
        $editResponse->assertForbidden();

        // Update forbidden
        $updateResponse = $this->actingAs($this->tSectionUser)
            ->put(route('petitions.update', $this->cpspPetition->hashid), [
                'receipt_no' => 'HACKED-RECEIPT',
            ]);
        $updateResponse->assertForbidden();

        // Delete forbidden
        $deleteResponse = $this->actingAs($this->tSectionUser)
            ->delete(route('petitions.destroy', $this->cpspPetition->hashid));
        $deleteResponse->assertForbidden();
    }

    public function test_cpsp_user_cannot_view_or_access_t_section_petition(): void
    {
        // View forbidden
        $showResponse = $this->actingAs($this->cpspUser)
            ->get(route('petitions.show', $this->tSectionPetition->hashid));
        $showResponse->assertForbidden();

        // Edit forbidden
        $editResponse = $this->actingAs($this->cpspUser)
            ->get(route('petitions.edit', $this->tSectionPetition->hashid));
        $editResponse->assertForbidden();

        // Delete forbidden
        $deleteResponse = $this->actingAs($this->cpspUser)
            ->delete(route('petitions.destroy', $this->tSectionPetition->hashid));
        $deleteResponse->assertForbidden();
    }

    public function test_t_section_user_can_only_view_inward_petitions_assigned_to_their_seat(): void
    {
        $inwardRole = Role::firstOrCreate(['name' => 'Inward', 'guard_name' => 'web']);
        $inwardSeat = Seat::factory()->create([
            'seat_name' => 'Inward Registration Seat',
            'is_active' => true,
        ]);
        $inwardSeat->assignRole($inwardRole);
        $inwardUser = User::factory()->create(['name' => 'Inward Clerk']);
        $inwardUser->assignRole($inwardRole);
        SeatUser::factory()->create([
            'user_id' => $inwardUser->user_id,
            'seat_id' => $inwardSeat->seat_id,
            'is_active' => true,
        ]);

        // Inward petition assigned to CPSP seat
        $cpspInward = Petition::factory()->create([
            'seat_id' => $this->cpspSeat->seat_id,
            'user_id' => $inwardUser->user_id,
            'created_by_seat_id' => $inwardSeat->seat_id,
            'receipt_no' => 'INW/CPSP/001',
            'status' => Petition::STATUS_RECEIVED,
            'is_cpsp_processed' => false,
        ]);

        // Inward petition assigned to T-Section seat
        $tSectionInward = Petition::factory()->create([
            'seat_id' => $this->tSectionSeat->seat_id,
            'user_id' => $inwardUser->user_id,
            'created_by_seat_id' => $inwardSeat->seat_id,
            'receipt_no' => 'INW/TSEC/002',
            'status' => Petition::STATUS_RECEIVED,
            'is_cpsp_processed' => false,
        ]);

        // T-Section user viewing CPSP Inward petition -> Forbidden
        $responseCpspInw = $this->actingAs($this->tSectionUser)
            ->get(route('petitions.show', $cpspInward->hashid));
        $responseCpspInw->assertForbidden();

        // T-Section user viewing assigned T-Section Inward petition -> Allowed
        $responseTSecInw = $this->actingAs($this->tSectionUser)
            ->get(route('petitions.show', $tSectionInward->hashid));
        $responseTSecInw->assertOk();
        $responseTSecInw->assertSee('INW/TSEC/002');

        // Scoped accessible query strictly filters out CPSP inward
        $accessible = Petition::accessibleInwardPetitions($this->tSectionUser)->pluck('petition_id')->all();
        $this->assertContains($tSectionInward->petition_id, $accessible);
        $this->assertNotContains($cpspInward->petition_id, $accessible);
    }

    public function test_option_b_cross_section_linking_preserves_independent_workflow(): void
    {
        // T-Section officer links T-Section petition to CPSP petition using original_receipt_no
        $response = $this->actingAs($this->tSectionUser)
            ->post(route('petitions.linkDuplicate', $this->tSectionPetition->hashid), [
                'original_receipt_no' => $this->cpspPetition->receipt_no,
            ]);

        $response->assertRedirect(route('petitions.show', $this->tSectionPetition->hashid));

        $this->tSectionPetition->refresh();
        $this->cpspPetition->refresh();

        // 1. Reference linked
        $this->assertEquals($this->cpspPetition->petition_id, $this->tSectionPetition->linked_petition_id);

        // 2. Cross-section check returns true
        $this->assertTrue($this->tSectionPetition->isCrossSectionLinked());

        // 3. Option B requirements: Status remains independent (NOT changed to Duplicate)
        $this->assertEquals(Petition::STATUS_RECEIVED, $this->tSectionPetition->status);

        // 4. CPSP petition is unaffected
        $this->assertEquals(Petition::STATUS_INTERNAL_VIGILANCE, $this->cpspPetition->status);
        $this->assertNull($this->cpspPetition->linked_petition_id);

        // 5. Viewing T-Section petition displays the linked cross-section reference
        $viewResponse = $this->actingAs($this->tSectionUser)
            ->get(route('petitions.show', $this->tSectionPetition->hashid));
        $viewResponse->assertOk();
        $viewResponse->assertSee('CPSP/2026/101');
        $viewResponse->assertSee('Cross-Section Ref');

        // 6. CPSP user viewing CPSP petition does NOT see the secret T-Section linked duplicate
        $cpspViewResponse = $this->actingAs($this->cpspUser)
            ->get(route('petitions.show', $this->cpspPetition->hashid));
        $cpspViewResponse->assertOk();
        $cpspViewResponse->assertDontSee('TSEC/2026/505');
    }

    public function test_unlinking_cross_section_petition(): void
    {
        // Link first
        $this->tSectionPetition->linked_petition_id = $this->cpspPetition->petition_id;
        $this->tSectionPetition->save();

        $this->assertTrue($this->tSectionPetition->isCrossSectionLinked());

        // Unlink
        $response = $this->actingAs($this->tSectionUser)
            ->post(route('petitions.unlinkDuplicate', $this->tSectionPetition->hashid));

        $response->assertRedirect(route('petitions.show', $this->tSectionPetition->hashid));

        $this->tSectionPetition->refresh();
        $this->assertNull($this->tSectionPetition->linked_petition_id);
        $this->assertFalse($this->tSectionPetition->isCrossSectionLinked());
        $this->assertEquals(Petition::STATUS_RECEIVED, $this->tSectionPetition->status);
    }

    public function test_t_section_operational_tabs_strictly_isolate_cpsp_workflow(): void
    {
        // 1. T-Section operational tab 'decisions':
        // CPSP petition has status 'Internal_Vigilance' (which is a decision status).
        // It must NOT appear in T-Section user's 'decisions' tab!
        $responseDecisions = $this->actingAs($this->tSectionUser)
            ->get(route('petitions.index', ['tab' => 'decisions']));

        $responseDecisions->assertOk();
        $responseDecisions->assertDontSee('CPSP/2026/101');

        // 2. Tab counts for T-Section user only reflect T-Section workload:
        // CPSP petition decision is not counted in 'decisions' count for T-Section user
        $counts = $responseDecisions->viewData('unifiedCounts');
        $this->assertEquals(0, $counts['decisions']);
        $this->assertEquals(1, $counts['received']); // T-Section petition is status Received
        $this->assertEquals(1, $counts['all']);      // Only T-Section processed petition counted
    }

    public function test_t_section_view_petitions_section_filter_and_defaults(): void
    {
        // 1. Default tab=all without search defaults to 't_section':
        // Shows T-Section petition, hides CPSP reference petition to prevent clutter
        $responseDefault = $this->actingAs($this->tSectionUser)
            ->get(route('petitions.index', ['tab' => 'all']));

        $responseDefault->assertOk();
        $responseDefault->assertSee('TSEC/2026/505');
        $responseDefault->assertDontSee('CPSP/2026/101');
        $responseDefault->assertSee('T-Section');

        // 2. When section=all is requested, both appear:
        $responseAllSections = $this->actingAs($this->tSectionUser)
            ->get(route('petitions.index', ['tab' => 'all', 'section' => 'all']));

        $responseAllSections->assertOk();
        $responseAllSections->assertSee('TSEC/2026/505');
        $responseAllSections->assertSee('CPSP/2026/101');

        // Verify visual badges:
        $responseAllSections->assertSee('T-Section');
        $responseAllSections->assertSee('CPSP Ref');

        // Verify masked status & processing in table view for CPSP reference petition:
        $responseAllSections->assertSee('CPSP Processed');
        $responseAllSections->assertSee('Reference Only');
        $responseAllSections->assertDontSee('Final: Internal Vigilance');

        // 3. When section=cpsp is requested, only CPSP reference petitions appear:
        $responseCpspOnly = $this->actingAs($this->tSectionUser)
            ->get(route('petitions.index', ['tab' => 'all', 'section' => 'cpsp']));

        $responseCpspOnly->assertOk();
        $responseCpspOnly->assertSee('CPSP/2026/101');
        $responseCpspOnly->assertDontSee('TSEC/2026/505');

        // 4. When searching by keyword or receipt number with section=all, searches cross-section:
        $responseSearch = $this->actingAs($this->tSectionUser)
            ->get(route('petitions.index', ['tab' => 'all', 'section' => 'all', 'search' => 'CPSP/2026/101']));

        $responseSearch->assertOk();
        $responseSearch->assertSee('CPSP/2026/101');
    }

    public function test_t_section_master_reports_defaults_to_t_section(): void
    {
        $this->tSectionUser->givePermissionTo('view master reports');

        // 1. Loading reports without section filter defaults to 't_section'
        $responseReports = $this->actingAs($this->tSectionUser)
            ->get(route('petitions.reports'));

        $responseReports->assertOk();
        $responseReports->assertSee('TSEC/2026/505');
        $responseReports->assertDontSee('CPSP/2026/101');

        // 2. Loading reports with section=all shows both
        $responseAllReports = $this->actingAs($this->tSectionUser)
            ->get(route('petitions.reports', ['section' => 'all']));

        $responseAllReports->assertOk();
        $responseAllReports->assertSee('TSEC/2026/505');
        $responseAllReports->assertSee('CPSP/2026/101');
    }

    public function test_cpsp_workflow_details_masked_in_excel_export_for_t_section_user(): void
    {
        $this->tSectionUser->givePermissionTo(['export petitions', 'view master reports']);

        $response = $this->actingAs($this->tSectionUser)
            ->get(route('petitions.export', ['tab' => 'all', 'section' => 'all']));

        $response->assertOk();
        $content = $response->getContent();

        // Verify CPSP petition basic info is present
        $this->assertStringContainsString('CPSP/2026/101', $content);

        // Verify sensitive workflow is masked
        $this->assertStringContainsString('CPSP Processed', $content);
        $this->assertStringNotContainsString('DIR-CONF-9876', $content);
        $this->assertStringNotContainsString('Internal_Vigilance', $content);
        $this->assertStringNotContainsString('Highly confidential internal CPSP decision notes', $content);
    }

    public function test_duplicate_detection_includes_seat_and_user_name_and_masks_cpsp_workflow_for_t_section(): void
    {
        $response = $this->actingAs($this->tSectionUser)
            ->postJson(route('petitions.checkDuplicates'), [
                'complainants' => [['name' => 'Ramesh Kumar']],
                'accused' => [['name' => 'Suresh Officer']],
            ]);

        $response->assertOk();
        $duplicates = $response->json();
        $this->assertNotEmpty($duplicates);

        $match = collect($duplicates)->firstWhere('receipt_no', 'CPSP/2026/101');
        $this->assertNotNull($match);
        $this->assertEquals('CPSP 1', $match['seat_name']);
        $this->assertEquals('CPSP User', $match['user_name']);

        // Workflow details masked for T-Section user:
        $this->assertEquals('CPSP Processed', $match['status']);
        $this->assertNull($match['decision']);
    }
}
