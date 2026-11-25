<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Ledger;
use App\Models\StockItem;
use App\Models\TallyCompanyConnection;
use App\Models\Voucher;
use App\Models\VoucherEntry;
use App\Services\Reporting\LedgerGroupClassifier;
use App\Services\Reporting\ReportBuilder;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function __construct(private readonly ReportBuilder $reports)
    {
    }

    public function index(Request $request): View
    {
        $connections = TallyCompanyConnection::with('tallyCompany')->get();

        return view('customer.reports.index', [
            'connections' => $connections,
            'selected' => $this->selectedConnection($request, $connections),
        ]);
    }

    public function trialBalance(Request $request): View|\Symfony\Component\HttpFoundation\StreamedResponse|\Symfony\Component\HttpFoundation\Response
    {
        $connection = $this->requireConnection($request);

        $rows = Ledger::where('tally_company_id', $connection->tally_company_id)
            ->orderBy('name')
            ->get(['name', 'opening_balance', 'closing_balance']);

        if ($request->get('export') === 'csv') {
            return $this->csv('trial-balance', ['Ledger', 'Opening Balance', 'Closing Balance'], $rows->map(
                fn ($r) => [$r->name, $r->opening_balance, $r->closing_balance]
            ));
        }

        if ($request->get('export') === 'pdf') {
            return $this->pdf('customer.reports.print.trial-balance', compact('connection', 'rows'), 'trial-balance');
        }

        return view('customer.reports.trial-balance', compact('connection', 'rows'));
    }

    public function profitAndLoss(Request $request): View|\Symfony\Component\HttpFoundation\Response
    {
        $connection = $this->requireConnection($request);
        $data = $this->reports->profitAndLoss($connection->tally_company_id);

        if ($request->get('export') === 'pdf') {
            return $this->pdf('customer.reports.print.profit-and-loss', compact('connection', 'data'), 'profit-and-loss');
        }

        return view('customer.reports.profit-and-loss', compact('connection', 'data'));
    }

    public function balanceSheet(Request $request): View|\Symfony\Component\HttpFoundation\Response
    {
        $connection = $this->requireConnection($request);
        $data = $this->reports->balanceSheet($connection->tally_company_id);

        if ($request->get('export') === 'pdf') {
            return $this->pdf('customer.reports.print.balance-sheet', compact('connection', 'data'), 'balance-sheet');
        }

        return view('customer.reports.balance-sheet', compact('connection', 'data'));
    }

    public function receivables(Request $request): View|\Symfony\Component\HttpFoundation\StreamedResponse
    {
        $connection = $this->requireConnection($request);
        $rows = $this->reports->receivables($connection->tally_company_id);

        if ($request->get('export') === 'csv') {
            return $this->csv('receivables', ['Customer', 'Outstanding'], $rows->map(fn ($l) => [$l->name, $l->closing_balance]));
        }

        return view('customer.reports.outstanding', ['connection' => $connection, 'rows' => $rows, 'title' => 'Receivables', 'partyLabel' => 'Customer']);
    }

    public function payables(Request $request): View|\Symfony\Component\HttpFoundation\StreamedResponse
    {
        $connection = $this->requireConnection($request);
        $rows = $this->reports->payables($connection->tally_company_id);

        if ($request->get('export') === 'csv') {
            return $this->csv('payables', ['Supplier', 'Outstanding'], $rows->map(fn ($l) => [$l->name, $l->closing_balance]));
        }

        return view('customer.reports.outstanding', ['connection' => $connection, 'rows' => $rows, 'title' => 'Payables', 'partyLabel' => 'Supplier']);
    }

    public function cashBook(Request $request): View
    {
        return $this->cashOrBankBook($request, onlyCash: true);
    }

    public function bankBook(Request $request): View
    {
        return $this->cashOrBankBook($request, onlyCash: false);
    }

    private function cashOrBankBook(Request $request, bool $onlyCash): View
    {
        $connection = $this->requireConnection($request);
        $ledgers = $this->reports->cashAndBankLedgers($connection->tally_company_id);
        $classifier = LedgerGroupClassifier::forCompany($connection->tally_company_id);
        $ledgers = $ledgers->filter(fn (Ledger $l) => $onlyCash
            ? $classifier->isCash($l->ledger_group_tally_guid)
            : $classifier->isBank($l->ledger_group_tally_guid));

        $selectedLedger = $request->filled('ledger')
            ? $ledgers->firstWhere('id', (int) $request->get('ledger'))
            : $ledgers->first();

        $entries = collect();
        if ($selectedLedger !== null) {
            $entries = VoucherEntry::where('ledger_tally_guid', $selectedLedger->tally_guid)
                ->whereHas('voucher', fn ($q) => $q->where('tally_company_id', $connection->tally_company_id))
                ->with('voucher')
                ->get()
                ->sortBy(fn ($e) => $e->voucher->voucher_date);
        }

        return view('customer.reports.cash-bank-book', [
            'connection' => $connection,
            'ledgers' => $ledgers,
            'selectedLedger' => $selectedLedger,
            'entries' => $entries,
            'title' => $onlyCash ? 'Cash Book' : 'Bank Book',
        ]);
    }

    public function costCentreSummary(Request $request): View
    {
        $connection = $this->requireConnection($request);
        $rows = $this->reports->costCentreSummary($connection->tally_company_id);

        return view('customer.reports.cost-centre-summary', compact('connection', 'rows'));
    }

    public function gst(Request $request): View
    {
        $connection = $this->requireConnection($request);

        return view('customer.reports.gst', compact('connection'));
    }

    public function dayBook(Request $request): View|\Symfony\Component\HttpFoundation\StreamedResponse
    {
        $connection = $this->requireConnection($request);
        $rows = $this->vouchersFor($connection, $request)->get();

        if ($request->get('export') === 'csv') {
            return $this->csv('day-book', ['Date', 'Type', 'Number', 'Narration', 'Amount'], $rows->map(
                fn ($v) => [$v->voucher_date->toDateString(), $v->voucher_type, $v->voucher_number, $v->narration, $v->total_amount]
            ));
        }

        return view('customer.reports.day-book', compact('connection', 'rows'));
    }

    public function salesRegister(Request $request): View|\Symfony\Component\HttpFoundation\StreamedResponse
    {
        return $this->registerFor($request, 'sales');
    }

    public function purchaseRegister(Request $request): View|\Symfony\Component\HttpFoundation\StreamedResponse
    {
        return $this->registerFor($request, 'purchase');
    }

    private function registerFor(Request $request, string $type): View|\Symfony\Component\HttpFoundation\StreamedResponse
    {
        $connection = $this->requireConnection($request);
        $rows = $this->vouchersFor($connection, $request)
            ->where('voucher_type', 'like', "%{$type}%")
            ->get();

        if ($request->get('export') === 'csv') {
            return $this->csv($type.'-register', ['Date', 'Number', 'Narration', 'Amount'], $rows->map(
                fn ($v) => [$v->voucher_date->toDateString(), $v->voucher_number, $v->narration, $v->total_amount]
            ));
        }

        return view('customer.reports.register', ['connection' => $connection, 'rows' => $rows, 'title' => ucfirst($type).' Register']);
    }

    public function stockSummary(Request $request): View|\Symfony\Component\HttpFoundation\StreamedResponse
    {
        $connection = $this->requireConnection($request);

        $items = StockItem::where('tally_company_id', $connection->tally_company_id)
            ->with(['balances' => fn ($q) => $q->latest('as_of_date')->limit(1)])
            ->orderBy('name')
            ->get();

        if ($request->get('export') === 'csv') {
            return $this->csv('stock-summary', ['Item', 'Unit', 'Quantity', 'Value'], $items->map(
                fn ($i) => [$i->name, $i->unit, $i->balances->first()?->quantity, $i->balances->first()?->value]
            ));
        }

        return view('customer.reports.stock-summary', compact('connection', 'items'));
    }

    public function ledgerStatement(Request $request, Ledger $ledger): View
    {
        abort_unless($ledger->customer_id === $request->user()->customer_id, 404);

        $voucherEntries = VoucherEntry::where('ledger_tally_guid', $ledger->tally_guid)
            ->whereHas('voucher', fn ($q) => $q->where('tally_company_id', $ledger->tally_company_id))
            ->with('voucher')
            ->get();

        return view('customer.reports.ledger-statement', compact('ledger', 'voucherEntries'));
    }

    private function vouchersFor(TallyCompanyConnection $connection, Request $request)
    {
        $query = Voucher::where('tally_company_id', $connection->tally_company_id)->orderBy('voucher_date');

        if ($request->filled('from')) {
            $query->whereDate('voucher_date', '>=', $request->date('from'));
        }
        if ($request->filled('to')) {
            $query->whereDate('voucher_date', '<=', $request->date('to'));
        }

        return $query;
    }

    private function selectedConnection(Request $request, $connections): ?TallyCompanyConnection
    {
        if ($request->filled('company')) {
            return $connections->firstWhere('id', (int) $request->get('company'));
        }

        return $connections->first();
    }

    private function requireConnection(Request $request): TallyCompanyConnection
    {
        $connection = TallyCompanyConnection::with('tallyCompany')->find($request->get('company'));
        abort_if($connection === null, 404, 'Select a company to run this report.');

        return $connection;
    }

    private function csv(string $filename, array $headers, $rows): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        return Response::streamDownload(function () use ($headers, $rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $headers);
            foreach ($rows as $row) {
                fputcsv($out, $row);
            }
            fclose($out);
        }, "{$filename}.csv", ['Content-Type' => 'text/csv']);
    }

    private function pdf(string $view, array $data, string $filename): \Symfony\Component\HttpFoundation\Response
    {
        $options = new Options();
        $options->set('isRemoteEnabled', false); // no external resource loading from report HTML

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml(view($view, $data)->render());
        $dompdf->setPaper('a4', 'portrait');
        $dompdf->render();

        return Response::make($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "inline; filename=\"{$filename}.pdf\"",
        ]);
    }
}
