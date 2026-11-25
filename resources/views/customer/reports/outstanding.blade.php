@extends('layouts.customer')

@section('title', $title)

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">{{ $title }} &mdash; {{ $connection->tallyCompany->name }}</h1>
        <a class="btn btn-outline-secondary btn-sm" href="{{ request()->fullUrlWithQuery(['export' => 'csv']) }}">Export CSV</a>
    </div>

    <div class="card">
        <table class="table table-hover mb-0">
            <thead><tr><th>{{ $partyLabel }}</th><th class="text-end">Outstanding</th></tr></thead>
            <tbody>
                @forelse ($rows as $ledger)
                    <tr>
                        <td><a href="{{ route('customer.reports.ledger-statement', $ledger) }}">{{ $ledger->name }}</a></td>
                        <td class="text-end">{{ number_format(abs($ledger->closing_balance), 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="2" class="text-center text-muted py-4">Nothing outstanding.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
