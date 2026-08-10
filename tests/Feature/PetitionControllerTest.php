<?php

namespace Tests\Feature;

use App\Models\Petition;
use App\Models\User;
use App\Models\Seat;
use App\Models\SeatUser;
use App\Models\Address;
use App\Models\Upload;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PetitionControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $user;
    protected $seat;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->user = User::factory()->create(['role' => 'user']);
        $this->seat = Seat::factory()->create();
        
        // Assign user to seat
        SeatUser::factory()->create([
            'user_id' => $this->user->user_id,
            'seat_id' => $this->seat->seat_id,
            'is_active' => true
        ]);
        
        // Set current seat in session for controller logic
        $this->withSession(['current_seat_id' => $this->seat->seat_id]);
    }

    public function test_user_can_view_create_petition_page(): void
    {
        $response = $this->actingAs($this->user)->get(route('petitions.create'));

        $response->assertStatus(200);
        $response->assertViewIs('user.petition_add');
    }

    public function test_user_can_store_petition_with_complainants_and_files(): void
    {
        Storage::fake('public');

        $data = [
            'petition_no' => 'PET-2024-0001',
            'date_of_petition_received' => '2024-01-01',
            'nature_of_petition' => 'Bribery',
            'mode_of_petition_received' => 'Direct',
            'description' => 'Test description',
            'complainants' => [
                [
                    'name' => 'John Doe',
                    'phone' => '1234567890',
                    'addresses' => [
                        [
                            'address_type' => 'Permanent',
                            'address' => '123 Main St',
                            'district' => 'Test District',
                            'pincode' => '123456'
                        ]
                    ]
                ]
            ],
            'accused' => [
                [
                    'name' => 'Jane Smith',
                    'addresses' => [
                        [
                            'address_type' => 'Office',
                            'address' => '456 Office Way',
                            'district' => 'Off District'
                        ]
                    ]
                ]
            ],
            'evidence_files' => [
                UploadedFile::fake()->create('evidence.pdf', 100)
            ]
        ];

        $response = $this->actingAs($this->user)->post(route('petitions.store'), $data);

        $response->assertRedirect(route('petitions.index'));
        $this->assertDatabaseHas('petitions', ['petition_no' => 'PET-2024-0001', 'seat_id' => $this->seat->seat_id]);
        $this->assertDatabaseHas('addresses', ['person_name' => 'John Doe', 'person_type' => 'Complainant']);
        $this->assertDatabaseHas('addresses', ['person_name' => 'Jane Smith', 'person_type' => 'Accused']);
        
        $petition = Petition::where('petition_no', 'PET-2024-0001')->first();
        $this->assertCount(1, $petition->uploads);
        Storage::disk('public')->assertExists($petition->uploads->first()->file_path);
    }

    public function test_user_can_view_petitions_index(): void
    {
        Petition::factory()->create(['user_id' => $this->user->user_id, 'seat_id' => $this->seat->seat_id]);

        $response = $this->actingAs($this->user)->get(route('petitions.index'));

        $response->assertStatus(200);
        $response->assertViewIs('user.petition_view');
        $response->assertViewHas('petitions');
    }

    public function test_user_cannot_view_others_petition(): void
    {
        $otherUser = User::factory()->create();
        $otherSeat = Seat::factory()->create();
        $petition = Petition::factory()->create(['user_id' => $otherUser->user_id, 'seat_id' => $otherSeat->seat_id]);

        $response = $this->actingAs($this->user)->get(route('petitions.show', $petition->petition_id));

        $response->assertStatus(403);
    }

    public function test_admin_can_view_any_petition(): void
    {
        $petition = Petition::factory()->create();

        $response = $this->actingAs($this->admin)->get(route('petitions.show', $petition->petition_id));

        $response->assertStatus(200);
    }

    public function test_user_can_update_petition(): void
    {
        $petition = Petition::factory()->create(['user_id' => $this->user->user_id, 'seat_id' => $this->seat->seat_id]);
        
        $updateData = [
            'petition_no' => 'UPDATED-NO-1',
            'date_of_petition_received' => '2024-02-02',
            'nature_of_petition' => 'Misuse of authority',
            'mode_of_petition_received' => 'Email',
            'description' => 'Updated description content',
        ];

        $response = $this->actingAs($this->user)->put(route('petitions.update', $petition->petition_id), $updateData);

        $response->assertRedirect(route('petitions.index'));
        $this->assertDatabaseHas('petitions', [
            'petition_id' => $petition->petition_id,
            'petition_no' => 'UPDATED-NO-1',
            'nature_of_petition' => 'Misuse of authority'
        ]);
    }

    public function test_user_can_delete_petition(): void
    {
        $petition = Petition::factory()->create(['user_id' => $this->user->user_id, 'seat_id' => $this->seat->seat_id]);

        $response = $this->actingAs($this->user)->delete(route('petitions.destroy', $petition->petition_id));

        $response->assertRedirect(route('petitions.index'));
        $this->assertSoftDeleted('petitions', ['petition_id' => $petition->petition_id]);
    }

    public function test_check_petition_no_endpoint(): void
    {
        Petition::factory()->create(['petition_no' => 'EXIST-123']);

        $response = $this->actingAs($this->user)->post(route('petitions.checkPetitionNo'), ['petition_no' => 'EXIST-123']);
        $response->assertJson(['exists' => true]);

        $response = $this->actingAs($this->user)->post(route('petitions.checkPetitionNo'), ['petition_no' => 'NEW-999']);
        $response->assertJson(['exists' => false]);
    }

    public function test_user_can_filter_petitions_by_nature_and_mode(): void
    {
        Petition::factory()->create([
            'user_id' => $this->user->user_id,
            'seat_id' => $this->seat->seat_id,
            'nature_of_petition' => 'Bribery',
            'mode_of_petition_received' => 'Direct'
        ]);
        Petition::factory()->create([
            'user_id' => $this->user->user_id,
            'seat_id' => $this->seat->seat_id,
            'nature_of_petition' => 'Serious negligence',
            'mode_of_petition_received' => 'Email'
        ]);

        $response = $this->actingAs($this->user)->get(route('petitions.index', ['nature_of_petition' => 'Bribery']));
        $response->assertStatus(200);
        $this->assertCount(1, $response->viewData('petitions'));

        $response = $this->actingAs($this->user)->get(route('petitions.index', ['mode_of_petition' => 'Email']));
        $response->assertStatus(200);
        $this->assertCount(1, $response->viewData('petitions'));
    }

    public function test_user_can_filter_petitions_by_date_range(): void
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

        $response = $this->actingAs($this->user)->get(route('petitions.index', [
            'date_from' => '2024-01-01',
            'date_to' => '2024-01-15'
        ]));
        $response->assertStatus(200);
        $this->assertCount(1, $response->viewData('petitions'));
    }

    public function test_user_can_export_petitions_to_excel(): void
    {
        Petition::factory()->create(['user_id' => $this->user->user_id, 'seat_id' => $this->seat->seat_id]);

        $response = $this->actingAs($this->user)->get(route('petitions.export'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.ms-excel');
        $response->assertHeader('Content-Disposition', 'attachment; filename="petitions_report_all_'.date('Y-m-d').'.xls"');
    }
}
