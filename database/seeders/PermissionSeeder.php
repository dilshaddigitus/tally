<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            [Permission::VIEW_DASHBOARD, 'View dashboard'],
            [Permission::VIEW_SALES, 'View sales'],
            [Permission::VIEW_PURCHASES, 'View purchases'],
            [Permission::VIEW_INVENTORY, 'View inventory'],
            [Permission::VIEW_LEDGERS, 'View ledgers'],
            [Permission::VIEW_FINANCIAL_REPORTS, 'View financial reports'],
            [Permission::EXPORT_REPORTS, 'Export reports'],
        ];

        foreach ($permissions as [$key, $label]) {
            Permission::updateOrCreate(['key' => $key], ['label' => $label]);
        }
    }
}
