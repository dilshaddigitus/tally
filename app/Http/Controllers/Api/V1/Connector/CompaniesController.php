<?php

namespace App\Http\Controllers\Api\V1\Connector;

use App\Http\Controllers\Controller;
use App\Models\ConnectorDevice;
use App\Services\Sync\InstallationReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompaniesController extends Controller
{
    public function __construct(private readonly InstallationReportService $installations)
    {
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'detected_version' => ['nullable', 'string', 'max:50'],
            'os_info' => ['nullable', 'string', 'max:255'],
            'companies' => ['present', 'array'],
            'companies.*.tally_guid' => ['required', 'string', 'max:100'],
            'companies.*.name' => ['required', 'string', 'max:255'],
            'companies.*.financial_year_start' => ['nullable', 'date'],
        ]);

        /** @var ConnectorDevice $device */
        $device = $request->attributes->get('connectorDevice');

        $installation = $this->installations->report(
            $device,
            ['detected_version' => $data['detected_version'] ?? null, 'os_info' => $data['os_info'] ?? null],
            $data['companies']
        );

        return response()->json([
            'installation_id' => $installation->id,
            'companies' => $installation->companies()->get(['id', 'tally_guid', 'name'])
                ->map(fn ($c) => [
                    'id' => $c->id,
                    'tally_guid' => $c->tally_guid,
                    'name' => $c->name,
                    'authorized' => $c->isAuthorized(),
                ]),
        ]);
    }
}
