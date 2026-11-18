<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DepartmentManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = $this->createSuperAdminUser();
    }

    public function test_admin_can_view_departments_index(): void
    {
        Department::create(['department_name' => 'Health & Family Welfare']);
        Department::create(['department_name' => 'Police Department']);

        $response = $this->actingAs($this->admin)->get(route('admin.departments.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.departments.index');
        $response->assertViewHas('departments');
        $response->assertSee('Health & Family Welfare');
        $response->assertSee('Police Department');
        $response->assertSee('Search departments by name...');
    }

    public function test_departments_live_search_filters_results(): void
    {
        Department::create(['department_name' => 'Forest and Wildlife Department']);
        Department::create(['department_name' => 'Revenue Department']);
        Department::create(['department_name' => 'Police Department']);

        $response = $this->actingAs($this->admin)->get(route('admin.departments.index', ['search' => 'Forest']));

        $response->assertStatus(200);
        $response->assertSee('Forest and Wildlife Department');
        $response->assertDontSee('Revenue Department');
        $response->assertDontSee('Police Department');
    }

    public function test_departments_live_search_via_ajax_returns_json(): void
    {
        Department::create(['department_name' => 'Motor Vehicles Department']);
        Department::create(['department_name' => 'Commercial Taxes']);

        $response = $this->actingAs($this->admin)->getJson(
            route('admin.departments.index', ['search' => 'Motor']),
            ['X-Requested-With' => 'XMLHttpRequest']
        );

        $response->assertStatus(200);
        $response->assertJsonStructure(['html', 'total', 'search']);
        $this->assertEquals(1, $response->json('total'));
        $this->assertStringContainsString('Motor Vehicles Department', $response->json('html'));
        $this->assertStringNotContainsString('Commercial Taxes', $response->json('html'));
    }

    public function test_departments_search_empty_state_displayed_when_no_match(): void
    {
        Department::create(['department_name' => 'Home Department']);

        $response = $this->actingAs($this->admin)->get(route('admin.departments.index', ['search' => 'NonExistentXYZ']));

        $response->assertStatus(200);
        $response->assertSee('No departments found');
        $response->assertSee('NonExistentXYZ');
    }

    public function test_check_department_name_endpoint_detects_duplicates(): void
    {
        Department::create(['department_name' => 'Agriculture Development']);

        // Exact match
        $response1 = $this->actingAs($this->admin)->postJson(route('admin.departments.checkName'), [
            'department_name' => 'Agriculture Development',
        ]);
        $response1->assertStatus(200);
        $response1->assertJson(['exists' => true, 'message' => 'A department with this name already exists.']);

        // Case-insensitive match with extra spaces
        $response2 = $this->actingAs($this->admin)->postJson(route('admin.departments.checkName'), [
            'department_name' => '  agriculture development  ',
        ]);
        $response2->assertStatus(200);
        $response2->assertJson(['exists' => true]);

        // Non-existing department
        $response3 = $this->actingAs($this->admin)->postJson(route('admin.departments.checkName'), [
            'department_name' => 'Brand New Unique Department',
        ]);
        $response3->assertStatus(200);
        $response3->assertJson(['exists' => false]);
    }

    public function test_check_department_name_endpoint_respects_ignore_id(): void
    {
        $dept = Department::create(['department_name' => 'Fisheries Department']);

        $response = $this->actingAs($this->admin)->postJson(route('admin.departments.checkName'), [
            'department_name' => 'Fisheries Department',
            'ignore_id' => $dept->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['exists' => false]);
    }

    public function test_store_department_provides_toast_success_feedback(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.departments.store'), [
            'department_name' => 'Public Works Department',
        ]);

        $response->assertRedirect(route('admin.departments.index'));
        $response->assertSessionHas('success', 'Department added successfully.');

        $this->assertDatabaseHas('department_lists', [
            'department_name' => 'Public Works Department',
        ]);
    }

    public function test_store_department_prevents_duplicate_name(): void
    {
        Department::create(['department_name' => 'Registration Department']);

        $response = $this->actingAs($this->admin)->post(route('admin.departments.store'), [
            'department_name' => 'registration department',
        ]);

        $response->assertSessionHasErrors(['department_name']);
        $this->assertEquals(1, Department::where('department_name', 'Registration Department')->count());
    }

    public function test_update_department_prevents_duplicate_name_but_allows_own_name(): void
    {
        $dept1 = Department::create(['department_name' => 'Excise Department']);
        $dept2 = Department::create(['department_name' => 'Mining and Geology']);

        // Updating dept2 with dept1 name should fail
        $failResponse = $this->actingAs($this->admin)->put(route('admin.departments.update', $dept2->id), [
            'department_name' => 'excise department',
        ]);
        $failResponse->assertSessionHasErrors(['department_name']);

        // Updating dept2 with same or modified unique name should succeed
        $successResponse = $this->actingAs($this->admin)->put(route('admin.departments.update', $dept2->id), [
            'department_name' => 'Mining and Geology Department',
        ]);
        $successResponse->assertRedirect(route('admin.departments.index'));
        $successResponse->assertSessionHas('success', 'Department updated successfully.');
    }

    public function test_admin_can_delete_department(): void
    {
        $dept = Department::create(['department_name' => 'Temporary Dept']);

        $response = $this->actingAs($this->admin)->delete(route('admin.departments.destroy', $dept->id));

        $response->assertRedirect(route('admin.departments.index'));
        $response->assertSessionHas('success', 'Department deleted successfully.');
        $this->assertSoftDeleted('department_lists', ['id' => $dept->id]);
    }
}
