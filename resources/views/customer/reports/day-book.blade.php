@extends('layouts.customer')

@section('title', 'Day Book')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Day Book &mdash; {{ $connection->tallyCompany->name }}</h1>
        <a class="btn btn-outline-secondary btn-sm" href="{{ route('customer.reports.day-book', array_merge(request()->query(), ['export' => 'csv'])) }}">Export CSV</a>
    </div>

    <form method="GET" class="row g-2 mb-3">
        <input type="hidden" name="company" value="{{ $connection->id }}">
        <div class="col-auto"><input type="date" name="from" value="{{ request('from') }}" class="form-control"></div>
        <div class="col-auto"><input type="date" name="to" value="{{ request('to') }}" class="form-control"></div>
        <div class="col-auto"><button class="btn btn-outline-primary">Filter</button></div>
    </form>

    <div class="card">
        <table class="table table-hover mb-0">
            <thead><tr><th>Date</th><th>Type</th><th>Number</th><th>Narration</th><th class="text-end">Amount</th></tr></thead>
            <tbody>
                @forelse ($rows as $voucher)
                    <tr>
                        <td>{{ $voucher->voucher_date->format('d-M-Y') }}</td>
                        <td>{{ $voucher->voucher_type }}</td>
                        <td>{{ $voucher->voucher_number }}</td>
                        <td>{{ $voucher->narration }}</td>
                        <td class="text-end">{{ number_format($voucher->total_amount, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">No vouchers synced yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
