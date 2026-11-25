@extends('layouts.customer')

@section('title', 'Dashboard')

@section('content')
    <h1 class="h3 mb-4">Welcome, {{ auth()->user()->name }}</h1>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">Your business</div>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item"><strong>Business name:</strong> {{ $customer->business_name ?: '—' }}</li>
                    <li class="list-group-item"><strong>Email:</strong> {{ $customer->email }}</li>
                </ul>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">Subscription</div>
                <div class="card-body">
                    @if ($customer->activeSubscription)
                        <p class="mb-1"><strong>Plan:</strong> {{ $customer->activeSubscription->plan->name }}</p>
                        <p class="mb-1"><strong>Billing:</strong> {{ $customer->activeSubscription->billing_type === 'one_time' ? 'One-time' : 'Recurring' }}</p>
                        <p class="mb-0"><strong>Status:</strong> <span class="badge bg-success">{{ $customer->activeSubscription->status }}</span></p>
                    @else
                        <p class="text-muted mb-0">No active subscription.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="alert alert-info mb-0">
                No Tally connector is paired yet. Connector pairing and device management arrive in the next phase.
            </div>
        </div>
    </div>
@endsection
