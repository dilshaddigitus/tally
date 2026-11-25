<?php

namespace App\Services\Pairing;

use App\Models\ConnectorDevice;
use App\Models\ConnectorPairing;
use App\Services\Connector\DeviceAuthService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DevicePairingService
{
    public function __construct(
        private readonly PairingCodeService $pairingCodes,
        private readonly DeviceAuthService $deviceAuth,
    ) {
    }

    /**
     * @return array{0: ConnectorDevice, 1: string, 2: string}|null device, access token, refresh token
     */
    public function pair(string $code, string $deviceName): ?array
    {
        $pairing = $this->pairingCodes->findByPlainCode($code);

        if ($pairing === null) {
            $this->recordFailedAttemptIfCodeExistsButWrong($code);

            return null;
        }

        return DB::transaction(function () use ($pairing, $deviceName) {
            $pairing->update(['used_at' => now()]);

            $device = ConnectorDevice::create([
                'customer_id' => $pairing->customer_id,
                'device_name' => $deviceName,
                'status' => 'awaiting_pairing',
            ]);

            [$accessToken, $refreshToken] = $this->deviceAuth->issueTokenPair($device);

            return [$device, $accessToken, $refreshToken];
        });
    }

    /**
     * A code that matches no live pairing might still match an expired/used one —
     * bump its attempt counter so repeated guesses against a real code are visible.
     */
    private function recordFailedAttemptIfCodeExistsButWrong(string $code): void
    {
        ConnectorPairing::query()
            ->latest()
            ->take(50)
            ->get()
            ->each(function (ConnectorPairing $pairing) use ($code) {
                if (Hash::check($code, $pairing->code_hash)) {
                    $pairing->increment('attempts');
                }
            });
    }
}
