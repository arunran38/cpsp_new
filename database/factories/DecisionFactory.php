<?php

namespace Database\Factories;

use App\Models\Decision;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Decision>
 */
class DecisionFactory extends Factory
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
            'decided_by_seat_id' => \App\Models\Seat::factory(),
            'decision_remarks' => $this->faker->randomElement(['PE', 'SC', 'QV', 'Closed', 'Sent to Govt', 'ICell']),
            'final_remarks' => $this->faker->paragraph(),
            'decision_date' => $this->faker->date(),
        ];
    }
}
