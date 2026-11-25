<?php

namespace Tests\Feature\Auth;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_login_and_is_redirected_to_admin_dashboard(): void
    {
        $user = User::factory()->superAdmin()->create(['password' => bcrypt('secret123')]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'secret123',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_customer_admin_is_redirected_to_customer_dashboard(): void
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->customerAdmin()->create([
            'customer_id' => $customer->id,
            'password' => bcrypt('secret123'),
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'secret123',
        ]);

        $response->assertRedirect(route('customer.dashboard'));
    }

    public function test_login_fails_with_wrong_password(): void
    {
        $user = User::factory()->customerAdmin()->create(['password' => bcrypt('secret123')]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_deactivated_user_cannot_login(): void
    {
        $user = User::factory()->customerAdmin()->create([
            'password' => bcrypt('secret123'),
            'is_active' => false,
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'secret123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_logout_ends_the_session(): void
    {
        $user = User::factory()->superAdmin()->create();

        $this->actingAs($user)->post('/logout')->assertRedirect('/login');
        $this->assertGuest();
    }
}
