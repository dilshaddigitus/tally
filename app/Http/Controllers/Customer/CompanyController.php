<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\TallyCompany;
use App\Models\TallyCompanyConnection;
use App\Services\Sync\SyncJobDispatcher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function __construct(private readonly SyncJobDispatcher $dispatcher)
    {
    }

    public function connect(Request $request, TallyCompany $company): RedirectResponse
    {
        $this->assertBelongsToCurrentCustomer($company);

        $connection = TallyCompanyConnection::firstOrCreate(
            ['customer_id' => $request->user()->customer_id, 'tally_company_id' => $company->id],
            ['authorized_by_user_id' => $request->user()->id, 'authorized_at' => now()]
        );

        $this->dispatcher->dispatchFullSync($connection);

        return back()->with('status', "{$company->name} authorized — initial sync has started.");
    }

    public function disconnect(Request $request, TallyCompany $company): RedirectResponse
    {
        $this->assertBelongsToCurrentCustomer($company);

        TallyCompanyConnection::where('customer_id', $request->user()->customer_id)
            ->where('tally_company_id', $company->id)
            ->delete();

        return back()->with('status', "{$company->name} disconnected. Previously synced data is kept.");
    }

    /**
     * TallyCompany has no customer_id of its own (it's reached through the
     * device's installation), so its tenant check goes through the device.
     */
    private function assertBelongsToCurrentCustomer(TallyCompany $company): void
    {
        $company->loadMissing('installation.device');

        abort_unless(
            $company->installation?->device?->customer_id === request()->user()->customer_id,
            404
        );
    }
}
