<?php

namespace Tests\Feature\Customer;

use App\Models\Customer;
use App\Models\Ledger;
use App\Models\TallyCompanyConnection;
use App\Models\User;
use App\Models\Voucher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportsTest extends TestCase
{
    use RefreshDatabase;

    public function test_trial_balance_shows_only_the_current_customers_ledgers(): void
    {
        $customerA = Customer::factory()->create();
        $customerB = Customer::factory()->create();
        $connectionA = TallyCompanyConnection::factory()->create(['customer_id' => $customerA->id]);

        Ledger::factory()->create([
            'customer_id' => $customerA->id,
            'tally_company_id' => $connectionA->tally_company_id,
            'name' => 'Cash (A)',
            'opening_balance' => 1000,
            'closing_balance' => 500,
        ]);
        Ledger::factory()->create([
            'customer_id' => $customerB->id,
            'tally_company_id' => TallyCompanyConnection::factory()->create(['customer_id' => $customerB->id])->tally_company_id,
            'name' => 'Cash (B)',
        ]);

        $userA = User::factory()->customerAdmin()->create(['customer_id' => $customerA->id]);

        $response = $this->actingAs($userA)
            ->get(route('customer.reports.trial-balance', ['company' => $connectionA->id]));

        $response->assertOk();
        $response->assertSee('Cash (A)');
        $response->assertDontSee('Cash (B)');
    }

    public function test_trial_balance_csv_export_streams_a_csv(): void
    {
        $customer = Customer::factory()->create();
        $connection = TallyCompanyConnection::factory()->create(['customer_id' => $customer->id]);
        Ledger::factory()->create([
            'customer_id' => $customer->id,
            'tally_company_id' => $connection->tally_company_id,
            'name' => 'Bank',
        ]);
        $user = User::factory()->customerAdmin()->create(['customer_id' => $customer->id]);

        $response = $this->actingAs($user)->get(
            route('customer.reports.trial-balance', ['company' => $connection->id, 'export' => 'csv'])
        );

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=utf-8');
    }

    public function test_sales_register_only_shows_sales_type_vouchers(): void
    {
        $customer = Customer::factory()->create();
        $connection = TallyCompanyConnection::factory()->create(['customer_id' => $customer->id]);

        Voucher::factory()->create([
            'customer_id' => $customer->id,
            'tally_company_id' => $connection->tally_company_id,
            'voucher_type' => 'Sales',
            'voucher_number' => 'S-001',
        ]);
        Voucher::factory()->create([
            'customer_id' => $customer->id,
            'tally_company_id' => $connection->tally_company_id,
            'voucher_type' => 'Purchase',
            'voucher_number' => 'P-001',
        ]);

        $user = User::factory()->customerAdmin()->create(['customer_id' => $customer->id]);

        $response = $this->actingAs($user)
            ->get(route('customer.reports.sales-register', ['company' => $connection->id]));

        $response->assertOk();
        $response->assertSee('S-001');
        $response->assertDontSee('P-001');
    }

    public function test_a_customer_cannot_view_another_tenants_ledger_statement(): void
    {
        $customerA = Customer::factory()->create();
        $customerB = Customer::factory()->create();
        $connectionB = TallyCompanyConnection::factory()->create(['customer_id' => $customerB->id]);

        $ledgerB = Ledger::factory()->create([
            'customer_id' => $customerB->id,
            'tally_company_id' => $connectionB->tally_company_id,
        ]);

        $userA = User::factory()->customerAdmin()->create(['customer_id' => $customerA->id]);

        $this->actingAs($userA)
            ->get(route('customer.reports.ledger-statement', $ledgerB))
            ->assertNotFound();
    }
}
