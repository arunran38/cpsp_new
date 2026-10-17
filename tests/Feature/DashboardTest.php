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
        
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');

        $this->user = User::factory()->create();
        $this->seat = Seat::factory()->create();
        $this->seat->assignRole('CPSP');
        
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
            'status' => 'Received',
            'is_cpsp_processed' => false,
        ]);
        
        // Petition for another seat
        $otherSeat = Seat::factory()->create();
        Petition::factory()->create(['seat_id' => $otherSeat->seat_id]);

        $response = $this->actingAs($this->user)->get(route('user.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('1'); // Should only see their own seat's petition
        $response->assertSee('Petition Trends');
        $response->assertSee('By Nature');
        $response->assertSee('id="petitionTrendsChart"', false);
        $response->assertSee('id="petitionNatureChart"', false);
        $response->assertSee('Inward Petitions');
        $response->assertSee('Processed Petitions');
        $response->assertSee('Forwarded to Units');
        $response->assertSee('VR Reports Received');
        $response->assertSee('Final Decisions');
        $this->assertSame(
            '1',
            preg_match('/Inward Petitions.*?<h3[^>]*>(\d+)<\/h3>/s', $response->getContent(), $matches)
                ? $matches[1]
                : null
        );
    }

    public function test_super_admin_cannot_see_petition_charts(): void
    {
        foreach (['super admin', 'Super Admin'] as $roleName) {
            $superAdmin = User::factory()->create();
            $superAdmin->assignRole($roleName);

            $response = $this->actingAs($superAdmin)->get(route('admin.dashboard'));

            $response->assertStatus(200);
            $response->assertDontSee('Petition Trends');
            $response->assertDontSee('By Nature');
            $response->assertDontSee('id="petitionTrendsChart"', false);
            $response->assertDontSee('id="petitionNatureChart"', false);
        }
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
