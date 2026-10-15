<?php

namespace Tests\Unit;

use App\Http\Requests\UpdatePetitionRequest;
use App\Models\Petition;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Mockery;
use Tests\TestCase;

class UpdatePetitionRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_ignores_the_current_petition_when_receipt_number_is_unchanged(): void
    {
        $petition = Petition::factory()->create(['receipt_no' => '4556565/2026']);
        $route = Mockery::mock();
        $route->shouldReceive('parameter')
            ->with('petition', null)
            ->andReturn($petition->hashid);

        $request = new UpdatePetitionRequest();
        $request->setRouteResolver(fn () => $route);

        $validator = Validator::make(
            ['receipt_no' => $petition->receipt_no],
            $request->rules()
        );

        $this->assertFalse($validator->errors()->has('receipt_no'));
    }

    public function test_it_rejects_a_receipt_number_used_by_another_petition(): void
    {
        $petition = Petition::factory()->create(['receipt_no' => '4556565/2026']);
        Petition::factory()->create(['receipt_no' => 'OTHER-RECEIPT']);
        $route = Mockery::mock();
        $route->shouldReceive('parameter')
            ->with('petition', null)
            ->andReturn($petition->hashid);

        $request = new UpdatePetitionRequest();
        $request->setRouteResolver(fn () => $route);

        $validator = Validator::make(
            ['receipt_no' => 'OTHER-RECEIPT'],
            $request->rules()
        );

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('receipt_no'));
    }
}
