<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\TallyCompany;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LedgerGroup>
 */
class LedgerGroupFactory extends Factory
{
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'tally_company_id' => TallyCompany::factory(),
            'tally_guid' => (string) fake()->uuid(),
            'name' => fake()->words(2, true),
            'parent_tally_guid' => null,
        ];
    }
}
