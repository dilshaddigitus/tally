<!doctype html>
<html>
<head>
<meta charset="utf-8">
<style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #222; }
    h1 { font-size: 16px; margin-bottom: 4px; }
    .muted { color: #666; margin-bottom: 16px; }
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 4px 6px; border-bottom: 1px solid #ddd; text-align: left; }
    th { background: #f2f2f2; }
    .text-end { text-align: right; }
</style>
</head>
<body>
    <h1>Trial Balance</h1>
    <div class="muted">{{ $connection->tallyCompany->name }} &middot; as of {{ now()->format('d M Y') }}</div>
    <table>
        <thead><tr><th>Ledger</th><th class="text-end">Opening</th><th class="text-end">Closing</th></tr></thead>
        <tbody>
            @foreach ($rows as $row)
                <tr>
                    <td>{{ $row->name }}</td>
                    <td class="text-end">{{ number_format($row->opening_balance, 2) }}</td>
                    <td class="text-end">{{ number_format($row->closing_balance, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
