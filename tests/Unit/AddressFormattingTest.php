<?php

namespace Tests\Unit;

use App\Models\Address;
use App\Models\District;
use Tests\TestCase;

class AddressFormattingTest extends TestCase
{
    public function test_address_formatting_with_all_fields(): void
    {
        $address = new Address([
            'full_address' => 'Software Division',
            'pincode' => '695033'
        ]);
        $address->setRelation('district', new District(['district_name' => 'Malappuram']));

        $this->assertEquals('Software Division, Malappuram - 695033', $address->formatted_address);
    }

    public function test_address_formatting_strips_trailing_commas(): void
    {
        $address = new Address([
            'full_address' => 'Software Division,',
            'pincode' => '695033'
        ]);
        $address->setRelation('district', new District(['district_name' => 'Malappuram']));

        $this->assertEquals('Software Division, Malappuram - 695033', $address->formatted_address);
    }

    public function test_address_formatting_removes_internal_duplicate_commas(): void
    {
        $address = new Address([
            'full_address' => 'Software Division, , Building 2, ,',
            'pincode' => '695033'
        ]);
        $address->setRelation('district', new District(['district_name' => 'Malappuram']));

        $this->assertEquals('Software Division, Building 2, Malappuram - 695033', $address->formatted_address);
    }

    public function test_address_formatting_without_district(): void
    {
        $address = new Address([
            'full_address' => 'Software Division,',
            'pincode' => '695033'
        ]);

        $this->assertEquals('Software Division - 695033', $address->formatted_address);
    }

    public function test_address_formatting_without_pincode(): void
    {
        $address = new Address([
            'full_address' => 'Software Division,',
            'pincode' => null
        ]);
        $address->setRelation('district', new District(['district_name' => 'Malappuram']));

        $this->assertEquals('Software Division, Malappuram', $address->formatted_address);
    }

    public function test_address_formatting_without_address_line(): void
    {
        $address = new Address([
            'full_address' => 'Not provided',
            'pincode' => '695033'
        ]);
        $address->setRelation('district', new District(['district_name' => 'Malappuram']));

        $this->assertEquals('Malappuram - 695033', $address->formatted_address);
    }

    public function test_address_formatting_when_completely_empty(): void
    {
        $address = new Address([
            'full_address' => 'Not provided',
            'pincode' => null
        ]);

        $this->assertEquals('Not provided', $address->formatted_address);
    }
}

