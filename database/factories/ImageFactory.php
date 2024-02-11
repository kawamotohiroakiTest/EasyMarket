<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Image>
 */
class ImageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'file_path' => $this->faker->imageUrl(),
            'mime_type' => $this->faker->mimeType,
            'size' => $this->faker->randomNumber(),
            'width' => $this->faker->randomNumber(3),
            'height' => $this->faker->randomNumber(3),
        ];
    }
}
