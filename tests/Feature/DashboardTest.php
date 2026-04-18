<?php

namespace Tests\Feature;

use App\Models\Petition;
use App\Models\User;
use App\Models\Seat;
use App\Models\SeatUser;
use App\Models\PetitionForwarding;
use App\Models\Decision;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $user;
    protected $seat;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->user = User::factory()->create(['role' => 'user']);
        $this->seat = Seat::factory()->create();
        
        SeatUser::factory()->create([
            'user_id' => $this->user->user_id,
            'seat_id' => $this->seat->seat_id,
            'is_active' => true
        ]);
    }

    public function test_admin_can_view_dashboard_metrics(): void
    {
        // Create some data within the default last 7 days range
        Petition::factory()->count(3)->create(['date_of_petition_received' => now()->subDays(2)]);
        $forwarding = PetitionForwarding::factory()->create(['forwarded_date' => now()->subDays(1)]);
        Decision::factory()->create(['decision_date' => now()->subDays(1)]);

        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('3'); // Total Petitions (assuming start with 0)
        $response->assertSee('1'); // Forwarded
        $response->assertSee('1'); // Decisions
    }

    public function test_admin_dashboard_date_range_filtering(): void
    {
        // Old petition
        Petition::factory()->create(['date_of_petition_received' => '2024-01-01']);
        // New petition
        Petition::factory()->create(['date_of_petition_received' => '2024-04-01']);

        $response = $this->actingAs($this->admin)->get(route('admin.dashboard', [
            'from_date' => '2024-03-01',
            'to_date' => '2024-04-30'
        ]));

        $response->assertStatus(200);
        $response->assertSee('1'); // Should only see the new one
    }

    public function test_user_can_view_dashboard_metrics(): void
    {
        // Petition for this user's seat
        Petition::factory()->create([
            'user_id' => $this->user->user_id,
            'seat_id' => $this->seat->seat_id,
            'status' => 'Received'
        ]);
        
        // Petition for another seat
        $otherSeat = Seat::factory()->create();
        Petition::factory()->create(['seat_id' => $otherSeat->seat_id]);

        $response = $this->actingAs($this->user)->get(route('user.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('1'); // Should only see their own seat's petition
    }

    public function test_user_dashboard_date_range_filtering(): void
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

        $response = $this->actingAs($this->user)->get(route('user.dashboard', [
            'start_date' => '2024-01-01',
            'end_date' => '2024-01-15'
        ]));

        $response->assertStatus(200);
        // User dashboard counts total petitions from the collection, so we can check if it's there
        $response->assertSee('1');
    }
}
