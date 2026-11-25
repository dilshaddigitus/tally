<?php

namespace App\Http\Controllers\Api\V1\Connector;

use App\Http\Controllers\Controller;
use App\Models\ConnectorDevice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StatusController extends Controller
{
    private const VALID_STATUSES = [
        'tally_not_running',
        'tally_service_unavailable',
        'no_company_loaded',
        'company_not_authorized',
        'internet_disconnected',
        'synchronizing',
        'sync_failed',
        'connected',
    ];

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'status' => ['required', 'string', 'in:'.implode(',', self::VALID_STATUSES)],
        ]);

        /** @var ConnectorDevice $device */
        $device = $request->attributes->get('connectorDevice');

        // Idempotent: pushing the same status twice is a no-op (brief §17).
        if ($device->status !== $data['status']) {
            $device->update(['status' => $data['status']]);
        }

        return response()->json(['status' => $device->fresh()->status]);
    }
}
