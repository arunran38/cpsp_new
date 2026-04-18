<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Seat;
use App\Models\SeatUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_count_users(): void
    {
        User::factory()->count(3)->create(['role' => 'user']);
        User::factory()->create(['role' => 'admin']);

        $this->assertEquals(3, User::countUser());
    }

    public function test_user_current_seat_user_logic(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $seat1 = Seat::factory()->create(['seat_name' => 'Seat 1']);
        $seat2 = Seat::factory()->create(['seat_name' => 'Seat 2']);

        $su1 = SeatUser::factory()->create([
            'user_id' => $user->user_id,
            'seat_id' => $seat1->seat_id,
            'is_active' => true
        ]);
        $su2 = SeatUser::factory()->create([
            'user_id' => $user->user_id,
            'seat_id' => $seat2->seat_id,
            'is_active' => true
        ]);

        // Default should be the first active one
        $this->assertEquals($su1->seat_id, $user->currentSeatUser()->seat_id);
        $this->assertEquals($su1->seat_id, session('current_seat_id'));

        // Test switching via session
        session(['current_seat_id' => $seat2->seat_id]);
        $this->assertEquals($su2->seat_id, $user->currentSeatUser()->seat_id);
    }
}
