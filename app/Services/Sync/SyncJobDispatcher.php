<?php

namespace App\Services\Sync;

use App\Models\SyncJob;
use App\Models\TallyCompanyConnection;

class SyncJobDispatcher
{
    private const MODULES = ['company', 'ledgers', 'vouchers', 'inventory'];

    /**
     * One job per data module, so a failure in vouchers never blocks inventory,
     * and the connector can work through them independently (brief §13).
     */
    public function dispatch(TallyCompanyConnection $connection, string $jobType, array $dateRange = []): array
    {
        return array_map(
            fn (string $module) => SyncJob::create([
                'customer_id' => $connection->customer_id,
                'tally_company_id' => $connection->tally_company_id,
                'job_type' => $jobType,
                'data_module' => $module,
                'status' => 'pending',
                'date_from' => $dateRange['from'] ?? null,
                'date_to' => $dateRange['to'] ?? null,
            ]),
            self::MODULES
        );
    }

    public function dispatchFullSync(TallyCompanyConnection $connection): array
    {
        return $this->dispatch($connection, 'full');
    }

    public function dispatchManualSync(TallyCompanyConnection $connection): array
    {
        return $this->dispatch($connection, 'manual');
    }
}
