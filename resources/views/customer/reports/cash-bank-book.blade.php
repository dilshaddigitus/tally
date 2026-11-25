@extends('layouts.customer')

@section('title', $title)

@section('content')
    <h1 class="h3 mb-4">{{ $title }} &mdash; {{ $connection->tallyCompany->name }}</h1>

    @if ($ledgers->count() > 1)
        <form method="GET" class="mb-3" style="max-width: 320px;">
            <input type="hidden" name="company" value="{{ $connection->id }}">
            <select name="ledger" class="form-select" onchange="this.form.submit()">
                @foreach ($ledgers as $ledger)
                    <option value="{{ $ledger->id }}" @selected($selectedLedger?->id === $ledger->id)>{{ $ledger->name }}</option>
                @endforeach
            </select>
        </form>
    @endif

    @if ($selectedLedger === null)
        <div class="alert alert-info">No {{ strtolower($title) }} ledgers synced yet.</div>
    @else
        <div class="card">
            <div class="card-header">{{ $selectedLedger->name }}</div>
            <table class="table table-hover mb-0">
                <thead><tr><th>Date</th><th>Voucher</th><th class="text-end">Debit</th><th class="text-end">Credit</th></tr></thead>
                <tbody>
                    @forelse ($entries as $entry)
                        <tr>
                            <td>{{ $entry->voucher->voucher_date->format('d-M-Y') }}</td>
                            <td>{{ $entry->voucher->voucher_type }} {{ $entry->voucher->voucher_number }}</td>
                            <td class="text-end">{{ $entry->is_debit ? number_format($entry->amount, 2) : '' }}</td>
                            <td class="text-end">{{ ! $entry->is_debit ? number_format($entry->amount, 2) : '' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-4">No transactions yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif
@endsection
