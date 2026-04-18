<?php

namespace Tests\Feature;

use App\Models\Seat;
use App\Models\User;
use App\Models\SeatUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeatUserControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    public function test_admin_can_view_assign_seat_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.seatuser.create'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.seatuser_add');
        $response->assertViewHasAll(['users', 'seats']);
    }

    public function test_admin_can_assign_primary_seat_to_user(): void
    {
        $user = User::factory()->create();
        $seat = Seat::factory()->create();

        $response = $this->actingAs($this->admin)->post(route('admin.seatuser.store'), [
            'user_id' => $user->user_id,
            'seat_id' => $seat->seat_id,
            'is_additional' => false,
        ]);

        $response->assertRedirect(route('admin.seatuser.index'));
        $this->assertDatabaseHas('seat_users', [
            'user_id' => $user->user_id,
            'seat_id' => $seat->seat_id,
            'is_active' => true,
            'is_additional' => false,
        ]);
    }

    public function test_assign_revokes_previous_occupant(): void
    {
        $oldUser = User::factory()->create();
        $newUser = User::factory()->create();
        $seat = Seat::factory()->create();
        
        $oldAssignment = SeatUser::factory()->create([
            'user_id' => $oldUser->user_id,
            'seat_id' => $seat->seat_id,
            'is_active' => true
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.seatuser.store'), [
            'user_id' => $newUser->user_id,
            'seat_id' => $seat->seat_id,
            'is_additional' => true,
        ]);

        $response->assertRedirect(route('admin.seatuser.index'));
        $this->assertFalse($oldAssignment->fresh()->is_active);
        $this->assertNotNull($oldAssignment->fresh()->revoked_at);
        $this->assertDatabaseHas('seat_users', [
            'user_id' => $newUser->user_id,
            'seat_id' => $seat->seat_id,
            'is_active' => true
        ]);
    }

    public function test_cannot_assign_double_primary_to_user(): void
    {
        $user = User::factory()->create();
        $seat1 = Seat::factory()->create();
        $seat2 = Seat::factory()->create();
        
        SeatUser::factory()->create([
            'user_id' => $user->user_id,
            'seat_id' => $seat1->seat_id,
            'is_active' => true,
            'is_additional' => false
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.seatuser.store'), [
            'user_id' => $user->user_id,
            'seat_id' => $seat2->seat_id,
            'is_additional' => false,
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('error');
    }

    public function test_revoking_additional_charge_reactivates_previous_primary(): void
    {
        $primaryUser = User::factory()->create();
        $additionalUser = User::factory()->create();
        $seat = Seat::factory()->create();
        
        $primaryAssignment = SeatUser::factory()->create([
            'user_id' => $primaryUser->user_id,
            'seat_id' => $seat->seat_id,
            'is_active' => false, // Initially active, then revoked by additional charge
            'is_additional' => false,
            'created_at' => now()->subDay()
        ]);

        $additionalAssignment = SeatUser::factory()->create([
            'user_id' => $additionalUser->user_id,
            'seat_id' => $seat->seat_id,
            'is_active' => true,
            'is_additional' => true,
            'created_at' => now()
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.seatuser.destroy', $additionalAssignment->seat_user_id));

        $response->assertRedirect(route('admin.seatuser.index'));
        $this->assertFalse($additionalAssignment->fresh()->is_active);
        $this->assertTrue($primaryAssignment->fresh()->is_active);
        $this->assertNull($primaryAssignment->fresh()->revoked_at);
    }
}
