<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\TallyCompany;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SyncJob>
 */
class SyncJobFactory extends Factory
{
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'tally_company_id' => TallyCompany::factory(),
            'job_type' => 'manual',
            'data_module' => 'ledgers',
            'status' => 'pending',
        ];
    }
}
