<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\AuditLog;
use App\Models\PetitionForwarding;
use App\Models\Petition;
use App\Models\Seat;
use App\Models\SeatUser;
use App\Models\Upload;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class InwardPetitionDeleteTest extends TestCase
{
    use RefreshDatabase;

    protected User $inwardUser;
    protected Seat $inwardSeat;
    protected Seat $cpspSeat;

    protected function setUp(): void
    {
        parent::setUp();

        $inwardRole = Role::firstOrCreate(['name' => 'Inward', 'guard_name' => 'web']);
        $cpspRole = Role::firstOrCreate(['name' => 'CPSP', 'guard_name' => 'web']);

        $deletePerm = Permission::firstOrCreate(['name' => 'delete inward petitions', 'guard_name' => 'web']);
        $inwardRole->givePermissionTo($deletePerm);

        $this->inwardUser = User::factory()->create();
        $this->inwardUser->assignRole($inwardRole);

        $this->inwardSeat = Seat::factory()->create(['seat_name' => 'Inward 1', 'is_active' => true]);
        $this->inwardSeat->assignRole($inwardRole);

        $this->cpspSeat = Seat::factory()->create(['seat_name' => 'CPSP 1', 'is_active' => true]);
        $this->cpspSeat->assignRole($cpspRole);

        SeatUser::factory()->create([
            'user_id' => $this->inwardUser->user_id,
            'seat_id' => $this->inwardSeat->seat_id,
            'is_active' => true,
            'is_additional' => false,
        ]);

        $this->withSession(['current_seat_id' => $this->inwardSeat->seat_id]);
    }

    public function test_inward_user_can_permanently_delete_returned_petition(): void
    {
        Storage::fake('public');

        $petition = Petition::factory()->create([
            'user_id' => $this->inwardUser->user_id,
            'seat_id' => $this->cpspSeat->seat_id,
            'is_returned_to_inward' => true,
            'return_reason' => 'Wrong section, not CPSP',
            'is_cpsp_processed' => false,
        ]);

        $address = Address::factory()->create([
            'petition_id' => $petition->petition_id,
            'person_type' => 'Complainant',
            'person_name' => 'Ramesh Kumar',
        ]);

        $file = UploadedFile::fake()->create('document.pdf', 100);
        $path = $file->store('petitions/evidence', 'public');

        $upload = Upload::factory()->create([
            'petition_id' => $petition->petition_id,
            'file_path' => $path,
            'original_filename' => 'document.pdf',
            'uploaded_by' => $this->inwardUser->user_id,
        ]);

        $this->assertTrue(Storage::disk('public')->exists($path));

        $response = $this->actingAs($this->inwardUser)
            ->delete(route('inward.petitions.destroy', $petition->hashid), [
                'deletion_reason' => 'Registered by mistake, does not belong to Vigilance',
            ]);

        $response->assertSessionHas('success');

        // Verify permanent deletion from database
        $this->assertDatabaseMissing('petitions', [
            'petition_id' => $petition->petition_id,
        ]);
        $this->assertDatabaseMissing('addresses', [
            'address_id' => $address->address_id,
        ]);
        $this->assertDatabaseMissing('uploads', [
            'upload_id' => $upload->upload_id,
        ]);

        // Verify physical file was purged from storage
        $this->assertFalse(Storage::disk('public')->exists($path));

        // Verify AuditLog recorded
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'PERMANENT_DELETE',
            'module' => 'Inward',
            'user_id' => $this->inwardUser->user_id,
        ]);
    }

    public function test_deletion_reason_is_required_for_permanent_deletion(): void
    {
        $petition = Petition::factory()->create([
            'user_id' => $this->inwardUser->user_id,
            'seat_id' => $this->cpspSeat->seat_id,
            'is_returned_to_inward' => true,
            'return_reason' => 'Wrong section',
            'is_cpsp_processed' => false,
        ]);

        $response = $this->actingAs($this->inwardUser)
            ->delete(route('inward.petitions.destroy', $petition->hashid), [
                'deletion_reason' => '',
            ]);

        $response->assertSessionHasErrors('deletion_reason');

        $this->assertDatabaseHas('petitions', [
            'petition_id' => $petition->petition_id,
        ]);
    }

    public function test_inward_user_cannot_delete_non_returned_petition(): void
    {
        $petition = Petition::factory()->create([
            'user_id' => $this->inwardUser->user_id,
            'seat_id' => $this->cpspSeat->seat_id,
            'is_returned_to_inward' => false,
            'is_cpsp_processed' => false,
        ]);

        $response = $this->actingAs($this->inwardUser)
            ->delete(route('inward.petitions.destroy', $petition->hashid), [
                'deletion_reason' => 'Testing deletion attempt',
            ]);

        $response->assertSessionHas('error', 'Only petitions returned from seats can be deleted.');
        $this->assertDatabaseHas('petitions', [
            'petition_id' => $petition->petition_id,
        ]);
    }

    public function test_inward_user_cannot_delete_processed_petition(): void
    {
        $petition = Petition::factory()->create([
            'user_id' => $this->inwardUser->user_id,
            'seat_id' => $this->cpspSeat->seat_id,
            'is_returned_to_inward' => true,
            'return_reason' => 'Some remark',
            'is_cpsp_processed' => true,
        ]);

        $response = $this->actingAs($this->inwardUser)
            ->delete(route('inward.petitions.destroy', $petition->hashid), [
                'deletion_reason' => 'Testing deletion attempt',
            ]);

        $response->assertSessionHas('error', 'Cannot delete a petition that has already been processed by CPSP.');
        $this->assertDatabaseHas('petitions', [
            'petition_id' => $petition->petition_id,
        ]);
    }

    public function test_inward_user_cannot_delete_petition_with_forwardings(): void
    {
        $petition = Petition::factory()->create([
            'user_id' => $this->inwardUser->user_id,
            'seat_id' => $this->cpspSeat->seat_id,
            'is_returned_to_inward' => true,
            'return_reason' => 'Has forward history',
            'is_cpsp_processed' => false,
        ]);

        PetitionForwarding::factory()->create([
            'petition_id' => $petition->petition_id,
        ]);

        $response = $this->actingAs($this->inwardUser)
            ->delete(route('inward.petitions.destroy', $petition->hashid), [
                'deletion_reason' => 'Testing deletion with forwardings',
            ]);

        $response->assertSessionHas('error', 'Cannot delete a petition with existing forwarding, decision, or processing history.');
        $this->assertDatabaseHas('petitions', [
            'petition_id' => $petition->petition_id,
        ]);
    }

    public function test_unauthorized_user_cannot_delete_inward_petition(): void
    {
        $regularUser = User::factory()->create();
        $regularSeat = Seat::factory()->create(['seat_name' => 'General Seat', 'is_active' => true]);
        SeatUser::factory()->create([
            'user_id' => $regularUser->user_id,
            'seat_id' => $regularSeat->seat_id,
            'is_active' => true,
            'is_additional' => false,
        ]);

        $petition = Petition::factory()->create([
            'user_id' => $this->inwardUser->user_id,
            'seat_id' => $this->cpspSeat->seat_id,
            'is_returned_to_inward' => true,
            'return_reason' => 'Not CPSP',
            'is_cpsp_processed' => false,
        ]);

        $response = $this->actingAs($regularUser)
            ->withSession(['current_seat_id' => $regularSeat->seat_id])
            ->delete(route('inward.petitions.destroy', $petition->hashid), [
                'deletion_reason' => 'Unauthorized deletion attempt',
            ]);

        $response->assertStatus(403);
        $this->assertDatabaseHas('petitions', [
            'petition_id' => $petition->petition_id,
        ]);
    }
}
