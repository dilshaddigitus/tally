<?php

namespace Tests\Feature\Customer;

use App\Models\Customer;
use App\Models\Ledger;
use App\Models\LedgerGroup;
use App\Models\TallyCompanyConnection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinancialReportsTest extends TestCase
{
    use RefreshDatabase;

    private function companyWithGroups(Customer $customer): array
    {
        $connection = TallyCompanyConnection::factory()->create(['customer_id' => $customer->id]);
        $companyId = $connection->tally_company_id;

        $salesGroup = LedgerGroup::factory()->create([
            'customer_id' => $customer->id, 'tally_company_id' => $companyId,
            'tally_guid' => 'g-sales', 'name' => 'Sales Accounts',
        ]);
        $purchaseGroup = LedgerGroup::factory()->create([
            'customer_id' => $customer->id, 'tally_company_id' => $companyId,
            'tally_guid' => 'g-purchase', 'name' => 'Purchase Accounts',
        ]);
        $debtorsGroup = LedgerGroup::factory()->create([
            'customer_id' => $customer->id, 'tally_company_id' => $companyId,
            'tally_guid' => 'g-debtors', 'name' => 'Sundry Debtors',
        ]);
        $creditorsGroup = LedgerGroup::factory()->create([
            'customer_id' => $customer->id, 'tally_company_id' => $companyId,
            'tally_guid' => 'g-creditors', 'name' => 'Sundry Creditors',
        ]);
        $cashGroup = LedgerGroup::factory()->create([
            'customer_id' => $customer->id, 'tally_company_id' => $companyId,
            'tally_guid' => 'g-cash', 'name' => 'Cash-in-Hand',
        ]);

        return compact('connection', 'companyId', 'salesGroup', 'purchaseGroup', 'debtorsGroup', 'creditorsGroup', 'cashGroup');
    }

    public function test_profit_and_loss_separates_income_and_expense_and_nets_them(): void
    {
        $customer = Customer::factory()->create();
        $ctx = $this->companyWithGroups($customer);

        Ledger::factory()->create([
            'customer_id' => $customer->id, 'tally_company_id' => $ctx['companyId'],
            'name' => 'Sales', 'ledger_group_tally_guid' => 'g-sales', 'closing_balance' => -50000,
        ]);
        Ledger::factory()->create([
            'customer_id' => $customer->id, 'tally_company_id' => $ctx['companyId'],
            'name' => 'Purchases', 'ledger_group_tally_guid' => 'g-purchase', 'closing_balance' => 30000,
        ]);

        $user = User::factory()->customerAdmin()->create(['customer_id' => $customer->id]);

        $response = $this->actingAs($user)->get(route('customer.reports.profit-and-loss', ['company' => $ctx['connection']->id]));

        $response->assertOk();
        $response->assertSee('Sales');
        $response->assertSee('Purchases');
        $response->assertSee('20,000.00'); // net profit = 50000 - 30000
    }

    public function test_balance_sheet_splits_assets_and_liabilities_by_sign(): void
    {
        $customer = Customer::factory()->create();
        $ctx = $this->companyWithGroups($customer);

        Ledger::factory()->create([
            'customer_id' => $customer->id, 'tally_company_id' => $ctx['companyId'],
            'name' => 'Petty Cash', 'ledger_group_tally_guid' => 'g-cash', 'closing_balance' => 5000,
        ]);
        Ledger::factory()->create([
            'customer_id' => $customer->id, 'tally_company_id' => $ctx['companyId'],
            'name' => 'Vendor Payable', 'ledger_group_tally_guid' => 'g-creditors', 'closing_balance' => -5000,
        ]);

        $user = User::factory()->customerAdmin()->create(['customer_id' => $customer->id]);

        $response = $this->actingAs($user)->get(route('customer.reports.balance-sheet', ['company' => $ctx['connection']->id]));

        $response->assertOk();
        $response->assertSee('Petty Cash');
        $response->assertSee('Vendor Payable');
    }

    public function test_receivables_and_payables_only_list_their_own_group(): void
    {
        $customer = Customer::factory()->create();
        $ctx = $this->companyWithGroups($customer);

        Ledger::factory()->create([
            'customer_id' => $customer->id, 'tally_company_id' => $ctx['companyId'],
            'name' => 'Acme Corp', 'ledger_group_tally_guid' => 'g-debtors', 'closing_balance' => 12000,
        ]);
        Ledger::factory()->create([
            'customer_id' => $customer->id, 'tally_company_id' => $ctx['companyId'],
            'name' => 'Steel Supplier', 'ledger_group_tally_guid' => 'g-creditors', 'closing_balance' => -8000,
        ]);

        $user = User::factory()->customerAdmin()->create(['customer_id' => $customer->id]);

        $receivables = $this->actingAs($user)->get(route('customer.reports.receivables', ['company' => $ctx['connection']->id]));
        $receivables->assertOk()->assertSee('Acme Corp')->assertDontSee('Steel Supplier');

        $payables = $this->actingAs($user)->get(route('customer.reports.payables', ['company' => $ctx['connection']->id]));
        $payables->assertOk()->assertSee('Steel Supplier')->assertDontSee('Acme Corp');
    }

    public function test_trial_balance_pdf_export_returns_a_pdf(): void
    {
        $customer = Customer::factory()->create();
        $connection = TallyCompanyConnection::factory()->create(['customer_id' => $customer->id]);
        Ledger::factory()->create(['customer_id' => $customer->id, 'tally_company_id' => $connection->tally_company_id, 'name' => 'Cash']);

        $user = User::factory()->customerAdmin()->create(['customer_id' => $customer->id]);

        $response = $this->actingAs($user)->get(
            route('customer.reports.trial-balance', ['company' => $connection->id, 'export' => 'pdf'])
        );

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_gst_report_page_is_honest_about_missing_data_instead_of_showing_numbers(): void
    {
        $customer = Customer::factory()->create();
        $connection = TallyCompanyConnection::factory()->create(['customer_id' => $customer->id]);
        $user = User::factory()->customerAdmin()->create(['customer_id' => $customer->id]);

        $response = $this->actingAs($user)->get(route('customer.reports.gst', ['company' => $connection->id]));

        $response->assertOk();
        $response->assertSee('Not available yet');
    }
}
