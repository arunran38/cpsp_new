<?php

namespace Database\Factories;

use App\Models\Upload;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Upload>
 */
class UploadFactory extends Factory
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
            'category' => 'Petition Document',
            'original_filename' => 'test.pdf',
            'file_path' => 'petitions/test.pdf',
            'uploaded_by' => \App\Models\User::factory(),
        ];
    }
}
