<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Petition;
use App\Models\Seat;
use App\Models\SeatUser;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class InwardPetitionEditAndReceiptCheckboxTest extends TestCase
{
    use RefreshDatabase;

    protected User $inwardUser;
    protected Seat $inwardSeat;
    protected Seat $cpspSeat;
    protected Seat $targetSeat2;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        $inwardRole = Role::findByName('Inward', 'web');
        $cpspRole = Role::findByName('CPSP', 'web');

        $this->inwardSeat = Seat::factory()->create(['seat_name' => 'Inward Desk', 'is_active' => true]);
        $this->inwardSeat->assignRole($inwardRole);

        $this->cpspSeat = Seat::factory()->create(['seat_name' => 'CPSP Desk 1', 'is_active' => true]);
        $this->cpspSeat->assignRole($cpspRole);

        $this->targetSeat2 = Seat::factory()->create(['seat_name' => 'CPSP Desk 2', 'is_active' => true]);
        $this->targetSeat2->assignRole($cpspRole);

        $this->inwardUser = User::factory()->create(['name' => 'Inward Officer']);
        $this->inwardUser->assignRole($inwardRole);

        SeatUser::factory()->create([
            'user_id' => $this->inwardUser->user_id,
            'seat_id' => $this->inwardSeat->seat_id,
            'is_active' => true,
        ]);
    }

    public function test_inward_store_with_print_receipt_checked_flashes_receipt_in_session(): void
    {
        $response = $this->actingAs($this->inwardUser)
            ->withSession(['current_seat_id' => $this->inwardSeat->seat_id])
            ->post(route('petitions.store'), [
                'receipt_no' => 'INW/2026/PRINT-YES',
                'date_of_petition_received' => now()->toDateString(),
                'mode_of_petition_received' => 'Tapal',
                'is_inward_entry' => '1',
                'print_receipt' => '1',
                'seat_id' => $this->cpspSeat->seat_id,
                'complainants' => [
                    ['name' => 'Gopalan Nair', 'phone' => '9447012345'],
                ],
            ]);

        $response->assertRedirect(route('inward.enter_petition'));
        $response->assertSessionHas('receipt_petition_hashid');
        $response->assertSessionHas('receipt_petition_no', 'INW/2026/PRINT-YES');
    }

    public function test_inward_store_with_print_receipt_unchecked_does_not_flash_receipt_in_session(): void
    {
        $response = $this->actingAs($this->inwardUser)
            ->withSession(['current_seat_id' => $this->inwardSeat->seat_id])
            ->post(route('petitions.store'), [
                'receipt_no' => 'INW/2026/PRINT-NO',
                'date_of_petition_received' => now()->toDateString(),
                'mode_of_petition_received' => 'Tapal',
                'is_inward_entry' => '1',
                'print_receipt' => '0',
                'seat_id' => $this->cpspSeat->seat_id,
                'complainants' => [
                    ['name' => 'Madhavan K', 'phone' => '9447098765'],
                ],
            ]);

        $response->assertRedirect(route('inward.enter_petition'));
        $response->assertSessionMissing('receipt_petition_hashid');
        $response->assertSessionMissing('receipt_petition_no');
    }

    public function test_inward_user_can_edit_petition_before_processing(): void
    {
        $petition = Petition::factory()->create([
            'receipt_no' => 'INW/2026/EDIT-100',
            'date_of_petition_received' => now()->toDateString(),
            'mode_of_petition_received' => 'Tapal',
            'user_id' => $this->inwardUser->user_id,
            'seat_id' => $this->cpspSeat->seat_id,
            'is_cpsp_processed' => false,
        ]);

        Address::factory()->create([
            'petition_id' => $petition->petition_id,
            'person_type' => 'Complainant',
            'person_name' => 'Old Complainant Name',
        ]);

        $response = $this->actingAs($this->inwardUser)
            ->withSession(['current_seat_id' => $this->inwardSeat->seat_id])
            ->put(route('inward.petitions.update', $petition->hashid), [
                'receipt_no' => 'INW/2026/EDIT-MODIFIED',
                'date_of_petition_received' => now()->toDateString(),
                'mode_of_petition_received' => 'Direct',
                'complainant_name' => 'Updated Complainant Name',
                'seat_id' => $this->targetSeat2->seat_id,
            ]);

        $response->assertRedirect(route('inward.enter_petition'));
        $response->assertSessionHas('success');

        $petition->refresh();
        $this->assertEquals('INW/2026/EDIT-MODIFIED', $petition->receipt_no);
        $this->assertEquals('Direct', $petition->mode_of_petition_received);
        $this->assertEquals($this->targetSeat2->seat_id, $petition->seat_id);

        $complainant = Address::where('petition_id', $petition->petition_id)
            ->where('person_type', 'Complainant')
            ->first();
        $this->assertNotNull($complainant);
        $this->assertEquals('Updated Complainant Name', $complainant->person_name);
    }

    public function test_inward_user_can_edit_petition_and_flash_receipt_when_print_checked(): void
    {
        $petition = Petition::factory()->create([
            'receipt_no' => 'INW/2026/EDIT-PRINT-YES',
            'date_of_petition_received' => now()->toDateString(),
            'mode_of_petition_received' => 'Tapal',
            'user_id' => $this->inwardUser->user_id,
            'seat_id' => $this->cpspSeat->seat_id,
            'is_cpsp_processed' => false,
        ]);

        Address::factory()->create([
            'petition_id' => $petition->petition_id,
            'person_type' => 'Complainant',
            'person_name' => 'Original Name',
        ]);

        $response = $this->actingAs($this->inwardUser)
            ->withSession(['current_seat_id' => $this->inwardSeat->seat_id])
            ->put(route('inward.petitions.update', $petition->hashid), [
                'receipt_no' => 'INW/2026/EDIT-PRINT-UPDATED',
                'date_of_petition_received' => now()->toDateString(),
                'mode_of_petition_received' => 'Direct',
                'complainant_name' => 'Corrected Name',
                'seat_id' => $this->cpspSeat->seat_id,
                'print_receipt' => '1',
            ]);

        $response->assertRedirect(route('inward.enter_petition'));
        $response->assertSessionHas('success');
        $response->assertSessionHas('receipt_petition_hashid', $petition->hashid);
        $response->assertSessionHas('receipt_petition_no', 'INW/2026/EDIT-PRINT-UPDATED');
    }

    public function test_inward_user_cannot_edit_petition_after_cpsp_processing(): void
    {
        $petition = Petition::factory()->create([
            'receipt_no' => 'INW/2026/PROCESSED-LOCK',
            'date_of_petition_received' => now()->toDateString(),
            'mode_of_petition_received' => 'Direct',
            'user_id' => $this->inwardUser->user_id,
            'seat_id' => $this->cpspSeat->seat_id,
            'is_cpsp_processed' => true,
            'processed_at' => now(),
        ]);

        Address::factory()->create([
            'petition_id' => $petition->petition_id,
            'person_type' => 'Complainant',
            'person_name' => 'Original Name',
        ]);

        $response = $this->actingAs($this->inwardUser)
            ->withSession(['current_seat_id' => $this->inwardSeat->seat_id])
            ->put(route('inward.petitions.update', $petition->hashid), [
                'receipt_no' => 'INW/2026/ATTEMPT-EDIT',
                'date_of_petition_received' => now()->toDateString(),
                'mode_of_petition_received' => 'Direct',
                'complainant_name' => 'Hacked Name',
                'seat_id' => $this->targetSeat2->seat_id,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Cannot edit a petition that has already been processed by CPSP / T-Section.');

        $petition->refresh();
        $this->assertEquals('INW/2026/PROCESSED-LOCK', $petition->receipt_no);
    }

    public function test_inward_table_renders_edit_button_only_for_unprocessed_petitions(): void
    {
        $unprocessedPetition = Petition::factory()->create([
            'receipt_no' => 'INW/2026/UNPROCESSED-ROW',
            'date_of_petition_received' => now()->toDateString(),
            'mode_of_petition_received' => 'Direct',
            'user_id' => $this->inwardUser->user_id,
            'seat_id' => $this->cpspSeat->seat_id,
            'is_cpsp_processed' => false,
        ]);

        $processedPetition = Petition::factory()->create([
            'receipt_no' => 'INW/2026/PROCESSED-ROW',
            'date_of_petition_received' => now()->toDateString(),
            'mode_of_petition_received' => 'Direct',
            'user_id' => $this->inwardUser->user_id,
            'seat_id' => $this->cpspSeat->seat_id,
            'is_cpsp_processed' => true,
            'processed_at' => now(),
        ]);

        $response = $this->actingAs($this->inwardUser)
            ->withSession(['current_seat_id' => $this->inwardSeat->seat_id])
            ->get(route('inward.enter_petition'));

        $response->assertStatus(200);
        $response->assertSee('INW/2026/UNPROCESSED-ROW');
        $response->assertSee('INW/2026/PROCESSED-ROW');
        $response->assertSee('Edit Inward Registration');
        $response->assertSee('Print Receipt');
    }

    public function test_unauthorized_user_cannot_edit_inward_petition(): void
    {
        $randomUser = User::factory()->create();
        $randomSeat = Seat::factory()->create(['is_active' => true]);
        SeatUser::factory()->create([
            'user_id' => $randomUser->user_id,
            'seat_id' => $randomSeat->seat_id,
            'is_active' => true,
        ]);

        $petition = Petition::factory()->create([
            'receipt_no' => 'INW/2026/UNAUTH-TEST',
            'date_of_petition_received' => now()->toDateString(),
            'user_id' => $this->inwardUser->user_id,
            'seat_id' => $this->cpspSeat->seat_id,
            'is_cpsp_processed' => false,
        ]);

        $response = $this->actingAs($randomUser)
            ->withSession(['current_seat_id' => $randomSeat->seat_id])
            ->put(route('inward.petitions.update', $petition->hashid), [
                'receipt_no' => 'INW/2026/UNAUTH-MODIFIED',
                'date_of_petition_received' => now()->toDateString(),
                'mode_of_petition_received' => 'Direct',
                'complainant_name' => 'Intruder',
                'seat_id' => $this->cpspSeat->seat_id,
            ]);

        $response->assertForbidden();
    }
}
