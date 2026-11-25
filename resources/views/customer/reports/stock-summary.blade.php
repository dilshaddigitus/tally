@extends('layouts.customer')

@section('title', 'Stock Summary')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Stock Summary &mdash; {{ $connection->tallyCompany->name }}</h1>
        <a class="btn btn-outline-secondary btn-sm" href="{{ route('customer.reports.stock-summary', ['company' => $connection->id, 'export' => 'csv']) }}">Export CSV</a>
    </div>

    <div class="card">
        <table class="table table-hover mb-0">
            <thead><tr><th>Item</th><th>Unit</th><th class="text-end">Quantity</th><th class="text-end">Value</th></tr></thead>
            <tbody>
                @forelse ($items as $item)
                    <tr>
                        <td>{{ $item->name }}</td>
                        <td>{{ $item->unit }}</td>
                        <td class="text-end">{{ $item->balances->first()?->quantity }}</td>
                        <td class="text-end">{{ $item->balances->first()?->value !== null ? number_format($item->balances->first()->value, 2) : '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted py-4">No stock items synced yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
