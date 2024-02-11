<?php

namespace Database\Factories;

use App\Models\AdminUser;
use App\Models\Deal;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DealEvent>
 */
class DealEventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'deal_id' => function() {
                return Deal::factory()->create()->id;
            },
            'deal_eventable_type' => $this->faker->randomElement(['App\\Models\\User', 'App\\Models\\AdminUser']),
            'deal_eventable_id' => function(array $attributes) {
                return match($attributes['deal_eventable_type']) {
                    'App\\Models\\User' => User::factory()->create()->id,
                    'App\\Models\\AdminUser' => AdminUser::factory()->create()->id,
                };
            },
            'actor_type' => $this->faker->randomElement(['seller', 'buyer', 'admin']),
            'event_type' => $this->faker->randomElement(['listing', 'purchase', 'report_delivery', 'report_receipt', 'cancel']),
        ];
    }

}
