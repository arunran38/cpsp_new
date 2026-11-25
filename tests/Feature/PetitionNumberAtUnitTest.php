<?php

namespace Tests\Feature;

use App\Models\DepartmentList;
use App\Models\District;
use App\Models\Petition;
use App\Models\Seat;
use App\Models\SeatUser;
use App\Models\Unit;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PetitionNumberAtUnitTest extends TestCase
{
    use RefreshDatabase;

    protected User $cpspUser;
    protected Seat $cpspSeat;
    protected User $inwardUser;
    protected Seat $inwardSeat;
    protected Unit $unit;
    protected District $district;
    protected DepartmentList $department;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        $cpspRole = Role::findByName('CPSP', 'web');
        $inwardRole = Role::findByName('Inward', 'web');

        $this->cpspSeat = Seat::factory()->create(['seat_name' => 'CPSP 1', 'is_active' => true]);
        $this->cpspSeat->assignRole($cpspRole);

        $this->cpspUser = User::factory()->create(['name' => 'CPSP Officer']);
        $this->cpspUser->assignRole($cpspRole);
        SeatUser::factory()->create([
            'user_id' => $this->cpspUser->user_id,
            'seat_id' => $this->cpspSeat->seat_id,
            'is_active' => true,
        ]);

        $this->inwardSeat = Seat::factory()->create(['seat_name' => 'Inward 1', 'is_active' => true]);
        $this->inwardSeat->assignRole($inwardRole);

        $this->inwardUser = User::factory()->create(['name' => 'Inward Officer']);
        $this->inwardUser->assignRole($inwardRole);
        SeatUser::factory()->create([
            'user_id' => $this->inwardUser->user_id,
            'seat_id' => $this->inwardSeat->seat_id,
            'is_active' => true,
        ]);

        $this->unit = Unit::factory()->create(['unit_name' => 'SIU-I', 'unit_code' => 'SIU1']);
        $this->district = District::firstOrCreate(['district_name' => 'Thiruvananthapuram', 'district_code' => 'TVM']);
        $this->department = DepartmentList::firstOrCreate(['department_name' => 'Police']);
    }

    public function test_non_admin_user_sees_petition_number_at_unit_on_create_and_edit_forms(): void
    {
        $createResponse = $this->actingAs($this->cpspUser)
            ->withSession(['current_seat_id' => $this->cpspSeat->seat_id])
            ->get(route('petitions.create'));

        $createResponse->assertStatus(200);
        $createResponse->assertSee('petition_number_at_unit');
        $createResponse->assertSee('Petition Number at Unit');

        $petition = Petition::factory()->create([
            'user_id' => $this->cpspUser->user_id,
            'seat_id' => $this->cpspSeat->seat_id,
            'mode_of_petition_received' => 'Unit',
            'unit_id' => $this->unit->unit_id,
            'petition_number_at_unit' => 'SIU1/2026/99',
            'date_of_petition_received_at_unit' => now()->toDateString(),
        ]);

        $editResponse = $this->actingAs($this->cpspUser)
            ->withSession(['current_seat_id' => $this->cpspSeat->seat_id])
            ->get(route('petitions.edit', $petition->hashid));

        $editResponse->assertStatus(200);
        $editResponse->assertSee('petition_number_at_unit');
        $editResponse->assertSee('SIU1/2026/99');
    }

    public function test_non_admin_user_can_create_unit_petition_with_and_without_petition_number_at_unit(): void
    {
        $basePayload = [
            'receipt_no' => 'CPSP/2026/UNIT-001',
            'date_of_petition_received' => now()->toDateString(),
            'concerned_district_id' => $this->district->district_id,
            'nature_of_petition' => 'Bribery',
            'mode_of_petition_received' => 'Unit',
            'unit_id' => $this->unit->unit_id,
            'petition_number_at_unit' => 'UNIT-REF/2026/12',
            'date_of_petition_received_at_unit' => now()->subDay()->toDateString(),
            'description' => 'Complaint received through unit.',
            'complainants' => [
                ['name' => 'Arjun Menon', 'phone' => '9447001122'],
            ],
            'accused' => [
                ['entity_type' => 'Person', 'name' => 'Ravi Kumar', 'department_id' => $this->department->id],
            ],
        ];

        $responseWithNumber = $this->actingAs($this->cpspUser)
            ->withSession(['current_seat_id' => $this->cpspSeat->seat_id])
            ->post(route('petitions.store'), $basePayload);

        $responseWithNumber->assertRedirect(route('petitions.index', ['tab' => 'all']));
        $this->assertDatabaseHas('petitions', [
            'receipt_no' => 'CPSP/2026/UNIT-001',
            'petition_number_at_unit' => 'UNIT-REF/2026/12',
        ]);

        $payloadWithoutNumber = $basePayload;
        $payloadWithoutNumber['receipt_no'] = 'CPSP/2026/UNIT-002';
        $payloadWithoutNumber['petition_number_at_unit'] = '';

        $responseWithoutNumber = $this->actingAs($this->cpspUser)
            ->withSession(['current_seat_id' => $this->cpspSeat->seat_id])
            ->post(route('petitions.store'), $payloadWithoutNumber);

        $responseWithoutNumber->assertRedirect(route('petitions.index', ['tab' => 'all']));
        $this->assertDatabaseHas('petitions', [
            'receipt_no' => 'CPSP/2026/UNIT-002',
            'petition_number_at_unit' => null,
        ]);
    }

    public function test_updating_petition_saves_and_clears_petition_number_at_unit(): void
    {
        $petition = Petition::factory()->create([
            'user_id' => $this->cpspUser->user_id,
            'seat_id' => $this->cpspSeat->seat_id,
            'concerned_district_id' => $this->district->district_id,
            'nature_of_petition' => 'Bribery',
            'mode_of_petition_received' => 'Unit',
            'unit_id' => $this->unit->unit_id,
            'petition_number_at_unit' => 'OLD-UNIT-NO',
            'date_of_petition_received_at_unit' => now()->subDays(2)->toDateString(),
        ]);

        $updatePayload = [
            'receipt_no' => $petition->receipt_no,
            'date_of_petition_received' => $petition->date_of_petition_received,
            'concerned_district_id' => $this->district->district_id,
            'nature_of_petition' => 'Bribery',
            'mode_of_petition_received' => 'Unit',
            'unit_id' => $this->unit->unit_id,
            'petition_number_at_unit' => 'NEW-UNIT-NO/2026',
            'date_of_petition_received_at_unit' => now()->subDay()->toDateString(),
            'description' => 'Updated description.',
            'complainants' => [
                ['name' => 'Arjun Menon'],
            ],
            'accused' => [
                ['entity_type' => 'Person', 'name' => 'Ravi Kumar', 'department_id' => $this->department->id],
            ],
        ];

        $this->actingAs($this->cpspUser)
            ->withSession(['current_seat_id' => $this->cpspSeat->seat_id])
            ->put(route('petitions.update', $petition->hashid), $updatePayload)
            ->assertRedirect(route('petitions.index', ['tab' => 'all']));

        $petition->refresh();
        $this->assertEquals('NEW-UNIT-NO/2026', $petition->petition_number_at_unit);

        // Changing mode away from Unit clears petition_number_at_unit
        $updatePayload['mode_of_petition_received'] = 'Direct';
        $this->actingAs($this->cpspUser)
            ->withSession(['current_seat_id' => $this->cpspSeat->seat_id])
            ->put(route('petitions.update', $petition->hashid), $updatePayload)
            ->assertRedirect(route('petitions.index', ['tab' => 'all']));

        $petition->refresh();
        $this->assertNull($petition->petition_number_at_unit);
    }

    public function test_inward_user_can_create_and_edit_unit_petition_with_optional_number(): void
    {
        $this->actingAs($this->inwardUser)
            ->withSession(['current_seat_id' => $this->inwardSeat->seat_id])
            ->post(route('petitions.store'), [
                'receipt_no' => 'INW/2026/UNIT-77',
                'date_of_petition_received' => now()->toDateString(),
                'mode_of_petition_received' => 'Unit',
                'unit_id' => $this->unit->unit_id,
                'petition_number_at_unit' => 'INW-UNIT/007',
                'date_of_petition_received_at_unit' => now()->subDay()->toDateString(),
                'is_inward_entry' => '1',
                'seat_id' => $this->cpspSeat->seat_id,
                'complainants' => [
                    ['name' => 'Suresh Babu'],
                ],
            ])
            ->assertRedirect(route('inward.enter_petition'));

        $petition = Petition::where('receipt_no', 'INW/2026/UNIT-77')->firstOrFail();
        $this->assertEquals('INW-UNIT/007', $petition->petition_number_at_unit);

        $this->actingAs($this->inwardUser)
            ->withSession(['current_seat_id' => $this->inwardSeat->seat_id])
            ->put(route('inward.petitions.update', $petition->hashid), [
                'receipt_no' => 'INW/2026/UNIT-77',
                'date_of_petition_received' => now()->toDateString(),
                'mode_of_petition_received' => 'Unit',
                'unit_id' => $this->unit->unit_id,
                'petition_number_at_unit' => '',
                'date_of_petition_received_at_unit' => now()->subDay()->toDateString(),
                'complainant_name' => 'Suresh Babu',
                'seat_id' => $this->cpspSeat->seat_id,
            ])
            ->assertRedirect(route('inward.enter_petition'));

        $petition->refresh();
        $this->assertNull($petition->petition_number_at_unit);
    }

    public function test_petition_show_displays_petition_number_at_unit(): void
    {
        $petition = Petition::factory()->create([
            'user_id' => $this->cpspUser->user_id,
            'seat_id' => $this->cpspSeat->seat_id,
            'mode_of_petition_received' => 'Unit',
            'unit_id' => $this->unit->unit_id,
            'petition_number_at_unit' => 'UNIT-SHOW-123',
            'date_of_petition_received_at_unit' => now()->toDateString(),
        ]);

        $response = $this->actingAs($this->cpspUser)
            ->withSession(['current_seat_id' => $this->cpspSeat->seat_id])
            ->get(route('petitions.show', $petition->hashid));

        $response->assertStatus(200);
        $response->assertSee('Petition');
        $response->assertSee('Number at Unit');
        $response->assertSee('UNIT-SHOW-123');
    }
}
