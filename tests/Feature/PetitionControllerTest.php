<?php

namespace Tests\Feature;

use App\Models\Petition;
use App\Models\User;
use App\Models\Seat;
use App\Models\SeatUser;
use App\Models\Address;
use App\Models\Upload;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PetitionControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $user;
    protected $seat;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $this->seed(\Database\Seeders\DeveloperRoleSeeder::class);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'CPSP', 'guard_name' => 'web']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'cpsp', 'guard_name' => 'web']);

        $this->admin = User::factory()->create();
        $this->user = User::factory()->create();
        $role = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'super admin', 'guard_name' => 'web']);
        $this->admin->assignRole($role);
        $this->user->assignRole($role);
        $this->seat = Seat::factory()->create();
        $this->seat->assignRole($role);
        
        // Assign user to seat
        SeatUser::factory()->create([
            'user_id' => $this->user->user_id,
            'seat_id' => $this->seat->seat_id,
            'is_active' => true
        ]);
        
        // Set current seat in session for controller logic
        $this->withSession(['current_seat_id' => $this->seat->seat_id]);
    }

    public function test_user_can_view_create_petition_page(): void
    {
        $response = $this->actingAs($this->user)->get(route('petitions.create'));

        $response->assertStatus(200);
        $response->assertViewIs('user.petition_add');
    }

    public function test_user_can_store_petition_with_complainants_and_files(): void
    {
        Storage::fake('public');

        $data = [
            'receipt_no' => 'REC-2024-0001',
            'date_of_petition_received' => '2024-01-01',
            'nature_of_petition' => 'Bribery',
            'mode_of_petition_received' => 'Direct',
            'description' => 'Test description',
            'complainants' => [
                [
                    'name' => 'John Doe',
                    'phone' => '1234567890',
                    'addresses' => [
                        [
                            'address_type' => 'Permanent',
                            'address' => '123 Main St',
                            'pincode' => '123456'
                        ]
                    ]
                ]
            ],
            'accused' => [
                [
                    'name' => 'Jane Smith',
                    'entity_type' => 'Person',
                    'department_id' => \App\Models\DepartmentList::firstOrCreate(['department_name' => 'Police'])->id,
                    'addresses' => [
                        [
                            'address_type' => 'Office',
                            'address' => '456 Office Way'
                        ]
                    ]
                ]
            ],
            'evidence_files' => [
                UploadedFile::fake()->create('evidence.pdf', 100)
            ]
        ];

        $response = $this->actingAs($this->user)->post(route('petitions.store'), $data);

        $response->assertRedirect(route('petitions.index', ['tab' => 'all']));
        $this->assertDatabaseHas('petitions', ['receipt_no' => 'REC-2024-0001', 'seat_id' => $this->seat->seat_id]);
        $this->assertDatabaseHas('addresses', ['person_name' => 'John Doe', 'person_type' => 'Complainant']);
        $this->assertDatabaseHas('addresses', ['person_name' => 'Jane Smith', 'person_type' => 'Accused']);
        
        $petition = Petition::where('receipt_no', 'REC-2024-0001')->first();
        $this->assertCount(1, $petition->uploads);
        Storage::disk('public')->assertExists($petition->uploads->first()->file_path);
    }

    public function test_non_inward_petition_creator_is_counted_by_workflow_state_and_visible_in_matching_tabs(): void
    {
        $creator = User::factory()->create();
        $creator->givePermissionTo([
            \Spatie\Permission\Models\Permission::findOrCreate('create petitions', 'web'),
            \Spatie\Permission\Models\Permission::findOrCreate('view petitions', 'web'),
            \Spatie\Permission\Models\Permission::findOrCreate('create inward petitions', 'web'),
        ]);
        $seat = Seat::factory()->create();
        SeatUser::factory()->create([
            'user_id' => $creator->user_id,
            'seat_id' => $seat->seat_id,
            'is_active' => true,
        ]);
        $seat->givePermissionTo([
            \Spatie\Permission\Models\Permission::findOrCreate('create petitions', 'web'),
            \Spatie\Permission\Models\Permission::findOrCreate('view petitions', 'web'),
            \Spatie\Permission\Models\Permission::findOrCreate('create inward petitions', 'web'),
        ]);
        $department = \App\Models\DepartmentList::firstOrCreate(['department_name' => 'Petition Test Department']);

        $this->actingAs($creator)->withSession(['current_seat_id' => $seat->seat_id])
            ->get(route('petitions.create'))
            ->assertOk();

        $this->actingAs($creator)->withSession(['current_seat_id' => $seat->seat_id])
            ->post(route('petitions.store'), [
                'receipt_no' => 'NON-INWARD-CREATOR-1',
                'date_of_petition_received' => now()->toDateString(),
                'nature_of_petition' => 'Bribery',
                'mode_of_petition_received' => 'Direct',
                'description' => 'Created by a user without the Inward role.',
                'seat_id' => $seat->seat_id,
                'accused' => [[
                    'name' => 'Test Suspect',
                    'entity_type' => 'Person',
                    'department_id' => $department->id,
                ]],
            ])
            ->assertRedirect(route('petitions.index', ['tab' => 'all']));

        $outsideSeat = Seat::factory()->create();
        Petition::factory()->create([
            'user_id' => User::factory()->create()->user_id,
            'seat_id' => $outsideSeat->seat_id,
            'receipt_no' => 'OUTSIDE-CREATOR-SCOPE-1',
            'is_cpsp_processed' => true,
        ]);

        $petition = Petition::where('receipt_no', 'NON-INWARD-CREATOR-1')->firstOrFail();
        $this->assertFalse($creator->hasRole(['inward', 'Inward']));
        $this->assertTrue($petition->is_cpsp_processed);

        $allResponse = $this->actingAs($creator)->withSession(['current_seat_id' => $seat->seat_id])
            ->get(route('petitions.index', ['tab' => 'all']));
        $allResponse->assertOk();
        $this->assertSame(1, $allResponse->viewData('petitions')->total());
        $this->assertSame(1, $allResponse->viewData('unifiedCounts')['all']);

        $processedResponse = $this->actingAs($creator)->withSession(['current_seat_id' => $seat->seat_id])
            ->get(route('petitions.index', ['tab' => 'received']));
        $processedResponse->assertOk();
        $this->assertSame(1, $processedResponse->viewData('petitions')->total());
        $this->assertSame(1, $processedResponse->viewData('unifiedCounts')['received']);
        $this->assertSame(0, $processedResponse->viewData('unifiedCounts')['inward']);

        $petition->update(['status' => Petition::STATUS_FORWARDED]);
        $forwardedResponse = $this->actingAs($creator)->withSession(['current_seat_id' => $seat->seat_id])
            ->get(route('petitions.index', ['tab' => 'forwarded']));
        $forwardedResponse->assertOk();
        $this->assertSame(1, $forwardedResponse->viewData('petitions')->total());
        $this->assertSame(1, $forwardedResponse->viewData('unifiedCounts')['forwarded']);
        $this->assertSame(0, $forwardedResponse->viewData('unifiedCounts')['received']);

        $petition->update(['status' => Petition::STATUS_SENT_TO_GOVT]);
        \App\Models\Decision::factory()->create([
            'petition_id' => $petition->petition_id,
            'decided_by_seat_id' => $seat->seat_id,
            'final_decision' => 'Sent to Govt',
        ]);
        $decisionsResponse = $this->actingAs($creator)->withSession(['current_seat_id' => $seat->seat_id])
            ->get(route('petitions.index', ['tab' => 'decisions']));
        $decisionsResponse->assertOk();
        $this->assertSame(1, $decisionsResponse->viewData('petitions')->total());
        $this->assertSame(1, $decisionsResponse->viewData('unifiedCounts')['decisions']);
        $this->assertSame(1, $decisionsResponse->viewData('decisionCounts')['Sent to Govt']);
    }

    public function test_inward_tab_counts_unprocessed_workflow_records_without_creator_role_filter(): void
    {
        $creator = User::factory()->create();
        $creator->givePermissionTo([
            \Spatie\Permission\Models\Permission::findOrCreate('view petitions', 'web'),
        ]);
        $petition = Petition::factory()->create([
            'user_id' => $creator->user_id,
            'seat_id' => $this->seat->seat_id,
            'is_cpsp_processed' => false,
            'is_returned_to_inward' => false,
        ]);
        SeatUser::factory()->create([
            'user_id' => $creator->user_id,
            'seat_id' => $this->seat->seat_id,
            'is_active' => true,
        ]);

        $response = $this->actingAs($creator)->withSession(['current_seat_id' => $this->seat->seat_id])
            ->get(route('petitions.index', ['tab' => 'inward']));

        $response->assertOk();
        $this->assertSame(1, $response->viewData('petitions')->total());
        $this->assertSame(1, $response->viewData('unifiedCounts')['inward']);
    }

    public function test_user_can_view_petitions_index(): void
    {
        Petition::factory()->create(['user_id' => $this->user->user_id, 'seat_id' => $this->seat->seat_id]);

        $response = $this->actingAs($this->user)->get(route('petitions.index'));

        $response->assertStatus(200);
        $response->assertViewIs('user.petition_view');
        $response->assertViewHas('petitions');
    }

    public function test_admin_can_filter_reports_by_petition_creator(): void
    {
        $permission = \Spatie\Permission\Models\Permission::firstOrCreate([
            'name' => 'access admin dashboard',
            'guard_name' => 'web',
        ]);
        $this->admin->givePermissionTo($permission);

        $selectedUser = User::factory()->create();
        $otherUser = User::factory()->create();
        $selectedPetition = Petition::factory()->create(['user_id' => $selectedUser->user_id]);
        Petition::factory()->create(['user_id' => $otherUser->user_id]);

        $response = $this->actingAs($this->admin)->get(route('petitions.reports', [
            'user_id' => $selectedUser->user_id,
        ]));

        $response->assertOk();
        $response->assertViewHas('petitions', function ($petitions) use ($selectedPetition) {
            return $petitions->total() === 1
                && $petitions->first()->petition_id === $selectedPetition->petition_id;
        });
    }

    public function test_user_cannot_view_others_petition(): void
    {
        $restrictedUser = User::factory()->create();
        $restrictedSeat = Seat::factory()->create(['seat_name' => 'Inward Section']);
        SeatUser::factory()->create([
            'user_id' => $restrictedUser->user_id,
            'seat_id' => $restrictedSeat->seat_id,
            'is_active' => true
        ]);
        $inwardRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'inward', 'guard_name' => 'web']);
        $restrictedUser->assignRole($inwardRole);
        $restrictedSeat->assignRole($inwardRole);

        $otherUser = User::factory()->create();
        $otherSeat = Seat::factory()->create();
        $petition = Petition::factory()->create(['user_id' => $otherUser->user_id, 'seat_id' => $otherSeat->seat_id]);

        $response = $this->actingAs($restrictedUser)
            ->withSession(['current_seat_id' => $restrictedSeat->seat_id])
            ->get(route('petitions.show', $petition->petition_id));

        $response->assertStatus(403);
    }

    public function test_admin_can_view_any_petition(): void
    {
        $petition = Petition::factory()->create();

        $response = $this->actingAs($this->admin)->get(route('petitions.show', $petition->petition_id));

        $response->assertStatus(200);
    }

    public function test_create_page_exposes_duplicate_search_and_create_link_workflow(): void
    {
        $response = $this->actingAs($this->user)->get(route('petitions.create'));

        $response->assertOk()
            ->assertSee(route('petitions.checkDuplicates'), false)
            ->assertSee('duplicate_link_number', false);
    }

    public function test_edit_page_uses_duplicate_link_endpoint_without_submitting_update_form(): void
    {
        $petition = Petition::factory()->create([
            'user_id' => $this->user->user_id,
            'seat_id' => $this->seat->seat_id,
        ]);

        $response = $this->actingAs($this->user)->get(route('petitions.edit', $petition->hashid));

        $response->assertOk()
            ->assertSee(route('petitions.linkDuplicate', $petition->hashid), false)
            ->assertSee('original_receipt_no', false)
            ->assertSee('fetch(', false);
    }

    public function test_duplicate_search_finds_petition_by_complainant_and_accused(): void
    {
        $petition = Petition::factory()->create(['receipt_no' => 'DUP-SEARCH-1']);
        Address::factory()->create([
            'petition_id' => $petition->petition_id,
            'person_name' => 'Alex Complainant',
            'person_type' => 'Complainant',
        ]);
        Address::factory()->create([
            'petition_id' => $petition->petition_id,
            'person_name' => 'Jamie Accused',
            'person_type' => 'Accused',
            'department_id' => null,
        ]);

        $response = $this->actingAs($this->user)->postJson(route('petitions.checkDuplicates'), [
            'complainants' => [['name' => 'Alex Complainant']],
            'accused' => [['name' => 'Jamie Accused']],
        ]);

        $response->assertOk()->assertJsonFragment([
            'petition_id' => $petition->petition_id,
            'receipt_no' => 'DUP-SEARCH-1',
        ]);
    }

    public function test_duplicate_create_does_not_require_department_for_legacy_target(): void
    {
        $legacyPetition = Petition::factory()->create(['receipt_no' => 'LEGACY-NO-DEPT']);
        Address::factory()->create([
            'petition_id' => $legacyPetition->petition_id,
            'person_type' => 'Accused',
            'department_id' => null,
        ]);

        $response = $this->actingAs($this->user)->post(route('petitions.store'), [
            'receipt_no' => 'DUPLICATE-NEW-1',
            'date_of_petition_received' => now()->toDateString(),
            'duplicate_link_number' => $legacyPetition->receipt_no,
        ]);

        $duplicate = Petition::where('receipt_no', 'DUPLICATE-NEW-1')->firstOrFail();
        $response->assertRedirect(route('petitions.show', $duplicate->hashid))
            ->assertSessionHas('success');
        $this->assertSame($legacyPetition->petition_id, $duplicate->linked_petition_id);
        $this->assertSame(Petition::STATUS_DUPLICATE, $duplicate->status);
    }

    public function test_normal_petition_create_still_requires_department(): void
    {
        $response = $this->actingAs($this->user)->from(route('petitions.create'))->post(route('petitions.store'), [
            'receipt_no' => 'MISSING-DEPARTMENT-1',
            'date_of_petition_received' => now()->toDateString(),
            'nature_of_petition' => 'Bribery',
            'mode_of_petition_received' => 'Direct',
            'description' => 'Department validation regression test',
            'accused' => [[
                'name' => 'Jamie Accused',
                'entity_type' => 'Person',
                'department_id' => null,
            ]],
        ]);

        $response->assertRedirect(route('petitions.create'))
            ->assertSessionHasErrors('accused.0.department_id');
        $this->assertDatabaseMissing('petitions', ['receipt_no' => 'MISSING-DEPARTMENT-1']);
    }

    public function test_edit_duplicate_link_succeeds_for_legacy_petitions_without_department(): void
    {
        $original = Petition::factory()->create(['receipt_no' => 'EDIT-LEGACY-ORIGINAL']);
        Address::factory()->create([
            'petition_id' => $original->petition_id,
            'person_type' => 'Accused',
            'department_id' => null,
            'full_address' => 'Original petition address',
        ]);
        $duplicate = Petition::factory()->create([
            'receipt_no' => 'EDIT-LEGACY-DUPLICATE',
            'user_id' => $this->user->user_id,
            'seat_id' => $this->seat->seat_id,
        ]);
        Address::factory()->create([
            'petition_id' => $duplicate->petition_id,
            'person_type' => 'Accused',
            'department_id' => null,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson(route('petitions.linkDuplicate', $duplicate->hashid), [
                'original_receipt_no' => $original->receipt_no,
            ]);

        $response->assertOk()
            ->assertJsonPath('redirect_url', route('petitions.show', $duplicate->hashid));
        $this->assertSame(
            'Petition successfully linked as a duplicate of ' . $original->receipt_no . '.',
            $response->json('message')
        );
        $this->assertSame(1, Address::where('petition_id', $original->petition_id)->count());
        $this->assertDatabaseHas('petitions', [
            'petition_id' => $duplicate->petition_id,
            'linked_petition_id' => $original->petition_id,
            'status' => Petition::STATUS_DUPLICATE,
        ]);
    }

    public function test_petition_details_show_identical_legacy_address_copies_once_and_keep_distinct_addresses(): void
    {
        $petition = Petition::factory()->create([
            'user_id' => $this->user->user_id,
            'seat_id' => $this->seat->seat_id,
        ]);
        foreach (['Complainant', 'Accused'] as $personType) {
            $addressData = [
                'petition_id' => $petition->petition_id,
                'person_name' => $personType === 'Complainant' ? 'Riya Complainant' : 'Mohan Suspect',
                'person_type' => $personType,
                'address_type' => 'Permanent',
                'full_address' => $personType === 'Complainant'
                    ? '14 Complainant Software Road'
                    : '14 Suspect Software Road',
                'phone' => $personType === 'Complainant' ? '9000000001' : '9000000002',
                'pincode' => '695033',
            ];
            Address::factory()->create($addressData);
            Address::factory()->create($addressData);
            Address::factory()->create(array_merge($addressData, [
                'full_address' => $personType === 'Complainant'
                    ? '15 Complainant Software Road'
                    : '15 Suspect Software Road',
            ]));
        }

        $response = $this->actingAs($this->user)->get(route('petitions.show', $petition->hashid));

        $response->assertOk();
        $html = $response->getContent();
        $this->assertSame(1, substr_count($html, '14 Complainant Software Road'));
        $this->assertSame(1, substr_count($html, '14 Suspect Software Road'));
        $this->assertSame(1, substr_count($html, '15 Complainant Software Road'));
        $this->assertSame(1, substr_count($html, '15 Suspect Software Road'));
    }

    public function test_linked_petition_displays_original_and_independent_addresses_once_for_multiple_duplicates(): void
    {
        $original = Petition::factory()->create([
            'user_id' => $this->user->user_id,
            'seat_id' => $this->seat->seat_id,
        ]);

        foreach (['Complainant', 'Accused'] as $personType) {
            $addressData = [
                'petition_id' => $original->petition_id,
                'person_name' => $personType === 'Complainant' ? 'Riya Complainant' : 'Mohan Suspect',
                'person_type' => $personType,
                'address_type' => 'Permanent',
                'full_address' => $personType === 'Complainant'
                    ? '14 Complainant Software Road'
                    : '14 Suspect Software Road',
                'phone' => $personType === 'Complainant' ? '9000000001' : '9000000002',
                'pincode' => '695033',
            ];
            Address::factory()->create($addressData);
            Address::factory()->create($addressData);
        }

        $duplicates = collect(['DUP-ADDR-1', 'DUP-ADDR-2'])->map(function ($receiptNo) use ($original) {
            $duplicate = Petition::factory()->create([
                'receipt_no' => $receiptNo,
                'user_id' => $this->user->user_id,
                'seat_id' => $this->seat->seat_id,
                'linked_petition_id' => $original->petition_id,
                'status' => Petition::STATUS_DUPLICATE,
            ]);

            foreach (['Complainant', 'Accused'] as $personType) {
                Address::factory()->create([
                    'petition_id' => $duplicate->petition_id,
                    'person_name' => $personType === 'Complainant' ? 'Riya Complainant' : 'Mohan Suspect',
                    'person_type' => $personType,
                    'address_type' => 'Permanent',
                    'full_address' => $personType === 'Complainant'
                        ? '16 Independent Complainant Road'
                        : '17 Independent Suspect Road',
                    'phone' => $personType === 'Complainant' ? '9000000001' : '9000000002',
                    'pincode' => '695033',
                ]);
            }

            return $duplicate;
        });

        foreach ($duplicates as $duplicate) {
            $response = $this->actingAs($this->user)->get(route('petitions.show', $duplicate->hashid));

            $response->assertOk();
            $html = $response->getContent();
            $this->assertSame(1, substr_count($html, '14 Complainant Software Road'));
            $this->assertSame(1, substr_count($html, '14 Suspect Software Road'));
            $this->assertSame(1, substr_count($html, '16 Independent Complainant Road'));
            $this->assertSame(1, substr_count($html, '17 Independent Suspect Road'));
        }
    }

    public function test_user_can_update_petition(): void
    {
        $petition = Petition::factory()->create(['user_id' => $this->user->user_id, 'seat_id' => $this->seat->seat_id]);
        
        $department = \App\Models\DepartmentList::firstOrCreate(['department_name' => 'Police']);
        $updateData = [
            'receipt_no' => 'UPDATED-NO-1',
            'date_of_petition_received' => '2024-02-02',
            'nature_of_petition' => 'Misuse of authority',
            'mode_of_petition_received' => 'Email',
            'description' => 'Updated description content',
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
        $this->assertDatabaseHas('petitions', [
            'petition_id' => $petition->petition_id,
            'receipt_no' => 'UPDATED-NO-1',
            'nature_of_petition' => 'Misuse of authority'
        ]);
    }

    public function test_user_can_delete_petition(): void
    {
        $petition = Petition::factory()->create(['user_id' => $this->user->user_id, 'seat_id' => $this->seat->seat_id]);

        $response = $this->actingAs($this->user)->delete(route('petitions.destroy', $petition->petition_id));

        $response->assertRedirect(route('petitions.index', ['tab' => 'all']));
        $this->assertSoftDeleted('petitions', ['petition_id' => $petition->petition_id]);
    }

    public function test_check_petition_no_endpoint(): void
    {
        Petition::factory()->create(['receipt_no' => 'EXIST-123']);

        $response = $this->actingAs($this->user)->post(route('petitions.checkPetitionNo'), ['receipt_no' => 'EXIST-123']);
        $response->assertJson(['exists' => true]);

        $response = $this->actingAs($this->user)->post(route('petitions.checkPetitionNo'), ['receipt_no' => 'NEW-999']);
        $response->assertJson(['exists' => false]);
    }

    public function test_user_can_filter_petitions_by_nature_and_mode(): void
    {
        Petition::factory()->create([
            'user_id' => $this->user->user_id,
            'seat_id' => $this->seat->seat_id,
            'nature_of_petition' => 'Bribery',
            'mode_of_petition_received' => 'Direct'
        ]);
        Petition::factory()->create([
            'user_id' => $this->user->user_id,
            'seat_id' => $this->seat->seat_id,
            'nature_of_petition' => 'Serious negligence',
            'mode_of_petition_received' => 'Email'
        ]);

        $response = $this->actingAs($this->user)->get(route('petitions.index', ['nature_of_petition' => 'Bribery']));
        $response->assertStatus(200);
        $this->assertCount(1, $response->viewData('petitions'));

        $response = $this->actingAs($this->user)->get(route('petitions.index', ['mode_of_petition' => 'Email']));
        $response->assertStatus(200);
        $this->assertCount(1, $response->viewData('petitions'));
    }

    public function test_user_can_filter_petitions_by_date_range(): void
    {
        Petition::factory()->create([
            'user_id' => $this->user->user_id,
            'seat_id' => $this->seat->seat_id,
            'date_of_petition_received' => '2024-01-01'
        ]);
        Petition::factory()->create([
            'user_id' => $this->user->user_id,
            'seat_id' => $this->seat->seat_id,
            'date_of_petition_received' => '2024-02-01'
        ]);

        $response = $this->actingAs($this->user)->get(route('petitions.index', [
            'date_from' => '2024-01-01',
            'date_to' => '2024-01-15'
        ]));
        $response->assertStatus(200);
        $this->assertCount(1, $response->viewData('petitions'));
    }

    public function test_user_can_export_petitions_to_excel(): void
    {
        Petition::factory()->create(['user_id' => $this->user->user_id, 'seat_id' => $this->seat->seat_id]);

        $response = $this->actingAs($this->user)->get(route('petitions.export'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.ms-excel');
        $response->assertHeader('Content-Disposition', 'attachment; filename="petitions_report_all_'.date('Y-m-d').'.xls"');
    }

    public function test_future_dates_are_rejected_in_date_filter(): void
    {
        $tomorrow = now()->addDay()->format('Y-m-d');
        $response = $this->actingAs($this->user)->get(route('petitions.index', [
            'date_from' => $tomorrow,
            'date_to' => $tomorrow
        ]));
        $response->assertSessionHasErrors(['date_from', 'date_to']);
    }

    public function test_date_to_earlier_than_date_from_is_rejected(): void
    {
        $response = $this->actingAs($this->user)->get(route('petitions.index', [
            'date_from' => '2024-05-10',
            'date_to' => '2024-05-01'
        ]));
        $response->assertSessionHasErrors(['date_to']);
    }

    public function test_decisions_tab_sent_to_govt_shows_recommendation_subnav_and_counts(): void
    {
        $petition1 = Petition::factory()->create([
            'user_id' => $this->user->user_id,
            'seat_id' => $this->seat->seat_id,
            'status' => Petition::STATUS_SENT_TO_GOVT
        ]);
        \App\Models\Decision::factory()->create([
            'petition_id' => $petition1->petition_id,
            'final_decision' => 'Sent to Govt',
            'govt_recommendation_type' => '17 A Sanction'
        ]);

        $petition2 = Petition::factory()->create([
            'user_id' => $this->user->user_id,
            'seat_id' => $this->seat->seat_id,
            'status' => Petition::STATUS_SENT_TO_GOVT
        ]);
        \App\Models\Decision::factory()->create([
            'petition_id' => $petition2->petition_id,
            'final_decision' => 'Sent to Govt',
            'govt_recommendation_type' => 'Report with Recommendations'
        ]);

        $response = $this->actingAs($this->user)->get(route('petitions.index', [
            'tab' => 'decisions',
            'status' => 'Sent to Govt'
        ]));

        $response->assertStatus(200);
        $response->assertSee('17 A Sanction');
        $response->assertSee('Report with Recommendations');
        $response->assertSee('No Recommendations');
        
        $govtRecCounts = $response->viewData('govtRecCounts');
        $this->assertEquals(2, $govtRecCounts['All']);
        $this->assertEquals(1, $govtRecCounts['17 A Sanction']);
        $this->assertEquals(1, $govtRecCounts['Report with Recommendations']);
        $this->assertEquals(0, $govtRecCounts['No Recommendations']);
    }

    public function test_decisions_tab_filters_by_recommendation_type(): void
    {
        $petition1 = Petition::factory()->create([
            'user_id' => $this->user->user_id,
            'seat_id' => $this->seat->seat_id,
            'status' => Petition::STATUS_SENT_TO_GOVT
        ]);
        \App\Models\Decision::factory()->create([
            'petition_id' => $petition1->petition_id,
            'final_decision' => 'Sent to Govt',
            'govt_recommendation_type' => '17 A Sanction'
        ]);

        $petition2 = Petition::factory()->create([
            'user_id' => $this->user->user_id,
            'seat_id' => $this->seat->seat_id,
            'status' => Petition::STATUS_SENT_TO_GOVT
        ]);
        \App\Models\Decision::factory()->create([
            'petition_id' => $petition2->petition_id,
            'final_decision' => 'Sent to Govt',
            'govt_recommendation_type' => 'Report with Recommendations'
        ]);

        $response = $this->actingAs($this->user)->get(route('petitions.index', [
            'tab' => 'decisions',
            'status' => 'Sent to Govt',
            'recommendation_type' => '17 A Sanction'
        ]));

        $response->assertStatus(200);
        $petitions = $response->viewData('petitions');
        $this->assertCount(1, $petitions);
        $this->assertEquals($petition1->petition_id, $petitions->first()->petition_id);
    }

    public function test_petition_list_displays_final_sent_to_govt_without_recommendation_type(): void
    {
        $petition = Petition::factory()->create([
            'user_id' => $this->user->user_id,
            'seat_id' => $this->seat->seat_id,
            'status' => Petition::STATUS_SENT_TO_GOVT
        ]);
        \App\Models\Decision::factory()->create([
            'petition_id' => $petition->petition_id,
            'final_decision' => 'Sent to Govt',
            'govt_recommendation_type' => '17 A Sanction'
        ]);

        $response = $this->actingAs($this->user)->get(route('petitions.index', [
            'tab' => 'decisions',
            'status' => 'Sent to Govt'
        ]));

        $response->assertStatus(200);
        $response->assertSee('Final: Sent to Govt');
        $response->assertDontSee('(' . '17 A Sanction' . ')');
    }

    public function test_decision_counts_exclude_soft_deleted_and_prevent_duplicate_counts(): void
    {
        $petition1 = Petition::factory()->create([
            'user_id' => $this->user->user_id,
            'seat_id' => $this->seat->seat_id,
            'status' => Petition::STATUS_SENT_TO_GOVT,
            'is_cpsp_processed' => true,
        ]);
        // Soft-deleted decision on petition 1 (e.g. from previous pullback)
        $oldDecision = \App\Models\Decision::factory()->create([
            'petition_id' => $petition1->petition_id,
            'final_decision' => 'Sent to Govt',
            'govt_recommendation_type' => null,
        ]);
        $oldDecision->delete();

        // Active decision on petition 1
        \App\Models\Decision::factory()->create([
            'petition_id' => $petition1->petition_id,
            'final_decision' => 'Sent to Govt',
            'govt_recommendation_type' => '17 A Sanction',
        ]);

        // Petition 2 with active decision
        $petition2 = Petition::factory()->create([
            'user_id' => $this->user->user_id,
            'seat_id' => $this->seat->seat_id,
            'status' => Petition::STATUS_SENT_TO_GOVT,
            'is_cpsp_processed' => true,
        ]);
        \App\Models\Decision::factory()->create([
            'petition_id' => $petition2->petition_id,
            'final_decision' => 'Sent to Govt',
            'govt_recommendation_type' => 'Report with Recommendations',
        ]);

        $response = $this->actingAs($this->user)->get(route('petitions.index', [
            'tab' => 'decisions',
        ]));

        $response->assertStatus(200);

        $unifiedCounts = $response->viewData('unifiedCounts');
        $decisionCounts = $response->viewData('decisionCounts');
        $govtRecCounts = $response->viewData('govtRecCounts');

        // Only 2 petitions exist; counts must be exactly 2, not 3
        $this->assertEquals(2, $unifiedCounts['all']);
        $this->assertEquals(2, $unifiedCounts['decisions']);

        $this->assertEquals(2, $decisionCounts['All']);
        $this->assertEquals(2, $decisionCounts['Sent to Govt']);
        $this->assertEquals(0, $decisionCounts['VC']);
        $this->assertEquals(0, $decisionCounts['Closed']);
        $this->assertEquals(0, $decisionCounts['Sent to SPC']);

        $this->assertEquals(2, $govtRecCounts['All']);
        $this->assertEquals(1, $govtRecCounts['17 A Sanction']);
        $this->assertEquals(1, $govtRecCounts['Report with Recommendations']);
        $this->assertEquals(0, $govtRecCounts['No Recommendations']);
    }

    public function test_master_reports_filters_by_sent_to_govt_and_recommendation_type(): void
    {
        $petition1 = Petition::factory()->create([
            'user_id' => $this->user->user_id,
            'seat_id' => $this->seat->seat_id,
            'status' => Petition::STATUS_SENT_TO_GOVT,
            'is_cpsp_processed' => true,
        ]);
        \App\Models\Decision::factory()->create([
            'petition_id' => $petition1->petition_id,
            'final_decision' => 'Sent to Govt',
            'govt_recommendation_type' => '17 A Sanction',
        ]);

        $petition2 = Petition::factory()->create([
            'user_id' => $this->user->user_id,
            'seat_id' => $this->seat->seat_id,
            'status' => Petition::STATUS_SENT_TO_GOVT,
            'is_cpsp_processed' => true,
        ]);
        \App\Models\Decision::factory()->create([
            'petition_id' => $petition2->petition_id,
            'final_decision' => 'Sent to Govt',
            'govt_recommendation_type' => 'Report with Recommendations',
        ]);

        // Filter by 17 A Sanction
        $response = $this->actingAs($this->user)->get(route('petitions.reports', [
            'status' => 'Sent to Govt',
            'recommendation_type' => '17 A Sanction',
        ]));

        $response->assertStatus(200);
        $response->assertSee('Final: Sent to Govt');
        $response->assertDontSee('(' . '17 A Sanction' . ')');
        $petitions = $response->viewData('petitions');
        $this->assertCount(1, $petitions);
        $this->assertEquals($petition1->petition_id, $petitions->first()->petition_id);

        // Filter by All Recommendation Types
        $responseAll = $this->actingAs($this->user)->get(route('petitions.reports', [
            'status' => 'Sent to Govt',
            'recommendation_type' => 'All Recommendation Types',
        ]));

        $responseAll->assertStatus(200);
        $petitionsAll = $responseAll->viewData('petitions');
        $this->assertCount(2, $petitionsAll);
    }

    public function test_excel_export_respects_recommendation_type_filter_and_includes_column(): void
    {
        $petition1 = Petition::factory()->create([
            'user_id' => $this->user->user_id,
            'seat_id' => $this->seat->seat_id,
            'status' => Petition::STATUS_SENT_TO_GOVT,
            'is_cpsp_processed' => true,
        ]);
        \App\Models\Decision::factory()->create([
            'petition_id' => $petition1->petition_id,
            'final_decision' => 'Sent to Govt',
            'govt_recommendation_type' => '17 A Sanction',
        ]);

        $petition2 = Petition::factory()->create([
            'user_id' => $this->user->user_id,
            'seat_id' => $this->seat->seat_id,
            'status' => Petition::STATUS_SENT_TO_GOVT,
            'is_cpsp_processed' => true,
        ]);
        \App\Models\Decision::factory()->create([
            'petition_id' => $petition2->petition_id,
            'final_decision' => 'Sent to Govt',
            'govt_recommendation_type' => 'Report with Recommendations',
        ]);

        // Export filtered by 17 A Sanction
        $response = $this->actingAs($this->user)->get(route('petitions.export', [
            'status' => 'Sent to Govt',
            'recommendation_type' => '17 A Sanction',
        ]));

        $response->assertStatus(200);
        $content = $response->getContent();

        $this->assertStringContainsString('Recommendation Type', $content);
        $this->assertStringContainsString($petition1->receipt_no, $content);
        $this->assertStringContainsString('17 A Sanction', $content);
        $this->assertStringNotContainsString($petition2->receipt_no, $content);

        // Custom Export specifying custom_columns
        $responseCustom = $this->actingAs($this->user)->get(route('petitions.export', [
            'status' => 'Sent to Govt',
            'custom_columns' => ['Receipt No', 'Decision', 'Recommendation Type'],
        ]));

        $responseCustom->assertStatus(200);
        $customContent = $responseCustom->getContent();
        $this->assertStringContainsString('<th>Recommendation Type</th>', $customContent);
        $this->assertStringContainsString('17 A Sanction', $customContent);
        $this->assertStringContainsString('Report with Recommendations', $customContent);
    }
}
