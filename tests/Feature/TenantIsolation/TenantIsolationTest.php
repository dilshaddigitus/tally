<?php

namespace Tests\Feature\TenantIsolation;

use App\Models\Customer;
use App\Models\CustomerProfile;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Brief §5: "Add automated tenant-isolation tests." These exercise the
 * BelongsToCustomer global scope and the CustomerPolicy directly — the two
 * places tenant boundaries are actually enforced.
 */
class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_customer_staff_user_only_sees_their_own_tenants_profile(): void
    {
        $customerA = Customer::factory()->create();
        $customerB = Customer::factory()->create();

        $profileA = CustomerProfile::factory()->for($customerA)->create();
        CustomerProfile::factory()->for($customerB)->create();

        $staffA = User::factory()->customerStaff()->create(['customer_id' => $customerA->id]);

        $this->actingAs($staffA);

        $visible = CustomerProfile::all();

        $this->assertCount(1, $visible);
        $this->assertTrue($visible->first()->is($profileA));
    }

    public function test_a_customer_cannot_fetch_another_tenants_profile_by_id(): void
    {
        $customerA = Customer::factory()->create();
        $customerB = Customer::factory()->create();

        CustomerProfile::factory()->for($customerA)->create();
        $profileB = CustomerProfile::factory()->for($customerB)->create();

        $staffA = User::factory()->customerStaff()->create(['customer_id' => $customerA->id]);

        $this->actingAs($staffA);

        $this->assertNull(CustomerProfile::find($profileB->id));
    }

    public function test_a_customer_cannot_see_another_tenants_subscriptions(): void
    {
        $customerA = Customer::factory()->create();
        $customerB = Customer::factory()->create();

        Subscription::factory()->create(['customer_id' => $customerA->id]);
        Subscription::factory()->create(['customer_id' => $customerB->id]);

        $adminA = User::factory()->customerAdmin()->create(['customer_id' => $customerA->id]);

        $this->actingAs($adminA);

        $this->assertCount(1, Subscription::all());
    }

    public function test_super_admin_sees_every_tenants_records(): void
    {
        Customer::factory()->count(3)->create()->each(
            fn (Customer $customer) => CustomerProfile::factory()->for($customer)->create()
        );

        $superAdmin = User::factory()->superAdmin()->create();

        $this->actingAs($superAdmin);

        $this->assertCount(3, CustomerProfile::all());
    }

    public function test_customer_policy_blocks_viewing_another_tenant(): void
    {
        $customerA = Customer::factory()->create();
        $customerB = Customer::factory()->create();

        $adminA = User::factory()->customerAdmin()->create(['customer_id' => $customerA->id]);

        $this->assertFalse($adminA->can('view', $customerB));
        $this->assertTrue($adminA->can('view', $customerA));
    }

    public function test_a_new_record_is_auto_stamped_with_the_authenticated_users_tenant(): void
    {
        $customer = Customer::factory()->create();
        $staff = User::factory()->customerStaff()->create(['customer_id' => $customer->id]);

        $this->actingAs($staff);

        $profile = CustomerProfile::create(['billing_address' => '1 Test Street']);

        $this->assertEquals($customer->id, $profile->customer_id);
    }
}
