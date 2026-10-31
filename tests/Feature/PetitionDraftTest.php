<?php

namespace Tests\Feature;

use App\Models\Petition;
use App\Models\PetitionDraft;
use App\Models\Seat;
use App\Models\SeatUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PetitionDraftTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $seat;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $this->seed(\Database\Seeders\DeveloperRoleSeeder::class);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'CPSP', 'guard_name' => 'web']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'cpsp', 'guard_name' => 'web']);

        $this->user = User::factory()->create();
        $role = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'super admin', 'guard_name' => 'web']);
        $this->user->assignRole($role);
        $this->seat = Seat::factory()->create(['seat_name' => 'CPSP 1']);
        $this->seat->assignRole($role);

        SeatUser::factory()->create([
            'user_id' => $this->user->user_id,
            'seat_id' => $this->seat->seat_id,
            'is_active' => true,
        ]);

        $this->withSession(['current_seat_id' => $this->seat->seat_id]);
    }

    public function test_can_save_create_petition_draft(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson(route('petitions.draft.save'), [
                'type' => 'create',
                'step' => 2,
                'form_data' => [
                    'petitionDetails' => [
                        'receipt_number' => '99999/2026',
                        'description' => 'Draft test description',
                    ],
                    'complainants' => [
                        ['name' => 'John Doe', 'phone' => '9876543210'],
                    ],
                    'accused' => [],
                ],
            ]);

        $response->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('petition_drafts', [
            'user_id' => $this->user->user_id,
            'type' => 'create',
            'petition_id' => null,
            'step' => 2,
        ]);

        $draft = PetitionDraft::where('user_id', $this->user->user_id)
            ->where('type', 'create')
            ->first();

        $this->assertNotNull($draft);
        $this->assertEquals('99999/2026', $draft->form_data['petitionDetails']['receipt_number']);
        $this->assertEquals('John Doe', $draft->form_data['complainants'][0]['name']);
    }

    public function test_create_form_restores_saved_draft_data(): void
    {
        PetitionDraft::create([
            'user_id' => $this->user->user_id,
            'type' => 'create',
            'step' => 3,
            'form_data' => [
                'petitionDetails' => [
                    'receipt_number' => '88888/2026',
                    'description' => 'Restored draft note',
                ],
                'complainants' => [
                    ['id' => 1, 'name' => 'Alice Doe', 'phone' => '9998887776'],
                ],
                'accused' => [],
            ],
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('petitions.create'));

        $response->assertOk();
        $response->assertSee('88888/2026');
        $response->assertSee('Alice Doe');
        $response->assertSee('Draft Restored');
    }

    public function test_can_clear_create_petition_draft(): void
    {
        PetitionDraft::create([
            'user_id' => $this->user->user_id,
            'type' => 'create',
            'step' => 2,
            'form_data' => ['test' => 'data'],
        ]);

        $response = $this->actingAs($this->user)
            ->deleteJson(route('petitions.draft.clear'), [
                'type' => 'create',
            ]);

        $response->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('petition_drafts', [
            'user_id' => $this->user->user_id,
            'type' => 'create',
        ]);
    }

    public function test_can_save_and_restore_update_petition_draft(): void
    {
        $petition = Petition::factory()->create([
            'seat_id' => $this->seat->seat_id,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson(route('petitions.draft.save'), [
                'type' => 'update',
                'petition_id' => $petition->petition_id,
                'step' => 2,
                'form_data' => [
                    'petitionDetails' => [
                        'receipt_no' => $petition->receipt_no,
                        'description' => 'Updated draft in-progress note',
                    ],
                    'complainants' => [],
                    'accused' => [],
                ],
            ]);

        $response->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('petition_drafts', [
            'user_id' => $this->user->user_id,
            'type' => 'update',
            'petition_id' => $petition->petition_id,
            'step' => 2,
        ]);

        $editResponse = $this->actingAs($this->user)
            ->get(route('petitions.edit', $petition->petition_id));

        $editResponse->assertOk();
        $editResponse->assertSee('Updated draft in-progress note');
        $editResponse->assertSee('Draft Changes Restored');
    }

    public function test_can_clear_update_petition_draft(): void
    {
        $petition = Petition::factory()->create([
            'seat_id' => $this->seat->seat_id,
        ]);

        PetitionDraft::create([
            'user_id' => $this->user->user_id,
            'type' => 'update',
            'petition_id' => $petition->petition_id,
            'step' => 2,
            'form_data' => ['test' => 'data'],
        ]);

        $response = $this->actingAs($this->user)
            ->deleteJson(route('petitions.draft.clear'), [
                'type' => 'update',
                'petition_id' => $petition->petition_id,
            ]);

        $response->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('petition_drafts', [
            'user_id' => $this->user->user_id,
            'type' => 'update',
            'petition_id' => $petition->petition_id,
        ]);
    }

    public function test_submitting_petition_clears_create_draft(): void
    {
        $department = \App\Models\DepartmentList::create(['department_name' => 'General Administration']);

        PetitionDraft::create([
            'user_id' => $this->user->user_id,
            'type' => 'create',
            'step' => 4,
            'form_data' => ['test' => 'create draft to be cleared on submit'],
        ]);

        $this->assertDatabaseHas('petition_drafts', [
            'user_id' => $this->user->user_id,
            'type' => 'create',
        ]);

        $response = $this->actingAs($this->user)->post(route('petitions.store'), [
            'receipt_no' => 'CLEAR-DRAFT-001',
            'date_of_petition_received' => now()->toDateString(),
            'nature_of_petition' => 'Bribery',
            'mode_of_petition_received' => 'Direct',
            'description' => 'Draft clear test on petition store',
            'seat_id' => $this->seat->seat_id,
            'accused' => [[
                'name' => 'Draft Suspect',
                'entity_type' => 'Person',
                'department_id' => $department->id,
            ]],
        ]);

        $response->assertRedirect(route('petitions.index', ['tab' => 'all']));

        $this->assertDatabaseMissing('petition_drafts', [
            'user_id' => $this->user->user_id,
            'type' => 'create',
        ]);
    }

    public function test_updating_petition_clears_update_draft(): void
    {
        $petition = Petition::factory()->create([
            'user_id' => $this->user->user_id,
            'seat_id' => $this->seat->seat_id,
        ]);

        $department = \App\Models\DepartmentList::firstOrCreate(['department_name' => 'Police']);

        PetitionDraft::create([
            'user_id' => $this->user->user_id,
            'type' => 'update',
            'petition_id' => $petition->petition_id,
            'step' => 3,
            'form_data' => ['test' => 'update draft to be cleared on update'],
        ]);

        $this->assertDatabaseHas('petition_drafts', [
            'user_id' => $this->user->user_id,
            'type' => 'update',
            'petition_id' => $petition->petition_id,
        ]);

        $updateData = [
            'receipt_no' => 'UPDATED-NO-1',
            'date_of_petition_received' => '2024-02-02',
            'nature_of_petition' => 'Misuse of authority',
            'mode_of_petition_received' => 'Email',
            'description' => 'Updated description to verify draft clear',
            'accused' => [
                [
                    'name' => 'Jane Smith',
                    'entity_type' => 'Person',
                    'department_id' => $department->id,
                ]
            ]
        ];

        $response = $this->actingAs($this->user)->put(route('petitions.update', $petition->petition_id), $updateData);

        $response->assertRedirect(route('petitions.index', ['tab' => 'all']));

        $this->assertDatabaseMissing('petition_drafts', [
            'user_id' => $this->user->user_id,
            'type' => 'update',
            'petition_id' => $petition->petition_id,
        ]);
    }
}

