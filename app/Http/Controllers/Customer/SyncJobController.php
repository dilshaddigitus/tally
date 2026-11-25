<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\SyncJob;
use App\Models\TallyCompanyConnection;
use App\Services\Sync\SyncJobDispatcher;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SyncJobController extends Controller
{
    public function __construct(private readonly SyncJobDispatcher $dispatcher)
    {
    }

    public function index(): View
    {
        $jobs = SyncJob::with('tallyCompany')->latest()->paginate(25);
        $connections = TallyCompanyConnection::with('tallyCompany')->get();

        return view('customer.sync-jobs.index', compact('jobs', 'connections'));
    }

    public function store(TallyCompanyConnection $connection): RedirectResponse
    {
        abort_unless($connection->customer_id === request()->user()->customer_id, 404);

        $this->dispatcher->dispatchManualSync($connection);

        return back()->with('status', 'Manual sync requested — the connector will pick it up on its next poll.');
    }
}
