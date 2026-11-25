<?php

namespace Tests\Feature\Admin;

use App\Models\Customer;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdmin = User::factory()->superAdmin()->create();
    }

    public function test_super_admin_can_create_a_customer_with_plan_and_admin_user(): void
    {
        $plan = SubscriptionPlan::factory()->create(['billing_type' => 'subscription']);

        $response = $this->actingAs($this->superAdmin)->post('/admin/customers', [
            'name' => 'Jane Doe',
            'business_name' => 'Doe Traders',
            'email' => 'jane@doe-traders.test',
            'subscription_plan_id' => $plan->id,
            'admin_name' => 'Jane Doe',
            'admin_email' => 'jane.admin@doe-traders.test',
        ]);

        $customer = Customer::where('email', 'jane@doe-traders.test')->firstOrFail();

        $response->assertRedirect(route('admin.customers.show', $customer));
        $this->assertDatabaseHas('customers', ['email' => 'jane@doe-traders.test']);
        $this->assertDatabaseHas('users', [
            'email' => 'jane.admin@doe-traders.test',
            'role' => User::ROLE_CUSTOMER_ADMIN,
            'customer_id' => $customer->id,
        ]);
        $this->assertDatabaseHas('subscriptions', [
            'customer_id' => $customer->id,
            'subscription_plan_id' => $plan->id,
        ]);
        $this->assertNotNull($customer->profile);
    }

    public function test_creating_a_customer_requires_a_unique_email(): void
    {
        $existing = Customer::factory()->create();
        $plan = SubscriptionPlan::factory()->create();

        $response = $this->actingAs($this->superAdmin)->post('/admin/customers', [
            'name' => 'Someone Else',
            'email' => $existing->email,
            'subscription_plan_id' => $plan->id,
            'admin_name' => 'Someone Else',
            'admin_email' => 'someone.else@example.test',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_super_admin_can_toggle_customer_status(): void
    {
        $customer = Customer::factory()->create(['status' => 'active']);

        $this->actingAs($this->superAdmin)
            ->patch(route('admin.customers.toggle-status', $customer))
            ->assertRedirect();

        $this->assertSame('inactive', $customer->fresh()->status);

        $this->actingAs($this->superAdmin)
            ->patch(route('admin.customers.toggle-status', $customer));

        $this->assertSame('active', $customer->fresh()->status);
    }

    public function test_customer_list_can_be_searched(): void
    {
        Customer::factory()->create(['name' => 'Alpha Traders']);
        Customer::factory()->create(['name' => 'Beta Logistics']);

        $response = $this->actingAs($this->superAdmin)->get('/admin/customers?search=Alpha');

        $response->assertOk();
        $response->assertSee('Alpha Traders');
        $response->assertDontSee('Beta Logistics');
    }
}
