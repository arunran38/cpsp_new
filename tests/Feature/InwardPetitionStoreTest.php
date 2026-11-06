<?php

namespace Tests\Feature;

use App\Models\Petition;
use App\Models\Seat;
use App\Models\SeatUser;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class InwardPetitionStoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_inward_user_can_submit_and_transfer_inward_petition(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $inwardRole = Role::findByName('Inward', 'web');
        $cpspRole = Role::findByName('CPSP', 'web');

        $inwardSeat = Seat::factory()->create([
            'seat_name' => 'Inward Desk Seat',
            'is_active' => true,
        ]);
        $inwardSeat->assignRole($inwardRole);

        $targetSeat = Seat::factory()->create([
            'seat_name' => 'CPSP Desk 1',
            'is_active' => true,
        ]);
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
                'receipt_no' => 'INW/2026/001',
                'date_of_petition_received' => now()->toDateString(),
                'mode_of_petition_received' => 'Direct',
                'is_inward_entry' => '1',
                'seat_id' => $targetSeat->seat_id,
                'complainants' => [
                    ['name' => 'John Doe', 'phone' => '9876543210'],
                ],
            ]);

        $response->assertRedirect(route('inward.enter_petition'));
        $this->assertDatabaseHas('petitions', [
            'receipt_no' => 'INW/2026/001',
            'seat_id' => $targetSeat->seat_id,
        ]);
    }

    public function test_unauthorized_user_cannot_submit_inward_petition(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $targetSeat = Seat::factory()->create([
            'seat_name' => 'CPSP Desk 1',
            'is_active' => true,
        ]);

        $user = User::factory()->create(['name' => 'User Without Inward Permissions']);
        $userSeat = Seat::factory()->create(['is_active' => true]);
        SeatUser::factory()->create([
            'user_id' => $user->user_id,
            'seat_id' => $userSeat->seat_id,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)
            ->withSession(['current_seat_id' => $userSeat->seat_id])
            ->post(route('petitions.store'), [
                'receipt_no' => 'INW/2026/002',
                'date_of_petition_received' => now()->toDateString(),
                'mode_of_petition_received' => 'Direct',
                'is_inward_entry' => '1',
                'seat_id' => $targetSeat->seat_id,
            ]);

        $response->assertForbidden();
    }

    public function test_inward_petition_date_of_receipt_is_automatically_set_to_current_date_and_read_only(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $inwardRole = Role::findByName('Inward', 'web');
        $cpspRole = Role::findByName('CPSP', 'web');

        $inwardSeat = Seat::factory()->create([
            'seat_name' => 'Inward Desk Seat',
            'is_active' => true,
        ]);
        $inwardSeat->assignRole($inwardRole);

        $targetSeat = Seat::factory()->create([
            'seat_name' => 'CPSP Desk 1',
            'is_active' => true,
        ]);
        $targetSeat->assignRole($cpspRole);

        $inwardUser = User::factory()->create(['name' => 'Inward Officer']);
        $inwardUser->assignRole($inwardRole);
        SeatUser::factory()->create([
            'user_id' => $inwardUser->user_id,
            'seat_id' => $inwardSeat->seat_id,
            'is_active' => true,
        ]);

        $today = now()->timezone(config('app.timezone', 'Asia/Kolkata'))->format('Y-m-d');

        // 1. Verify UI renders the date field as readonly with today's date
        $pageResponse = $this->actingAs($inwardUser)
            ->withSession(['current_seat_id' => $inwardSeat->seat_id])
            ->get(route('inward.enter_petition'));

        $pageResponse->assertStatus(200);
        $pageResponse->assertSee('name="date_of_petition_received"', false);
        $pageResponse->assertSee('readonly', false);
        $pageResponse->assertSee('value="' . $today . '"', false);

        // 2. Submit with an attempt to override the date to a past date
        $response = $this->actingAs($inwardUser)
            ->withSession(['current_seat_id' => $inwardSeat->seat_id])
            ->post(route('petitions.store'), [
                'receipt_no' => 'INW/2026/DATE-TEST-1',
                'date_of_petition_received' => '2024-01-01', // Manual override attempt
                'mode_of_petition_received' => 'Direct',
                'is_inward_entry' => '1',
                'seat_id' => $targetSeat->seat_id,
            ]);

        $response->assertRedirect(route('inward.enter_petition'));

        // 3. Database MUST have today's date, overriding the submitted past date
        $this->assertDatabaseHas('petitions', [
            'receipt_no' => 'INW/2026/DATE-TEST-1',
            'date_of_petition_received' => $today,
        ]);
    }
}
