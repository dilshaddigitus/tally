<?php

namespace App\Services\Reporting;

use App\Models\CostCentre;
use App\Models\Ledger;
use App\Models\VoucherEntry;
use Illuminate\Support\Collection;

/**
 * Computes statements from already-synced ledger balances rather than
 * fetching a Tally report directly — one source of truth, no reconciling
 * two different figures (brief §11, §16). The grouping logic below is
 * standard double-entry accounting; the risk is entirely in whether a given
 * customer's ledger-group hierarchy classifies the way LedgerGroupClassifier
 * expects (see its docblock) — treat output as indicative until checked
 * against the customer's own Tally reports on real data.
 */
class ReportBuilder
{
    public function profitAndLoss(int $tallyCompanyId): array
    {
        $classifier = LedgerGroupClassifier::forCompany($tallyCompanyId);
        $ledgers = Ledger::where('tally_company_id', $tallyCompanyId)->get();

        $income = $ledgers->filter(fn (Ledger $l) => $classifier->isIncome($l->ledger_group_tally_guid));
        $expense = $ledgers->filter(fn (Ledger $l) => $classifier->isExpense($l->ledger_group_tally_guid));

        $totalIncome = (float) $income->sum(fn (Ledger $l) => abs((float) $l->closing_balance));
        $totalExpense = (float) $expense->sum(fn (Ledger $l) => abs((float) $l->closing_balance));

        return [
            'income' => $income,
            'expense' => $expense,
            'total_income' => $totalIncome,
            'total_expense' => $totalExpense,
            'net_profit' => $totalIncome - $totalExpense,
        ];
    }

    public function balanceSheet(int $tallyCompanyId): array
    {
        $classifier = LedgerGroupClassifier::forCompany($tallyCompanyId);
        $ledgers = Ledger::where('tally_company_id', $tallyCompanyId)->get();

        $bsLedgers = $ledgers->filter(fn (Ledger $l) => $classifier->isBalanceSheet($l->ledger_group_tally_guid));

        // Tally convention (unverified on real data, see class docblock): asset
        // ledgers carry a positive closing balance, liabilities negative.
        $assets = $bsLedgers->filter(fn (Ledger $l) => (float) $l->closing_balance >= 0);
        $liabilities = $bsLedgers->filter(fn (Ledger $l) => (float) $l->closing_balance < 0);

        $totalAssets = (float) $assets->sum(fn (Ledger $l) => (float) $l->closing_balance);
        $totalLiabilities = (float) $liabilities->sum(fn (Ledger $l) => abs((float) $l->closing_balance));

        return [
            'assets' => $assets,
            'liabilities' => $liabilities,
            'total_assets' => $totalAssets,
            'total_liabilities' => $totalLiabilities,
            'balances' => abs($totalAssets - $totalLiabilities) < 0.01,
        ];
    }

    public function receivables(int $tallyCompanyId): Collection
    {
        $classifier = LedgerGroupClassifier::forCompany($tallyCompanyId);

        return Ledger::where('tally_company_id', $tallyCompanyId)->get()
            ->filter(fn (Ledger $l) => $classifier->isReceivable($l->ledger_group_tally_guid))
            ->filter(fn (Ledger $l) => (float) $l->closing_balance != 0.0)
            ->sortByDesc(fn (Ledger $l) => abs((float) $l->closing_balance))
            ->values();
    }

    public function payables(int $tallyCompanyId): Collection
    {
        $classifier = LedgerGroupClassifier::forCompany($tallyCompanyId);

        return Ledger::where('tally_company_id', $tallyCompanyId)->get()
            ->filter(fn (Ledger $l) => $classifier->isPayable($l->ledger_group_tally_guid))
            ->filter(fn (Ledger $l) => (float) $l->closing_balance != 0.0)
            ->sortByDesc(fn (Ledger $l) => abs((float) $l->closing_balance))
            ->values();
    }

    /**
     * @return Collection<int, Ledger> cash or bank ledgers for the book selector
     */
    public function cashAndBankLedgers(int $tallyCompanyId): Collection
    {
        $classifier = LedgerGroupClassifier::forCompany($tallyCompanyId);

        return Ledger::where('tally_company_id', $tallyCompanyId)->get()
            ->filter(fn (Ledger $l) => $classifier->isCashOrBank($l->ledger_group_tally_guid))
            ->values();
    }

    public function costCentreSummary(int $tallyCompanyId): Collection
    {
        $costCentres = CostCentre::where('tally_company_id', $tallyCompanyId)->get()->keyBy('tally_guid');

        return VoucherEntry::whereHas('voucher', fn ($q) => $q->where('tally_company_id', $tallyCompanyId))
            ->whereNotNull('cost_centre_tally_guid')
            ->get()
            ->groupBy('cost_centre_tally_guid')
            ->map(function (Collection $entries, string $guid) use ($costCentres) {
                return [
                    'name' => $costCentres->get($guid)?->name ?? $guid,
                    'debit' => (float) $entries->where('is_debit', true)->sum('amount'),
                    'credit' => (float) $entries->where('is_debit', false)->sum('amount'),
                ];
            })
            ->values();
    }
}
