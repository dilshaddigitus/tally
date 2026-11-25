<?php

namespace App\Http\Middleware;

use App\Services\Connector\DeviceAuthService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Connector endpoints are never reachable with a dashboard session — only a
 * device's own bearer token, bound to exactly one customer at pairing time
 * (brief §17: "Do not expose administrative endpoints to connector authentication").
 */
class AuthenticateConnectorDevice
{
    public function __construct(private readonly DeviceAuthService $deviceAuth)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if ($token === null) {
            return response()->json(['message' => 'Missing bearer token.'], 401);
        }

        $device = $this->deviceAuth->findByAccessToken($token);

        if ($device === null) {
            return response()->json(['message' => 'Invalid or expired token.'], 401);
        }

        if ($device->tokenExpired()) {
            return response()->json(['message' => 'Access token expired. Use /connector/refresh-token.'], 401);
        }

        $request->attributes->set('connectorDevice', $device);

        return $next($request);
    }
}
