@extends('layouts.customer')

@section('title', 'Cost Centre Summary')

@section('content')
    <h1 class="h3 mb-4">Cost Centre Summary &mdash; {{ $connection->tallyCompany->name }}</h1>

    @if ($rows->isEmpty())
        <div class="alert alert-info">
            No cost-centre-tagged entries synced yet. Voucher-entry extraction from Tally's nested XML (ledger-wise
            allocations, including cost centres) is a Phase 6 follow-up — see the connector project's notes.
        </div>
    @else
        <div class="card">
            <table class="table table-hover mb-0">
                <thead><tr><th>Cost Centre</th><th class="text-end">Debit</th><th class="text-end">Credit</th></tr></thead>
                <tbody>
                    @foreach ($rows as $row)
                        <tr>
                            <td>{{ $row['name'] }}</td>
                            <td class="text-end">{{ number_format($row['debit'], 2) }}</td>
                            <td class="text-end">{{ number_format($row['credit'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
@endsection
