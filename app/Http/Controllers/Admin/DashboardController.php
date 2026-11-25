<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'totalCustomers' => Customer::count(),
            'activeCustomers' => Customer::where('status', 'active')->count(),
            'inactiveCustomers' => Customer::where('status', 'inactive')->count(),
            'recentCustomers' => Customer::latest()->take(5)->get(),
        ]);
    }
}
