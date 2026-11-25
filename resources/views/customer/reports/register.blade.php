@extends('layouts.customer')

@section('title', $title)

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">{{ $title }} &mdash; {{ $connection->tallyCompany->name }}</h1>
        <a class="btn btn-outline-secondary btn-sm" href="{{ request()->fullUrlWithQuery(['export' => 'csv']) }}">Export CSV</a>
    </div>

    <div class="card">
        <table class="table table-hover mb-0">
            <thead><tr><th>Date</th><th>Number</th><th>Narration</th><th class="text-end">Amount</th></tr></thead>
            <tbody>
                @forelse ($rows as $voucher)
                    <tr>
                        <td>{{ $voucher->voucher_date->format('d-M-Y') }}</td>
                        <td>{{ $voucher->voucher_number }}</td>
                        <td>{{ $voucher->narration }}</td>
                        <td class="text-end">{{ number_format($voucher->total_amount, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted py-4">No vouchers in this register yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
