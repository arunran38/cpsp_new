<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Seat;
use App\Models\SeatUser;
use App\Models\Upload;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    public function test_admin_can_view_users_index(): void
    {
        $response = $this->actingAs($this->admin)->get(route('users.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.users_view');
        $response->assertViewHas('users');
    }

    public function test_admin_can_view_create_user_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('users.create'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.user_registration');
    }

    public function test_admin_can_store_user_with_photo(): void
    {
        Storage::fake('public');

        $userData = [
            'name' => 'John Officer',
            'pen' => '123456',
            'email' => 'john@example.com',
            'mobile_number' => '9876543210',
            'role' => 'user',
            'designation' => 'IP',
            'password' => 'password123',
            'user_photo' => UploadedFile::fake()->image('profile.jpg')
        ];

        $response = $this->actingAs($this->admin)->post(route('users.store'), $userData);

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', ['name' => 'John Officer', 'pen' => '123456']);

        $user = User::where('pen', '123456')->first();
        $this->assertNotNull($user->photo);
        $upload = Upload::find($user->photo);
        Storage::disk('public')->assertExists($upload->file_path);
    }

    public function test_store_user_validation_errors(): void
    {
        $response = $this->actingAs($this->admin)->post(route('users.store'), []);

        $response->assertSessionHasErrors(['name', 'pen', 'email', 'role', 'designation', 'password']);
    }

    public function test_admin_can_edit_user_with_encrypted_id(): void
    {
        $user = User::factory()->create();
        $encryptedId = encrypt($user->user_id);

        $response = $this->actingAs($this->admin)->get(route('users.edit', $encryptedId));

        $response->assertStatus(200);
        $response->assertViewIs('admin.edit_users');
        $response->assertViewHas('user');
    }

    public function test_admin_can_update_user_and_status(): void
    {
        $user = User::factory()->create(['status' => 'Active']);
        $encryptedId = encrypt($user->user_id);

        $seat = Seat::factory()->create();
        SeatUser::factory()->create(['user_id' => $user->user_id, 'seat_id' => $seat->seat_id, 'is_active' => true]);

        $updateData = [
            'name' => 'Updated Name',
            'pen' => (string) $user->pen,
            'email' => 'updated@example.com',
            'mobile_number' => '1122334455',
            'role' => 'admin',
            'designation' => 'SI',
            'status' => 'Transferred',
        ];

        $response = $this->actingAs($this->admin)->put(route('users.update', $encryptedId), $updateData);

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', [
            'user_id' => $user->user_id,
            'name' => 'Updated Name',
            'status' => 'Transferred'
        ]);

        // Check if seat assignment was revoked
        $this->assertDatabaseHas('seat_users', [
            'user_id' => $user->user_id,
            'is_active' => false
        ]);
    }

    public function test_admin_can_delete_user(): void
    {
        $user = User::factory()->create();
        $encryptedId = encrypt($user->user_id);

        $seat = Seat::factory()->create();
        SeatUser::factory()->create(['user_id' => $user->user_id, 'seat_id' => $seat->seat_id, 'is_active' => true]);

        $response = $this->actingAs($this->admin)->delete(route('users.destroy', $encryptedId));

        $response->assertRedirect(route('users.index'));
        $this->assertSoftDeleted('users', ['user_id' => $user->user_id]);

        // Final seat should be revoked
        $this->assertDatabaseHas('seat_users', [
            'user_id' => $user->user_id,
            'is_active' => false
        ]);
    }

    public function test_check_unique_endpoint(): void
    {
        User::factory()->create(['email' => 'taken@example.com', 'pen' => 999888]);

        $response = $this->actingAs($this->admin)->post(route('users.checkUnique'), [
            'field' => 'email',
            'value' => 'taken@example.com'
        ]);
        $response->assertJson(['exists' => true]);

        $response = $this->actingAs($this->admin)->post(route('users.checkUnique'), [
            'field' => 'pen',
            'value' => '777666'
        ]);
        $response->assertJson(['exists' => false]);
    }
}
