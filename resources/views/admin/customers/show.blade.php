@extends('layouts.admin')

@section('title', $customer->name)

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">{{ $customer->name }}</h1>
        <div>
            <span class="badge {{ $customer->status === 'active' ? 'bg-success' : 'bg-secondary' }}">{{ $customer->status }}</span>
            <a href="{{ route('admin.customers.edit', $customer) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">Business</div>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item"><strong>Business name:</strong> {{ $customer->business_name ?: '—' }}</li>
                    <li class="list-group-item"><strong>Email:</strong> {{ $customer->email }}</li>
                    <li class="list-group-item"><strong>Phone:</strong> {{ $customer->phone ?: '—' }}</li>
                    <li class="list-group-item"><strong>Address:</strong> {{ $customer->address ?: '—' }}</li>
                    <li class="list-group-item"><strong>Tax ID:</strong> {{ $customer->profile?->tax_id ?: '—' }}</li>
                </ul>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    Connector pairing
                    <form method="POST" action="{{ route('admin.customers.pairing-codes.store', $customer) }}">
                        @csrf
                        <button class="btn btn-sm btn-primary">Generate pairing code</button>
                    </form>
                </div>
                <div class="card-body text-muted small">
                    Single-use, expires in 30 minutes. Share it with the customer to enter in the connector's setup screen.
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">Subscriptions</div>
                <ul class="list-group list-group-flush">
                    @forelse ($customer->subscriptions as $subscription)
                        <li class="list-group-item d-flex justify-content-between">
                            <span>{{ $subscription->plan->name }} ({{ $subscription->billing_type === 'one_time' ? 'one-time' : 'subscription' }})</span>
                            <span class="badge bg-{{ $subscription->status === 'active' ? 'success' : 'secondary' }}">{{ $subscription->status }}</span>
                        </li>
                    @empty
                        <li class="list-group-item text-muted">No subscription yet.</li>
                    @endforelse
                </ul>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header">Users</div>
                <ul class="list-group list-group-flush">
                    @forelse ($customer->users as $user)
                        <li class="list-group-item d-flex justify-content-between">
                            <span>{{ $user->name }} ({{ $user->email }})</span>
                            <span class="badge bg-info text-dark">{{ $user->role }}</span>
                        </li>
                    @empty
                        <li class="list-group-item text-muted">No users yet.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
@endsection
