<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleEnforcementTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_admin_cannot_access_admin_routes(): void
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->customerAdmin()->create(['customer_id' => $customer->id]);

        $this->actingAs($user)->get('/admin/dashboard')->assertForbidden();
        $this->actingAs($user)->get('/admin/customers')->assertForbidden();
    }

    public function test_customer_staff_cannot_access_admin_routes(): void
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->customerStaff()->create(['customer_id' => $customer->id]);

        $this->actingAs($user)->get('/admin/dashboard')->assertForbidden();
    }

    public function test_super_admin_can_access_admin_routes(): void
    {
        $user = User::factory()->superAdmin()->create();

        $this->actingAs($user)->get('/admin/dashboard')->assertOk();
        $this->actingAs($user)->get('/admin/customers')->assertOk();
    }

    public function test_super_admin_cannot_access_customer_portal_routes(): void
    {
        $user = User::factory()->superAdmin()->create();

        $this->actingAs($user)->get('/customer/dashboard')->assertForbidden();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/admin/dashboard')->assertRedirect('/login');
        $this->get('/customer/dashboard')->assertRedirect('/login');
    }
}
