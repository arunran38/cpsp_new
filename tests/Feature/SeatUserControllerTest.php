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

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
            'mobile_number' => '1234567890',
            'role' => 'user', // must be admin or user
            'designation' => 'Others', // must be CPO, SCPO, ASI, SI, IP, or Others
            'pen' => 123456, // must be integer
        ]);

        $this->seat1 = Seat::create(['seat_name' => 'Seat 1']);
        $this->seat2 = Seat::create(['seat_name' => 'Seat 2']);
    }

    public function test_can_view_assignments_index()
    {
        $response = $this->get(route('admin.seatuser.index'));
        $response->assertStatus(200);
    }

    public function test_standard_assignment_revokes_previous_ones()
    {
        // First assignment
        SeatUser::create([
            'user_id' => $this->user->user_id,
            'seat_id' => $this->seat1->seat_id,
            'is_active' => true,
            'assigned_at' => now(),
        ]);

        // Second standard assignment (not additional)
        $response = $this->post(route('admin.seatuser.store'), [
            'user_id' => $this->user->user_id,
            'seat_id' => $this->seat2->seat_id,
        ]);

        $response->assertRedirect(route('admin.seatuser.index'));

        // Check first seat is revoked
        $this->assertDatabaseHas('seat_users', [
            'seat_id' => $this->seat1->seat_id,
            'user_id' => $this->user->user_id,
            'is_active' => false,
        ]);

        // Check second seat is active
        $this->assertDatabaseHas('seat_users', [
            'seat_id' => $this->seat2->seat_id,
            'user_id' => $this->user->user_id,
            'is_active' => true,
        ]);
    }

    public function test_additional_charge_allows_multiple_seats()
    {
        // First assignment
        SeatUser::create([
            'user_id' => $this->user->user_id,
            'seat_id' => $this->seat1->seat_id,
            'is_active' => true,
            'assigned_at' => now(),
        ]);

        // Second assignment as additional charge
        $response = $this->post(route('admin.seatuser.store'), [
            'user_id' => $this->user->user_id,
            'seat_id' => $this->seat2->seat_id,
            'is_additional' => 1,
        ]);

        $response->assertRedirect(route('admin.seatuser.index'));

        // Check both seats are active
        $this->assertDatabaseHas('seat_users', [
            'seat_id' => $this->seat1->seat_id,
            'user_id' => $this->user->user_id,
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('seat_users', [
            'seat_id' => $this->seat2->seat_id,
            'user_id' => $this->user->user_id,
            'is_active' => true,
            'is_additional' => true,
        ]);
    }

    public function test_can_revoke_assignment()
    {
        $assignment = SeatUser::create([
            'user_id' => $this->user->user_id,
            'seat_id' => $this->seat1->seat_id,
            'is_active' => true,
            'assigned_at' => now(),
        ]);

        $response = $this->delete(route('admin.seatuser.destroy', $assignment->seat_user_id));

        $response->assertRedirect(route('admin.seatuser.index'));
        $this->assertDatabaseHas('seat_users', [
            'seat_user_id' => $assignment->seat_user_id,
            'is_active' => false,
        ]);
    }

    public function test_standard_assignment_revokes_previous_occupant()
    {
        // User A is in Seat 1
        $userA = User::factory()->create();
        SeatUser::create([
            'user_id' => $userA->user_id,
            'seat_id' => $this->seat1->seat_id,
            'is_active' => true,
            'assigned_at' => now(),
        ]);

        // Assign Seat 1 to User B (standard)
        $userB = $this->user;
        $this->post(route('admin.seatuser.store'), [
            'user_id' => $userB->user_id,
            'seat_id' => $this->seat1->seat_id,
        ]);

        // Check User A is revoked from Seat 1
        $this->assertDatabaseHas('seat_users', [
            'user_id' => $userA->user_id,
            'seat_id' => $this->seat1->seat_id,
            'is_active' => false,
        ]);
    }

    public function test_additional_charge_allows_shared_occupancy_for_absent_user()
    {
        // User A (Absent) is in Seat 1
        $userA = User::factory()->create();
        SeatUser::create([
            'user_id' => $userA->user_id,
            'seat_id' => $this->seat1->seat_id,
            'is_active' => true,
            'assigned_at' => now(),
        ]);

        // Assign Seat 1 to User B (Additional Charge)
        $userB = $this->user;
        $this->post(route('admin.seatuser.store'), [
            'user_id' => $userB->user_id,
            'seat_id' => $this->seat1->seat_id,
            'is_additional' => 1,
        ]);

        // Check both are active in Seat 1
        $this->assertDatabaseHas('seat_users', [
            'user_id' => $userA->user_id,
            'seat_id' => $this->seat1->seat_id,
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('seat_users', [
            'user_id' => $userB->user_id,
            'seat_id' => $this->seat1->seat_id,
            'is_active' => true,
            'is_additional' => true,
        ]);
    }
}
