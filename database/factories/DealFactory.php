<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Deal>
 */
class DealFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'product_id' => function() {
                return Product::factory()->create()->id;
            },
            'seller_id' => function() {
                return User::factory()->create()->id;
            },
            'buyer_id' => function() {
                return User::factory()->create()->id;
            },
            'status' => $this->faker->randomElement(['listing', 'purchased', 'shipping', 'completed', 'canceled']),
        ];
    }

}
