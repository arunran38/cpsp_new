<?php

namespace Tests\Feature;

use App\Models\Petition;
use App\Models\Seat;
use App\Models\SeatUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

    public function test_inward_user_can_delete_returned_petition(): void
    {
        $petition = Petition::factory()->create([
            'user_id' => $this->inwardUser->user_id,
            'seat_id' => $this->cpspSeat->seat_id,
            'is_returned_to_inward' => true,
            'return_reason' => 'Wrong section, not CPSP',
            'is_cpsp_processed' => false,
        ]);

        $response = $this->actingAs($this->inwardUser)
            ->delete(route('inward.petitions.destroy', $petition->hashid));

        $response->assertSessionHas('success');
        $this->assertSoftDeleted('petitions', [
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
            ->delete(route('inward.petitions.destroy', $petition->hashid));

        $response->assertSessionHas('error', 'Only petitions returned from seats can be deleted.');
        $this->assertNotSoftDeleted('petitions', [
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
            ->delete(route('inward.petitions.destroy', $petition->hashid));

        $response->assertSessionHas('error', 'Cannot delete a petition that has already been processed by CPSP.');
        $this->assertNotSoftDeleted('petitions', [
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
            ->delete(route('inward.petitions.destroy', $petition->hashid));

        $response->assertStatus(403);
        $this->assertNotSoftDeleted('petitions', [
            'petition_id' => $petition->petition_id,
        ]);
    }
}

