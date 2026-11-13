<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\AppNotification;
use App\Models\DepartmentList;
use App\Models\District;
use App\Models\Petition;
use App\Models\PetitionDraft;
use App\Models\PetitionForwarding;
use App\Models\Seat;
use App\Models\SeatUser;
use App\Models\Upload;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SecurityAndAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $cpspUser1;
    protected User $cpspUser2;
    protected User $tSectionUser;
    protected User $inwardUser;
    protected User $plainUser;

    protected Seat $cpspSeat1;
    protected Seat $cpspSeat2;
    protected Seat $tSectionSeat;
    protected Seat $inwardSeat;

    protected District $district;
    protected DepartmentList $dept;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $this->seed(\Database\Seeders\DeveloperRoleSeeder::class);

        // Roles
        $superAdminRole = Role::firstOrCreate(['name' => 'super admin', 'guard_name' => 'web']);
        $cpspRole       = Role::firstOrCreate(['name' => 'CPSP', 'guard_name' => 'web']);
        $tSectionRole   = Role::firstOrCreate(['name' => 'T Section', 'guard_name' => 'web']);
        $inwardRole     = Role::firstOrCreate(['name' => 'Inward', 'guard_name' => 'web']);

        // Permissions
        $permAccessCpsp   = Permission::firstOrCreate(['name' => 'access cpsp section', 'guard_name' => 'web']);
        $permAccessT      = Permission::firstOrCreate(['name' => 'access t-section', 'guard_name' => 'web']);
        $permCreatePet    = Permission::firstOrCreate(['name' => 'create petitions', 'guard_name' => 'web']);
        $permUpdatePet    = Permission::firstOrCreate(['name' => 'update petitions', 'guard_name' => 'web']);
        $permDeletePet    = Permission::firstOrCreate(['name' => 'delete petitions', 'guard_name' => 'web']);
        $permForwardPet   = Permission::firstOrCreate(['name' => 'forward petitions', 'guard_name' => 'web']);
        $permInwardReg    = Permission::firstOrCreate(['name' => 'view inward registration', 'guard_name' => 'web']);
        $permCreateInward = Permission::firstOrCreate(['name' => 'create inward petitions', 'guard_name' => 'web']);

        $cpspRole->syncPermissions([$permAccessCpsp, $permCreatePet, $permUpdatePet, $permForwardPet]);
        $tSectionRole->syncPermissions([$permAccessT, $permCreatePet, $permUpdatePet]);
        $inwardRole->syncPermissions([$permInwardReg, $permCreateInward]);

        // Seats
        $this->cpspSeat1 = Seat::create(['seat_name' => 'CPSP 1', 'is_active' => true]);
        $this->cpspSeat1->assignRole($cpspRole);

        $this->cpspSeat2 = Seat::create(['seat_name' => 'CPSP 2', 'is_active' => true]);
        $this->cpspSeat2->assignRole($cpspRole);

        $this->tSectionSeat = Seat::create(['seat_name' => 'T Section Secret', 'is_active' => true]);
        $this->tSectionSeat->assignRole($tSectionRole);

        $this->inwardSeat = Seat::create(['seat_name' => 'INWARD I', 'is_active' => true]);
        $this->inwardSeat->assignRole($inwardRole);

        // Users
        $this->adminUser = User::factory()->create(['name' => 'Admin Officer', 'pen' => '100001']);
        $this->adminUser->assignRole($superAdminRole);

        $this->cpspUser1 = User::factory()->create(['name' => 'CPSP Officer 1', 'pen' => '100002']);
        $this->cpspUser1->assignRole($cpspRole);
        SeatUser::create(['user_id' => $this->cpspUser1->user_id, 'seat_id' => $this->cpspSeat1->seat_id, 'is_active' => true]);

        $this->cpspUser2 = User::factory()->create(['name' => 'CPSP Officer 2', 'pen' => '100003']);
        $this->cpspUser2->assignRole($cpspRole);
        SeatUser::create(['user_id' => $this->cpspUser2->user_id, 'seat_id' => $this->cpspSeat2->seat_id, 'is_active' => true]);

        $this->tSectionUser = User::factory()->create(['name' => 'T Section Officer', 'pen' => '100004']);
        $this->tSectionUser->assignRole($tSectionRole);
        SeatUser::create(['user_id' => $this->tSectionUser->user_id, 'seat_id' => $this->tSectionSeat->seat_id, 'is_active' => true]);

        $this->inwardUser = User::factory()->create(['name' => 'Inward Officer', 'pen' => '100005']);
        $this->inwardUser->assignRole($inwardRole);
        SeatUser::create(['user_id' => $this->inwardUser->user_id, 'seat_id' => $this->inwardSeat->seat_id, 'is_active' => true]);

        $this->plainUser = User::factory()->create(['name' => 'Unprivileged User', 'pen' => '100006']);

        // Assign plainUser to a basic seat without petition creation permission
        SeatUser::create(['user_id' => $this->plainUser->user_id, 'seat_id' => $this->inwardSeat->seat_id, 'is_active' => true]);

        // Data helpers
        $this->district = District::firstOrCreate(['district_name' => 'Thiruvananthapuram', 'district_code' => 'TVM']);
        $this->dept = DepartmentList::firstOrCreate(['department_name' => 'Vigilance & Anti-Corruption Bureau']);
        $this->unit = \App\Models\Unit::firstOrCreate(['unit_name' => 'HQ Unit'], ['unit_code' => 'HQU']);
    }

    /**
     * Test 1: Unauthenticated requests are completely blocked
     */
    public function test_unauthenticated_requests_are_redirected_to_login(): void
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');

        $responseAdmin = $this->get('/admin/dashboard');
        $responseAdmin->assertRedirect('/login');

        $responsePetitions = $this->get('/petitions');
        $responsePetitions->assertRedirect('/login');

        $responseRoles = $this->get('/admin/roles');
        $responseRoles->assertRedirect('/login');
    }

    /**
     * Test 2: Row-Level Security (RLS) - T-Section records cannot be read by CPSP or Inward users
     */
    public function test_t_section_confidential_petitions_are_blocked_from_cpsp_and_inward_users(): void
    {
        $tPetition = Petition::create([
            'receipt_no' => 'T-CONFIDENTIAL-01',
            'date_of_petition_received' => now()->toDateString(),
            'nature_of_petition' => 'Confidential Intelligence',
            'mode_of_petition_received' => 'Direct',
            'description' => 'Top secret investigation details',
            'status' => Petition::STATUS_RECEIVED,
            'seat_id' => $this->tSectionSeat->seat_id,
            'user_id' => $this->tSectionUser->user_id,
        ]);

        // CPSP User cannot view
        $responseCpsp = $this->actingAs($this->cpspUser1)
            ->withSession(['current_seat_id' => $this->cpspSeat1->seat_id])
            ->get(route('petitions.show', $tPetition->hashid));
        $responseCpsp->assertStatus(403);

        // Inward User cannot view
        $responseInward = $this->actingAs($this->inwardUser)
            ->withSession(['current_seat_id' => $this->inwardSeat->seat_id])
            ->get(route('petitions.show', $tPetition->hashid));
        $responseInward->assertStatus(403);

        // T-Section User CAN view
        $responseT = $this->actingAs($this->tSectionUser)
            ->withSession(['current_seat_id' => $this->tSectionSeat->seat_id])
            ->get(route('petitions.show', $tPetition->hashid));
        $responseT->assertStatus(200);
    }

    /**
     * Test 3: Cross-Seat Data Isolation - Officer in Seat A cannot view or edit Seat B's petition
     */
    public function test_cross_seat_petition_modification_is_strictly_forbidden(): void
    {
        $petitionSeat2 = Petition::create([
            'receipt_no' => 'CPSP2-PRIVATE-01',
            'date_of_petition_received' => now()->toDateString(),
            'nature_of_petition' => 'Misappropriation',
            'mode_of_petition_received' => 'Email',
            'description' => 'Only for Seat 2 officer',
            'status' => Petition::STATUS_RECEIVED,
            'is_cpsp_processed' => true,
            'seat_id' => $this->cpspSeat2->seat_id,
            'user_id' => $this->cpspUser2->user_id,
            'concerned_district_id' => $this->district->district_id,
        ]);

        // Officer in Seat 1 tries to UPDATE Seat 2's petition
        $updatePayload = [
            'nature_of_petition' => 'Hacked Nature',
            'mode_of_petition_received' => 'Direct',
            'concerned_district_id' => $this->district->district_id,
            'description' => 'Unauthorized overwrite attempt',
            'accused' => [
                [
                    'name' => 'Suspect Name',
                    'department_id' => $this->dept->id,
                ]
            ]
        ];

        $response = $this->actingAs($this->cpspUser1)
            ->withSession(['current_seat_id' => $this->cpspSeat1->seat_id])
            ->put(route('petitions.update', $petitionSeat2->petition_id), $updatePayload);

        $response->assertStatus(403);

        // Verify database did NOT change
        $this->assertDatabaseMissing('petitions', [
            'petition_id' => $petitionSeat2->petition_id,
            'description' => 'Unauthorized overwrite attempt',
        ]);
    }

    /**
     * Test 4: Delete Authorization - Regular user cannot delete; only authorized role can delete
     */
    public function test_regular_user_cannot_delete_petition(): void
    {
        $petition = Petition::create([
            'receipt_no' => 'DELETE-TEST-01',
            'date_of_petition_received' => now()->toDateString(),
            'nature_of_petition' => 'Bribery',
            'mode_of_petition_received' => 'Direct',
            'description' => 'Test petition description',
            'status' => Petition::STATUS_RECEIVED,
            'seat_id' => $this->cpspSeat1->seat_id,
            'user_id' => $this->cpspUser1->user_id,
        ]);

        // CPSP User without delete permission tries to delete
        $response = $this->actingAs($this->cpspUser1)
            ->withSession(['current_seat_id' => $this->cpspSeat1->seat_id])
            ->delete(route('petitions.destroy', $petition->petition_id));

        $response->assertStatus(403);
        $this->assertNotSoftDeleted('petitions', ['petition_id' => $petition->petition_id]);

        // Super Admin CAN delete
        $responseAdmin = $this->actingAs($this->adminUser)
            ->delete(route('petitions.destroy', $petition->petition_id));

        $responseAdmin->assertRedirect(route('petitions.index', ['tab' => 'all']));
        $this->assertSoftDeleted('petitions', ['petition_id' => $petition->petition_id]);
    }

    /**
     * Test 5: IDOR Attachment Protection - User cannot download confidential attachments
     */
    public function test_unauthorized_user_cannot_download_petition_attachments(): void
    {
        Storage::fake('public');
        $fakePath = 'Uploads/test_confidential.pdf';
        Storage::disk('public')->put($fakePath, 'Secret document contents');

        $tPetition = Petition::create([
            'receipt_no' => 'T-ATTACHMENT-01',
            'date_of_petition_received' => now()->toDateString(),
            'nature_of_petition' => 'Secret Evidence',
            'mode_of_petition_received' => 'Direct',
            'description' => 'Evidence attached',
            'status' => Petition::STATUS_RECEIVED,
            'seat_id' => $this->tSectionSeat->seat_id,
            'user_id' => $this->tSectionUser->user_id,
        ]);

        $upload = Upload::create([
            'petition_id' => $tPetition->petition_id,
            'category' => Upload::CATEGORY_PETITION_DOCUMENT,
            'original_filename' => 'test_confidential.pdf',
            'file_path' => $fakePath,
            'uploaded_by' => $this->tSectionUser->user_id,
        ]);

        // CPSP user tries to download T-Section attachment
        $response = $this->actingAs($this->cpspUser1)
            ->withSession(['current_seat_id' => $this->cpspSeat1->seat_id])
            ->get(route('petitions.download', $upload->upload_id));

        $response->assertStatus(403);
    }

    /**
     * Test 6: Private User Data Protection - Drafts are isolated per user
     */
    public function test_user_cannot_view_or_clear_another_users_draft(): void
    {
        // User 1 creates draft
        PetitionDraft::create([
            'user_id' => $this->cpspUser1->user_id,
            'type' => 'create',
            'step' => 1,
            'form_data' => ['description' => 'User 1 private draft notes'],
        ]);

        // User 2 clears draft
        $response = $this->actingAs($this->cpspUser2)
            ->withSession(['current_seat_id' => $this->cpspSeat2->seat_id])
            ->deleteJson(route('petitions.draft.clear'), ['type' => 'create']);

        $response->assertStatus(200);

        // User 1's draft must STILL exist
        $this->assertDatabaseHas('petition_drafts', [
            'user_id' => $this->cpspUser1->user_id,
            'type' => 'create',
        ]);
    }

    /**
     * Test 7: Private User Notifications - User cannot mark another user's notifications as read
     */
    public function test_user_cannot_mark_or_delete_another_users_notification(): void
    {
        // Notification strictly for User 1
        $notification = AppNotification::create([
            'user_id' => $this->cpspUser1->user_id,
            'seat_id' => $this->cpspSeat1->seat_id,
            'type' => 'petition_transferred',
            'title' => 'Personal Notice for User 1',
            'message' => 'Private message content',
        ]);

        // User 2 tries to mark it as read
        $response = $this->actingAs($this->cpspUser2)
            ->withSession(['current_seat_id' => $this->cpspSeat2->seat_id])
            ->postJson(route('notifications.mark_read', $notification->id));

        // It returns success: true without updating the record because of recipient scoping
        $this->assertNull(AppNotification::find($notification->id)->read_at);
    }

    /**
     * Test 8: Create Authorization - User without create permission cannot store petition
     */
    public function test_user_without_create_permission_cannot_create_petition(): void
    {
        $payload = [
            'nature_of_petition' => 'Bribery',
            'mode_of_petition_received' => 'Direct',
            'concerned_district_id' => $this->district->district_id,
            'description' => 'Unauthorized create attempt',
            'accused' => [
                [
                    'name' => 'Suspect Name',
                    'department_id' => $this->dept->id,
                ]
            ]
        ];

        $response = $this->actingAs($this->plainUser)
            ->withSession(['current_seat_id' => $this->inwardSeat->seat_id])
            ->post(route('petitions.store'), $payload);

        $response->assertStatus(403);
    }

    /**
     * Test 9: Admin Actions Security - Admin management routes are strictly forbidden to regular users
     */
    public function test_admin_routes_are_forbidden_to_regular_users(): void
    {
        // 1. Roles management
        $responseRoles = $this->actingAs($this->cpspUser1)
            ->withSession(['current_seat_id' => $this->cpspSeat1->seat_id])
            ->get(route('admin.roles.index'));
        $responseRoles->assertStatus(403);

        // 2. Permissions management
        $responsePerms = $this->actingAs($this->cpspUser1)
            ->withSession(['current_seat_id' => $this->cpspSeat1->seat_id])
            ->get(route('admin.permissions.index'));
        $responsePerms->assertStatus(403);

        // 3. User management
        $responseUsers = $this->actingAs($this->cpspUser1)
            ->withSession(['current_seat_id' => $this->cpspSeat1->seat_id])
            ->get(route('users.index'));
        $responseUsers->assertStatus(403);

        // 4. Audit logs
        $responseLogs = $this->actingAs($this->cpspUser1)
            ->withSession(['current_seat_id' => $this->cpspSeat1->seat_id])
            ->get(route('admin.audit_logs.index'));
        $responseLogs->assertStatus(403);
    }

    /**
     * Test 10: Forwarding Pullback Authorization - Unauthorized user cannot pullback forwarding
     */
    public function test_forwarding_pullback_requires_authorization_on_petition(): void
    {
        $petition = Petition::create([
            'receipt_no' => 'PULLBACK-SEC-01',
            'date_of_petition_received' => now()->toDateString(),
            'nature_of_petition' => 'Bribery',
            'mode_of_petition_received' => 'Direct',
            'description' => 'Test petition description',
            'status' => Petition::STATUS_FORWARDED,
            'seat_id' => $this->cpspSeat1->seat_id,
            'user_id' => $this->cpspUser1->user_id,
        ]);

        $forwarding = PetitionForwarding::create([
            'petition_id' => $petition->petition_id,
            'from_seat_id' => $this->cpspSeat1->seat_id,
            'to_unit_id' => $this->unit->unit_id,
            'forwarded_date' => now()->toDateString(),
            'processed_by_user_id' => $this->cpspUser1->user_id,
        ]);

        // Officer 2 from Seat 2 tries to pullback Officer 1's forwarding
        $response = $this->actingAs($this->cpspUser2)
            ->withSession(['current_seat_id' => $this->cpspSeat2->seat_id])
            ->delete(route('forwardings.pullback', $forwarding->petition_forwarding_id));

        // Must be forbidden (either 403 or redirect back with unauthorized error)
        $this->assertTrue(in_array($response->getStatusCode(), [302, 403]));
        if ($response->getStatusCode() === 302) {
            $response->assertSessionHas('error');
        }

        // The forwarding record must STILL exist
        $this->assertDatabaseHas('petition_forwardings', [
            'petition_forwarding_id' => $forwarding->petition_forwarding_id,
        ]);
    }
}
