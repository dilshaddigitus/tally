<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\TallyCompany;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Voucher>
 */
class VoucherFactory extends Factory
{
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'tally_company_id' => TallyCompany::factory(),
            'tally_guid' => (string) fake()->uuid(),
            'voucher_type' => 'Sales',
            'voucher_number' => fake()->bothify('V-####'),
            'voucher_date' => fake()->dateTimeBetween('-1 year')->format('Y-m-d'),
            'narration' => fake()->sentence(),
            'total_amount' => fake()->randomFloat(2, 100, 10000),
        ];
    }
}
