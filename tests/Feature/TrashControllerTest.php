<?php

namespace Tests\Feature;

use App\Models\Petition;
use App\Models\User;
use App\Models\Seat;
use App\Models\SeatUser;
use App\Models\Upload;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TrashControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    public function test_admin_can_view_trashed_petitions(): void
    {
        $petition = Petition::factory()->create();
        $petition->delete();

        $response = $this->actingAs($this->admin)->get(route('admin.trash.index'));

        $response->assertStatus(200);
        $response->assertSee($petition->petition_no);
    }

    public function test_admin_can_restore_petition(): void
    {
        $petition = Petition::factory()->create();
        $petition->delete();

        $response = $this->actingAs($this->admin)->post(route('admin.trash.restore', $petition->petition_id));

        $response->assertRedirect(route('admin.trash.index'));
        $this->assertNotSoftDeleted('petitions', ['petition_id' => $petition->petition_id]);
    }

    public function test_admin_can_force_delete_petition_and_files(): void
    {
        Storage::fake('public');
        $petition = Petition::factory()->create();
        $upload = Upload::factory()->create([
            'petition_id' => $petition->petition_id,
            'file_path' => 'petitions/test.pdf'
        ]);
        Storage::disk('public')->put('petitions/test.pdf', 'content');

        $petition->delete();

        $response = $this->actingAs($this->admin)->delete(route('admin.trash.forceDelete', $petition->petition_id));

        $response->assertRedirect(route('admin.trash.index'));
        $this->assertDatabaseMissing('petitions', ['petition_id' => $petition->petition_id]);
        $this->assertDatabaseMissing('uploads', ['upload_id' => $upload->upload_id]);
        Storage::disk('public')->assertMissing('petitions/test.pdf');
    }
}
