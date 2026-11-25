@extends('layouts.customer')

@section('title', 'Trial Balance')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Trial Balance &mdash; {{ $connection->tallyCompany->name }}</h1>
        <a class="btn btn-outline-secondary btn-sm" href="{{ route('customer.reports.trial-balance', ['company' => $connection->id, 'export' => 'csv']) }}">Export CSV</a>
    </div>

    <div class="card">
        <table class="table table-hover mb-0">
            <thead><tr><th>Ledger</th><th class="text-end">Opening</th><th class="text-end">Closing</th></tr></thead>
            <tbody>
                @forelse ($rows as $row)
                    <tr>
                        <td>{{ $row->name }}</td>
                        <td class="text-end">{{ number_format($row->opening_balance, 2) }}</td>
                        <td class="text-end">{{ number_format($row->closing_balance, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="text-center text-muted py-4">No ledgers synced yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
