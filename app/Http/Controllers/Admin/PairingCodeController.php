<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Services\Pairing\PairingCodeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PairingCodeController extends Controller
{
    public function __construct(private readonly PairingCodeService $pairingCodes)
    {
    }

    public function store(Request $request, Customer $customer): RedirectResponse
    {
        $this->authorize('view', $customer);

        [, $code] = $this->pairingCodes->generate($customer, $request->user());

        return back()->with('status', "Pairing code for {$customer->name}: {$code} — expires in 30 minutes, single use.");
    }
}
