<?php

namespace App\Services\Reporting;

use App\Models\LedgerGroup;
use Illuminate\Support\Collection;

/**
 * Classifies a ledger group by walking up its parent chain to one of Tally's
 * ~28 standard primary groups — these names are long-stable and documented,
 * unlike the per-report XML field names flagged elsewhere in this project.
 * A customer who has renamed or restructured their primary groups will not
 * classify correctly here; that's a real limitation, not an oversight.
 */
class LedgerGroupClassifier
{
    private const BALANCE_SHEET_PRIMARY_GROUPS = [
        'Capital Account', 'Reserves & Surplus', 'Loans (Liability)',
        'Secured Loans', 'Unsecured Loans', 'Current Liabilities',
        'Duties & Taxes', 'Provisions', 'Sundry Creditors',
        'Fixed Assets', 'Investments', 'Current Assets',
        'Bank Accounts', 'Cash-in-Hand', 'Deposits (Asset)',
        'Loans & Advances (Asset)', 'Stock-in-Hand', 'Sundry Debtors',
        'Branch / Divisions', 'Misc. Expenses (ASSET)', 'Suspense A/c',
    ];

    private const PROFIT_AND_LOSS_PRIMARY_GROUPS = [
        'Sales Accounts', 'Purchase Accounts', 'Direct Incomes',
        'Indirect Incomes', 'Direct Expenses', 'Indirect Expenses',
    ];

    private const RECEIVABLES_GROUP = 'Sundry Debtors';
    private const PAYABLES_GROUP = 'Sundry Creditors';
    private const CASH_GROUP = 'Cash-in-Hand';
    private const BANK_GROUP = 'Bank Accounts';

    private const MAX_DEPTH = 20;

    public function __construct(private readonly Collection $groupsByGuid)
    {
    }

    public static function forCompany(int $tallyCompanyId): self
    {
        return new self(LedgerGroup::where('tally_company_id', $tallyCompanyId)->get()->keyBy('tally_guid'));
    }

    public function rootGroupName(?string $groupGuidOrName): ?string
    {
        if ($groupGuidOrName === null || $groupGuidOrName === '') {
            return null;
        }

        $current = $this->groupsByGuid->get($groupGuidOrName);
        $depth = 0;
        $lastName = $current?->name ?? $groupGuidOrName;

        while ($current !== null && $current->parent_tally_guid !== null && $depth < self::MAX_DEPTH) {
            $parent = $this->groupsByGuid->get($current->parent_tally_guid);

            if ($parent === null) {
                break;
            }

            $current = $parent;
            $lastName = $parent->name;
            $depth++;
        }

        return $lastName;
    }

    public function isBalanceSheet(?string $groupGuidOrName): bool
    {
        return in_array($this->rootGroupName($groupGuidOrName), self::BALANCE_SHEET_PRIMARY_GROUPS, true);
    }

    public function isProfitAndLoss(?string $groupGuidOrName): bool
    {
        return in_array($this->rootGroupName($groupGuidOrName), self::PROFIT_AND_LOSS_PRIMARY_GROUPS, true);
    }

    public function isReceivable(?string $groupGuidOrName): bool
    {
        return $this->rootGroupName($groupGuidOrName) === self::RECEIVABLES_GROUP;
    }

    public function isPayable(?string $groupGuidOrName): bool
    {
        return $this->rootGroupName($groupGuidOrName) === self::PAYABLES_GROUP;
    }

    public function isCashOrBank(?string $groupGuidOrName): bool
    {
        $root = $this->rootGroupName($groupGuidOrName);

        return $root === self::CASH_GROUP || $root === self::BANK_GROUP;
    }

    public function isCash(?string $groupGuidOrName): bool
    {
        return $this->rootGroupName($groupGuidOrName) === self::CASH_GROUP;
    }

    public function isBank(?string $groupGuidOrName): bool
    {
        return $this->rootGroupName($groupGuidOrName) === self::BANK_GROUP;
    }

    public function isIncome(?string $groupGuidOrName): bool
    {
        return in_array($this->rootGroupName($groupGuidOrName), ['Sales Accounts', 'Direct Incomes', 'Indirect Incomes'], true);
    }

    public function isExpense(?string $groupGuidOrName): bool
    {
        return in_array($this->rootGroupName($groupGuidOrName), ['Purchase Accounts', 'Direct Expenses', 'Indirect Expenses'], true);
    }
}
