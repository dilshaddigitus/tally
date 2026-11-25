@extends('layouts.admin')

@section('title', 'Customers')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Customers</h1>
        <a href="{{ route('admin.customers.create') }}" class="btn btn-primary">Add customer</a>
    </div>

    <form method="GET" class="mb-3">
        <input type="search" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search by name, business or email">
    </form>

    <div class="card">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Business</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($customers as $customer)
                    <tr>
                        <td><a href="{{ route('admin.customers.show', $customer) }}">{{ $customer->name }}</a></td>
                        <td>{{ $customer->business_name }}</td>
                        <td>{{ $customer->email }}</td>
                        <td><span class="badge {{ $customer->status === 'active' ? 'bg-success' : 'bg-secondary' }}">{{ $customer->status }}</span></td>
                        <td class="text-end">
                            <a href="{{ route('admin.customers.edit', $customer) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                            <form method="POST" action="{{ route('admin.customers.toggle-status', $customer) }}" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <button class="btn btn-sm btn-outline-{{ $customer->status === 'active' ? 'danger' : 'success' }}">
                                    {{ $customer->status === 'active' ? 'Deactivate' : 'Activate' }}
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">No customers found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $customers->links() }}</div>
@endsection
