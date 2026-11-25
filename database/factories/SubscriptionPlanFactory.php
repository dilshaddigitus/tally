<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SubscriptionPlan>
 */
class SubscriptionPlanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'slug' => fake()->unique()->slug(2),
            'billing_type' => 'subscription',
            'billing_interval' => 'month',
            'max_devices' => 1,
            'max_companies' => 1,
            'price' => fake()->randomFloat(2, 500, 5000),
            'currency' => 'INR',
            'is_active' => true,
        ];
    }
}
