<?php

namespace App\Http\Controllers\Api\V1\Connector;

use App\Http\Controllers\Controller;
use App\Models\ConnectorDevice;
use App\Models\SyncJob;
use App\Services\Sync\SyncResultIngestor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JobController extends Controller
{
    public function __construct(private readonly SyncResultIngestor $ingestor)
    {
    }

    public function pending(Request $request): JsonResponse
    {
        /** @var ConnectorDevice $device */
        $device = $request->attributes->get('connectorDevice');

        $jobs = SyncJob::where('customer_id', $device->customer_id)
            ->where('status', 'pending')
            ->whereHas('tallyCompany.companyConnection')
            ->orderBy('created_at')
            ->limit(20)
            ->get(['id', 'correlation_id', 'tally_company_id', 'job_type', 'data_module', 'date_from', 'date_to']);

        return response()->json(['jobs' => $jobs]);
    }

    public function start(Request $request, SyncJob $job): JsonResponse
    {
        /** @var ConnectorDevice $device */
        $device = $request->attributes->get('connectorDevice');

        $this->authorizeDeviceForJob($device, $job);

        $claimed = DB::table('sync_jobs')
            ->where('id', $job->id)
            ->where('status', 'pending')
            ->update([
                'status' => 'running',
                'connector_device_id' => $device->id,
                'claimed_at' => now(),
                'started_at' => now(),
                'attempt' => $job->attempt + 1,
                'updated_at' => now(),
            ]);

        if ($claimed === 0) {
            return response()->json(['message' => 'Job already claimed by another poll.'], 409);
        }

        return response()->json(['status' => 'running', 'correlation_id' => $job->correlation_id]);
    }

    public function complete(Request $request, SyncJob $job): JsonResponse
    {
        /** @var ConnectorDevice $device */
        $device = $request->attributes->get('connectorDevice');

        $this->authorizeDeviceForJob($device, $job);

        $data = $request->validate(['data' => ['present', 'array']]);

        $counts = match ($job->data_module) {
            'ledgers' => $this->ingestor->ingestLedgers($job, $data['data']),
            'vouchers' => $this->ingestor->ingestVouchers($job, $data['data']),
            'inventory' => $this->ingestor->ingestInventory($job, $data['data']),
            'company' => $this->ingestor->ingestCompany($job, $data['data']),
            default => ['inserted' => 0, 'updated' => 0],
        };

        $job->update(['status' => 'completed', 'completed_at' => now()]);
        $job->log()->create([
            'records_processed' => $counts['inserted'] + $counts['updated'],
            'records_inserted' => $counts['inserted'],
            'records_updated' => $counts['updated'],
            'records_skipped' => 0,
        ]);

        return response()->json(['status' => 'completed', 'counts' => $counts]);
    }

    public function fail(Request $request, SyncJob $job): JsonResponse
    {
        /** @var ConnectorDevice $device */
        $device = $request->attributes->get('connectorDevice');

        $this->authorizeDeviceForJob($device, $job);

        $data = $request->validate([
            'error_code' => ['required', 'string', 'max:100'],
            'message' => ['required', 'string'],
        ]);

        $job->errors()->create($data);

        // Capped retry with backoff: the connector re-polls after a delay it computes
        // itself from `attempt`; Laravel's only job here is to decide pending vs failed.
        $job->update(['status' => $job->canRetry() ? 'pending' : 'failed']);

        return response()->json(['status' => $job->status, 'attempt' => $job->attempt, 'max_attempts' => $job->max_attempts]);
    }

    private function authorizeDeviceForJob(ConnectorDevice $device, SyncJob $job): void
    {
        abort_unless($job->customer_id === $device->customer_id, 404);
    }
}
