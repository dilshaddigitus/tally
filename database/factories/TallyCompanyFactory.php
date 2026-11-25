<?php

namespace Database\Factories;

use App\Models\TallyInstallation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TallyCompany>
 */
class TallyCompanyFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tally_installation_id' => TallyInstallation::factory(),
            'tally_guid' => (string) fake()->uuid(),
            'name' => fake()->company(),
        ];
    }
}
