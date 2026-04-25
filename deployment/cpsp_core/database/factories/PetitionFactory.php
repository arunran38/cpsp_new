<?php

namespace Database\Factories;

use App\Models\Petition;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Petition>
 */
class PetitionFactory extends Factory
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
            'petition_no' => $this->faker->unique()->bothify('PET-####-????'),
            'date_of_petition_received' => $this->faker->date(),
            'mode_of_petition_received' => $this->faker->randomElement(['Email', 'Whatsapp', 'Tollfree', 'Direct', 'Unit', 'Tapal','iaps','others']),
            'nature_of_petition' => $this->faker->randomElement(['Bribery', 'Misuse of authority', 'Fraud / financial irregularities', 'Serious negligence','others']),
            'description' => $this->faker->paragraph(),
            'status' => 'Received',
        ];
    }
}
