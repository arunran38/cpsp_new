<?php

namespace Tests\Feature;

use App\Models\Petition;
use App\Models\Seat;
use App\Models\SeatUser;
use App\Models\Unit;
use App\Models\User;
use App\Services\TSectionPermissionRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TSectionPetitionPermissionTest extends TestCase
{
    use RefreshDatabase;

    protected User $tSectionUser;
    protected User $regularUser;
    protected Seat $tSectionSeat;
    protected Seat $regularSeat;
    protected Unit $directorateUnit;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Setup Permissions
        $permissions = [
            'view petitions',
            'create petitions',
            'update petitions',
            'delete petitions',
            'view own petitions',
            'access t-section dashboard',
            'forward t-section petitions',
            'export t-section petitions',
            TSectionPermissionRegistry::VIEW_DIAGNOSTICS,
            TSectionPermissionRegistry::VIEW_PENDING_DETAILS,
        ];

        foreach ($permissions as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        // 2. Setup Directorate Unit (Unit 8) & Seats
        $this->directorateUnit = Unit::factory()->create(['unit_id' => 8, 'unit_name' => 'Vigilance Directorate']);

        $this->tSectionSeat = Seat::factory()->create(['seat_name' => 'T Section Desk', 'is_active' => true]);
        $this->tSectionSeat->units()->attach($this->directorateUnit->unit_id);

        $this->regularSeat = Seat::factory()->create(['seat_name' => 'Inward / General Seat', 'is_active' => true]);

        // 3. Setup Roles
        $tSectionRole = Role::firstOrCreate(['name' => 'T Section', 'guard_name' => 'web']);
        $tSectionRole->syncPermissions([
            'view petitions',
            'create petitions',
            'update petitions',
            'view own petitions',
            'access t-section dashboard',
            'forward t-section petitions',
        ]);

        $regularRole = Role::firstOrCreate(['name' => 'User', 'guard_name' => 'web']);
        $regularRole->syncPermissions([
            'view petitions',
            'create petitions',
            'update petitions',
            'view own petitions',
        ]);

        $this->tSectionSeat->syncRoles([$tSectionRole]);
        $this->regularSeat->syncRoles([$regularRole]);

        // 4. Setup Users
        $this->tSectionUser = User::factory()->create();
        $this->tSectionUser->assignRole($tSectionRole);
        SeatUser::factory()->create([
            'user_id' => $this->tSectionUser->user_id,
            'seat_id' => $this->tSectionSeat->seat_id,
            'is_active' => true,
        ]);

        $this->regularUser = User::factory()->create();
        $this->regularUser->assignRole($regularRole);
        SeatUser::factory()->create([
            'user_id' => $this->regularUser->user_id,
            'seat_id' => $this->regularSeat->seat_id,
            'is_active' => true,
        ]);
    }

    public function test_t_section_user_with_shared_crud_can_create_and_view_own_petition(): void
    {
        $petition = Petition::factory()->create([
            'user_id' => $this->tSectionUser->user_id,
            'seat_id' => $this->tSectionSeat->seat_id,
            'status' => 'Received',
        ]);

        $this->assertTrue($petition->isTSection());
        $this->assertTrue($this->tSectionUser->can('view', $petition));
        $this->assertTrue($this->tSectionUser->can('create', Petition::class));
        $this->assertTrue($this->tSectionUser->can('update', $petition));
    }

    public function test_regular_user_with_view_petitions_cannot_view_t_section_petition(): void
    {
        $petition = Petition::factory()->create([
            'user_id' => $this->tSectionUser->user_id,
            'seat_id' => $this->tSectionSeat->seat_id,
            'status' => 'Received',
        ]);

        $this->assertTrue($petition->isTSection());
        $this->assertFalse($this->regularUser->canAccessTSection());

        // Regular user cannot view confidential T-Section petition
        $this->assertFalse($this->regularUser->can('view', $petition));

        // Query scope excludes T-Section petitions for regular user
        $accessible = Petition::excludeTSectionIfNotAuthorized($this->regularUser)->get();
        $this->assertFalse($accessible->contains('petition_id', $petition->petition_id));
    }

    public function test_regular_user_with_update_petitions_cannot_update_t_section_petition(): void
    {
        $petition = Petition::factory()->create([
            'user_id' => $this->tSectionUser->user_id,
            'seat_id' => $this->tSectionSeat->seat_id,
            'status' => 'Received',
        ]);

        $this->assertFalse($this->regularUser->can('update', $petition));
    }

    public function test_t_section_petition_cannot_be_deleted_by_regular_or_t_section_user_without_admin(): void
    {
        $petition = Petition::factory()->create([
            'user_id' => $this->tSectionUser->user_id,
            'seat_id' => $this->tSectionSeat->seat_id,
            'status' => 'Received',
        ]);

        $this->assertFalse($this->regularUser->can('delete', $petition));
        $this->assertFalse($this->tSectionUser->can('delete', $petition));
    }
}
