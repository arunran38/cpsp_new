<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Department;
use App\Models\Petition;
use App\Models\Seat;
use App\Models\SeatUser;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class GranularPermissionRestructureTest extends TestCase
{
    use RefreshDatabase;

    protected Seat $cpspSeat;
    protected Seat $tSectionSeat;
    protected Department $department;
    protected Petition $cpspPetition;
    protected Petition $tSectionPetition;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->department = Department::create([
            'department_name' => 'General Administration Department',
        ]);

        $cpspRole = Role::findByName('CPSP', 'web');
        $tSectionRole = Role::findByName('T Section', 'web');

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

        // CPSP Petition
        $this->cpspPetition = Petition::factory()->create([
            'seat_id' => $this->cpspSeat->seat_id,
            'receipt_no' => 'CPSP/2026/001',
            'file_no' => 'CPSP-FILE-001',
        ]);
        Address::factory()->create([
            'petition_id' => $this->cpspPetition->petition_id,
            'department_id' => $this->department->id,
            'person_type' => 'Accused',
            'person_name' => 'CPSP Person',
        ]);

        // T-Section Petition
        $this->tSectionPetition = Petition::factory()->create([
            'seat_id' => $this->tSectionSeat->seat_id,
            'receipt_no' => 'T-SEC/2026/999',
            'file_no' => 'T-SEC-FILE-999',
        ]);
        Address::factory()->create([
            'petition_id' => $this->tSectionPetition->petition_id,
            'department_id' => $this->department->id,
            'person_type' => 'Accused',
            'person_name' => 'T-Section Person',
        ]);
    }

    /**
     * Helper to create a user and assign to a seat.
     */
    protected function createUserInSeat(string $name, Seat $seat, array $permissions = []): User
    {
        $user = User::factory()->create(['name' => $name]);
        SeatUser::factory()->create([
            'user_id' => $user->user_id,
            'seat_id' => $seat->seat_id,
            'is_active' => true,
        ]);
        if (!empty($permissions)) {
            $user->givePermissionTo($permissions);
        }
        return $user;
    }

    /**
     * 1. CPSP-only clearance user cannot access T-Section petitions or reports.
     */
    public function test_cpsp_only_user_cannot_access_t_section_petitions_or_reports(): void
    {
        $user = $this->createUserInSeat('CPSP Officer', $this->cpspSeat, [
            'access cpsp section',
            'view petitions',
            'view seat diagnostics',
            'view department-wise report',
        ]);
        session(['current_seat_id' => $this->cpspSeat->seat_id]);

        $this->actingAs($user);

        // View CPSP petition allowed
        $this->get(route('petitions.show', $this->cpspPetition->hashid))
            ->assertOk();

        // View T-Section petition forbidden
        $this->get(route('petitions.show', $this->tSectionPetition->hashid))
            ->assertForbidden();

        // Requesting T-Section filter in department analysis is forbidden
        $this->get(route('admin.departments.analysis', ['section' => 't_section']))
            ->assertForbidden();

        // Department analysis default loads without T-Section
        $deptResponse = $this->get(route('admin.departments.analysis'));
        $deptResponse->assertOk()
            ->assertDontSee('T-Section Person')
            ->assertDontSee('<option value="t_section"', false);
    }

    /**
     * 2. T-Section-only clearance user cannot access CPSP petitions or reports.
     */
    public function test_t_section_only_user_cannot_access_cpsp_petitions_or_reports(): void
    {
        $user = $this->createUserInSeat('T-Section Officer', $this->tSectionSeat, [
            'access t-section',
            'view petitions',
            'view t-section diagnostics',
            'view department-wise report',
        ]);
        session(['current_seat_id' => $this->tSectionSeat->seat_id]);

        $this->actingAs($user);

        // View T-Section petition allowed
        $this->get(route('petitions.show', $this->tSectionPetition->hashid))
            ->assertOk();

        // View CPSP petition forbidden
        $this->get(route('petitions.show', $this->cpspPetition->hashid))
            ->assertForbidden();

        // Requesting CPSP filter in department analysis is forbidden
        $this->get(route('admin.departments.analysis', ['section' => 'cpsp']))
            ->assertForbidden();

        // Department analysis default loads T-Section only
        $deptResponse = $this->get(route('admin.departments.analysis'));
        $deptResponse->assertOk()
            ->assertDontSee('CPSP Person')
            ->assertDontSee('<option value="cpsp"', false);
    }

    /**
     * 3. User with both section permissions can access both sections seamlessly.
     */
    public function test_dual_clearance_user_can_access_both_sections(): void
    {
        $user = $this->createUserInSeat('Dual Officer', $this->cpspSeat, [
            'access cpsp section',
            'access t-section',
            'view petitions',
            'view all petitions',
            'view seat diagnostics',
            'view t-section diagnostics',
            'view department-wise report',
        ]);
        session(['current_seat_id' => $this->cpspSeat->seat_id]);

        $this->actingAs($user);

        // Can view both petitions
        $this->get(route('petitions.show', $this->cpspPetition->hashid))
            ->assertOk();
        $this->get(route('petitions.show', $this->tSectionPetition->hashid))
            ->assertOk();

        // Can filter both in department analysis
        $this->get(route('admin.departments.analysis', ['section' => 'cpsp']))
            ->assertOk();
        $this->get(route('admin.departments.analysis', ['section' => 't_section']))
            ->assertOk();
        $this->get(route('admin.departments.analysis', ['section' => 'all']))
            ->assertOk();
    }

    /**
     * 4. User with View Inward Registration CANNOT access Inward Statistics (403).
     */
    public function test_inward_registration_user_cannot_access_inward_statistics(): void
    {
        $regSeat = Seat::factory()->create([
            'seat_name' => 'Registration Counter 1',
            'is_active' => true,
        ]);
        $user = $this->createUserInSeat('Inward Registration Staff', $regSeat, [
            'view inward registration',
            'create inward petitions',
            'access cpsp section',
        ]);
        session(['current_seat_id' => $regSeat->seat_id]);

        $this->actingAs($user);

        // Can access Inward Registration / Create page
        $this->get(route('inward.create'))
            ->assertOk();

        // Inward Statistics is strictly forbidden (403)
        $this->get(route('inward.statistics'))
            ->assertForbidden();
    }

    /**
     * 5. User with View Inward Statistics CANNOT access Inward Registration (403).
     */
    public function test_inward_statistics_user_cannot_access_inward_registration(): void
    {
        $statsSeat = Seat::factory()->create([
            'seat_name' => 'Statistics Desk 1',
            'is_active' => true,
        ]);
        $user = $this->createUserInSeat('Statistics Staff', $statsSeat, [
            'view inward statistics',
            'inward statistics',
            'access cpsp section',
        ]);
        session(['current_seat_id' => $statsSeat->seat_id]);

        $this->actingAs($user);

        // Can access Inward Statistics
        $this->get(route('inward.statistics'))
            ->assertOk();

        // Inward Registration / Create is strictly forbidden (403)
        $this->get(route('inward.create'))
            ->assertForbidden();
    }

    /**
     * 6. User with only View Seat Diagnostics cannot access Department Report or Pendency Details.
     */
    public function test_seat_diagnostics_user_cannot_access_other_reports(): void
    {
        $diagSeat = Seat::factory()->create([
            'seat_name' => 'Diagnostics Desk 1',
            'is_active' => true,
        ]);
        $user = $this->createUserInSeat('Diagnostics Officer', $diagSeat, [
            'view seat diagnostics',
            'access cpsp section',
        ]);
        session(['current_seat_id' => $diagSeat->seat_id]);

        $this->actingAs($user);

        // Can access Seat Diagnostics
        $this->get(route('admin.seats.statistics'))
            ->assertOk();

        // Department-wise Report is forbidden (403)
        $this->get(route('admin.departments.analysis'))
            ->assertForbidden();

        // Pendency Details is forbidden (403)
        $this->get(route('admin.reports.pendency'))
            ->assertForbidden();
    }

    /**
     * 7. User with only View Department-wise Report cannot access Seat Diagnostics or Pendency Details.
     */
    public function test_department_report_user_cannot_access_other_reports(): void
    {
        $deptSeat = Seat::factory()->create([
            'seat_name' => 'Analysis Desk 1',
            'is_active' => true,
        ]);
        $user = $this->createUserInSeat('Dept Analysis Officer', $deptSeat, [
            'view department-wise report',
            'access cpsp section',
        ]);
        session(['current_seat_id' => $deptSeat->seat_id]);

        $this->actingAs($user);

        // Can access Department-wise Report
        $this->get(route('admin.departments.analysis'))
            ->assertOk();

        // Seat Diagnostics is forbidden (403)
        $this->get(route('admin.seats.statistics'))
            ->assertForbidden();

        // Pendency Details is forbidden (403)
        $this->get(route('admin.reports.pendency'))
            ->assertForbidden();
    }

    /**
     * 8. User with View Petitions but WITHOUT Update Petitions cannot edit or update petitions.
     */
    public function test_view_only_user_cannot_edit_or_update_petitions(): void
    {
        $reviewSeat = Seat::factory()->create([
            'seat_name' => 'Reviewer Desk 1',
            'is_active' => true,
        ]);
        $user = $this->createUserInSeat('Viewer Officer', $reviewSeat, [
            'view petitions',
            'access cpsp section',
        ]);
        session(['current_seat_id' => $reviewSeat->seat_id]);

        // Place petition on the user's review seat so view is authorized
        $this->cpspPetition->update(['seat_id' => $reviewSeat->seat_id]);

        $this->actingAs($user);

        // View allowed
        $this->get(route('petitions.show', $this->cpspPetition->hashid))
            ->assertOk();

        // Edit form forbidden (403)
        $this->get(route('petitions.edit', $this->cpspPetition->hashid))
            ->assertForbidden();

        // Update action forbidden (403)
        $this->put(route('petitions.update', $this->cpspPetition->hashid), [
            'description' => 'Unauthorized update attempt',
        ])->assertForbidden();
    }

    /**
     * 9. User WITHOUT export permissions cannot export reports.
     */
    public function test_user_without_export_permissions_cannot_export(): void
    {
        $viewSeat = Seat::factory()->create([
            'seat_name' => 'View Only Desk 1',
            'is_active' => true,
        ]);
        $user = $this->createUserInSeat('Non-Export Officer', $viewSeat, [
            'view department-wise report',
            'view seat diagnostics',
            'access cpsp section',
        ]);
        session(['current_seat_id' => $viewSeat->seat_id]);

        $this->actingAs($user);

        // Can view reports
        $this->get(route('admin.departments.analysis'))->assertOk();
        $this->get(route('admin.seats.statistics'))->assertOk();

        // Export department analysis forbidden (403)
        $this->get(route('admin.departments.analysis.export'))
            ->assertForbidden();

        // Export seat statistics forbidden (403)
        $this->get(route('admin.seats.statistics.export'))
            ->assertForbidden();
    }

    /**
     * 10. Normal user cannot access role/permission management or escalate privileges.
     */
    public function test_normal_user_cannot_access_roles_or_permissions_management(): void
    {
        $userSeat = Seat::factory()->create([
            'seat_name' => 'Standard Desk 1',
            'is_active' => true,
        ]);
        $user = $this->createUserInSeat('Normal User', $userSeat, [
            'view petitions',
            'access cpsp section',
        ]);
        session(['current_seat_id' => $userSeat->seat_id]);

        $this->actingAs($user);

        // Cannot view roles
        $this->get(route('admin.roles.index'))->assertForbidden();

        // Cannot view permissions
        $this->get(route('admin.permissions.index'))->assertForbidden();

        // Cannot edit user permissions
        $otherUser = User::factory()->create();
        $this->get(route('users.edit', encrypt($otherUser->user_id)))->assertForbidden();
    }

    /**
     * 11. Permission revocation immediately takes effect on next request.
     */
    public function test_permission_revocation_immediately_denies_access(): void
    {
        $revSeat = Seat::factory()->create([
            'seat_name' => 'Revocable Desk 1',
            'is_active' => true,
        ]);
        $user = $this->createUserInSeat('Revocable Officer', $revSeat, [
            'view department-wise report',
            'access cpsp section',
        ]);
        session(['current_seat_id' => $revSeat->seat_id]);

        $this->actingAs($user);

        // Access allowed initially
        $this->get(route('admin.departments.analysis'))->assertOk();

        // Revoke permission
        $user->revokePermissionTo('view department-wise report');

        // Immediate next request is forbidden (403)
        $this->get(route('admin.departments.analysis'))->assertForbidden();
    }

    /**
     * 12. Authorized administrative user can assign granular permissions via Users edit.
     */
    public function test_admin_can_assign_and_revoke_granular_permissions(): void
    {
        $admin = User::factory()->create(['name' => 'System Admin']);
        $admin->assignRole('super admin');
        session(['current_seat_id' => null]);

        $targetUser = User::factory()->create([
            'name' => 'Target Desk Officer',
            'pen' => '654321',
            'mobile_number' => '9876543210',
            'email' => 'target.desk@example.com',
        ]);
        $this->assertFalse($targetUser->canAccess('view pendency details'));

        $this->actingAs($admin);

        // Update target user with granular permissions
        $encryptedId = encrypt($targetUser->user_id);
        $response = $this->put(route('users.update', $encryptedId), [
            'name' => 'Target Desk Officer',
            'pen' => '654321',
            'mobile_number' => '9876543210',
            'email' => 'target.desk@example.com',
            'status' => 'Active',
            'designation' => 'CPO',
            'permissions' => ['view pendency details', 'access cpsp section'],
        ]);

        $response->assertRedirect(route('users.index'));
        $targetUser->refresh();

        $this->assertTrue($targetUser->canAccess('view pendency details'));
        $this->assertTrue($targetUser->canAccess('access cpsp section'));
    }

    /**
     * 13. Authorized operational workflows operate properly with granular permissions.
     */
    public function test_authorized_operational_workflows_operate_properly_with_granular_permissions(): void
    {
        $unit = \App\Models\Unit::factory()->create();

        // 1. Forwarding petition with 'forward petitions' permission
        $fwdSeat = Seat::factory()->create(['seat_name' => 'Forwarding Desk 1']);
        $fwdUser = $this->createUserInSeat('Forwarding Officer', $fwdSeat, [
            'forward petitions',
            'access cpsp section',
            'view petitions',
        ]);
        session(['current_seat_id' => $fwdSeat->seat_id]);

        $petition = Petition::factory()->create([
            'seat_id' => $fwdSeat->seat_id,
            'status' => 'Received',
            'date_of_petition_received' => '2024-01-01',
            'file_no' => 'FWD-FILE-INITIAL-1',
        ]);

        $this->actingAs($fwdUser);
        $forwardResponse = $this->post(route('forwardings.store'), [
            'petition_id' => $petition->petition_id,
            'action' => 'Forward_To_Unit',
            'director_remarks' => 'Forwarding to field unit for verification.',
            'to_unit_id' => $unit->unit_id,
            'file_no' => 'FWD-FILE-100',
            'forwarded_date' => '2024-01-05',
        ]);
        $forwardResponse->assertStatus(302);
        $this->assertDatabaseHas('petitions', ['petition_id' => $petition->petition_id, 'status' => 'Forwarded']);

        $forwarding = \App\Models\PetitionForwarding::where('petition_id', $petition->petition_id)->latest('petition_forwarding_id')->first();
        $this->assertNotNull($forwarding);

        // 2. Pullback forwarding with 'pullback forwardings' permission
        $pullbackSeat = Seat::factory()->create(['seat_name' => 'Pullback Desk 1']);
        $pullbackUser = $this->createUserInSeat('Pullback Officer', $pullbackSeat, [
            'pullback forwardings',
            'access cpsp section',
            'view petitions',
        ]);
        session(['current_seat_id' => $pullbackSeat->seat_id]);

        $this->actingAs($pullbackUser);
        $pullbackResponse = $this->delete(route('forwardings.pullback', $forwarding->petition_forwarding_id));
        $pullbackResponse->assertStatus(302);
        $this->assertSoftDeleted('petition_forwardings', ['petition_forwarding_id' => $forwarding->petition_forwarding_id]);

        // 3. Return to inward with 'return petitions to inward' permission
        $returnSeat = Seat::factory()->create(['seat_name' => 'Return Desk 1']);
        $returnUser = $this->createUserInSeat('Return Officer', $returnSeat, [
            'return petitions to inward',
            'access cpsp section',
            'view petitions',
        ]);
        session(['current_seat_id' => $returnSeat->seat_id]);

        $inwardPetition = Petition::factory()->create([
            'seat_id' => $returnSeat->seat_id,
            'status' => 'Received',
            'is_cpsp_processed' => false,
            'is_returned_to_inward' => false,
        ]);

        $this->actingAs($returnUser);
        $returnResponse = $this->post(route('petitions.returnToInward', $inwardPetition->petition_id), [
            'return_reason' => 'Incorrect department assignment',
        ]);
        $returnResponse->assertStatus(302);
        $inwardPetition->refresh();
        $this->assertTrue((bool)$inwardPetition->is_returned_to_inward);
    }
}
