<?php

namespace Tests\Feature;

use App\Models\Petition;
use App\Models\PetitionForwarding;
use App\Models\Unit;
use App\Models\User;
use App\Models\Seat;
use App\Models\SeatUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PetitionForwardingControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $seat;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seat = Seat::factory()->create();
        $this->admin = $this->createSuperAdminUser($this->seat);
        
        SeatUser::factory()->create([
            'user_id' => $this->admin->user_id,
            'seat_id' => $this->seat->seat_id,
            'is_active' => true
        ]);
    }

    public function test_admin_can_forward_petition_to_unit(): void
    {
        $petition = Petition::factory()->create(['status' => 'Received']);
        $unit = Unit::factory()->create();

        $response = $this->actingAs($this->admin)->post(route('forwardings.store'), [
            'petition_id' => $petition->petition_id,
            'action' => 'Forward_To_Unit',
            'director_remarks' => 'Please verify this.',
            'to_unit_id' => $unit->unit_id,
            'file_no' => 'FILE-TEST-001',
            'forwarded_date' => '2024-03-01',
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('petitions', ['petition_id' => $petition->petition_id, 'status' => 'Forwarded']);
        $this->assertDatabaseHas('petition_forwardings', [
            'petition_id' => $petition->petition_id,
            'to_unit_id' => $unit->unit_id,
            'director_remarks' => 'Please verify this.'
        ]);
    }

    public function test_admin_can_submit_verification_report(): void
    {
        Storage::fake('public');
        $forwarding = PetitionForwarding::factory()->create();
        
        $vrData = [
            'vr_ref_no' => 'VR-2024-X',
            'vr_date' => '2024-03-01',
            'vr_received_at_cpsp_date' => '2024-03-05',
            'vr_remarks' => 'Everything looks fine.',
            'vr_file' => UploadedFile::fake()->create('vr_report.pdf', 500)
        ];

        $response = $this->actingAs($this->admin)->put(route('forwardings.updateVr', $forwarding->petition_forwarding_id), $vrData);

        $response->assertStatus(302);
        $this->assertDatabaseHas('petition_forwardings', [
            'petition_forwarding_id' => $forwarding->petition_forwarding_id,
            'vr_ref_no' => 'VR-2024-X'
        ]);
        $this->assertDatabaseHas('petitions', [
            'petition_id' => $forwarding->petition_id,
            'status' => 'VR_Received'
        ]);
        
        $this->assertCount(1, $forwarding->petition->uploads->where('category', \App\Models\Upload::CATEGORY_VERIFICATION_REPORT));
    }

    public function test_admin_can_receive_vr_at_cpsp(): void
    {
        $forwarding = PetitionForwarding::factory()->create(['vr_received_at_cpsp_date' => null]);
        
        $response = $this->actingAs($this->admin)->patch(route('forwardings.receiveVr', $forwarding->petition_forwarding_id), [
            'vr_received_at_cpsp_date' => '2024-03-10'
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('petition_forwardings', [
            'petition_forwarding_id' => $forwarding->petition_forwarding_id,
            'vr_received_at_cpsp_date' => '2024-03-10'
        ]);
    }

    public function test_admin_can_close_petition_from_forwarding_form(): void
    {
        $petition = Petition::factory()->create([
            'status' => 'Received',
            'date_of_petition_received' => '2024-01-01',
        ]);

        $response = $this->actingAs($this->admin)->post(route('forwardings.store'), [
            'petition_id' => $petition->petition_id,
            'action' => 'Close',
            'director_remarks' => 'Closing based on initial review.',
            'directorate_order_number' => 'ORD-CLOSE-01',
            'file_no' => 'FILE-TEST-002',
            'forwarded_date' => '2024-03-01',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('petitions', ['petition_id' => $petition->petition_id, 'status' => 'Closed']);
        $this->assertDatabaseHas('decisions', [
            'petition_id' => $petition->petition_id,
            'final_decision' => 'Closed'
        ]);
    }

    public function test_admin_can_send_to_govt_from_forwarding_form(): void
    {
        $petition = Petition::factory()->create([
            'status' => 'Received',
            'date_of_petition_received' => '2024-01-01',
        ]);

        $response = $this->actingAs($this->admin)->post(route('forwardings.store'), [
            'petition_id' => $petition->petition_id,
            'action' => 'Sent_to_Govt',
            'govt_recommendation_type' => '17 A Sanction',
            'director_remarks' => 'Sending to government for sanction.',
            'directorate_order_number' => 'ORD-1234',
            'file_no' => 'FILE-TEST-003',
            'forwarded_date' => '2024-03-01',
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('petitions', ['petition_id' => $petition->petition_id, 'status' => 'Sent_to_Govt']);
        $this->assertDatabaseHas('decisions', [
            'petition_id' => $petition->petition_id,
            'final_decision' => 'Sent to Govt',
            'govt_recommendation_type' => '17 A Sanction',
        ]);
    }

    public function test_send_to_govt_requires_recommendation_type(): void
    {
        $petition = Petition::factory()->create(['status' => 'Received']);

        $response = $this->actingAs($this->admin)->post(route('forwardings.store'), [
            'petition_id' => $petition->petition_id,
            'action' => 'Sent_to_Govt',
            'govt_recommendation_type' => '',
            'director_remarks' => 'Sending to government.',
            'directorate_order_number' => 'ORD-1234',
            'file_no' => 'FILE-TEST-005',
            'forwarded_date' => '2024-03-01',
        ]);

        $response->assertSessionHasErrors(['govt_recommendation_type']);
    }

    public function test_admin_can_send_to_spc_from_forwarding_form(): void
    {
        $petition = Petition::factory()->create(['status' => 'Received']);

        $response = $this->actingAs($this->admin)->post(route('forwardings.store'), [
            'petition_id' => $petition->petition_id,
            'action' => 'Sent_to_SPC',
            'director_remarks' => 'Sending to SPC directly.',
            'directorate_order_number' => 'ORD-SPC-001',
            'file_no' => 'FILE-TEST-004',
            'forwarded_date' => '2024-03-01',
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('petitions', ['petition_id' => $petition->petition_id, 'status' => 'Sent_to_SPC']);
        $this->assertDatabaseHas('decisions', [
            'petition_id' => $petition->petition_id,
            'final_decision' => 'Sent to SPC',
        ]);
    }

    public function test_admin_can_pullback_forwarding(): void
    {
        $forwarding = PetitionForwarding::factory()->create();
        $petition = $forwarding->petition;
        $petition->update(['status' => 'Forwarded']);

        $response = $this->actingAs($this->admin)->delete(route('forwardings.pullback', $forwarding->petition_forwarding_id));

        $response->assertStatus(302);
        $this->assertSoftDeleted('petition_forwardings', ['petition_forwarding_id' => $forwarding->petition_forwarding_id]);
        $this->assertDatabaseHas('petitions', ['petition_id' => $petition->petition_id, 'status' => 'Received']);
    }

    public function test_admin_cannot_pullback_forwarding_if_vr_exists(): void
    {
        $forwarding = PetitionForwarding::factory()->create(['vr_ref_no' => 'SOME-VR']);
        
        $response = $this->actingAs($this->admin)->delete(route('forwardings.pullback', $forwarding->petition_forwarding_id));

        $response->assertStatus(302);
        $response->assertSessionHas('error', 'Cannot pull back. VR Report already submitted.');
        $this->assertDatabaseHas('petition_forwardings', ['petition_forwarding_id' => $forwarding->petition_forwarding_id]);
    }

    public function test_admin_can_pullback_vr(): void
    {
        $forwarding = PetitionForwarding::factory()->create([
            'vr_ref_no' => 'VR-123',
            'vr_date' => '2024-01-01'
        ]);
        $petition = $forwarding->petition;
        $petition->update(['status' => 'VR_Received']);

        $response = $this->actingAs($this->admin)->patch(route('forwardings.pullbackVr', $forwarding->petition_forwarding_id));

        $response->assertStatus(302);
        $this->assertDatabaseHas('petition_forwardings', [
            'petition_forwarding_id' => $forwarding->petition_forwarding_id,
            'vr_ref_no' => null
        ]);
        $this->assertDatabaseHas('petitions', ['petition_id' => $petition->petition_id, 'status' => 'Forwarded']);
    }

    public function test_can_forward_with_past_action_and_file_dates(): void
    {
        $petition = Petition::factory()->create([
            'status' => 'Received',
            'date_of_petition_received' => now()->format('Y-m-d')
        ]);
        $unit = Unit::factory()->create();

        // Action date can be in the past (even earlier than date_of_petition_received or file_created_date)
        $pastActionDate = now()->subDays(5)->format('Y-m-d');
        $pastFileDate = now()->subDays(2)->format('Y-m-d');

        $response = $this->actingAs($this->admin)->post(route('forwardings.store'), [
            'petition_id' => $petition->petition_id,
            'action' => 'Forward_To_Unit',
            'director_remarks' => 'Past date verification test.',
            'to_unit_id' => $unit->unit_id,
            'file_no' => 'FILE-PAST-001',
            'file_created_date' => $pastFileDate,
            'forwarded_date' => $pastActionDate,
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('petitions', [
            'petition_id' => $petition->petition_id,
            'file_created_date' => $pastFileDate . ' 00:00:00'
        ]);
        $this->assertDatabaseHas('petition_forwardings', [
            'petition_id' => $petition->petition_id,
            'forwarded_date' => $pastActionDate
        ]);
    }

    public function test_cannot_forward_with_future_action_or_file_date(): void
    {
        $petition = Petition::factory()->create(['status' => 'Received']);
        $unit = Unit::factory()->create();

        $futureDate = now()->addDays(2)->format('Y-m-d');

        $response = $this->actingAs($this->admin)->post(route('forwardings.store'), [
            'petition_id' => $petition->petition_id,
            'action' => 'Forward_To_Unit',
            'director_remarks' => 'Future date test.',
            'to_unit_id' => $unit->unit_id,
            'file_no' => 'FILE-FUT-001',
            'file_created_date' => $futureDate,
            'forwarded_date' => $futureDate,
        ]);

        $response->assertSessionHasErrors(['forwarded_date', 'file_created_date']);
    }

    public function test_vr_date_and_received_at_cpsp_date_allow_past_dates(): void
    {
        $petition = Petition::factory()->create([
            'status' => 'Forwarded',
            'date_of_petition_received' => now()->format('Y-m-d')
        ]);
        $forwarding = PetitionForwarding::factory()->create([
            'petition_id' => $petition->petition_id,
            'forwarded_date' => now()->format('Y-m-d')
        ]);

        $pastVrDate = now()->subDays(10)->format('Y-m-d');
        $pastCpspDate = now()->subDays(5)->format('Y-m-d');

        $response = $this->actingAs($this->admin)->put(route('forwardings.updateVr', $forwarding->petition_forwarding_id), [
            'vr_ref_no' => 'VR-PAST-01',
            'vr_date' => $pastVrDate,
            'vr_received_at_cpsp_date' => $pastCpspDate,
            'vr_remarks' => 'VC'
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('petition_forwardings', [
            'petition_forwarding_id' => $forwarding->petition_forwarding_id,
            'vr_date' => $pastVrDate,
            'vr_received_at_cpsp_date' => $pastCpspDate
        ]);

        // Also test receiveVr with a past date
        $anotherPastDate = now()->subDays(3)->format('Y-m-d');
        $receiveResponse = $this->actingAs($this->admin)->patch(route('forwardings.receiveVr', $forwarding->petition_forwarding_id), [
            'vr_received_at_cpsp_date' => $anotherPastDate
        ]);

        $receiveResponse->assertStatus(302);
        $receiveResponse->assertSessionHasNoErrors();
        $this->assertDatabaseHas('petition_forwardings', [
            'petition_forwarding_id' => $forwarding->petition_forwarding_id,
            'vr_received_at_cpsp_date' => $anotherPastDate
        ]);
    }

    public function test_vr_date_and_received_at_cpsp_date_prevent_future_dates(): void
    {
        $forwarding = PetitionForwarding::factory()->create();
        $futureDate = now()->addDays(2)->format('Y-m-d');

        $response = $this->actingAs($this->admin)->put(route('forwardings.updateVr', $forwarding->petition_forwarding_id), [
            'vr_ref_no' => 'VR-FUT-01',
            'vr_date' => $futureDate,
            'vr_received_at_cpsp_date' => $futureDate,
            'vr_remarks' => 'VC'
        ]);

        $response->assertSessionHasErrors(['vr_date', 'vr_received_at_cpsp_date']);

        $receiveResponse = $this->actingAs($this->admin)->patch(route('forwardings.receiveVr', $forwarding->petition_forwarding_id), [
            'vr_received_at_cpsp_date' => $futureDate
        ]);

        $receiveResponse->assertSessionHasErrors(['vr_received_at_cpsp_date']);
    }
}
