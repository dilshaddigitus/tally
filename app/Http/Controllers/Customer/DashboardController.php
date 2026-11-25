<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $customer = $request->user()->customer()->with(['profile', 'activeSubscription.plan'])->firstOrFail();

        return view('customer.dashboard', compact('customer'));
    }
}
