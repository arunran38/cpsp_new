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
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->seat = Seat::factory()->create();
        
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
        $petition = Petition::factory()->create(['status' => 'Received']);

        $response = $this->actingAs($this->admin)->post(route('forwardings.store'), [
            'petition_id' => $petition->petition_id,
            'action' => 'Close',
            'director_remarks' => 'Closing based on initial review.',
            'forwarded_date' => '2024-03-01',
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('petitions', ['petition_id' => $petition->petition_id, 'status' => 'Closed']);
        $this->assertDatabaseHas('decisions', [
            'petition_id' => $petition->petition_id,
            'decision_remarks' => 'Closed'
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
}
