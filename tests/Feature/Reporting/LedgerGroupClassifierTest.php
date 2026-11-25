<?php

namespace Tests\Feature\Reporting;

use App\Models\LedgerGroup;
use App\Models\TallyCompany;
use App\Services\Reporting\LedgerGroupClassifier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LedgerGroupClassifierTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_classifies_a_group_nested_under_a_primary_group(): void
    {
        $company = TallyCompany::factory()->create();

        $primary = LedgerGroup::factory()->create([
            'tally_company_id' => $company->id,
            'tally_guid' => 'guid-sundry-debtors',
            'name' => 'Sundry Debtors',
            'parent_tally_guid' => null,
        ]);

        $sub = LedgerGroup::factory()->create([
            'tally_company_id' => $company->id,
            'tally_guid' => 'guid-domestic-customers',
            'name' => 'Domestic Customers',
            'parent_tally_guid' => $primary->tally_guid,
        ]);

        $classifier = LedgerGroupClassifier::forCompany($company->id);

        $this->assertTrue($classifier->isReceivable($sub->tally_guid));
        $this->assertTrue($classifier->isBalanceSheet($sub->tally_guid));
        $this->assertFalse($classifier->isProfitAndLoss($sub->tally_guid));
        $this->assertFalse($classifier->isPayable($sub->tally_guid));
    }

    public function test_it_classifies_income_and_expense_groups(): void
    {
        $company = TallyCompany::factory()->create();

        $sales = LedgerGroup::factory()->create([
            'tally_company_id' => $company->id,
            'tally_guid' => 'guid-sales',
            'name' => 'Sales Accounts',
        ]);

        $purchase = LedgerGroup::factory()->create([
            'tally_company_id' => $company->id,
            'tally_guid' => 'guid-purchase',
            'name' => 'Purchase Accounts',
        ]);

        $classifier = LedgerGroupClassifier::forCompany($company->id);

        $this->assertTrue($classifier->isIncome($sales->tally_guid));
        $this->assertTrue($classifier->isProfitAndLoss($sales->tally_guid));
        $this->assertTrue($classifier->isExpense($purchase->tally_guid));
        $this->assertFalse($classifier->isIncome($purchase->tally_guid));
    }

    public function test_an_unknown_group_classifies_as_nothing(): void
    {
        $company = TallyCompany::factory()->create();
        $classifier = LedgerGroupClassifier::forCompany($company->id);

        $this->assertFalse($classifier->isBalanceSheet('nonexistent-guid'));
        $this->assertFalse($classifier->isProfitAndLoss(null));
    }

    public function test_it_does_not_loop_forever_on_a_circular_parent_chain(): void
    {
        $company = TallyCompany::factory()->create();

        $a = LedgerGroup::factory()->create([
            'tally_company_id' => $company->id,
            'tally_guid' => 'guid-a',
            'name' => 'A',
            'parent_tally_guid' => 'guid-b',
        ]);
        LedgerGroup::factory()->create([
            'tally_company_id' => $company->id,
            'tally_guid' => 'guid-b',
            'name' => 'B',
            'parent_tally_guid' => 'guid-a',
        ]);

        $classifier = LedgerGroupClassifier::forCompany($company->id);

        // Must terminate (MAX_DEPTH guard) rather than hang.
        $classifier->rootGroupName($a->tally_guid);
        $this->assertTrue(true);
    }
}
