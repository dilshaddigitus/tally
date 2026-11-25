@extends('layouts.base')

@section('nav')
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="{{ route('admin.dashboard') }}">Tally Connector &middot; Super Admin</a>
            <div class="navbar-nav ms-auto flex-row gap-3 align-items-center">
                <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'fw-bold text-white' : 'text-white-50' }}" href="{{ route('admin.dashboard') }}">Dashboard</a>
                <a class="nav-link {{ request()->routeIs('admin.customers.*') ? 'fw-bold text-white' : 'text-white-50' }}" href="{{ route('admin.customers.index') }}">Customers</a>
                <a class="nav-link {{ request()->routeIs('admin.devices.*') ? 'fw-bold text-white' : 'text-white-50' }}" href="{{ route('admin.devices.index') }}">Devices</a>
                <span class="text-white-50">{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}" class="mb-0">
                    @csrf
                    <button class="btn btn-sm btn-outline-light">Logout</button>
                </form>
            </div>
        </div>
    </nav>
@endsection
