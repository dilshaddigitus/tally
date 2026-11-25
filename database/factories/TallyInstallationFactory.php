<?php

namespace Database\Factories;

use App\Models\ConnectorDevice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TallyInstallation>
 */
class TallyInstallationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'connector_device_id' => ConnectorDevice::factory(),
            'detected_version' => 'TallyPrime 4.1',
            'os_info' => 'Windows 11',
        ];
    }
}
