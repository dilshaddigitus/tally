@extends('layouts.customer')

@section('title', 'Balance Sheet')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Balance Sheet &mdash; {{ $connection->tallyCompany->name }}</h1>
        <a class="btn btn-outline-secondary btn-sm" href="{{ route('customer.reports.balance-sheet', ['company' => $connection->id, 'export' => 'pdf']) }}" target="_blank">Export PDF</a>
    </div>

    <div class="alert alert-warning small">
        Computed from synced ledger balances; the asset/liability split assumes Tally's usual balance-sign convention, which is unverified on real data &mdash; see the architecture notes.
        @unless($data['balances'])
            <strong>Assets and liabilities don't currently balance</strong> &mdash; a sign of incomplete sync data or a misclassified ledger group, not necessarily a real accounting error.
        @endunless
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Assets</div>
                <ul class="list-group list-group-flush">
                    @forelse ($data['assets'] as $ledger)
                        <li class="list-group-item d-flex justify-content-between">
                            <span>{{ $ledger->name }}</span>
                            <span>{{ number_format($ledger->closing_balance, 2) }}</span>
                        </li>
                    @empty
                        <li class="list-group-item text-muted">No asset ledgers found.</li>
                    @endforelse
                </ul>
                <div class="card-footer d-flex justify-content-between fw-bold">
                    <span>Total Assets</span><span>{{ number_format($data['total_assets'], 2) }}</span>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Liabilities</div>
                <ul class="list-group list-group-flush">
                    @forelse ($data['liabilities'] as $ledger)
                        <li class="list-group-item d-flex justify-content-between">
                            <span>{{ $ledger->name }}</span>
                            <span>{{ number_format(abs($ledger->closing_balance), 2) }}</span>
                        </li>
                    @empty
                        <li class="list-group-item text-muted">No liability ledgers found.</li>
                    @endforelse
                </ul>
                <div class="card-footer d-flex justify-content-between fw-bold">
                    <span>Total Liabilities</span><span>{{ number_format($data['total_liabilities'], 2) }}</span>
                </div>
            </div>
        </div>
    </div>
@endsection
