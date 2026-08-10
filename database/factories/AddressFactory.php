<?php

namespace Database\Factories;

use App\Models\Address;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Address>
 */
class AddressFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'petition_id' => \App\Models\Petition::factory(),
            'person_name' => $this->faker->name(),
            'person_type' => $this->faker->randomElement(['Complainant', 'Accused']),
            'address_type' => $this->faker->randomElement(['Permanent', 'Temporary', 'Office']),
            'is_primary' => false,
            'phone' => $this->faker->phoneNumber(),
            'full_address' => $this->faker->address(),
            'district' => $this->faker->city(),
            'pincode' => $this->faker->numerify('######'),
        ];
    }
}
