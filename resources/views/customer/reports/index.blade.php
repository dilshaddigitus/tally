@extends('layouts.customer')

@section('title', 'Reports')

@section('content')
    <h1 class="h3 mb-4">Reports</h1>

    @if ($connections->isEmpty())
        <div class="alert alert-info">No authorized company yet — connect one from the Devices page first.</div>
    @else
        <form method="GET" class="mb-4" style="max-width: 360px;">
            <label class="form-label">Company</label>
            <select name="company" class="form-select" onchange="this.form.submit()">
                @foreach ($connections as $connection)
                    <option value="{{ $connection->id }}" @selected($selected?->id === $connection->id)>
                        {{ $connection->tallyCompany->name }}
                    </option>
                @endforeach
            </select>
        </form>

        @if ($selected)
            <div class="list-group" style="max-width: 480px;">
                <a class="list-group-item list-group-item-action" href="{{ route('customer.reports.trial-balance', ['company' => $selected->id]) }}">Trial Balance</a>
                <a class="list-group-item list-group-item-action" href="{{ route('customer.reports.profit-and-loss', ['company' => $selected->id]) }}">Profit &amp; Loss</a>
                <a class="list-group-item list-group-item-action" href="{{ route('customer.reports.balance-sheet', ['company' => $selected->id]) }}">Balance Sheet</a>
                <a class="list-group-item list-group-item-action" href="{{ route('customer.reports.day-book', ['company' => $selected->id]) }}">Day Book</a>
                <a class="list-group-item list-group-item-action" href="{{ route('customer.reports.cash-book', ['company' => $selected->id]) }}">Cash Book</a>
                <a class="list-group-item list-group-item-action" href="{{ route('customer.reports.bank-book', ['company' => $selected->id]) }}">Bank Book</a>
                <a class="list-group-item list-group-item-action" href="{{ route('customer.reports.sales-register', ['company' => $selected->id]) }}">Sales Register</a>
                <a class="list-group-item list-group-item-action" href="{{ route('customer.reports.purchase-register', ['company' => $selected->id]) }}">Purchase Register</a>
                <a class="list-group-item list-group-item-action" href="{{ route('customer.reports.receivables', ['company' => $selected->id]) }}">Receivables</a>
                <a class="list-group-item list-group-item-action" href="{{ route('customer.reports.payables', ['company' => $selected->id]) }}">Payables</a>
                <a class="list-group-item list-group-item-action" href="{{ route('customer.reports.stock-summary', ['company' => $selected->id]) }}">Stock Summary</a>
                <a class="list-group-item list-group-item-action" href="{{ route('customer.reports.cost-centre-summary', ['company' => $selected->id]) }}">Cost Centre Summary</a>
                <a class="list-group-item list-group-item-action" href="{{ route('customer.reports.gst', ['company' => $selected->id]) }}">GST Reports</a>
            </div>
        @endif
    @endif
@endsection
