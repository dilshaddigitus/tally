<?php

namespace App\Http\Controllers\Api\V1\Connector;

use App\Http\Controllers\Controller;
use App\Models\ConnectorDevice;
use App\Services\Connector\DeviceAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TokenController extends Controller
{
    public function __construct(private readonly DeviceAuthService $deviceAuth)
    {
    }

    public function refresh(Request $request): JsonResponse
    {
        $data = $request->validate([
            'device_id' => ['required', 'integer'],
            'refresh_token' => ['required', 'string'],
        ]);

        $device = ConnectorDevice::anyTenant()->whereNull('revoked_at')->find($data['device_id']);

        if ($device === null) {
            return response()->json(['message' => 'Unknown or revoked device.'], 401);
        }

        $pair = $this->deviceAuth->refresh($device, $data['refresh_token']);

        if ($pair === null) {
            $device->update(['status' => 'authentication_expired']);

            return response()->json(['message' => 'Refresh token invalid. Device must re-pair.'], 401);
        }

        [$accessToken, $refreshToken] = $pair;

        return response()->json([
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'expires_at' => $device->fresh()->token_expires_at,
        ]);
    }

    public function revoke(Request $request): JsonResponse
    {
        /** @var ConnectorDevice $device */
        $device = $request->attributes->get('connectorDevice');

        $this->deviceAuth->revoke($device);

        return response()->json(['message' => 'Device revoked.']);
    }
}
