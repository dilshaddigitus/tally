@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@section('content')
    <h1 class="h3 mb-4">Dashboard</h1>

    <div class="row g-3 mb-4">
        <div class="col-6 col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <div class="text-muted small">Total Customers</div>
                    <div class="fs-3 fw-bold">{{ $totalCustomers }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <div class="text-muted small">Active Customers</div>
                    <div class="fs-3 fw-bold text-success">{{ $activeCustomers }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <div class="text-muted small">Inactive Customers</div>
                    <div class="fs-3 fw-bold text-danger">{{ $inactiveCustomers }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Recently added customers</span>
            <a href="{{ route('admin.customers.index') }}" class="btn btn-sm btn-outline-primary">View all</a>
        </div>
        <ul class="list-group list-group-flush">
            @forelse ($recentCustomers as $customer)
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <a href="{{ route('admin.customers.show', $customer) }}">{{ $customer->name }}</a>
                    <span class="badge {{ $customer->status === 'active' ? 'bg-success' : 'bg-secondary' }}">{{ $customer->status }}</span>
                </li>
            @empty
                <li class="list-group-item text-muted">No customers yet.</li>
            @endforelse
        </ul>
    </div>
@endsection
