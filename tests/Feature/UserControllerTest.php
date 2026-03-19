<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Upload;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_register_user_with_photo()
    {
        Storage::fake('public');

        $photo = UploadedFile::fake()->image('profile.jpg');

        $userData = [
            'name' => 'Jane Doe',
            'pen' => '654321', // Use string explicitly
            'email' => 'jane@example.com',
            'mobile_number' => '0987654321',
            'password' => 'secret123',
            'role' => 'user',
            'designation' => 'SI',
            'photo' => $photo,
        ];

        $response = $this->post(route('users.store'), $userData);

        if ($response->status() !== 302) {
            // If it failed validation, let's see why
            $errors = session('errors');
            if ($errors) {
                fwrite(STDERR, print_r($errors->all(), true));
            } else {
                fwrite(STDERR, $response->getContent());
            }
        }

        $response->assertRedirect(route('users.index'));
        
        $user = User::where('email', 'jane@example.com')->first();
        $this->assertNotNull($user, 'User should have been created');
        $this->assertNotNull($user->photo, 'User should have a photo link');

        // Check if upload record exists
        $this->assertDatabaseHas('uploads', [
            'upload_id' => $user->photo,
            'category' => 'Profile Photo',
        ]);

        // Check if file is stored
        $upload = Upload::find($user->photo);
        Storage::disk('public')->assertExists($upload->file_path);
    }

    public function test_can_update_user_photo()
    {
        Storage::fake('public');
        $user = User::factory()->create(['photo' => null]);
        $photo = UploadedFile::fake()->image('updated.jpg');

        $response = $this->put(route('users.update', encrypt($user->user_id)), [
            'name' => 'Updated Name',
            'pen' => '999999',
            'mobile_number' => '1234567890',
            'email' => $user->email,
            'role' => 'user',
            'designation' => 'SI',
            'photo' => $photo,
        ]);

        $response->assertRedirect(route('users.index'));
        if (session('error')) {
            dump(session('error'));
        }
        $response->assertSessionHas('success');
        
        $user->refresh();
        $this->assertNotNull($user->photo);
        
        $upload = Upload::find($user->photo);
        $this->assertEquals('Profile Photo', $upload->category);
        Storage::disk('public')->assertExists($upload->file_path);
    }
}
