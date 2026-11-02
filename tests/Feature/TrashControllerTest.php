<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Petition;
use App\Models\User;
use App\Models\Seat;
use App\Models\SeatUser;
use App\Models\Upload;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class TrashControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = $this->createSuperAdminUser();
    }

    public function test_admin_can_view_trashed_petitions(): void
    {
        $petition = Petition::factory()->create([
            'receipt_no' => 'TRASH-1001/2026',
        ]);
        $petition->delete();

        $response = $this->actingAs($this->admin)->get(route('admin.trash.index'));

        $response->assertStatus(200);
        $response->assertSee('TRASH-1001/2026');
        $response->assertSee('Recycle Bin');
    }

    public function test_unauthorized_user_cannot_view_recycle_bin(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('admin.trash.index'));
        $response->assertStatus(403);
    }

    public function test_admin_can_filter_trashed_petitions(): void
    {
        $match = Petition::factory()->create([
            'receipt_no' => 'MATCH-9999/2026',
            'nature_of_petition' => 'Complaint',
        ]);
        $match->delete();

        $other = Petition::factory()->create([
            'receipt_no' => 'OTHER-0000/2026',
            'nature_of_petition' => 'Vigilance',
        ]);
        $other->delete();

        $response = $this->actingAs($this->admin)->get(route('admin.trash.index', [
            'search' => 'MATCH-9999',
            'nature' => 'Complaint',
        ]));

        $response->assertStatus(200);
        $response->assertSee('MATCH-9999/2026');
        $response->assertDontSee('OTHER-0000/2026');
    }

    public function test_admin_can_view_trashed_petition_details(): void
    {
        $petition = Petition::factory()->create([
            'receipt_no' => 'SHOW-1234/2026',
            'description' => 'Important deleted case description',
        ]);
        $petition->delete();

        // Test JSON preview endpoint for Alpine modal
        $jsonResponse = $this->actingAs($this->admin)->getJson(route('admin.trash.show', $petition->petition_id));
        $jsonResponse->assertStatus(200);
        $jsonResponse->assertJsonPath('success', true);
        $jsonResponse->assertJsonPath('data.receipt_no', 'SHOW-1234/2026');
        $jsonResponse->assertJsonPath('data.description', 'Important deleted case description');

        // Test HTML fallback view
        $htmlResponse = $this->actingAs($this->admin)->get(route('admin.trash.show', $petition->petition_id));
        $htmlResponse->assertStatus(200);
        $htmlResponse->assertSee('SHOW-1234/2026');
        $htmlResponse->assertSee('Important deleted case description');
    }

    public function test_admin_can_download_trashed_petition_attachment(): void
    {
        Storage::fake('public');
        $petition = Petition::factory()->create();
        $upload = Upload::factory()->create([
            'petition_id' => $petition->petition_id,
            'file_path' => 'petitions/trashed_doc.pdf',
            'original_filename' => 'trashed_doc.pdf',
        ]);
        Storage::disk('public')->put('petitions/trashed_doc.pdf', 'dummy content');

        $petition->delete();

        $response = $this->actingAs($this->admin)->get(route('admin.trash.download', $upload->upload_id));

        $response->assertStatus(200);
        $this->assertTrue($response->headers->get('content-disposition') !== null);
    }

    public function test_admin_can_restore_petition(): void
    {
        $petition = Petition::factory()->create([
            'receipt_no' => 'RESTORE-8888/2026',
        ]);
        $petition->delete();

        $response = $this->actingAs($this->admin)->post(route('admin.trash.restore', $petition->petition_id));

        $response->assertRedirect(route('admin.trash.index'));
        $this->assertNotSoftDeleted('petitions', ['petition_id' => $petition->petition_id]);
    }

    public function test_user_without_restore_permission_cannot_restore(): void
    {
        $user = User::factory()->create();
        // Give only view permission, but not restore
        Permission::firstOrCreate(['name' => 'view recycle bin', 'guard_name' => 'web']);
        $user->givePermissionTo('view recycle bin');

        $petition = Petition::factory()->create();
        $petition->delete();

        $response = $this->actingAs($user)->post(route('admin.trash.restore', $petition->petition_id));
        $response->assertStatus(403);
        $this->assertSoftDeleted('petitions', ['petition_id' => $petition->petition_id]);
    }

    public function test_admin_can_force_delete_petition_and_files(): void
    {
        Storage::fake('public');
        $petition = Petition::factory()->create([
            'receipt_no' => 'PURGE-5555/2026',
        ]);
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

    public function test_user_without_delete_permission_cannot_force_delete(): void
    {
        $user = User::factory()->create();
        Permission::firstOrCreate(['name' => 'view recycle bin', 'guard_name' => 'web']);
        $user->givePermissionTo('view recycle bin');

        $petition = Petition::factory()->create();
        $petition->delete();

        $response = $this->actingAs($user)->delete(route('admin.trash.forceDelete', $petition->petition_id));
        $response->assertStatus(403);
        $this->assertSoftDeleted('petitions', ['petition_id' => $petition->petition_id]);
    }
}
