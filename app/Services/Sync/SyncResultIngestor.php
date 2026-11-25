<?php

namespace App\Services\Sync;

use App\Models\CostCategory;
use App\Models\CostCentre;
use App\Models\Ledger;
use App\Models\LedgerGroup;
use App\Models\StockBalance;
use App\Models\StockGroup;
use App\Models\StockItem;
use App\Models\SyncJob;
use App\Models\TallyCompany;
use App\Models\Voucher;
use App\Models\VoucherEntry;
use App\Models\VoucherType;
use Illuminate\Support\Facades\DB;

/**
 * Stores data the connector has already pulled from Tally and normalized
 * client-side. Every row here carries the job's customer_id and
 * tally_company_id, and is keyed on (tally_company_id, tally_guid) — never
 * a name or voucher number alone (brief §12).
 */
class SyncResultIngestor
{
    public function ingestLedgers(SyncJob $job, array $payload): array
    {
        $customerId = $job->customer_id;
        $companyId = $job->tally_company_id;
        $counts = ['inserted' => 0, 'updated' => 0];

        DB::transaction(function () use ($payload, $customerId, $companyId, &$counts) {
            foreach ($payload['ledger_groups'] ?? [] as $row) {
                $this->upsert(LedgerGroup::class, $customerId, $companyId, $row, $counts);
            }

            foreach ($payload['voucher_types'] ?? [] as $row) {
                $this->upsert(VoucherType::class, $customerId, $companyId, $row, $counts);
            }

            foreach ($payload['cost_centres'] ?? [] as $row) {
                $this->upsert(CostCentre::class, $customerId, $companyId, $row, $counts);
            }

            foreach ($payload['cost_categories'] ?? [] as $row) {
                $this->upsert(CostCategory::class, $customerId, $companyId, $row, $counts);
            }

            foreach ($payload['ledgers'] ?? [] as $row) {
                $this->upsert(Ledger::class, $customerId, $companyId, $row, $counts);
            }
        });

        return $counts;
    }

    public function ingestVouchers(SyncJob $job, array $payload): array
    {
        $customerId = $job->customer_id;
        $companyId = $job->tally_company_id;
        $counts = ['inserted' => 0, 'updated' => 0];

        DB::transaction(function () use ($payload, $customerId, $companyId, &$counts) {
            foreach ($payload['vouchers'] ?? [] as $row) {
                $entries = $row['entries'] ?? [];
                unset($row['entries']);

                $existed = Voucher::where('tally_company_id', $companyId)
                    ->where('tally_guid', $row['tally_guid'])->exists();

                $voucher = Voucher::updateOrCreate(
                    ['tally_company_id' => $companyId, 'tally_guid' => $row['tally_guid']],
                    $row + ['customer_id' => $customerId]
                );

                $counts[$existed ? 'updated' : 'inserted']++;

                $voucher->entries()->delete();
                foreach ($entries as $entry) {
                    VoucherEntry::create($entry + ['customer_id' => $customerId, 'voucher_id' => $voucher->id]);
                }
            }
        });

        return $counts;
    }

    public function ingestInventory(SyncJob $job, array $payload): array
    {
        $customerId = $job->customer_id;
        $companyId = $job->tally_company_id;
        $counts = ['inserted' => 0, 'updated' => 0];

        DB::transaction(function () use ($payload, $customerId, $companyId, &$counts) {
            foreach ($payload['stock_groups'] ?? [] as $row) {
                $this->upsert(StockGroup::class, $customerId, $companyId, $row, $counts);
            }

            foreach ($payload['stock_items'] ?? [] as $row) {
                $this->upsert(StockItem::class, $customerId, $companyId, $row, $counts);
            }

            foreach ($payload['stock_balances'] ?? [] as $row) {
                $stockItem = StockItem::where('tally_company_id', $companyId)
                    ->where('tally_guid', $row['stock_item_tally_guid'])
                    ->first();

                if ($stockItem === null) {
                    continue; // the item itself failed to sync — skip its balance rather than orphan it
                }

                StockBalance::updateOrCreate(
                    [
                        'stock_item_id' => $stockItem->id,
                        'godown' => $row['godown'] ?? null,
                        'as_of_date' => $row['as_of_date'],
                    ],
                    [
                        'customer_id' => $customerId,
                        'quantity' => $row['quantity'] ?? 0,
                        'rate' => $row['rate'] ?? null,
                        'value' => $row['value'] ?? null,
                    ]
                );
                $counts['inserted']++;
            }
        });

        return $counts;
    }

    public function ingestCompany(SyncJob $job, array $payload): array
    {
        $updates = array_filter([
            'name' => $payload['name'] ?? null,
            'financial_year_start' => $payload['financial_year_start'] ?? null,
        ], fn ($value) => $value !== null);

        if ($updates !== []) {
            TallyCompany::where('id', $job->tally_company_id)->update($updates);
        }

        return ['inserted' => 0, 'updated' => 1];
    }

    private function upsert(string $modelClass, int $customerId, int $companyId, array $row, array &$counts): void
    {
        $existed = $modelClass::where('tally_company_id', $companyId)
            ->where('tally_guid', $row['tally_guid'])->exists();

        $modelClass::updateOrCreate(
            ['tally_company_id' => $companyId, 'tally_guid' => $row['tally_guid']],
            $row + ['customer_id' => $customerId]
        );

        $counts[$existed ? 'updated' : 'inserted']++;
    }
}
