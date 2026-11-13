<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\AppNotification;
use App\Models\Petition;
use App\Models\Seat;
use App\Models\SeatUser;
use App\Models\User;
use App\Services\DuplicateDetectionService;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class DuplicateDetectionAndNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected User $inwardUser;
    protected User $cpspUser;
    protected Seat $inwardSeat;
    protected Seat $cpspSeat;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles & permissions needed
        $roleInward = Role::firstOrCreate(['name' => 'Inward', 'guard_name' => 'web']);
        $roleCpsp = Role::firstOrCreate(['name' => 'CPSP', 'guard_name' => 'web']);

        $permAccessCpsp = Permission::firstOrCreate(['name' => 'access cpsp section', 'guard_name' => 'web']);
        $permViewDup = Permission::firstOrCreate(['name' => 'view duplicate warnings', 'guard_name' => 'web']);
        $permLinkDup = Permission::firstOrCreate(['name' => 'link petitions', 'guard_name' => 'web']);
        $permReturnInward = Permission::firstOrCreate(['name' => 'return petitions to inward', 'guard_name' => 'web']);

        $roleCpsp->givePermissionTo([$permAccessCpsp, $permViewDup, $permLinkDup, $permReturnInward]);
        $roleInward->givePermissionTo([$permAccessCpsp, $permViewDup]);

        // Create seats
        $this->inwardSeat = Seat::create([
            'seat_name' => 'Inward 1',
            'is_active' => true,
        ]);
        $this->inwardSeat->roles()->sync([$roleInward->id]);

        $this->cpspSeat = Seat::create([
            'seat_name' => 'CPSP 1',
            'is_active' => true,
        ]);
        $this->cpspSeat->roles()->sync([$roleCpsp->id]);

        // Create users
        $this->inwardUser = User::factory()->create(['name' => 'Inward Officer']);
        $this->inwardUser->assignRole($roleInward);
        SeatUser::create([
            'user_id' => $this->inwardUser->user_id,
            'seat_id' => $this->inwardSeat->seat_id,
            'is_active' => true,
            'assigned_date' => now(),
        ]);

        $this->cpspUser = User::factory()->create(['name' => 'CPSP Officer']);
        $this->cpspUser->assignRole($roleCpsp);
        SeatUser::create([
            'user_id' => $this->cpspUser->user_id,
            'seat_id' => $this->cpspSeat->seat_id,
            'is_active' => true,
            'assigned_date' => now(),
        ]);
    }

    /**
     * Test Soundex / Fuzzy duplicate check finds phonetically similar names
     */
    public function test_soundex_duplicate_check_detects_phonetic_variations(): void
    {
        // 1. Create a base petition with Complainant "Muhammed" and Accused "Suresh"
        $petition = Petition::create([
            'receipt_no' => 'DUP-TEST-1',
            'date_of_petition_received' => now()->toDateString(),
            'nature_of_petition' => 'Bribery Allegation',
            'mode_of_petition_received' => 'Email',
            'description' => 'Test petition description',
            'status' => Petition::STATUS_RECEIVED,
            'seat_id' => $this->cpspSeat->seat_id,
            'user_id' => $this->inwardUser->user_id,
        ]);

        Address::create([
            'petition_id' => $petition->petition_id,
            'person_type' => 'Complainant',
            'person_name' => 'Muhammed Shafi',
            'phone' => '9847000001',
            'full_address' => 'Palakkad, Kerala',
        ]);

        Address::create([
            'petition_id' => $petition->petition_id,
            'person_type' => 'Accused',
            'person_name' => 'Suresh Kumar',
            'phone' => '9847000002',
            'full_address' => 'Thrissur, Kerala',
        ]);

        // 2. Query with phonetic spelling variation: "Mohammed" instead of "Muhammed", and "Sooresh" instead of "Suresh"
        $results = DuplicateDetectionService::checkDuplicates(
            [['name' => 'Mohammed Shafi', 'phone' => '']],
            [['name' => 'Sooresh Kumar', 'phone' => '', 'pen_number' => '']],
            null,
            null,
            $this->cpspUser
        );

        $this->assertNotEmpty($results, 'Duplicate detector should identify phonetic matches via Soundex');
        $matched = $results[0];
        $this->assertEquals($petition->receipt_no, $matched['receipt_no']);
        $this->assertTrue(in_array($matched['match_type'], ['soundex', 'fuzzy', 'exact']));
        $this->assertGreaterThanOrEqual(70, $matched['match_confidence']);
    }

    /**
     * Test autocomplete duplicate search returns matches with Soundex query
     */
    public function test_autocomplete_search_duplicates_with_soundex(): void
    {
        $petition = Petition::create([
            'receipt_no' => 'DUP-SEARCH-2',
            'date_of_petition_received' => now()->toDateString(),
            'nature_of_petition' => 'Corruption Complaint',
            'mode_of_petition_received' => 'Direct',
            'description' => 'Test petition description for search',
            'status' => Petition::STATUS_RECEIVED,
            'seat_id' => $this->cpspSeat->seat_id,
            'user_id' => $this->cpspUser->user_id,
        ]);

        Address::create([
            'petition_id' => $petition->petition_id,
            'person_type' => 'Complainant',
            'person_name' => 'Vijayan Nair',
            'full_address' => 'Kollam, Kerala',
        ]);

        // Search using phonetic variation "Vijian"
        $response = $this->actingAs($this->cpspUser)
            ->getJson(route('petitions.searchDuplicates', ['q' => 'Vijian']));

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertNotEmpty($data);
        $this->assertEquals('DUP-SEARCH-2', $data[0]['receipt_no']);
    }

    /**
     * Test in-app bell notification lifecycle (send, unread count, list, mark read, mark all read)
     */
    public function test_in_app_notification_lifecycle(): void
    {
        // 1. Send notification to CPSP Seat
        $created = NotificationService::sendToSeat(
            $this->cpspSeat->seat_id,
            'petition_transferred',
            'New Petition Assigned (101/2026)',
            'Receipt 101/2026 has been transferred to your desk.',
            '/petitions/test-101',
            'file-input',
            'indigo'
        );

        $this->assertCount(1, $created);
        $notificationId = $created[0]->id;

        // 2. CPSP user checks unread count
        session(['current_seat_id' => $this->cpspSeat->seat_id]);
        $responseCount = $this->actingAs($this->cpspUser)
            ->withSession(['current_seat_id' => $this->cpspSeat->seat_id])
            ->getJson(route('notifications.unread_count'));

        $responseCount->assertStatus(200);
        $responseCount->assertJson(['unread_count' => 1]);

        // 3. Fetch notifications list
        $responseList = $this->actingAs($this->cpspUser)
            ->withSession(['current_seat_id' => $this->cpspSeat->seat_id])
            ->getJson(route('notifications.index'));

        $responseList->assertStatus(200);
        $data = $responseList->json();
        $this->assertCount(1, $data['notifications']);
        $this->assertEquals('New Petition Assigned (101/2026)', $data['notifications'][0]['title']);
        $this->assertFalse($data['notifications'][0]['is_read']);

        // 4. Mark single notification as read
        $responseRead = $this->actingAs($this->cpspUser)
            ->withSession(['current_seat_id' => $this->cpspSeat->seat_id])
            ->postJson(route('notifications.mark_read', $notificationId));

        $responseRead->assertStatus(200);
        $this->assertNotNull(AppNotification::find($notificationId)->read_at);

        // 5. Unread count becomes 0
        $responseCountAfter = $this->actingAs($this->cpspUser)
            ->withSession(['current_seat_id' => $this->cpspSeat->seat_id])
            ->getJson(route('notifications.unread_count'));
        $responseCountAfter->assertJson(['unread_count' => 0]);

        // 6. Send second notification and test mark all read
        NotificationService::sendToSeat(
            $this->cpspSeat->seat_id,
            'vr_received',
            'VR Received',
            'VR report arrived.'
        );

        $responseMarkAll = $this->actingAs($this->cpspUser)
            ->withSession(['current_seat_id' => $this->cpspSeat->seat_id])
            ->postJson(route('notifications.mark_all_read'));

        $responseMarkAll->assertStatus(200);

        $remainingUnread = AppNotification::forRecipient($this->cpspUser, $this->cpspSeat->seat_id)
            ->unread()
            ->count();
        $this->assertEquals(0, $remainingUnread);
    }
}
