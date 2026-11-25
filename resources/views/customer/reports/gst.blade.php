@extends('layouts.customer')

@section('title', 'GST Reports')

@section('content')
    <h1 class="h3 mb-4">GST Reports &mdash; {{ $connection->tallyCompany->name }}</h1>

    <div class="alert alert-warning">
        <strong>Not available yet.</strong> GST reports (GSTR-1/3B-style summaries, HSN-wise breakup, tax rate
        classification) need tax metadata &mdash; HSN/SAC codes, GST rate, place of supply, GSTIN &mdash; that
        nothing in the current sync pipeline captures. Showing a number here without that data would mean
        guessing at tax figures, which this project's own rules explicitly rule out (no fabricated financial
        figures).
    </div>

    <p class="text-muted">
        To build this for real: extend <code>voucher_entries</code> (or a new <code>voucher_tax_details</code>
        table) with HSN code, GST rate and taxable value per line, extend the Tally XML adapter to extract them
        from the voucher export, and confirm the exact field names against a real TallyPrime instance
        (Phase 6 scope, not done).
    </p>
@endsection
