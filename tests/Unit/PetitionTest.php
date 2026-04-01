<?php

namespace Tests\Unit;

use App\Models\Petition;
use App\Models\User;
use App\Models\Seat;
use App\Models\Address;
use App\Models\Upload;
use App\Models\PetitionForwarding;
use App\Models\Decision;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PetitionTest extends TestCase
{
    use RefreshDatabase;

    public function test_petition_has_relationships(): void
    {
        $petition = Petition::factory()->create();
        
        $this->assertInstanceOf(User::class, $petition->user);
        $this->assertInstanceOf(Seat::class, $petition->seat);
        
        Address::factory()->create(['petition_id' => $petition->petition_id]);
        $this->assertInstanceOf(Address::class, $petition->addresses->first());
        
        Upload::factory()->create(['petition_id' => $petition->petition_id]);
        $this->assertInstanceOf(Upload::class, $petition->uploads->first());
        
        PetitionForwarding::factory()->create(['petition_id' => $petition->petition_id]);
        $this->assertInstanceOf(PetitionForwarding::class, $petition->forwardings->first());
        $this->assertInstanceOf(PetitionForwarding::class, $petition->latestForwarding);
        
        Decision::factory()->create(['petition_id' => $petition->petition_id]);
        $this->assertInstanceOf(Decision::class, $petition->decision);
    }

    public function test_petition_status_counting_methods(): void
    {
        Petition::factory()->create(['status' => 'Received']);
        Petition::factory()->create(['status' => 'Forwarded']);
        Petition::factory()->create(['status' => 'VR_Received']);
        Petition::factory()->create(['status' => 'Sent_to_Govt']);

        $this->assertEquals(1, Petition::countPendingPetitions());
        $this->assertEquals(1, Petition::countForwardedPetitions());
        $this->assertEquals(1, Petition::countVrPetitions());
        $this->assertEquals(1, Petition::countDecisionPetitions());
    }
}
