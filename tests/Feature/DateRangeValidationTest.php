<?php

namespace Tests\Feature;

use App\Models\Seat;
use App\Models\SeatUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DateRangeValidationTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;
    private Seat $seat;
    private string $today;
    private string $tomorrow;
    private string $yesterday;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

        $permissions = [
            'access admin dashboard',
            'view seat distribution chart',
            'view petition status chart',
            'view petition trends chart',
            'view district analysis chart',
            'view district analysis report',
            'export district analysis',
            'view department-wise report',
            'view seat diagnostics',
            'view all inward statistics',
            'export statistics',
            'view compliances',
            'view recycle bin',
            'view log',
            'view petitions',
            'view all petitions',
            'view master reports',
            'export petitions',
            'view inward registration',
            'create inward petitions',
            'access cpsp section',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $cpspRole = Role::firstOrCreate(['name' => 'CPSP', 'guard_name' => 'web']);

        $this->seat = Seat::factory()->create([
            'seat_name' => 'CPSP 1',
            'is_active' => true,
        ]);
        $this->seat->assignRole($cpspRole);

        $this->adminUser = User::factory()->create(['name' => 'Validation Admin']);
        $this->adminUser->assignRole($adminRole);
        $this->adminUser->givePermissionTo($permissions);

        SeatUser::factory()->create([
            'user_id' => $this->adminUser->user_id,
            'seat_id' => $this->seat->seat_id,
            'is_active' => true,
        ]);

        $now = now()->timezone(config('app.timezone', 'Asia/Kolkata'));
        $this->today = $now->format('Y-m-d');
        $this->tomorrow = $now->copy()->addDay()->format('Y-m-d');
        $this->yesterday = $now->copy()->subDay()->format('Y-m-d');
    }

    public function test_dashboard_rejects_future_and_inverted_dates_for_all_three_charts(): void
    {
        // 1. Seat Distribution (from_date / to_date)
        $this->actingAs($this->adminUser)
            ->get(route('dashboard', ['from_date' => $this->tomorrow, 'to_date' => $this->tomorrow]))
            ->assertSessionHasErrors(['from_date', 'to_date']);

        $this->actingAs($this->adminUser)
            ->get(route('dashboard', ['from_date' => $this->today, 'to_date' => $this->yesterday]))
            ->assertSessionHasErrors(['to_date']);

        // 2. Petition Trends (start_date / end_date)
        $this->actingAs($this->adminUser)
            ->get(route('dashboard', ['start_date' => $this->tomorrow, 'end_date' => $this->tomorrow]))
            ->assertSessionHasErrors(['start_date', 'end_date']);

        $this->actingAs($this->adminUser)
            ->get(route('dashboard', ['start_date' => $this->today, 'end_date' => $this->yesterday]))
            ->assertSessionHasErrors(['end_date']);

        // 3. District Trend Visualisation (district_from_date / district_to_date)
        $this->actingAs($this->adminUser)
            ->get(route('dashboard', ['district_from_date' => $this->tomorrow, 'district_to_date' => $this->tomorrow]))
            ->assertSessionHasErrors(['district_from_date', 'district_to_date']);

        $this->actingAs($this->adminUser)
            ->get(route('dashboard', ['district_from_date' => $this->today, 'district_to_date' => $this->yesterday]))
            ->assertSessionHasErrors(['district_to_date']);

        // Valid equal today range across all three succeeds
        $response = $this->actingAs($this->adminUser)->get(route('dashboard', [
            'from_date' => $this->today,
            'to_date' => $this->today,
            'start_date' => $this->today,
            'end_date' => $this->today,
            'district_from_date' => $this->today,
            'district_to_date' => $this->today,
        ]));

        $response->assertOk();
        $response->assertSee('district_from_date', false);
        $response->assertSee('district_to_date', false);
    }

    public function test_audit_logs_and_export_validate_date_range(): void
    {
        foreach (['admin.audit_logs.index', 'admin.audit_logs.export'] as $route) {
            $this->actingAs($this->adminUser)
                ->get(route($route, ['from_date' => $this->tomorrow, 'to_date' => $this->tomorrow]))
                ->assertSessionHasErrors(['from_date', 'to_date']);

            $this->actingAs($this->adminUser)
                ->get(route($route, ['from_date' => $this->today, 'to_date' => $this->yesterday]))
                ->assertSessionHasErrors(['to_date']);
        }

        $page = $this->actingAs($this->adminUser)->get(route('admin.audit_logs.index', [
            'from_date' => $this->today,
            'to_date' => $this->today,
        ]));
        $page->assertOk();
        $page->assertSee('id="dateRangeError"', false);

        $this->actingAs($this->adminUser)->get(route('admin.audit_logs.export', [
            'from_date' => $this->today,
            'to_date' => $this->today,
        ]))->assertOk();
    }

    public function test_district_analysis_and_export_validate_date_range(): void
    {
        foreach (['admin.reports.district-analysis', 'admin.reports.district-analysis.export'] as $route) {
            $this->actingAs($this->adminUser)
                ->get(route($route, ['date_from' => $this->tomorrow, 'date_to' => $this->tomorrow]))
                ->assertSessionHasErrors(['date_from', 'date_to']);

            $this->actingAs($this->adminUser)
                ->get(route($route, ['date_from' => $this->today, 'date_to' => $this->yesterday]))
                ->assertSessionHasErrors(['date_to']);
        }

        // AJAX request returns 422 JSON
        $this->actingAs($this->adminUser)
            ->getJson(route('admin.reports.district-analysis', [
                'date_from' => $this->today,
                'date_to' => $this->yesterday,
            ]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['date_to']);

        $this->actingAs($this->adminUser)->get(route('admin.reports.district-analysis', [
            'date_from' => $this->today,
            'date_to' => $this->today,
        ]))->assertOk();
    }

    public function test_department_analysis_and_export_validate_date_range(): void
    {
        foreach (['admin.departments.analysis', 'admin.departments.analysis.export'] as $route) {
            $this->actingAs($this->adminUser)
                ->get(route($route, ['date_from' => $this->tomorrow, 'date_to' => $this->tomorrow]))
                ->assertSessionHasErrors(['date_from', 'date_to']);

            $this->actingAs($this->adminUser)
                ->get(route($route, ['date_from' => $this->today, 'date_to' => $this->yesterday]))
                ->assertSessionHasErrors(['date_to']);
        }

        $this->actingAs($this->adminUser)
            ->getJson(route('admin.departments.analysis', [
                'date_from' => $this->tomorrow,
            ]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['date_from']);

        $this->actingAs($this->adminUser)->get(route('admin.departments.analysis', [
            'date_from' => $this->today,
            'date_to' => $this->today,
        ]))->assertOk();
    }

    public function test_trash_inward_statistics_compliances_and_seats_validate_date_range(): void
    {
        $routes = [
            'admin.trash.index',
            'inward.statistics',
            'inward.statistics.export',
            'compliances.index',
            'compliances.reports',
            'compliances.reports.export',
            'compliances.export',
            'admin.seats.statistics',
            'admin.seats.statistics.export',
            'petitions.index',
            'petitions.reports',
            'petitions.export',
            'inward.enter_petition',
            'inward.export',
        ];

        foreach ($routes as $route) {
            $this->actingAs($this->adminUser)
                ->get(route($route, ['date_from' => $this->tomorrow, 'date_to' => $this->tomorrow]))
                ->assertSessionHasErrors(['date_from', 'date_to']);

            $this->actingAs($this->adminUser)
                ->get(route($route, ['date_from' => $this->today, 'date_to' => $this->yesterday]))
                ->assertSessionHasErrors(['date_to']);
        }

        // Verify valid equal today range succeeds for key GET pages
        foreach (['admin.trash.index', 'inward.statistics', 'compliances.index', 'compliances.reports', 'admin.seats.statistics'] as $route) {
            $this->actingAs($this->adminUser)
                ->get(route($route, ['date_from' => $this->today, 'date_to' => $this->today]))
                ->assertOk();
        }
    }
}
