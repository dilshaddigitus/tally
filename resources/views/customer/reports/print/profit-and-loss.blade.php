<!doctype html>
<html>
<head>
<meta charset="utf-8">
<style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #222; }
    h1 { font-size: 16px; margin-bottom: 4px; }
    .muted { color: #666; margin-bottom: 16px; }
    table { width: 48%; border-collapse: collapse; display: inline-block; vertical-align: top; }
    th, td { padding: 4px 6px; border-bottom: 1px solid #ddd; text-align: left; }
    th { background: #f2f2f2; }
    .text-end { text-align: right; }
    .total { font-weight: bold; }
    .net { margin-top: 16px; font-weight: bold; font-size: 13px; }
</style>
</head>
<body>
    <h1>Profit &amp; Loss</h1>
    <div class="muted">{{ $connection->tallyCompany->name }} &middot; as of {{ now()->format('d M Y') }}</div>

    <table>
        <thead><tr><th colspan="2">Income</th></tr></thead>
        <tbody>
            @foreach ($data['income'] as $ledger)
                <tr><td>{{ $ledger->name }}</td><td class="text-end">{{ number_format(abs($ledger->closing_balance), 2) }}</td></tr>
            @endforeach
            <tr class="total"><td>Total Income</td><td class="text-end">{{ number_format($data['total_income'], 2) }}</td></tr>
        </tbody>
    </table>

    <table>
        <thead><tr><th colspan="2">Expense</th></tr></thead>
        <tbody>
            @foreach ($data['expense'] as $ledger)
                <tr><td>{{ $ledger->name }}</td><td class="text-end">{{ number_format(abs($ledger->closing_balance), 2) }}</td></tr>
            @endforeach
            <tr class="total"><td>Total Expense</td><td class="text-end">{{ number_format($data['total_expense'], 2) }}</td></tr>
        </tbody>
    </table>

    <div class="net">{{ $data['net_profit'] >= 0 ? 'Net Profit' : 'Net Loss' }}: {{ number_format(abs($data['net_profit']), 2) }}</div>
</body>
</html>
