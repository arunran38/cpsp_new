<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Petition;
use App\Models\Seat;
use App\Models\SeatUser;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class InwardReceiptPrintTest extends TestCase
{
    use RefreshDatabase;

    public function test_inward_user_can_view_half_a4_printable_receipt(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $inwardRole = Role::findByName('Inward', 'web');
        $inwardSeat = Seat::factory()->create([
            'seat_name' => 'Inward Desk Seat',
            'is_active' => true,
        ]);
        $inwardSeat->assignRole($inwardRole);

        $inwardUser = User::factory()->create(['name' => 'Inward Officer']);
        $inwardUser->assignRole($inwardRole);
        SeatUser::factory()->create([
            'user_id' => $inwardUser->user_id,
            'seat_id' => $inwardSeat->seat_id,
            'is_active' => true,
        ]);

        $petition = Petition::factory()->create([
            'receipt_no' => 'INW/2026/REC-999',
            'date_of_petition_received' => '2026-11-21',
            'mode_of_petition_received' => 'Direct',
            'nature_of_petition' => 'Bribery',
            'user_id' => $inwardUser->user_id,
            'seat_id' => $inwardSeat->seat_id,
        ]);

        Address::factory()->create([
            'petition_id' => $petition->petition_id,
            'person_type' => 'Complainant',
            'person_name' => 'Suresh Kumar',
            'phone' => '9847012345',
        ]);

        $response = $this->actingAs($inwardUser)
            ->withSession(['current_seat_id' => $inwardSeat->seat_id])
            ->get(route('inward.receipt', $petition->hashid));

        $response->assertStatus(200);
        // Required contents
        $response->assertSee('INW/2026/REC-999');
        $response->assertSee('21/11/2026');
        $response->assertSee('Suresh Kumar');
        $response->assertSee('VIGILANCE &amp; ANTI-CORRUPTION BUREAU', false);
        $response->assertSee('Government of Kerala');
        $response->assertSee('cut-line');

        // Strictly excluded contents
        $response->assertDontSee('Cut along this line');
        $response->assertDontSee('Mode of Receipt');
        $response->assertDontSee('Nature of Petition');
    }

    public function test_unauthorized_user_cannot_view_receipt(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $user = User::factory()->create(['name' => 'Random User']);
        $seat = Seat::factory()->create(['is_active' => true]);
        SeatUser::factory()->create([
            'user_id' => $user->user_id,
            'seat_id' => $seat->seat_id,
            'is_active' => true,
        ]);

        $owner = User::factory()->create(['name' => 'Other User']);
        $petition = Petition::factory()->create([
            'receipt_no' => 'INW/2026/REC-SECRET',
            'date_of_petition_received' => '2026-11-21',
            'user_id' => $owner->user_id,
        ]);

        $response = $this->actingAs($user)
            ->withSession(['current_seat_id' => $seat->seat_id])
            ->get(route('inward.receipt', $petition->hashid));

        $response->assertForbidden();
    }

    public function test_user_without_print_inward_receipt_permission_cannot_view_receipt(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        // CPSP role typically does not have 'print inward receipt'
        $cpspRole = Role::findByName('CPSP', 'web');
        $cpspSeat = Seat::factory()->create(['seat_name' => 'CPSP Desk', 'is_active' => true]);
        $cpspSeat->assignRole($cpspRole);

        $user = User::factory()->create(['name' => 'CPSP Officer']);
        $user->assignRole($cpspRole);
        SeatUser::factory()->create([
            'user_id' => $user->user_id,
            'seat_id' => $cpspSeat->seat_id,
            'is_active' => true,
        ]);

        $petition = Petition::factory()->create([
            'receipt_no' => 'INW/2026/REC-NO-PRINT',
            'date_of_petition_received' => '2026-11-21',
            'user_id' => $user->user_id,
            'seat_id' => $cpspSeat->seat_id,
        ]);

        $response = $this->actingAs($user)
            ->withSession(['current_seat_id' => $cpspSeat->seat_id])
            ->get(route('inward.receipt', $petition->hashid));

        $response->assertForbidden();
    }

    public function test_successful_inward_store_redirects_with_receipt_hashid_in_session(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $inwardRole = Role::findByName('Inward', 'web');
        $cpspRole = Role::findByName('CPSP', 'web');

        $inwardSeat = Seat::factory()->create(['seat_name' => 'Inward Desk', 'is_active' => true]);
        $inwardSeat->assignRole($inwardRole);

        $targetSeat = Seat::factory()->create(['seat_name' => 'CPSP Desk 1', 'is_active' => true]);
        $targetSeat->assignRole($cpspRole);

        $inwardUser = User::factory()->create(['name' => 'Inward Officer']);
        $inwardUser->assignRole($inwardRole);
        SeatUser::factory()->create([
            'user_id' => $inwardUser->user_id,
            'seat_id' => $inwardSeat->seat_id,
            'is_active' => true,
        ]);

        $response = $this->actingAs($inwardUser)
            ->withSession(['current_seat_id' => $inwardSeat->seat_id])
            ->post(route('petitions.store'), [
                'receipt_no' => 'INW/2026/RECEIPT-TEST',
                'date_of_petition_received' => now()->toDateString(),
                'mode_of_petition_received' => 'Direct',
                'is_inward_entry' => '1',
                'seat_id' => $targetSeat->seat_id,
                'complainants' => [
                    ['name' => 'Mohanan Pillai', 'phone' => '9447012345'],
                ],
            ]);

        $response->assertRedirect(route('inward.enter_petition'));
        $response->assertSessionHas('receipt_petition_hashid');
        $response->assertSessionHas('receipt_petition_no', 'INW/2026/RECEIPT-TEST');

        $petition = Petition::where('receipt_no', 'INW/2026/RECEIPT-TEST')->first();
        $this->assertNotNull($petition);

        // Follow redirect to inward.enter_petition and assert Direct Auto-Print and banner is rendered
        $followResponse = $this->actingAs($inwardUser)
            ->withSession([
                'current_seat_id' => $inwardSeat->seat_id,
                'receipt_petition_hashid' => $petition->hashid,
                'receipt_petition_no' => 'INW/2026/RECEIPT-TEST',
            ])
            ->get(route('inward.enter_petition'));

        $followResponse->assertStatus(200);
        $followResponse->assertSee('Registration Successful');
        $followResponse->assertSee('INW/2026/RECEIPT-TEST');
        $followResponse->assertSee('Print Receipt');
        $followResponse->assertSee('window.printInwardReceipt');
    }

    public function test_view_inward_petitions_table_renders_actions_column_and_print_receipt_button(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $inwardRole = Role::findByName('Inward', 'web');
        $inwardSeat = Seat::factory()->create(['seat_name' => 'Inward Seat', 'is_active' => true]);
        $inwardSeat->assignRole($inwardRole);

        $inwardUser = User::factory()->create(['name' => 'Inward Officer']);
        $inwardUser->assignRole($inwardRole);
        SeatUser::factory()->create([
            'user_id' => $inwardUser->user_id,
            'seat_id' => $inwardSeat->seat_id,
            'is_active' => true,
        ]);

        $petition = Petition::factory()->create([
            'receipt_no' => 'INW/2026/TABLE-ROW',
            'date_of_petition_received' => '2026-11-21',
            'user_id' => $inwardUser->user_id,
            'seat_id' => $inwardSeat->seat_id,
        ]);

        $response = $this->actingAs($inwardUser)
            ->withSession(['current_seat_id' => $inwardSeat->seat_id])
            ->get(route('inward.enter_petition'));

        $response->assertStatus(200);
        $response->assertSee('Actions');
        $response->assertSee('Print Receipt');
        $response->assertSee(route('inward.receipt', $petition->hashid));
    }

    public function test_petition_show_does_not_render_print_receipt_button_and_only_admin_super_admin_inward_can_access_receipt(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $inwardRole = Role::findByName('Inward', 'web');
        $cpspRole = Role::findByName('CPSP', 'web');
        $tSectionRole = Role::findByName('T Section', 'web');
        $complianceRole = Role::findByName('Petition Compliances', 'web');
        $cpspAdminRole = Role::firstOrCreate(['name' => 'CPSP Admin', 'guard_name' => 'web']);
        $adminRole = Role::findByName('admin', 'web');
        $superAdminRole = Role::findByName('super admin', 'web');
        $this->seed(RolesAndPermissionsSeeder::class);

        $inwardSeat = Seat::factory()->create(['seat_name' => 'Inward Desk', 'is_active' => true]);
        $inwardSeat->assignRole($inwardRole);

        $cpspSeat = Seat::factory()->create(['seat_name' => 'CPSP 1', 'is_active' => true]);
        $cpspSeat->assignRole($cpspRole);

        $tSectionSeat = Seat::factory()->create(['seat_name' => 'T Section Desk', 'is_active' => true]);
        $tSectionSeat->assignRole($tSectionRole);

        $inwardUser = User::factory()->create(['name' => 'Inward User']);
        $inwardUser->assignRole($inwardRole);
        SeatUser::factory()->create(['user_id' => $inwardUser->user_id, 'seat_id' => $inwardSeat->seat_id, 'is_active' => true]);

        $cpspUser = User::factory()->create(['name' => 'CPSP User']);
        $cpspUser->assignRole($cpspRole);
        SeatUser::factory()->create(['user_id' => $cpspUser->user_id, 'seat_id' => $cpspSeat->seat_id, 'is_active' => true]);

        $tSectionUser = User::factory()->create(['name' => 'T Section User']);
        $tSectionUser->assignRole($tSectionRole);
        SeatUser::factory()->create(['user_id' => $tSectionUser->user_id, 'seat_id' => $tSectionSeat->seat_id, 'is_active' => true]);

        $complianceUser = User::factory()->create(['name' => 'Compliance User']);
        $complianceUser->assignRole($complianceRole);

        $cpspAdminUser = User::factory()->create(['name' => 'CPSP Admin User']);
        $cpspAdminUser->assignRole($cpspAdminRole);

        $adminUser = User::factory()->create(['name' => 'Admin User']);
        $adminUser->assignRole($adminRole);

        $superAdminUser = User::factory()->create(['name' => 'Super Admin User']);
        $superAdminUser->assignRole($superAdminRole);

        $petition = Petition::factory()->create([
            'receipt_no' => 'INW/2026/SHOW-TEST',
            'date_of_petition_received' => '2026-11-21',
            'user_id' => $inwardUser->user_id,
            'seat_id' => $cpspSeat->seat_id,
            'created_by_seat_id' => $inwardSeat->seat_id,
        ]);

        // Petition Details (petition_show) must NOT show Print Receipt button for anyone (CPSP, T-Section, Admin, or Inward)
        foreach ([
            [$cpspUser, $cpspSeat->seat_id],
            [$tSectionUser, $tSectionSeat->seat_id],
            [$adminUser, null],
            [$inwardUser, $inwardSeat->seat_id],
        ] as [$viewer, $seatId]) {
            $req = $this->actingAs($viewer);
            if ($seatId) {
                $req->withSession(['current_seat_id' => $seatId]);
            }
            $showResponse = $req->get(route('petitions.show', $petition->hashid));
            $showResponse->assertOk();
            $showResponse->assertDontSee('Print Receipt');
            $showResponse->assertDontSee(route('inward.receipt', $petition->hashid));
        }

        // Direct receipt route: allowed for Inward, Admin, Super Admin
        $this->actingAs($inwardUser)
            ->withSession(['current_seat_id' => $inwardSeat->seat_id])
            ->get(route('inward.receipt', $petition->hashid))
            ->assertOk();

        $this->actingAs($adminUser)
            ->get(route('inward.receipt', $petition->hashid))
            ->assertOk();

        $this->actingAs($superAdminUser)
            ->get(route('inward.receipt', $petition->hashid))
            ->assertOk();

        // Direct receipt route: forbidden for CPSP, T Section, Petition Compliances, CPSP Admin
        $this->actingAs($cpspUser)
            ->withSession(['current_seat_id' => $cpspSeat->seat_id])
            ->get(route('inward.receipt', $petition->hashid))
            ->assertForbidden();

        $this->actingAs($tSectionUser)
            ->withSession(['current_seat_id' => $tSectionSeat->seat_id])
            ->get(route('inward.receipt', $petition->hashid))
            ->assertForbidden();

        $this->actingAs($complianceUser)
            ->get(route('inward.receipt', $petition->hashid))
            ->assertForbidden();

        $this->actingAs($cpspAdminUser)
            ->get(route('inward.receipt', $petition->hashid))
            ->assertForbidden();
    }
}
