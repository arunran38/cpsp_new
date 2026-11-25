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

    public function test_petition_number_at_unit_is_optional_and_enforces_max_30_chars(): void
    {
        $request = new StorePetitionRequest();
        $request->replace([
            'mode_of_petition_received' => 'Unit',
        ]);

        $rules = ['petition_number_at_unit' => $request->rules()['petition_number_at_unit']];

        $emptyValidator = Validator::make(['petition_number_at_unit' => null], $rules);
        $this->assertFalse($emptyValidator->fails());

        $validValidator = Validator::make(['petition_number_at_unit' => str_repeat('A', 30)], $rules);
        $this->assertFalse($validValidator->fails());

        $tooLongValidator = Validator::make(['petition_number_at_unit' => str_repeat('A', 31)], $rules);
        $this->assertTrue($tooLongValidator->fails());
    }
}
