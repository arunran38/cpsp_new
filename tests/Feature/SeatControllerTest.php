<?php

namespace Tests\Feature;

use App\Models\Seat;
use App\Models\Unit;
use App\Models\User;
use App\Models\SeatUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeatControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    public function test_admin_can_view_seats_index(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.seats.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.seat_view');
        $response->assertViewHas('seats');
    }

    public function test_admin_can_view_create_seat_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.seats.create'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.seat_add');
        $response->assertViewHas('units');
    }

    public function test_admin_can_store_seat(): void
    {
        $units = Unit::factory()->count(2)->create();
        
        $seatData = [
            'seat_name' => 'Cyber Cell IP',
            'unit_ids' => $units->pluck('unit_id')->toArray(),
            'is_active' => true,
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.seats.store'), $seatData);

        $response->assertRedirect(route('admin.seats.index'));
        $this->assertDatabaseHas('seats', ['seat_name' => 'Cyber Cell IP']);
        
        $seat = Seat::where('seat_name', 'Cyber Cell IP')->first();
        $this->assertCount(2, $seat->units);
    }

    public function test_store_seat_validation_errors(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.seats.store'), [
            'seat_name' => '',
            'unit_ids' => [],
        ]);

        $response->assertSessionHasErrors(['seat_name', 'unit_ids']);
    }

    public function test_admin_can_update_seat(): void
    {
        $unit = Unit::factory()->create();
        $seat = Seat::factory()->create();
        $newUnit = Unit::factory()->create();

        $updateData = [
            'seat_name' => 'Updated Seat Name',
            'unit_ids' => [$newUnit->unit_id],
            'is_active' => false,
        ];

        $response = $this->actingAs($this->admin)->put(route('admin.seats.update', $seat->seat_id), $updateData);

        $response->assertRedirect(route('admin.seats.index'));
        $this->assertDatabaseHas('seats', [
            'seat_id' => $seat->seat_id,
            'seat_name' => 'Updated Seat Name',
            'is_active' => false,
        ]);
        
        $this->assertCount(1, $seat->fresh()->units);
        $this->assertEquals($newUnit->unit_id, $seat->fresh()->units->first()->unit_id);
    }

    public function test_admin_cannot_delete_seat_with_active_assignment(): void
    {
        $seat = Seat::factory()->create();
        $user = User::factory()->create();
        SeatUser::factory()->create([
            'seat_id' => $seat->seat_id,
            'user_id' => $user->user_id,
            'is_active' => true
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.seats.destroy', $seat->seat_id));

        $response->assertStatus(302);
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('seats', ['seat_id' => $seat->seat_id]);
    }

    public function test_admin_can_delete_vacant_seat(): void
    {
        $seat = Seat::factory()->create();

        $response = $this->actingAs($this->admin)->delete(route('admin.seats.destroy', $seat->seat_id));

        $response->assertRedirect(route('admin.seats.index'));
        $this->assertDatabaseMissing('seats', ['seat_id' => $seat->seat_id]);
    }

    public function test_admin_can_view_seat_statistics(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.seats.statistics'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.seat_statistics');
        $response->assertViewHas('seats');
    }

    public function test_admin_can_revoke_assignment(): void
    {
        $seat = Seat::factory()->create();
        $user = User::factory()->create();
        $assignment = SeatUser::factory()->create([
            'seat_id' => $seat->seat_id,
            'user_id' => $user->user_id,
            'is_active' => true
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.seats.revoke', $seat->seat_id));

        $response->assertRedirect(route('admin.seats.index'));
        $this->assertFalse($assignment->fresh()->is_active);
        $this->assertNotNull($assignment->fresh()->revoked_at);
    }

    public function test_user_can_switch_seat(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $seat1 = Seat::factory()->create();
        $seat2 = Seat::factory()->create();
        
        SeatUser::factory()->create(['user_id' => $user->user_id, 'seat_id' => $seat1->seat_id, 'is_active' => true]);
        SeatUser::factory()->create(['user_id' => $user->user_id, 'seat_id' => $seat2->seat_id, 'is_active' => true]);

        $response = $this->actingAs($user)->post(route('seat.switch', $seat2->seat_id));

        $response->assertStatus(302);
        $this->assertEquals($seat2->seat_id, session('current_seat_id'));
    }
}
