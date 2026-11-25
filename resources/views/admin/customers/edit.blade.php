@extends('layouts.admin')

@section('title', 'Edit customer')

@section('content')
    <h1 class="h3 mb-4">Edit {{ $customer->name }}</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.customers.update', $customer) }}" class="card">
        <div class="card-body">
            @csrf
            @method('PUT')

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label">Contact name</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $customer->name) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Business name</label>
                    <input type="text" name="business_name" class="form-control" value="{{ old('business_name', $customer->business_name) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $customer->email) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone', $customer->phone) }}">
                </div>
                <div class="col-12">
                    <label class="form-label">Address</label>
                    <textarea name="address" class="form-control" rows="2">{{ old('address', $customer->address) }}</textarea>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Save changes</button>
            <a href="{{ route('admin.customers.show', $customer) }}" class="btn btn-link">Cancel</a>
        </div>
    </form>
@endsection
