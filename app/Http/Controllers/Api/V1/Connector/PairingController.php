<?php

namespace App\Http\Controllers\Api\V1\Connector;

use App\Http\Controllers\Controller;
use App\Services\Pairing\DevicePairingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PairingController extends Controller
{
    public function __construct(private readonly DevicePairingService $pairing)
    {
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:20'],
            'device_name' => ['required', 'string', 'max:255'],
        ]);

        $result = $this->pairing->pair($data['code'], $data['device_name']);

        if ($result === null) {
            return response()->json(['message' => 'Pairing code is invalid, expired or already used.'], 422);
        }

        [$device, $accessToken, $refreshToken] = $result;

        return response()->json([
            'device_id' => $device->id,
            'status' => $device->status,
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'expires_at' => $device->token_expires_at,
        ], 201);
    }
}
