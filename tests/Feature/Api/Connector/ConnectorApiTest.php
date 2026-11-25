<?php

namespace Tests\Feature\Api\Connector;

use App\Models\Customer;
use App\Models\TallyCompanyConnection;
use App\Services\Pairing\PairingCodeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConnectorApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_pairing_to_sync_lifecycle(): void
    {
        $customer = Customer::factory()->create();
        [$pairing, $plainCode] = app(PairingCodeService::class)->generate($customer);

        // 1. Pair
        $pairResponse = $this->postJson('/api/v1/connector/pair', [
            'code' => $plainCode,
            'device_name' => 'ACCOUNTS-PC',
        ]);
        $pairResponse->assertCreated();
        $accessToken = $pairResponse->json('access_token');
        $refreshToken = $pairResponse->json('refresh_token');
        $this->assertNotEmpty($accessToken);
        $this->assertDatabaseHas('connector_pairings', ['id' => $pairing->id, 'used_at' => null === null ? $pairing->fresh()->used_at : null]);
        $this->assertNotNull($pairing->fresh()->used_at);

        // reusing the same code must fail
        $this->postJson('/api/v1/connector/pair', ['code' => $plainCode, 'device_name' => 'OTHER-PC'])
            ->assertStatus(422);

        // 2. Heartbeat
        $this->withHeader('Authorization', "Bearer {$accessToken}")
            ->postJson('/api/v1/connector/heartbeat', ['connector_version' => '1.0.0'])
            ->assertOk()
            ->assertJson(['status' => 'connected']);

        $this->assertDatabaseHas('connector_devices', ['device_name' => 'ACCOUNTS-PC', 'status' => 'connected']);

        // 3. Report companies
        $companiesResponse = $this->withHeader('Authorization', "Bearer {$accessToken}")
            ->postJson('/api/v1/connector/companies', [
                'detected_version' => 'TallyPrime 4.1',
                'companies' => [
                    ['tally_guid' => 'guid-co-1', 'name' => 'Acme Traders'],
                ],
            ])
            ->assertOk();

        $companyId = $companiesResponse->json('companies.0.id');
        $this->assertFalse($companiesResponse->json('companies.0.authorized'));

        // 4. Customer Admin authorizes the company (web route) — dispatches sync jobs
        $customerAdmin = \App\Models\User::factory()->customerAdmin()->create(['customer_id' => $customer->id]);
        $this->actingAs($customerAdmin)
            ->post(route('customer.companies.authorize', $companyId))
            ->assertRedirect();

        $this->assertDatabaseHas('tally_company_connections', ['customer_id' => $customer->id, 'tally_company_id' => $companyId]);
        $this->assertDatabaseCount('sync_jobs', 4); // company, ledgers, vouchers, inventory

        // 5. Connector polls for pending jobs
        $pending = $this->withHeader('Authorization', "Bearer {$accessToken}")
            ->getJson('/api/v1/connector/jobs/pending')
            ->assertOk();

        $ledgerJob = collect($pending->json('jobs'))->firstWhere('data_module', 'ledgers');
        $this->assertNotNull($ledgerJob);

        // 6. Claim it
        $this->withHeader('Authorization', "Bearer {$accessToken}")
            ->postJson("/api/v1/connector/jobs/{$ledgerJob['id']}/start")
            ->assertOk()
            ->assertJson(['status' => 'running']);

        // a second claim attempt must be rejected (idempotency)
        $this->withHeader('Authorization', "Bearer {$accessToken}")
            ->postJson("/api/v1/connector/jobs/{$ledgerJob['id']}/start")
            ->assertStatus(409);

        // 7. Upload results
        $this->withHeader('Authorization', "Bearer {$accessToken}")
            ->postJson("/api/v1/connector/jobs/{$ledgerJob['id']}/complete", [
                'data' => [
                    'ledgers' => [
                        ['tally_guid' => 'guid-ledger-1', 'name' => 'Cash', 'opening_balance' => 1000],
                    ],
                ],
            ])
            ->assertOk()
            ->assertJson(['status' => 'completed']);

        $this->assertDatabaseHas('ledgers', ['tally_guid' => 'guid-ledger-1', 'name' => 'Cash', 'customer_id' => $customer->id]);
        $this->assertDatabaseHas('sync_jobs', ['id' => $ledgerJob['id'], 'status' => 'completed']);
        $this->assertDatabaseHas('sync_logs', ['sync_job_id' => $ledgerJob['id'], 'records_inserted' => 1]);
    }

    public function test_invalid_pairing_code_is_rejected(): void
    {
        $this->postJson('/api/v1/connector/pair', ['code' => 'NOPE-NOPE-NOPE', 'device_name' => 'X'])
            ->assertStatus(422);
    }

    public function test_expired_pairing_code_is_rejected(): void
    {
        $customer = Customer::factory()->create();
        [$pairing, $plainCode] = app(PairingCodeService::class)->generate($customer);
        $pairing->update(['expires_at' => now()->subMinute()]);

        $this->postJson('/api/v1/connector/pair', ['code' => $plainCode, 'device_name' => 'X'])
            ->assertStatus(422);
    }

    public function test_heartbeat_requires_a_valid_token(): void
    {
        $this->postJson('/api/v1/connector/heartbeat', [])->assertStatus(401);
        $this->withHeader('Authorization', 'Bearer not-a-real-token')
            ->postJson('/api/v1/connector/heartbeat', [])
            ->assertStatus(401);
    }

    public function test_a_revoked_device_cannot_call_authenticated_endpoints(): void
    {
        $customer = Customer::factory()->create();
        [, $plainCode] = app(PairingCodeService::class)->generate($customer);

        $pair = $this->postJson('/api/v1/connector/pair', ['code' => $plainCode, 'device_name' => 'X'])->json();

        $this->withHeader('Authorization', "Bearer {$pair['access_token']}")
            ->postJson('/api/v1/connector/revoke')
            ->assertOk();

        $this->withHeader('Authorization', "Bearer {$pair['access_token']}")
            ->postJson('/api/v1/connector/heartbeat', [])
            ->assertStatus(401);
    }

    public function test_a_device_from_one_customer_cannot_see_or_touch_another_customers_jobs(): void
    {
        $customerA = Customer::factory()->create();
        $customerB = Customer::factory()->create();

        [, $codeA] = app(PairingCodeService::class)->generate($customerA);
        $deviceA = $this->postJson('/api/v1/connector/pair', ['code' => $codeA, 'device_name' => 'A'])->json();

        $connectionB = TallyCompanyConnection::factory()->create(['customer_id' => $customerB->id]);
        $jobB = \App\Models\SyncJob::factory()->create([
            'customer_id' => $customerB->id,
            'tally_company_id' => $connectionB->tally_company_id,
        ]);

        $pending = $this->withHeader('Authorization', "Bearer {$deviceA['access_token']}")
            ->getJson('/api/v1/connector/jobs/pending')->json('jobs');

        $this->assertEmpty(collect($pending)->where('id', $jobB->id));

        $this->withHeader('Authorization', "Bearer {$deviceA['access_token']}")
            ->postJson("/api/v1/connector/jobs/{$jobB->id}/start")
            ->assertStatus(404);
    }

    public function test_refresh_token_rotates_and_old_one_stops_working(): void
    {
        $customer = Customer::factory()->create();
        [, $plainCode] = app(PairingCodeService::class)->generate($customer);
        $pair = $this->postJson('/api/v1/connector/pair', ['code' => $plainCode, 'device_name' => 'X'])->json();

        $refreshed = $this->postJson('/api/v1/connector/refresh-token', [
            'device_id' => $pair['device_id'],
            'refresh_token' => $pair['refresh_token'],
        ])->assertOk()->json();

        $this->assertNotSame($pair['access_token'], $refreshed['access_token']);

        // old refresh token is now stale
        $this->postJson('/api/v1/connector/refresh-token', [
            'device_id' => $pair['device_id'],
            'refresh_token' => $pair['refresh_token'],
        ])->assertStatus(401);
    }
}
