<?php

namespace App\Http\Controllers\Api\V1\Connector;

use App\Http\Controllers\Controller;
use App\Models\ConnectorDevice;
use App\Services\Connector\HeartbeatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HeartbeatController extends Controller
{
    public function __construct(private readonly HeartbeatService $heartbeat)
    {
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'status' => ['nullable', 'string'],
            'connector_version' => ['nullable', 'string', 'max:50'],
        ]);

        /** @var ConnectorDevice $device */
        $device = $request->attributes->get('connectorDevice');

        $device = $this->heartbeat->record($device, $data['status'] ?? null, $data['connector_version'] ?? null);

        return response()->json([
            'status' => $device->status,
            'server_time' => now()->toIso8601String(),
        ]);
    }
}
