@extends('layouts.base')

@section('nav')
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="{{ route('customer.dashboard') }}">Tally Connector</a>
            <div class="navbar-nav ms-auto flex-row gap-3 align-items-center">
                <a class="nav-link text-white" href="{{ route('customer.dashboard') }}">Dashboard</a>
                <a class="nav-link text-white" href="{{ route('customer.devices.index') }}">Devices</a>
                <a class="nav-link text-white" href="{{ route('customer.sync-jobs.index') }}">Sync History</a>
                <a class="nav-link text-white" href="{{ route('customer.reports.index') }}">Reports</a>
                <span class="text-white-50">{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}" class="mb-0">
                    @csrf
                    <button class="btn btn-sm btn-outline-light">Logout</button>
                </form>
            </div>
        </div>
    </nav>
@endsection
