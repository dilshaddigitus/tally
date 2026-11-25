<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\CustomerProfile;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            PermissionSeeder::class,
            SubscriptionPlanSeeder::class,
        ]);

        User::factory()->superAdmin()->create([
            'name' => 'Super Admin',
            'email' => 'admin@tallyconnector.test',
        ]);

        $customer = Customer::factory()->create([
            'name' => 'Demo Admin',
            'business_name' => 'Demo Business Pvt Ltd',
            'email' => 'demo@tallyconnector.test',
        ]);

        CustomerProfile::factory()->for($customer)->create();

        Subscription::factory()->create([
            'customer_id' => $customer->id,
            'subscription_plan_id' => SubscriptionPlan::where('slug', 'growth')->first()->id,
            'billing_type' => 'subscription',
            'status' => 'active',
            'started_at' => now(),
            'renews_at' => now()->addMonth(),
            'payment_status' => 'paid',
            'payment_method' => 'manual',
        ]);

        User::factory()->customerAdmin()->create([
            'name' => 'Demo Customer Admin',
            'email' => 'customer.admin@tallyconnector.test',
            'customer_id' => $customer->id,
        ]);

        User::factory()->customerStaff()->create([
            'name' => 'Demo Customer Staff',
            'email' => 'customer.staff@tallyconnector.test',
            'customer_id' => $customer->id,
        ]);
    }
}
