<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ConnectorDevice;
use Illuminate\View\View;

class DeviceMonitorController extends Controller
{
    public function __invoke(): View
    {
        $devices = ConnectorDevice::anyTenant()
            ->with(['customer', 'tallyInstallation.companies'])
            ->latest('last_heartbeat_at')
            ->paginate(20);

        return view('admin.devices.index', compact('devices'));
    }
}
