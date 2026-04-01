<?php
 
namespace Tests\Feature;
 
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
 
class UnitControllerTest extends TestCase
{
    use RefreshDatabase;
 
    protected $admin;
 
    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
    }
 
    public function test_admin_can_view_units_index(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.units.index'));
 
        $response->assertStatus(200);
        $response->assertViewIs('admin.unit_add');
        $response->assertViewHas('units');
    }
 
    public function test_admin_can_store_unit(): void
    {
        $unitData = [
            'unit_name' => 'Testing Unit',
            'unit_code' => 'TEST-001',
        ];
 
        $response = $this->actingAs($this->admin)->post(route('admin.units.store'), $unitData);
 
        $response->assertRedirect(route('admin.units.index'));
        $this->assertDatabaseHas('units', [
            'unit_name' => 'Testing Unit',
            'unit_code' => 'TEST-001',
        ]);
    }
 
    public function test_store_unit_validation_errors(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.units.store'), [
            'unit_name' => '',
            'unit_code' => '',
        ]);
 
        $response->assertSessionHasErrors(['unit_name', 'unit_code']);
    }
 
    public function test_admin_can_update_unit(): void
    {
        $unit = Unit::factory()->create();
 
        $updateData = [
            'unit_name' => 'Updated Unit Name',
            'unit_code' => 'UPD-001',
        ];
 
        $response = $this->actingAs($this->admin)->put(route('admin.units.update', $unit->unit_id), $updateData);
 
        $response->assertRedirect(route('admin.units.index'));
        $this->assertDatabaseHas('units', [
            'unit_id' => $unit->unit_id,
            'unit_name' => 'Updated Unit Name',
            'unit_code' => 'UPD-001',
        ]);
    }
 
    public function test_admin_can_delete_unit(): void
    {
        $unit = Unit::factory()->create();
 
        $response = $this->actingAs($this->admin)->delete(route('admin.units.destroy', $unit->unit_id));
 
        $response->assertRedirect(route('admin.units.index'));
        $this->assertDatabaseMissing('units', ['unit_id' => $unit->unit_id]);
    }
 
    public function test_check_unit_code_endpoint(): void
    {
        $unit = Unit::factory()->create(['unit_code' => 'EXISTING-001']);
 
        $response = $this->actingAs($this->admin)->post(route('admin.units.checkCode'), [
            'unit_code' => 'EXISTING-001',
        ]);
 
        $response->assertStatus(200);
        $response->assertJson(['exists' => true]);
 
        $response = $this->actingAs($this->admin)->post(route('admin.units.checkCode'), [
            'unit_code' => 'NEW-CODE-999',
        ]);
 
        $response->assertStatus(200);
        $response->assertJson(['exists' => false]);
    }
}
