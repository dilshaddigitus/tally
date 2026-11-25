<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\TallyCompany;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TallyCompanyConnection>
 */
class TallyCompanyConnectionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'tally_company_id' => TallyCompany::factory(),
            'authorized_at' => now(),
        ];
    }
}
