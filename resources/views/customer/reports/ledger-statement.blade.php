@extends('layouts.customer')

@section('title', 'Ledger Statement')

@section('content')
    <h1 class="h3 mb-4">Ledger Statement &mdash; {{ $ledger->name }}</h1>

    <div class="card">
        <table class="table table-hover mb-0">
            <thead><tr><th>Date</th><th>Voucher</th><th class="text-end">Debit</th><th class="text-end">Credit</th></tr></thead>
            <tbody>
                @forelse ($voucherEntries as $entry)
                    <tr>
                        <td>{{ $entry->voucher->voucher_date->format('d-M-Y') }}</td>
                        <td>{{ $entry->voucher->voucher_type }} {{ $entry->voucher->voucher_number }}</td>
                        <td class="text-end">{{ $entry->is_debit ? number_format($entry->amount, 2) : '' }}</td>
                        <td class="text-end">{{ ! $entry->is_debit ? number_format($entry->amount, 2) : '' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted py-4">No entries for this ledger yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
