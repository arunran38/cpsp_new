<?php

namespace Database\Factories;

use App\Models\PetitionForwarding;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PetitionForwarding>
 */
class PetitionForwardingFactory extends Factory
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
            'from_seat_id' => \App\Models\Seat::factory(),
            'to_unit_id' => \App\Models\Unit::factory(),
            'director_remarks' => $this->faker->sentence(),
            'forwarded_date' => $this->faker->date(),
        ];
    }
}
