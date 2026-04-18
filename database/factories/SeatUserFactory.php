<?php

namespace Database\Factories;

use App\Models\SeatUser;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SeatUser>
 */
class SeatUserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'seat_id' => \App\Models\Seat::factory(),
            'is_additional' => false,
            'assigned_at' => now(),
            'is_active' => true,
        ];
    }
}
