<?php

namespace App\Services\Connector;

use App\Models\ConnectorDevice;

class HeartbeatService
{
    private const RECOGNIZED_STATUSES = [
        'connected',
        'tally_not_running',
        'tally_service_unavailable',
        'no_company_loaded',
        'company_not_authorized',
        'synchronizing',
        'sync_failed',
    ];

    public function record(ConnectorDevice $device, ?string $status, ?string $connectorVersion): ConnectorDevice
    {
        $device->update([
            'last_heartbeat_at' => now(),
            'status' => $this->resolveStatus($device, $status),
            'connector_version' => $connectorVersion ?? $device->connector_version,
        ]);

        return $device->fresh();
    }

    private function resolveStatus(ConnectorDevice $device, ?string $status): string
    {
        if ($status !== null && in_array($status, self::RECOGNIZED_STATUSES, true)) {
            return $status;
        }

        // Never let a successful heartbeat silently show a failed sync as "connected" —
        // brief §19: "Never show a successful synchronization status if the operation actually failed."
        return $device->status === 'device_revoked' ? $device->status : 'connected';
    }
}
