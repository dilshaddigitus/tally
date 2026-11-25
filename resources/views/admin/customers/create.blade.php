@extends('layouts.admin')

@section('title', 'Add customer')

@section('content')
    <h1 class="h3 mb-4">Add customer</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.customers.store') }}" class="card">
        <div class="card-body">
            @csrf

            <h2 class="h6 text-uppercase text-muted">Business</h2>
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label">Contact name</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Business name</label>
                    <input type="text" name="business_name" class="form-control" value="{{ old('business_name') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
                </div>
                <div class="col-12">
                    <label class="form-label">Address</label>
                    <textarea name="address" class="form-control" rows="2">{{ old('address') }}</textarea>
                </div>
            </div>

            <h2 class="h6 text-uppercase text-muted">Subscription</h2>
            <div class="mb-3">
                <label class="form-label">Plan</label>
                <select name="subscription_plan_id" class="form-select" required>
                    <option value="">Select a plan&hellip;</option>
                    @foreach ($plans as $plan)
                        <option value="{{ $plan->id }}" @selected(old('subscription_plan_id') == $plan->id)>
                            {{ $plan->name }} &mdash; {{ $plan->currency }} {{ number_format($plan->price, 2) }}
                            ({{ $plan->billing_type === 'one_time' ? 'one-time' : 'per '.$plan->billing_interval }})
                        </option>
                    @endforeach
                </select>
            </div>

            <h2 class="h6 text-uppercase text-muted">Customer Admin user</h2>
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label">Admin name</label>
                    <input type="text" name="admin_name" class="form-control" value="{{ old('admin_name') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Admin email</label>
                    <input type="email" name="admin_email" class="form-control" value="{{ old('admin_email') }}" required>
                </div>
            </div>
            <p class="text-muted small">A temporary password is generated automatically and shown once after saving.</p>

            <button type="submit" class="btn btn-primary">Create customer</button>
            <a href="{{ route('admin.customers.index') }}" class="btn btn-link">Cancel</a>
        </div>
    </form>
@endsection
