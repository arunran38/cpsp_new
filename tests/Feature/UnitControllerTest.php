<?php

namespace Tests\Feature;

use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UnitControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_view_units_index()
    {
        $response = $this->get(route('admin.units.index'));
        $response->assertStatus(200);
    }

    public function test_can_create_unit()
    {
        $unitData = [
            'unit_name' => 'Test Unit',
            'unit_code' => 'TU-001',
        ];

        $response = $this->post(route('admin.units.store'), $unitData);

        $response->assertRedirect(route('admin.units.index'));
        $this->assertDatabaseHas('units', $unitData);
    }

    public function test_can_update_unit()
    {
        $unit = Unit::create([
            'unit_name' => 'Old Name',
            'unit_code' => 'OLD-01',
        ]);

        $updatedData = [
            'unit_name' => 'New Name',
            'unit_code' => 'NEW-01',
        ];

        $response = $this->put(route('admin.units.update', $unit->unit_id), $updatedData);

        $response->assertRedirect(route('admin.units.index'));
        $this->assertDatabaseHas('units', $updatedData);
    }

    public function test_can_delete_unit()
    {
        $unit = Unit::create([
            'unit_name' => 'To Delete',
            'unit_code' => 'DEL-01',
        ]);

        $response = $this->delete(route('admin.units.destroy', $unit->unit_id));

        $response->assertRedirect(route('admin.units.index'));
        $this->assertDatabaseMissing('units', ['unit_id' => $unit->unit_id]);
    }
}
