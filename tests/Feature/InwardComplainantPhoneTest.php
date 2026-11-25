<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\DepartmentList;
use App\Models\District;
use App\Models\Petition;
use App\Models\Seat;
use App\Models\SeatUser;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class InwardComplainantPhoneTest extends TestCase
{
    use RefreshDatabase;

    protected User $inwardUser;
    protected Seat $inwardSeat;
    protected User $cpspUser;
    protected Seat $cpspSeat;
    protected User $tSectionUser;
    protected Seat $tSectionSeat;
    protected District $district;
    protected DepartmentList $department;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        $inwardRole = Role::findByName('Inward', 'web');
        $cpspRole = Role::findByName('CPSP', 'web');
        $tSectionRole = Role::findByName('T Section', 'web');

        $this->inwardSeat = Seat::factory()->create(['seat_name' => 'Inward 1', 'is_active' => true]);
        $this->inwardSeat->assignRole($inwardRole);

        $this->inwardUser = User::factory()->create(['name' => 'Inward Officer']);
        $this->inwardUser->assignRole($inwardRole);
        SeatUser::factory()->create([
            'user_id' => $this->inwardUser->user_id,
            'seat_id' => $this->inwardSeat->seat_id,
            'is_active' => true,
        ]);

        $this->cpspSeat = Seat::factory()->create(['seat_name' => 'CPSP 1', 'is_active' => true]);
        $this->cpspSeat->assignRole($cpspRole);

        $this->cpspUser = User::factory()->create(['name' => 'CPSP Officer']);
        $this->cpspUser->assignRole($cpspRole);
        SeatUser::factory()->create([
            'user_id' => $this->cpspUser->user_id,
            'seat_id' => $this->cpspSeat->seat_id,
            'is_active' => true,
        ]);

        $this->tSectionSeat = Seat::factory()->create(['seat_name' => 'T1', 'is_active' => true]);
        $this->tSectionSeat->assignRole($tSectionRole);

        $this->tSectionUser = User::factory()->create(['name' => 'T Section Officer']);
        $this->tSectionUser->assignRole($tSectionRole);
        SeatUser::factory()->create([
            'user_id' => $this->tSectionUser->user_id,
            'seat_id' => $this->tSectionSeat->seat_id,
            'is_active' => true,
        ]);

        $this->district = District::firstOrCreate(['district_name' => 'Thiruvananthapuram', 'district_code' => 'TVM']);
        $this->department = DepartmentList::firstOrCreate(['department_name' => 'Police']);
    }

    public function test_inward_form_renders_optional_complainant_phone_field_after_name(): void
    {
        $response = $this->actingAs($this->inwardUser)
            ->withSession(['current_seat_id' => $this->inwardSeat->seat_id])
            ->get(route('inward.enter_petition'));

        $response->assertStatus(200);
        $response->assertSee('Complainant Phone Number');
        $response->assertSee('complainants[0][phone]');

        $content = $response->getContent();
        $namePos = strpos($content, 'complainants[0][name]');
        $phonePos = strpos($content, 'complainants[0][phone]');
        $seatPos = strpos($content, 'name="seat_id"');

        $this->assertNotFalse($namePos);
        $this->assertNotFalse($phonePos);
        $this->assertNotFalse($seatPos);
        $this->assertTrue($namePos < $phonePos && $phonePos < $seatPos);
    }

    public function test_inward_registration_stores_optional_complainant_phone_in_addresses_table(): void
    {
        // 1. With valid 10-digit phone
        $this->actingAs($this->inwardUser)
            ->withSession(['current_seat_id' => $this->inwardSeat->seat_id])
            ->post(route('petitions.store'), [
                'receipt_no' => 'INW/2026/PH-01',
                'date_of_petition_received' => now()->toDateString(),
                'mode_of_petition_received' => 'Direct',
                'is_inward_entry' => '1',
                'seat_id' => $this->cpspSeat->seat_id,
                'complainants' => [
                    ['name' => 'Rajan Pillai', 'phone' => '9847012345'],
                ],
            ])
            ->assertRedirect(route('inward.enter_petition'));

        $petitionWithPhone = Petition::where('receipt_no', 'INW/2026/PH-01')->firstOrFail();
        $this->assertDatabaseHas('addresses', [
            'petition_id' => $petitionWithPhone->petition_id,
            'person_type' => 'Complainant',
            'person_name' => 'Rajan Pillai',
            'phone' => '9847012345',
        ]);

        // 2. Without phone (empty string / null)
        $this->actingAs($this->inwardUser)
            ->withSession(['current_seat_id' => $this->inwardSeat->seat_id])
            ->post(route('petitions.store'), [
                'receipt_no' => 'INW/2026/PH-02',
                'date_of_petition_received' => now()->toDateString(),
                'mode_of_petition_received' => 'Tapal',
                'is_inward_entry' => '1',
                'seat_id' => $this->cpspSeat->seat_id,
                'complainants' => [
                    ['name' => 'Suma Devi', 'phone' => ''],
                ],
            ])
            ->assertRedirect(route('inward.enter_petition'));

        $petitionWithoutPhone = Petition::where('receipt_no', 'INW/2026/PH-02')->firstOrFail();
        $this->assertDatabaseHas('addresses', [
            'petition_id' => $petitionWithoutPhone->petition_id,
            'person_type' => 'Complainant',
            'person_name' => 'Suma Devi',
            'phone' => null,
        ]);
    }

    public function test_inward_registration_rejects_invalid_phone_format_when_provided(): void
    {
        $response = $this->actingAs($this->inwardUser)
            ->withSession(['current_seat_id' => $this->inwardSeat->seat_id])
            ->post(route('petitions.store'), [
                'receipt_no' => 'INW/2026/PH-BAD',
                'date_of_petition_received' => now()->toDateString(),
                'mode_of_petition_received' => 'Direct',
                'is_inward_entry' => '1',
                'seat_id' => $this->cpspSeat->seat_id,
                'complainants' => [
                    ['name' => 'Invalid Phone User', 'phone' => '12345'],
                ],
            ]);

        $response->assertSessionHasErrors('complainants.0.phone');
        $this->assertDatabaseMissing('petitions', [
            'receipt_no' => 'INW/2026/PH-BAD',
        ]);
    }

    public function test_cpsp_and_t_section_users_can_view_and_update_inward_complainant_phone_preserving_address(): void
    {
        // Register inward petition for CPSP
        $this->actingAs($this->inwardUser)
            ->withSession(['current_seat_id' => $this->inwardSeat->seat_id])
            ->post(route('petitions.store'), [
                'receipt_no' => 'INW/2026/CPSP-EDIT',
                'date_of_petition_received' => now()->toDateString(),
                'mode_of_petition_received' => 'Direct',
                'is_inward_entry' => '1',
                'seat_id' => $this->cpspSeat->seat_id,
                'complainants' => [
                    ['name' => 'Hari Krishnan', 'phone' => '9447112233'],
                ],
            ]);

        $cpspPetition = Petition::where('receipt_no', 'INW/2026/CPSP-EDIT')->firstOrFail();

        // CPSP opens edit page -> phone is prefilled
        $editPage = $this->actingAs($this->cpspUser)
            ->withSession(['current_seat_id' => $this->cpspSeat->seat_id])
            ->get(route('petitions.edit', $cpspPetition->hashid));

        $editPage->assertStatus(200);
        $editPage->assertSee('9447112233');

        // CPSP updates petition with modified phone and full address details
        $this->actingAs($this->cpspUser)
            ->withSession(['current_seat_id' => $this->cpspSeat->seat_id])
            ->put(route('petitions.update', $cpspPetition->hashid), [
                'receipt_no' => $cpspPetition->receipt_no,
                'date_of_petition_received' => $cpspPetition->date_of_petition_received,
                'concerned_district_id' => $this->district->district_id,
                'nature_of_petition' => 'Bribery',
                'mode_of_petition_received' => 'Direct',
                'description' => 'Updated by CPSP.',
                'complainants' => [
                    [
                        'name' => 'Hari Krishnan',
                        'phone' => '9447998877',
                        'email' => 'hari@example.com',
                        'addresses' => [
                            [
                                'address_type' => 'Permanent',
                                'address' => 'Rose Villa, Kowdiar',
                                'district_id' => $this->district->district_id,
                                'pincode' => '695003',
                            ],
                        ],
                    ],
                ],
                'accused' => [
                    ['entity_type' => 'Person', 'name' => 'Official X', 'department_id' => $this->department->id],
                ],
            ])
            ->assertRedirect(route('petitions.index', ['tab' => 'all']));

        $this->assertDatabaseHas('addresses', [
            'petition_id' => $cpspPetition->petition_id,
            'person_type' => 'Complainant',
            'phone' => '9447998877',
            'full_address' => 'Rose Villa, Kowdiar',
            'pincode' => '695003',
        ]);

        // Now verify T-Section edit flow as well
        $this->actingAs($this->inwardUser)
            ->withSession(['current_seat_id' => $this->inwardSeat->seat_id])
            ->post(route('petitions.store'), [
                'receipt_no' => 'INW/2026/TSEC-EDIT',
                'date_of_petition_received' => now()->toDateString(),
                'mode_of_petition_received' => 'Tapal',
                'is_inward_entry' => '1',
                'seat_id' => $this->tSectionSeat->seat_id,
                'complainants' => [
                    ['name' => 'Meera Nair', 'phone' => '9895001122'],
                ],
            ]);

        $tPetition = Petition::where('receipt_no', 'INW/2026/TSEC-EDIT')->firstOrFail();

        $tEditPage = $this->actingAs($this->tSectionUser)
            ->withSession(['current_seat_id' => $this->tSectionSeat->seat_id])
            ->get(route('petitions.edit', $tPetition->hashid));

        $tEditPage->assertStatus(200);
        $tEditPage->assertSee('9895001122');

        $this->actingAs($this->tSectionUser)
            ->withSession(['current_seat_id' => $this->tSectionSeat->seat_id])
            ->put(route('petitions.update', $tPetition->hashid), [
                'receipt_no' => $tPetition->receipt_no,
                'date_of_petition_received' => $tPetition->date_of_petition_received,
                'concerned_district_id' => $this->district->district_id,
                'nature_of_petition' => 'Misuse of authority',
                'mode_of_petition_received' => 'Tapal',
                'description' => 'Updated by T-Section.',
                'complainants' => [
                    [
                        'name' => 'Meera Nair',
                        'phone' => '9895776655',
                        'addresses' => [
                            [
                                'address_type' => 'Permanent',
                                'address' => 'Green Lane, Pattom',
                                'district_id' => $this->district->district_id,
                                'pincode' => '695004',
                            ],
                        ],
                    ],
                ],
                'accused' => [
                    ['entity_type' => 'Person', 'name' => 'Official Y', 'department_id' => $this->department->id],
                ],
            ])
            ->assertRedirect(route('petitions.index', ['tab' => 'all']));

        $this->assertDatabaseHas('addresses', [
            'petition_id' => $tPetition->petition_id,
            'person_type' => 'Complainant',
            'phone' => '9895776655',
            'full_address' => 'Green Lane, Pattom',
        ]);
    }

    public function test_inward_edit_modal_updates_phone_while_preserving_existing_address_details(): void
    {
        $petition = Petition::factory()->create([
            'receipt_no' => 'INW/2026/MOD-PH',
            'date_of_petition_received' => now()->toDateString(),
            'mode_of_petition_received' => 'Direct',
            'user_id' => $this->inwardUser->user_id,
            'seat_id' => $this->cpspSeat->seat_id,
            'is_cpsp_processed' => false,
        ]);

        Address::factory()->create([
            'petition_id' => $petition->petition_id,
            'person_type' => 'Complainant',
            'person_name' => 'Venu Gopal',
            'phone' => '9447000011',
            'full_address' => 'Existing House Address',
            'district_id' => $this->district->district_id,
            'pincode' => '695001',
        ]);

        $this->actingAs($this->inwardUser)
            ->withSession(['current_seat_id' => $this->inwardSeat->seat_id])
            ->put(route('inward.petitions.update', $petition->hashid), [
                'receipt_no' => 'INW/2026/MOD-PH',
                'date_of_petition_received' => now()->toDateString(),
                'mode_of_petition_received' => 'Direct',
                'complainant_name' => 'Venu Gopal',
                'complainant_phone' => '9447999900',
                'seat_id' => $this->cpspSeat->seat_id,
            ])
            ->assertRedirect(route('inward.enter_petition'));

        $this->assertDatabaseHas('addresses', [
            'petition_id' => $petition->petition_id,
            'person_type' => 'Complainant',
            'person_name' => 'Venu Gopal',
            'phone' => '9447999900',
            'full_address' => 'Existing House Address',
            'pincode' => '695001',
        ]);
    }
}
