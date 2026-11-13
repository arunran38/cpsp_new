<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\AuditLog;
use App\Models\DepartmentList;
use App\Models\DesignationList;
use App\Models\District;
use App\Models\Petition;
use App\Models\Seat;
use App\Models\SeatUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuditLogValueDiffTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $user;
    protected $seat;
    protected $district1;
    protected $district2;
    protected $dept1;
    protected $dept2;
    protected $desig1;
    protected $desig2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $this->seed(\Database\Seeders\DeveloperRoleSeeder::class);

        $role = Role::firstOrCreate(['name' => 'super admin', 'guard_name' => 'web']);
        $this->admin = User::factory()->create();
        $this->user = User::factory()->create();
        $this->admin->assignRole($role);
        $this->user->assignRole($role);

        $this->seat = Seat::factory()->create();
        $this->seat->assignRole($role);

        SeatUser::factory()->create([
            'user_id' => $this->user->user_id,
            'seat_id' => $this->seat->seat_id,
            'is_active' => true,
        ]);

        $this->withSession(['current_seat_id' => $this->seat->seat_id]);

        $this->district1 = District::firstOrCreate(['district_name' => 'Thiruvananthapuram', 'district_code' => 'TVM']);
        $this->district2 = District::firstOrCreate(['district_name' => 'Ernakulam', 'district_code' => 'EKM']);

        $this->dept1 = DepartmentList::firstOrCreate(['department_name' => 'Revenue Department']);
        $this->dept2 = DepartmentList::firstOrCreate(['department_name' => 'Police Department']);

        $this->desig1 = DesignationList::firstOrCreate(['designation_name' => 'Senior Clerk']);
        $this->desig2 = DesignationList::firstOrCreate(['designation_name' => 'Sub Inspector']);
    }

    public function test_editing_petition_records_structured_diffs_in_audit_log(): void
    {
        // 1. Create petition
        $petition = Petition::factory()->create([
            'user_id'                   => $this->user->user_id,
            'seat_id'                   => $this->seat->seat_id,
            'receipt_no'                => 'REC-DIFF-TEST-001',
            'nature_of_petition'        => 'Bribery Allegation',
            'mode_of_petition_received' => 'Direct',
            'concerned_district_id'     => $this->district1->district_id,
            'description'               => 'Original description content here',
            'proposed_action'           => 'Initial enquiry',
        ]);

        // Add complainant
        Address::create([
            'petition_id'  => $petition->petition_id,
            'person_name'  => 'Old Complainant',
            'person_type'  => 'Complainant',
            'phone'        => '9847111111',
            'full_address' => 'Old Address Line 1',
        ]);

        // Add suspect
        Address::create([
            'petition_id'    => $petition->petition_id,
            'person_name'    => 'Old Suspect',
            'person_type'    => 'Accused',
            'department_id'  => $this->dept1->id,
            'designation_id' => $this->desig1->id,
            'pen_number'     => '123456',
            'full_address'   => 'Old Suspect Office Address',
        ]);

        // 2. Perform Update via PUT route
        $updatePayload = [
            'nature_of_petition'        => 'Disproportionate Assets',
            'mode_of_petition_received' => 'Email',
            'concerned_district_id'     => $this->district2->district_id,
            'description'               => 'Updated comprehensive description',
            'proposed_action'           => 'Surprise Vigilance Check',
            'complainants'              => [
                [
                    'name'      => 'New Complainant',
                    'phone'     => '9847999999',
                    'addresses' => [
                        [
                            'address'     => 'New Address Line 2',
                            'district_id' => $this->district2->district_id,
                            'pincode'     => '682001',
                        ]
                    ]
                ]
            ],
            'accused'                   => [
                [
                    'name'           => 'New Suspect',
                    'entity_type'    => 'Person',
                    'department_id'  => $this->dept2->id,
                    'designation_id' => $this->desig2->id,
                    'pen_number'     => '654321',
                ]
            ],
        ];

        $response = $this->actingAs($this->user)
            ->put(route('petitions.update', $petition->petition_id), $updatePayload);

        $response->assertRedirect(route('petitions.index', ['tab' => 'all']));

        // 3. Verify Database Updates
        $this->assertDatabaseHas('petitions', [
            'petition_id'        => $petition->petition_id,
            'nature_of_petition' => 'Disproportionate Assets',
            'mode_of_petition_received' => 'Email',
            'concerned_district_id' => $this->district2->district_id,
            'description'        => 'Updated comprehensive description',
        ]);

        // 4. Verify Audit Log was recorded with accurate diffs
        $auditLog = AuditLog::where('action', 'UPDATE')
            ->where('module', 'Petitions')
            ->latest('audit_id')
            ->first();

        $this->assertNotNull($auditLog, 'Audit log was not recorded for petition update.');
        $this->assertStringContainsString('REC-DIFF-TEST-001', $auditLog->description);
        $this->assertStringContainsString('Modified:', $auditLog->description);

        $properties = $auditLog->properties;
        $this->assertIsArray($properties);
        $this->assertArrayHasKey('diffs', $properties);

        $diffs = $properties['diffs'];
        $this->assertNotEmpty($diffs);

        // Find diffs by field_key
        $diffKeys = array_column($diffs, 'field_key');
        $this->assertContains('nature_of_petition', $diffKeys);
        $this->assertContains('mode_of_petition_received', $diffKeys);
        $this->assertContains('concerned_district_id', $diffKeys);
        $this->assertContains('description', $diffKeys);
        $this->assertContains('proposed_action', $diffKeys);
        $this->assertContains('complainants', $diffKeys);
        $this->assertContains('accused', $diffKeys);

        // Verify values
        $natureDiff = collect($diffs)->firstWhere('field_key', 'nature_of_petition');
        $this->assertEquals('Bribery Allegation', $natureDiff['old']);
        $this->assertEquals('Disproportionate Assets', $natureDiff['new']);

        $districtDiff = collect($diffs)->firstWhere('field_key', 'concerned_district_id');
        $this->assertStringContainsString('Thiruvananthapuram', $districtDiff['old']);
        $this->assertStringContainsString('Ernakulam', $districtDiff['new']);

        $accusedDiff = collect($diffs)->firstWhere('field_key', 'accused');
        $this->assertStringContainsString('Old Suspect', $accusedDiff['old']);
        $this->assertStringContainsString('Revenue Department', $accusedDiff['old']);
        $this->assertStringContainsString('New Suspect', $accusedDiff['new']);
        $this->assertStringContainsString('Police Department', $accusedDiff['new']);
    }

    public function test_audit_logs_index_page_loads_with_diff_logs(): void
    {
        $petition = Petition::factory()->create([
            'user_id' => $this->user->user_id,
            'seat_id' => $this->seat->seat_id,
        ]);

        AuditLog::create([
            'user_id'     => $this->user->user_id,
            'user_name'   => $this->user->name,
            'action'      => 'UPDATE',
            'module'      => 'Petitions',
            'description' => 'Updated Petition - Modified: Nature of Petition',
            'properties'  => [
                'diffs' => [
                    [
                        'field'     => 'Nature of Petition',
                        'field_ml'  => 'പരാതിയുടെ സ്വഭാവം',
                        'field_key' => 'nature_of_petition',
                        'old'       => 'Old Nature',
                        'new'       => 'New Nature',
                    ]
                ]
            ],
            'created_at'  => now(),
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.audit_logs.index'));
        $response->assertOk();
        $response->assertSee('Audit Log Details');
        $response->assertSee('Value Differences');
    }
}
