<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\DepartmentList;
use App\Models\District;
use App\Models\Petition;
use App\Models\Seat;
use App\Models\SeatUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PetitionExcelExportSuspectDetailsTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Seat $seat;
    protected DepartmentList $deptPolice;
    protected DepartmentList $deptRevenue;
    protected District $districtTvm;

    protected function setUp(): void
    {
        parent::setUp();

        $cpspRole = Role::firstOrCreate(['name' => 'CPSP', 'guard_name' => 'web']);
        $perms = [
            'view petitions',
            'view master reports',
            'export petitions',
            'export statistics',
            'access cpsp section',
        ];
        foreach ($perms as $perm) {
            $p = Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
            $cpspRole->givePermissionTo($p);
        }

        $this->user = User::factory()->create();
        $this->user->assignRole($cpspRole);

        $this->seat = Seat::factory()->create(['seat_name' => 'CPSP Desk 1', 'is_active' => true]);
        $this->seat->assignRole($cpspRole);

        SeatUser::factory()->create([
            'user_id' => $this->user->user_id,
            'seat_id' => $this->seat->seat_id,
            'is_active' => true,
            'is_additional' => false,
        ]);

        $this->deptPolice = DepartmentList::create(['department_name' => 'Police Department']);
        $this->deptRevenue = DepartmentList::create(['department_name' => 'Revenue Department']);
        $this->districtTvm = District::create(['district_name' => 'Thiruvananthapuram']);
    }

    /**
     * Requirement 1 & 5: When address is not entered / null / 'Not provided' and no department,
     * "Not Provided" is NOT displayed, and the address field is left blank.
     */
    public function test_suspect_without_address_and_department_does_not_display_not_provided(): void
    {
        $petition = Petition::factory()->create([
            'user_id' => $this->user->user_id,
            'seat_id' => $this->seat->seat_id,
            'receipt_no' => 'REC-SUSP-NONE-001',
        ]);

        Address::create([
            'petition_id' => $petition->petition_id,
            'person_name' => 'Complainant One',
            'person_type' => 'Complainant',
            'full_address' => 'Complainant House',
        ]);

        Address::create([
            'petition_id' => $petition->petition_id,
            'person_name' => 'Suspect No Details',
            'person_type' => 'Accused',
            'full_address' => 'Not provided',
            'department_id' => null,
            'district_id' => null,
        ]);

        // Default export: 'Suspect Name & Address'
        $response = $this->actingAs($this->user)->get(route('petitions.export'));
        $response->assertStatus(200);
        $content = $response->getContent();

        $this->assertStringContainsString('Suspect No Details', $content);
        $this->assertStringNotContainsString('Not provided', $content);
        $this->assertStringNotContainsString('Not Provided', $content);

        // Custom export: 'Suspect Address'
        $responseCustom = $this->actingAs($this->user)->get(route('petitions.export', [
            'custom_columns' => ['Receipt No', 'Suspect Name', 'Suspect Address'],
        ]));
        $responseCustom->assertStatus(200);
        $customContent = $responseCustom->getContent();

        $this->assertStringContainsString('Suspect No Details', $customContent);
        $this->assertStringNotContainsString('Not provided', $customContent);
        $this->assertStringNotContainsString('Not Provided', $customContent);
    }

    /**
     * Requirement 3, 4, 5: When both address and department are available,
     * display both with clear separation in both standard and custom export.
     */
    public function test_suspect_with_both_address_and_department_displays_both(): void
    {
        $petition = Petition::factory()->create([
            'user_id' => $this->user->user_id,
            'seat_id' => $this->seat->seat_id,
            'receipt_no' => 'REC-SUSP-BOTH-002',
        ]);

        Address::create([
            'petition_id' => $petition->petition_id,
            'person_name' => 'Inspector Gadget',
            'person_type' => 'Accused',
            'full_address' => 'Station Road, Kowdiar',
            'district_id' => $this->districtTvm->district_id,
            'pincode' => '695003',
            'department_id' => $this->deptPolice->id,
        ]);

        // Standard export
        $response = $this->actingAs($this->user)->get(route('petitions.export'));
        $response->assertStatus(200);
        $content = $response->getContent();

        $this->assertStringContainsString('Inspector Gadget', $content);
        $this->assertStringContainsString('Station Road, Kowdiar, Thiruvananthapuram, 695003', $content);
        $this->assertStringContainsString('Department: Police Department', $content);

        // Custom export for Suspect Address
        $responseCustom = $this->actingAs($this->user)->get(route('petitions.export', [
            'custom_columns' => ['Receipt No', 'Suspect Address'],
        ]));
        $responseCustom->assertStatus(200);
        $customContent = $responseCustom->getContent();

        $this->assertStringContainsString('Station Road, Kowdiar, Thiruvananthapuram, 695003', $customContent);
        $this->assertStringContainsString('Department: Police Department', $customContent);
    }

    /**
     * Requirement 5: When only address is available (no department), display address only.
     */
    public function test_suspect_with_only_address_displays_address_only(): void
    {
        $petition = Petition::factory()->create([
            'user_id' => $this->user->user_id,
            'seat_id' => $this->seat->seat_id,
            'receipt_no' => 'REC-SUSP-ADDR-003',
        ]);

        Address::create([
            'petition_id' => $petition->petition_id,
            'person_name' => 'Citizen John',
            'person_type' => 'Accused',
            'full_address' => 'Beach Road, Kovalam',
            'district_id' => $this->districtTvm->district_id,
            'pincode' => '695527',
            'department_id' => null,
        ]);

        $response = $this->actingAs($this->user)->get(route('petitions.export'));
        $response->assertStatus(200);
        $content = $response->getContent();

        $this->assertStringContainsString('Citizen John', $content);
        $this->assertStringContainsString('Beach Road, Kovalam, Thiruvananthapuram, 695527', $content);
        $this->assertStringNotContainsString('Department:', $content);
    }

    /**
     * Requirement 5: When only department is available (no address or address is 'Not provided'),
     * display department only, leaving address blank (no "Not Provided").
     */
    public function test_suspect_with_only_department_displays_department_only(): void
    {
        $petition = Petition::factory()->create([
            'user_id' => $this->user->user_id,
            'seat_id' => $this->seat->seat_id,
            'receipt_no' => 'REC-SUSP-DEPT-004',
        ]);

        Address::create([
            'petition_id' => $petition->petition_id,
            'person_name' => 'Revenue Officer Bob',
            'person_type' => 'Accused',
            'full_address' => 'Not provided',
            'district_id' => null,
            'pincode' => null,
            'department_id' => $this->deptRevenue->id,
        ]);

        // Standard export
        $response = $this->actingAs($this->user)->get(route('petitions.export'));
        $response->assertStatus(200);
        $content = $response->getContent();

        $this->assertStringContainsString('Revenue Officer Bob', $content);
        $this->assertStringContainsString('Department: Revenue Department', $content);
        $this->assertStringNotContainsString('Not provided', $content);
        $this->assertStringNotContainsString('Not Provided', $content);

        // Custom export for Suspect Address
        $responseCustom = $this->actingAs($this->user)->get(route('petitions.export', [
            'custom_columns' => ['Receipt No', 'Suspect Address'],
        ]));
        $responseCustom->assertStatus(200);
        $customContent = $responseCustom->getContent();

        $this->assertStringContainsString('Department: Revenue Department', $customContent);
        $this->assertStringNotContainsString('Not provided', $customContent);
        $this->assertStringNotContainsString('Not Provided', $customContent);
    }

    /**
     * Requirement 6: Multiple suspects in the same petition must maintain their respective
     * address and department details without mixing them up.
     */
    public function test_multiple_suspects_retain_respective_address_and_department(): void
    {
        $petition = Petition::factory()->create([
            'user_id' => $this->user->user_id,
            'seat_id' => $this->seat->seat_id,
            'receipt_no' => 'REC-SUSP-MULTI-005',
        ]);

        // Suspect 1: Has both address and police department
        Address::create([
            'petition_id' => $petition->petition_id,
            'person_name' => 'Officer Alpha',
            'person_type' => 'Accused',
            'full_address' => 'Police Barracks, TVM',
            'district_id' => $this->districtTvm->district_id,
            'department_id' => $this->deptPolice->id,
        ]);

        // Suspect 2: Has only revenue department, no address ('Not provided')
        Address::create([
            'petition_id' => $petition->petition_id,
            'person_name' => 'Clerk Beta',
            'person_type' => 'Accused',
            'full_address' => 'Not provided',
            'district_id' => null,
            'department_id' => $this->deptRevenue->id,
        ]);

        // Suspect 3: Has neither address nor department
        Address::create([
            'petition_id' => $petition->petition_id,
            'person_name' => 'Individual Gamma',
            'person_type' => 'Accused',
            'full_address' => 'Not provided',
            'district_id' => null,
            'department_id' => null,
        ]);

        // Standard export
        $response = $this->actingAs($this->user)->get(route('petitions.export'));
        $response->assertStatus(200);
        $content = $response->getContent();

        // Alpha details
        $this->assertStringContainsString('Officer Alpha', $content);
        $this->assertStringContainsString('Police Barracks, TVM, Thiruvananthapuram', $content);
        $this->assertStringContainsString('Department: Police Department', $content);

        // Beta details
        $this->assertStringContainsString('Clerk Beta', $content);
        $this->assertStringContainsString('Department: Revenue Department', $content);

        // Gamma details
        $this->assertStringContainsString('Individual Gamma', $content);

        // No placeholder text
        $this->assertStringNotContainsString('Not provided', $content);
        $this->assertStringNotContainsString('Not Provided', $content);

        // Custom export
        $responseCustom = $this->actingAs($this->user)->get(route('petitions.export', [
            'custom_columns' => ['Receipt No', 'Suspect Address'],
        ]));
        $responseCustom->assertStatus(200);
        $customContent = $responseCustom->getContent();

        $this->assertStringContainsString('Police Barracks, TVM, Thiruvananthapuram', $customContent);
        $this->assertStringContainsString('Department: Police Department', $customContent);
        $this->assertStringContainsString('Department: Revenue Department', $customContent);
        $this->assertStringNotContainsString('Not provided', $customContent);
    }

    /**
     * Test a single suspect with multiple addresses and department.
     */
    public function test_single_suspect_with_multiple_addresses_and_department(): void
    {
        $petition = Petition::factory()->create([
            'user_id' => $this->user->user_id,
            'seat_id' => $this->seat->seat_id,
            'receipt_no' => 'REC-SUSP-MULTI-ADDR-006',
        ]);

        // Address 1 (Primary / Office)
        Address::create([
            'petition_id' => $petition->petition_id,
            'person_name' => 'Dr. Who',
            'person_type' => 'Accused',
            'full_address' => 'TARDIS Office, London',
            'district_id' => $this->districtTvm->district_id,
            'department_id' => $this->deptPolice->id,
            'is_primary' => true,
        ]);

        // Address 2 (Secondary / Residence)
        Address::create([
            'petition_id' => $petition->petition_id,
            'person_name' => 'Dr. Who',
            'person_type' => 'Accused',
            'full_address' => 'Gallifrey Lane, Kowdiar',
            'district_id' => $this->districtTvm->district_id,
            'department_id' => $this->deptPolice->id,
            'is_primary' => false,
        ]);

        $response = $this->actingAs($this->user)->get(route('petitions.export'));
        $response->assertStatus(200);
        $content = $response->getContent();

        $this->assertStringContainsString('Dr. Who', $content);
        $this->assertStringContainsString('TARDIS Office, London, Thiruvananthapuram', $content);
        $this->assertStringContainsString('Gallifrey Lane, Kowdiar, Thiruvananthapuram', $content);
        $this->assertStringContainsString('Department: Police Department', $content);
    }

    /**
     * Ensure Complainant address details remain unaffected.
     */
    public function test_complainant_address_details_remain_unaffected(): void
    {
        $petition = Petition::factory()->create([
            'user_id' => $this->user->user_id,
            'seat_id' => $this->seat->seat_id,
            'receipt_no' => 'REC-COMP-007',
        ]);

        Address::create([
            'petition_id' => $petition->petition_id,
            'person_name' => 'Alice Petitioner',
            'person_type' => 'Complainant',
            'full_address' => 'Sunflower Villa, TVM',
            'district_id' => $this->districtTvm->district_id,
            'pincode' => '695001',
            'phone' => '9876543210',
            'email' => 'alice@example.com',
        ]);

        $response = $this->actingAs($this->user)->get(route('petitions.export'));
        $response->assertStatus(200);
        $content = $response->getContent();

        $this->assertStringContainsString('Alice Petitioner', $content);
        $this->assertStringContainsString('Sunflower Villa, TVM, Thiruvananthapuram, 695001', $content);
        $this->assertStringContainsString('Ph: 9876543210', $content);
        $this->assertStringContainsString('Email: alice@example.com', $content);
    }
}
