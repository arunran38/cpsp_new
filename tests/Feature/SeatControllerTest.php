<?php

namespace Tests\Feature;

use App\Models\Seat;
use App\Models\Unit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeatControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->unit = Unit::create([
            'unit_name' => 'Test Unit',
            'unit_code' => 'TU-001',
        ]);
        $this->unit2 = Unit::create([
            'unit_name' => 'Test Unit 2',
            'unit_code' => 'TU-002',
        ]);
    }

    public function test_can_view_seats_index()
    {
        $response = $this->get(route('admin.seats.index'));
        $response->assertStatus(200);
    }

    public function test_can_create_seat_with_multiple_units()
    {
        $seatData = [
            'unit_ids' => [$this->unit->unit_id, $this->unit2->unit_id],
            'seat_name' => 'Test Multi-Unit Seat',
        ];

        $response = $this->post(route('admin.seats.store'), $seatData);

        $response->assertRedirect(route('admin.seats.index'));
        $this->assertDatabaseHas('seats', ['seat_name' => 'Test Multi-Unit Seat']);
        $this->assertDatabaseHas('seat_unit', ['unit_id' => $this->unit->unit_id]);
        $this->assertDatabaseHas('seat_unit', ['unit_id' => $this->unit2->unit_id]);
    }

    public function test_can_update_seat_units()
    {
        $seat = Seat::create([
            'seat_name' => 'Old Seat',
        ]);
        $seat->units()->attach($this->unit->unit_id);

        $updatedData = [
            'unit_ids' => [$this->unit2->unit_id],
            'seat_name' => 'Updated Seat',
            'is_active' => 0,
        ];

        $response = $this->put(route('admin.seats.update', $seat->seat_id), $updatedData);

        $response->assertRedirect(route('admin.seats.index'));
        $this->assertDatabaseHas('seats', ['seat_name' => 'Updated Seat', 'is_active' => 0]);
        $this->assertDatabaseHas('seat_unit', ['unit_id' => $this->unit2->unit_id]);
        $this->assertDatabaseMissing('seat_unit', ['unit_id' => $this->unit->unit_id, 'seat_id' => $seat->seat_id]);
    }

    public function test_can_delete_seat()
    {
        $seat = Seat::create([
            'seat_name' => 'To Delete',
        ]);
        $seat->units()->attach($this->unit->unit_id);

        $response = $this->delete(route('admin.seats.destroy', $seat->seat_id));

        $response->assertRedirect(route('admin.seats.index'));
        $this->assertDatabaseMissing('seats', ['seat_id' => $seat->seat_id]);
        $this->assertDatabaseMissing('seat_unit', ['seat_id' => $seat->seat_id]);
    }
}
