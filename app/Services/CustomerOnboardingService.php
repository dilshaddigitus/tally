<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\CustomerProfile;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CustomerOnboardingService
{
    /**
     * Registers a customer, its profile, its first subscription and its
     * Customer Admin user as one unit — brief §12: transactions for critical operations.
     */
    public function register(array $data): Customer
    {
        return DB::transaction(function () use ($data) {
            $customer = Customer::create([
                'name' => $data['name'],
                'business_name' => $data['business_name'] ?? null,
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
                'status' => 'active',
            ]);

            $customer->profile()->create([]);

            $plan = SubscriptionPlan::findOrFail($data['subscription_plan_id']);

            $customer->subscriptions()->create([
                'subscription_plan_id' => $plan->id,
                'billing_type' => $plan->billing_type,
                'status' => 'active',
                'started_at' => now(),
                'renews_at' => $plan->isOneTime() ? null : now()->addMonth(),
                'payment_status' => 'pending',
                'payment_method' => 'manual',
            ]);

            $temporaryPassword = Str::password(16);

            $adminUser = User::create([
                'name' => $data['admin_name'],
                'email' => $data['admin_email'],
                'password' => $temporaryPassword,
                'role' => User::ROLE_CUSTOMER_ADMIN,
                'customer_id' => $customer->id,
            ]);

            // Surfaced to the Super Admin once, immediately after creation, never logged or stored in plain text.
            $customer->setAttribute('temporary_admin_password', $temporaryPassword);
            $customer->setAttribute('admin_user', $adminUser);

            return $customer;
        });
    }

    public function toggleStatus(Customer $customer): Customer
    {
        $customer->update([
            'status' => $customer->status === 'active' ? 'inactive' : 'active',
        ]);

        return $customer;
    }
}
