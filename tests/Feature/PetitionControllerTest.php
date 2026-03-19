<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Petition;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PetitionControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_submit_petition()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post(route('petitions.store'), [
            'petition_no' => 'PT-2026-001',
            'date_of_petition_received' => '2026-03-19',
            'nature_of_petition' => 'Bribery',
            'mode_of_petition_received' => 'Direct',
            'description' => 'Test description of a bribery case.',
            'complainants' => [
                [
                    'name' => 'John Doe',
                    'phone' => '1234567890',
                    'aadhar' => '123456789012',
                    'addresses' => [
                        [
                            'type' => 'Permanent',
                            'address' => '123 Main St',
                            'district' => 'Kochi',
                            'pincode' => '682001'
                        ]
                    ]
                ]
            ],
            'accused' => [
                [
                    'name' => 'Jane Smith',
                    'phone' => '0987654321',
                    'aadhar' => '210987654321',
                    'addresses' => [
                        [
                            'type' => 'Temporary',
                            'address' => '456 Side St',
                            'district' => 'Trivandrum',
                            'pincode' => '695001'
                        ]
                    ]
                ]
            ]
        ]);

        if ($response->status() !== 302) {
            $response->dump();
        }

        $response->assertRedirect(route('petitions.index'));
        $this->assertDatabaseHas('petitions', ['petition_no' => 'PT-2026-001']);
        $this->assertDatabaseCount('addresses', 2);
    }
}
