<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Seat;
use App\Models\SeatUser;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PermissionAliasConsolidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_migration_moves_legacy_role_and_user_grants_to_canonical_permission(): void
    {
        $legacy = Permission::create([
            'name' => 'edit petitions',
            'guard_name' => 'web',
        ]);
        $role = Role::create([
            'name' => 'Legacy Petition Editor',
            'guard_name' => 'web',
        ]);
        $role->givePermissionTo($legacy);

        $user = User::factory()->create();
        $user->givePermissionTo($legacy);

        $migration = require database_path('migrations/2026_11_04_180000_consolidate_duplicate_permission_aliases.php');
        $migration->up();

        $this->assertDatabaseMissing('permissions', ['name' => 'edit petitions', 'guard_name' => 'web']);
        $this->assertDatabaseHas('permissions', ['name' => 'update petitions', 'guard_name' => 'web']);
        $this->assertTrue($role->fresh()->hasPermissionTo('update petitions'));
        $this->assertTrue($user->fresh()->canAccess('update petitions'));
    }

    public function test_seeder_moves_legacy_permission_grants_to_canonical_permission_idempotently(): void
    {
        $legacy = Permission::create([
            'name' => 'pendency details',
            'guard_name' => 'web',
        ]);
        $role = Role::create([
            'name' => 'Legacy Reporting Role',
            'guard_name' => 'web',
        ]);
        $role->givePermissionTo($legacy);

        $user = User::factory()->create();
        $user->givePermissionTo($legacy);

        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->assertDatabaseMissing('permissions', ['name' => 'pendency details', 'guard_name' => 'web']);
        $this->assertDatabaseHas('permissions', ['name' => 'view pendency details', 'guard_name' => 'web']);
        $this->assertTrue($role->fresh()->hasPermissionTo('view pendency details'));
        $this->assertTrue($user->fresh()->canAccess('view pendency details'));
    }

    public function test_statistics_export_permission_does_not_authorize_petition_register_exports(): void
    {
        $user = User::factory()->create();
        $seat = Seat::factory()->create(['seat_name' => 'Permission Export Test Seat']);
        SeatUser::factory()->create([
            'user_id' => $user->user_id,
            'seat_id' => $seat->seat_id,
            'is_active' => true,
        ]);
        session(['current_seat_id' => $seat->seat_id]);

        $user->givePermissionTo(
            Permission::firstOrCreate(['name' => 'access cpsp section', 'guard_name' => 'web']),
            Permission::firstOrCreate(['name' => 'view master reports', 'guard_name' => 'web']),
            Permission::firstOrCreate(['name' => 'view inward registration', 'guard_name' => 'web']),
            Permission::firstOrCreate(['name' => 'export statistics', 'guard_name' => 'web'])
        );

        $this->actingAs($user)
            ->get(route('petitions.export'))
            ->assertForbidden();

        $this->get(route('inward.export'))
            ->assertForbidden();

        $user->givePermissionTo(Permission::firstOrCreate([
            'name' => 'export petitions',
            'guard_name' => 'web',
        ]));
        $this->get(route('petitions.export'))->assertOk();
    }
}
