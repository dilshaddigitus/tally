@extends('layouts.customer')

@section('title', 'Profit & Loss')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Profit &amp; Loss &mdash; {{ $connection->tallyCompany->name }}</h1>
        <a class="btn btn-outline-secondary btn-sm" href="{{ route('customer.reports.profit-and-loss', ['company' => $connection->id, 'export' => 'pdf']) }}" target="_blank">Export PDF</a>
    </div>

    <div class="alert alert-warning small">
        Computed from synced ledger balances grouped by Tally's standard primary groups. Verify against Tally's own P&amp;L before relying on this for filing or decisions &mdash; see the architecture notes on unverified group classification.
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Income</div>
                <ul class="list-group list-group-flush">
                    @forelse ($data['income'] as $ledger)
                        <li class="list-group-item d-flex justify-content-between">
                            <span>{{ $ledger->name }}</span>
                            <span>{{ number_format(abs($ledger->closing_balance), 2) }}</span>
                        </li>
                    @empty
                        <li class="list-group-item text-muted">No income ledgers found.</li>
                    @endforelse
                </ul>
                <div class="card-footer d-flex justify-content-between fw-bold">
                    <span>Total Income</span><span>{{ number_format($data['total_income'], 2) }}</span>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Expense</div>
                <ul class="list-group list-group-flush">
                    @forelse ($data['expense'] as $ledger)
                        <li class="list-group-item d-flex justify-content-between">
                            <span>{{ $ledger->name }}</span>
                            <span>{{ number_format(abs($ledger->closing_balance), 2) }}</span>
                        </li>
                    @empty
                        <li class="list-group-item text-muted">No expense ledgers found.</li>
                    @endforelse
                </ul>
                <div class="card-footer d-flex justify-content-between fw-bold">
                    <span>Total Expense</span><span>{{ number_format($data['total_expense'], 2) }}</span>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="alert {{ $data['net_profit'] >= 0 ? 'alert-success' : 'alert-danger' }} d-flex justify-content-between fw-bold">
                <span>{{ $data['net_profit'] >= 0 ? 'Net Profit' : 'Net Loss' }}</span>
                <span>{{ number_format(abs($data['net_profit']), 2) }}</span>
            </div>
        </div>
    </div>
@endsection
