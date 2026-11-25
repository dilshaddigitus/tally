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
</style>
</head>
<body>
    <h1>Balance Sheet</h1>
    <div class="muted">{{ $connection->tallyCompany->name }} &middot; as of {{ now()->format('d M Y') }}</div>

    <table>
        <thead><tr><th colspan="2">Assets</th></tr></thead>
        <tbody>
            @foreach ($data['assets'] as $ledger)
                <tr><td>{{ $ledger->name }}</td><td class="text-end">{{ number_format($ledger->closing_balance, 2) }}</td></tr>
            @endforeach
            <tr class="total"><td>Total Assets</td><td class="text-end">{{ number_format($data['total_assets'], 2) }}</td></tr>
        </tbody>
    </table>

    <table>
        <thead><tr><th colspan="2">Liabilities</th></tr></thead>
        <tbody>
            @foreach ($data['liabilities'] as $ledger)
                <tr><td>{{ $ledger->name }}</td><td class="text-end">{{ number_format(abs($ledger->closing_balance), 2) }}</td></tr>
            @endforeach
            <tr class="total"><td>Total Liabilities</td><td class="text-end">{{ number_format($data['total_liabilities'], 2) }}</td></tr>
        </tbody>
    </table>
</body>
</html>
