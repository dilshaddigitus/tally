<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCustomerRequest;
use App\Http\Requests\Admin\UpdateCustomerRequest;
use App\Models\Customer;
use App\Models\SubscriptionPlan;
use App\Services\CustomerOnboardingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function __construct(private readonly CustomerOnboardingService $onboarding)
    {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Customer::class);

        $customers = Customer::query()
            ->when($request->string('search')->toString(), fn ($query, $search) => $query
                ->where('name', 'like', "%{$search}%")
                ->orWhere('business_name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%"))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.customers.index', compact('customers'));
    }

    public function create(): View
    {
        $this->authorize('create', Customer::class);

        $plans = SubscriptionPlan::where('is_active', true)->get();

        return view('admin.customers.create', compact('plans'));
    }

    public function store(StoreCustomerRequest $request): RedirectResponse
    {
        $customer = $this->onboarding->register($request->validated());

        return redirect()
            ->route('admin.customers.show', $customer)
            ->with('status', "Customer created. Temporary admin password: {$customer->temporary_admin_password}");
    }

    public function show(Customer $customer): View
    {
        $this->authorize('view', $customer);

        $customer->load(['profile', 'subscriptions.plan', 'users']);

        return view('admin.customers.show', compact('customer'));
    }

    public function edit(Customer $customer): View
    {
        $this->authorize('update', $customer);

        return view('admin.customers.edit', compact('customer'));
    }

    public function update(UpdateCustomerRequest $request, Customer $customer): RedirectResponse
    {
        $customer->update($request->validated());

        return redirect()->route('admin.customers.show', $customer)->with('status', 'Customer updated.');
    }

    public function toggleStatus(Customer $customer): RedirectResponse
    {
        $this->authorize('toggleStatus', $customer);

        $this->onboarding->toggleStatus($customer);

        return back()->with('status', "Customer is now {$customer->fresh()->status}.");
    }
}
