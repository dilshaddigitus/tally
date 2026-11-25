<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\SubscriptionPlan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Subscription>
 */
class SubscriptionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'subscription_plan_id' => SubscriptionPlan::factory(),
            'billing_type' => 'subscription',
            'status' => 'active',
            'started_at' => now(),
            'renews_at' => now()->addMonth(),
            'payment_status' => 'paid',
            'payment_method' => 'manual',
        ];
    }
}
