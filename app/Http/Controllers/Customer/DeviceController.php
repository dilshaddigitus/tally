<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\ConnectorDevice;
use Illuminate\View\View;

class DeviceController extends Controller
{
    public function index(): View
    {
        // No explicit customer_id filter needed — BelongsToCustomer scopes this
        // to the authenticated user's own tenant automatically.
        $devices = ConnectorDevice::with('tallyInstallation.companies.companyConnection')->latest()->get();

        return view('customer.devices.index', compact('devices'));
    }
}
