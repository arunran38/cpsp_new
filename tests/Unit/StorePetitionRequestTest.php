<?php

namespace Tests\Unit;

use App\Http\Requests\StorePetitionRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class StorePetitionRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_inward_registration_allows_missing_unit_received_date(): void
    {
        $request = new StorePetitionRequest();
        $request->replace([
            'is_inward_entry' => '1',
            'mode_of_petition_received' => 'Unit',
        ]);

        $validator = Validator::make([], [
            'date_of_petition_received_at_unit' => $request->rules()['date_of_petition_received_at_unit'],
        ]);

        $this->assertFalse($validator->fails());
    }

    public function test_non_inward_unit_registration_still_requires_unit_received_date(): void
    {
        $request = new StorePetitionRequest();
        $request->replace([
            'mode_of_petition_received' => 'Unit',
        ]);

        $validator = Validator::make([], [
            'date_of_petition_received_at_unit' => $request->rules()['date_of_petition_received_at_unit'],
        ]);

        $this->assertTrue($validator->fails());
    }
}
