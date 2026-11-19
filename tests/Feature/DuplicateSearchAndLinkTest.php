<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Petition;
use App\Models\Seat;
use App\Models\SeatUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class DuplicateSearchAndLinkTest extends TestCase
{
    use RefreshDatabase;

    protected User $cpspUser;
    protected Seat $cpspSeat;

    protected function setUp(): void
    {
        parent::setUp();

        $roleCpsp = Role::firstOrCreate(['name' => 'CPSP', 'guard_name' => 'web']);
        $permAccessCpsp = Permission::firstOrCreate(['name' => 'access cpsp section', 'guard_name' => 'web']);
        $permViewDup = Permission::firstOrCreate(['name' => 'view duplicate warnings', 'guard_name' => 'web']);
        $permLinkDup = Permission::firstOrCreate(['name' => 'link petitions', 'guard_name' => 'web']);
        $permUpdate = Permission::firstOrCreate(['name' => 'update petitions', 'guard_name' => 'web']);

        $roleCpsp->givePermissionTo([$permAccessCpsp, $permViewDup, $permLinkDup, $permUpdate]);

        $this->cpspSeat = Seat::create([
            'seat_name' => 'CPSP 1',
            'is_active' => true,
        ]);
        $this->cpspSeat->roles()->sync([$roleCpsp->id]);

        $this->cpspUser = User::factory()->create(['name' => 'CPSP Officer']);
        $this->cpspUser->assignRole($roleCpsp);
        SeatUser::create([
            'user_id' => $this->cpspUser->user_id,
            'seat_id' => $this->cpspSeat->seat_id,
            'is_active' => true,
            'assigned_date' => now(),
        ]);
    }

    public function test_search_by_file_number(): void
    {
        $petition = Petition::create([
            'receipt_no' => 'RCP-2026-101',
            'file_no' => 'FILE-XYZ-999',
            'date_of_petition_received' => '2026-05-10',
            'nature_of_petition' => 'Enquiry on Land Fraud',
            'description' => 'Dispute regarding land deed.',
            'status' => 'Received',
            'user_id' => $this->cpspUser->user_id,
            'seat_id' => $this->cpspSeat->seat_id,
        ]);

        Address::create([
            'petition_id' => $petition->petition_id,
            'person_type' => 'Complainant',
            'person_name' => 'Madhavan K',
            'phone' => '9847123456',
            'full_address' => 'Calicut, Kerala',
        ]);

        // Search by file number
        $response = $this->actingAs($this->cpspUser)
            ->getJson(route('petitions.searchDuplicates', ['q' => 'XYZ-999']));

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertNotEmpty($data);
        $this->assertEquals('RCP-2026-101', $data[0]['receipt_no']);
        $this->assertEquals('FILE-XYZ-999', $data[0]['file_no']);
    }

    public function test_search_by_receipt_number_exact_priority(): void
    {
        // Create multiple petitions with similar numbers
        $p1 = Petition::create([
            'receipt_no' => '20050',
            'file_no' => 'F-20050',
            'date_of_petition_received' => '2026-01-01',
            'nature_of_petition' => 'Boundary dispute',
            'description' => 'Boundary dispute description',
            'status' => 'Received',
            'user_id' => $this->cpspUser->user_id,
            'seat_id' => $this->cpspSeat->seat_id,
        ]);

        $p2 = Petition::create([
            'receipt_no' => '200',
            'file_no' => 'F-200',
            'date_of_petition_received' => '2026-01-02',
            'nature_of_petition' => 'Enquiry',
            'description' => 'Enquiry description',
            'status' => 'Received',
            'user_id' => $this->cpspUser->user_id,
            'seat_id' => $this->cpspSeat->seat_id,
        ]);

        $p3 = Petition::create([
            'receipt_no' => '12009',
            'file_no' => 'F-12009',
            'date_of_petition_received' => '2026-01-03',
            'nature_of_petition' => 'Theft report',
            'description' => 'Theft report description',
            'status' => 'Received',
            'user_id' => $this->cpspUser->user_id,
            'seat_id' => $this->cpspSeat->seat_id,
        ]);

        // When searching for exact '200', it MUST rank #1 ahead of 20050 and 12009
        $response = $this->actingAs($this->cpspUser)
            ->getJson(route('petitions.searchDuplicates', ['q' => '200']));

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertNotEmpty($data);
        $this->assertEquals('200', $data[0]['receipt_no'], 'Exact receipt number match should be ranked #1');
    }

    public function test_search_by_phone_number_and_complainant(): void
    {
        $petition = Petition::create([
            'receipt_no' => 'RCP-PHONE-1',
            'date_of_petition_received' => '2026-06-01',
            'nature_of_petition' => 'Money dispute',
            'description' => 'Money dispute description',
            'status' => 'Received',
            'user_id' => $this->cpspUser->user_id,
            'seat_id' => $this->cpspSeat->seat_id,
        ]);

        Address::create([
            'petition_id' => $petition->petition_id,
            'person_type' => 'Complainant',
            'person_name' => 'Somanatha Pillai',
            'phone' => '9447998877',
            'full_address' => 'Trivandrum',
        ]);

        // Search by phone
        $response = $this->actingAs($this->cpspUser)
            ->getJson(route('petitions.searchDuplicates', ['q' => '9447998877']));

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertNotEmpty($data);
        $this->assertEquals('RCP-PHONE-1', $data[0]['receipt_no']);
        $this->assertEquals('Somanatha Pillai', $data[0]['complainant']);
    }

    public function test_link_duplicate_directly_without_further_details_or_reason(): void
    {
        $original = Petition::create([
            'receipt_no' => 'ORIGINAL-001',
            'date_of_petition_received' => '2026-04-01',
            'nature_of_petition' => 'Financial fraud',
            'description' => 'Original financial fraud petition',
            'status' => 'Received',
            'user_id' => $this->cpspUser->user_id,
            'seat_id' => $this->cpspSeat->seat_id,
        ]);

        // Current petition received from Inward and unprocessed
        $newPetition = Petition::create([
            'receipt_no' => 'DUPLICATE-002',
            'date_of_petition_received' => '2026-04-15',
            'nature_of_petition' => 'Financial fraud copy',
            'description' => 'Received from Inward desk.',
            'status' => 'Received',
            'is_cpsp_processed' => false,
            'user_id' => $this->cpspUser->user_id,
            'seat_id' => $this->cpspSeat->seat_id,
        ]);

        $response = $this->actingAs($this->cpspUser)
            ->post(route('petitions.linkDuplicate', $newPetition->hashid), [
                'original_receipt_no' => $original->receipt_no,
            ]);

        $response->assertRedirect(route('petitions.show', $newPetition->hashid));

        $newPetition->refresh();
        $this->assertEquals($original->petition_id, $newPetition->linked_petition_id);
        $this->assertEquals('Duplicate', $newPetition->status);
        $this->assertTrue($newPetition->is_cpsp_processed);
    }
}
