<?php

namespace App\Services\Sync;

use App\Models\ConnectorDevice;
use App\Models\TallyCompany;
use App\Models\TallyInstallation;
use Illuminate\Support\Facades\DB;

class InstallationReportService
{
    /**
     * @param  array{detected_version?: string, os_info?: string}  $installationData
     * @param  array<int, array{tally_guid: string, name: string, financial_year_start?: string}>  $companies
     */
    public function report(ConnectorDevice $device, array $installationData, array $companies): TallyInstallation
    {
        return DB::transaction(function () use ($device, $installationData, $companies) {
            $installation = TallyInstallation::updateOrCreate(
                ['connector_device_id' => $device->id],
                [
                    'detected_version' => $installationData['detected_version'] ?? null,
                    'os_info' => $installationData['os_info'] ?? null,
                ]
            );

            foreach ($companies as $company) {
                TallyCompany::updateOrCreate(
                    [
                        'tally_installation_id' => $installation->id,
                        'tally_guid' => $company['tally_guid'],
                    ],
                    [
                        'name' => $company['name'],
                        'financial_year_start' => $company['financial_year_start'] ?? null,
                    ]
                );
            }

            return $installation;
        });
    }
}
