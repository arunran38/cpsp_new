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

    public function test_seat_distribution_chart_sorts_temporary_seats_immediately_after_parent(): void
    {
        $this->admin->givePermissionTo('view seat distribution chart');

        // Create seats in random order: CPSP 5, CPSP 2, CPSP 2 B, CPSP 1, CPSP 2 A, CPSP 6, CPSP 5 A
        $seatNames = ['CPSP 5', 'CPSP 2', 'CPSP 2 B', 'CPSP 1', 'CPSP 2 A', 'CPSP 6', 'CPSP 5 A'];
        foreach ($seatNames as $name) {
            $s = Seat::factory()->create(['seat_name' => $name, 'is_active' => true]);
            $s->assignRole('CPSP');
            $u = User::factory()->create();
            SeatUser::factory()->create([
                'user_id' => $u->user_id,
                'seat_id' => $s->seat_id,
                'is_active' => true,
            ]);
        }

        $response = $this->actingAs($this->admin)->get(route('dashboard'));
        $response->assertStatus(200);

        $content = $response->getContent();
        preg_match('/labels:\s*(\[[^\]]+\])/', $content, $matches);
        $this->assertNotEmpty($matches, 'Seat chart labels array must be present');
        $labels = json_decode($matches[1], true);

        // Find positions of our seats
        $pos1 = array_search('CPSP 1', $labels);
        $pos2 = array_search('CPSP 2', $labels);
        $pos2A = array_search('CPSP 2 A', $labels);
        $pos2B = array_search('CPSP 2 B', $labels);
        $pos5 = array_search('CPSP 5', $labels);
        $pos5A = array_search('CPSP 5 A', $labels);
        $pos6 = array_search('CPSP 6', $labels);

        $this->assertTrue($pos1 < $pos2, 'CPSP 1 must appear before CPSP 2');
        $this->assertEquals($pos2 + 1, $pos2A, 'CPSP 2 A must appear immediately after CPSP 2');
        $this->assertEquals($pos2A + 1, $pos2B, 'CPSP 2 B must appear immediately after CPSP 2 A');
        $this->assertTrue($pos2B < $pos5, 'CPSP 2 B must appear before CPSP 5');
        $this->assertEquals($pos5 + 1, $pos5A, 'CPSP 5 A must appear immediately after CPSP 5');
        $this->assertTrue($pos5A < $pos6, 'CPSP 5 A must appear before CPSP 6');
    }

    public function test_seat_distribution_chart_excludes_vacant_seats(): void
    {
        $this->admin->givePermissionTo('view seat distribution chart');

        // Create an occupied seat
        $occupied = Seat::factory()->create(['seat_name' => 'CPSP 3', 'is_active' => true]);
        $occupied->assignRole('CPSP');
        $u = User::factory()->create();
        SeatUser::factory()->create([
            'user_id' => $u->user_id,
            'seat_id' => $occupied->seat_id,
            'is_active' => true,
        ]);

        // Create a vacant temporary seat (no active seatUser)
        $vacant = Seat::factory()->create(['seat_name' => 'CPSP 3 A', 'is_active' => true]);
        $vacant->assignRole('CPSP');

        $response = $this->actingAs($this->admin)->get(route('dashboard'));
        $response->assertStatus(200);

        $content = $response->getContent();
        preg_match('/labels:\s*(\[[^\]]+\])/', $content, $matches);
        $this->assertNotEmpty($matches);
        $labels = json_decode($matches[1], true);

        $this->assertContains('CPSP 3', $labels);
        $this->assertNotContains('CPSP 3 A', $labels, 'Vacant temporary seat must be excluded');

        // Now assign a user to CPSP 3 A and verify it appears immediately after CPSP 3
        SeatUser::factory()->create([
            'user_id' => User::factory()->create()->user_id,
            'seat_id' => $vacant->seat_id,
            'is_active' => true,
        ]);

        $responseAfter = $this->actingAs($this->admin)->get(route('dashboard'));
        preg_match('/labels:\s*(\[[^\]]+\])/', $responseAfter->getContent(), $matchesAfter);
        $labelsAfter = json_decode($matchesAfter[1], true);

        $this->assertContains('CPSP 3 A', $labelsAfter, 'Seat with assigned user must now appear');
        $p3 = array_search('CPSP 3', $labelsAfter);
        $p3A = array_search('CPSP 3 A', $labelsAfter);
        $this->assertEquals($p3 + 1, $p3A, 'CPSP 3 A must appear immediately after CPSP 3');
    }
}
