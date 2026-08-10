<?php

namespace Tests\Feature;

use App\Models\Petition;
use App\Models\User;
use App\Models\Seat;
use App\Models\SeatUser;
use App\Models\Decision;
use App\Models\Upload;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DecisionControllerTest extends TestCase
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

    public function test_admin_can_record_final_decision_with_file(): void
    {
        Storage::fake('public');
        $petition = Petition::factory()->create(['status' => 'VR_Received']);

        $decisionData = [
            'petition_id' => $petition->petition_id,
            'decision_remarks' => 'SC',
            'final_remarks' => 'Suggesting SC action.',
            'final_order_file' => UploadedFile::fake()->create('final_order.pdf', 1000),
            'decision_date' => '2024-03-01',
        ];

        $response = $this->actingAs($this->admin)->post(route('decisions.store'), $decisionData);

        $response->assertStatus(302);
        $this->assertDatabaseHas('decisions', [
            'petition_id' => $petition->petition_id,
            'decision_remarks' => 'SC',
            'final_remarks' => 'Suggesting SC action.'
        ]);
        $this->assertDatabaseHas('petitions', ['petition_id' => $petition->petition_id, 'status' => 'Closed']);
        
        $upload = Upload::where('petition_id', $petition->petition_id)->where('category', Upload::CATEGORY_FINAL_ORDER)->first();
        $this->assertNotNull($upload);
        Storage::disk('public')->assertExists($upload->file_path);
    }

    public function test_admin_can_record_sent_to_govt_decision(): void
    {
        $petition = Petition::factory()->create(['status' => 'VR_Received']);

        $response = $this->actingAs($this->admin)->post(route('decisions.store'), [
            'petition_id' => $petition->petition_id,
            'decision_remarks' => 'Sent to Govt',
            'final_remarks' => 'Sent to higher authorities.',
            'decision_date' => '2024-03-01',
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('petitions', ['petition_id' => $petition->petition_id, 'status' => 'Sent_to_Govt']);
    }

    public function test_decision_validation_errors(): void
    {
        $response = $this->actingAs($this->admin)->post(route('decisions.store'), [
            'petition_id' => 99999, // Invalid ID
            'decision_remarks' => 'INVALID_REMARK',
        ]);

        $response->assertSessionHasErrors(['petition_id', 'decision_remarks']);
    }

    public function test_admin_can_pullback_decision(): void
    {
        $petition = Petition::factory()->create(['status' => 'Closed']);
        $decision = Decision::factory()->create(['petition_id' => $petition->petition_id]);
        
        // Create a forwarding so it reverts to VR_Received status
        \App\Models\PetitionForwarding::factory()->create([
            'petition_id' => $petition->petition_id,
            'vr_ref_no' => 'VR-999'
        ]);

        $response = $this->actingAs($this->admin)->delete(route('decisions.pullback', $decision->decision_id));

        $response->assertStatus(302);
        $this->assertSoftDeleted('decisions', ['decision_id' => $decision->decision_id]);
        $this->assertDatabaseHas('petitions', ['petition_id' => $petition->petition_id, 'status' => 'VR_Received']);
    }
}
