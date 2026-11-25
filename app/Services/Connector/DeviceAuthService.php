<?php

namespace App\Services\Connector;

use App\Models\ConnectorDevice;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DeviceAuthService
{
    private const ACCESS_TOKEN_TTL_MINUTES = 60;
    private const REFRESH_TOKEN_TTL_DAYS = 30;

    /**
     * Issues a fresh access + refresh token pair, replacing any existing one.
     * Only hashes are persisted; the plain values are returned once, to the
     * caller that will hand them straight to the connector over HTTPS.
     */
    public function issueTokenPair(ConnectorDevice $device): array
    {
        $accessToken = Str::random(64);
        $refreshToken = Str::random(64);

        $device->update([
            'access_token_hash' => hash('sha256', $accessToken),
            'refresh_token_hash' => hash('sha256', $refreshToken),
            'token_expires_at' => now()->addMinutes(self::ACCESS_TOKEN_TTL_MINUTES),
        ]);

        return [$accessToken, $refreshToken];
    }

    public function findByAccessToken(string $token): ?ConnectorDevice
    {
        return ConnectorDevice::anyTenant()
            ->whereNotNull('access_token_hash')
            ->where('access_token_hash', hash('sha256', $token))
            ->whereNull('revoked_at')
            ->first();
    }

    /**
     * Refresh tokens are single-use: a stale refresh token (after a rotation it
     * didn't initiate) fails here, cutting off a cloned/stolen credential.
     */
    public function refresh(ConnectorDevice $device, string $refreshToken): ?array
    {
        if (! hash_equals((string) $device->refresh_token_hash, hash('sha256', $refreshToken))) {
            return null;
        }

        return $this->issueTokenPair($device);
    }

    public function revoke(ConnectorDevice $device): void
    {
        $device->update([
            'access_token_hash' => null,
            'refresh_token_hash' => null,
            'token_expires_at' => null,
            'revoked_at' => now(),
            'status' => 'device_revoked',
        ]);
    }
}
