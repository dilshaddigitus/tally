<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Starter',
                'slug' => 'starter',
                'billing_type' => 'subscription',
                'billing_interval' => 'month',
                'max_devices' => 1,
                'max_companies' => 1,
                'price' => 999.00,
            ],
            [
                'name' => 'Growth',
                'slug' => 'growth',
                'billing_type' => 'subscription',
                'billing_interval' => 'month',
                'max_devices' => 3,
                'max_companies' => 5,
                'price' => 2499.00,
            ],
            [
                'name' => 'Lifetime',
                'slug' => 'lifetime',
                'billing_type' => 'one_time',
                'billing_interval' => null,
                'max_devices' => 3,
                'max_companies' => 5,
                'price' => 24999.00,
            ],
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::updateOrCreate(
                ['slug' => $plan['slug']],
                $plan + ['currency' => 'INR', 'is_active' => true]
            );
        }
    }
}
