<?php

namespace Database\Seeders;

use App\Models\MobileRechargeProvider;
use App\Models\Plugin;
use Illuminate\Database\Seeder;

/**
 * Seed business rules for mobile recharge providers (fees, limits,
 * supported regions, default flag). Linked to credentials stored in
 * the matching `plugins` row via plugin_id.
 */
class MobileRechargeProviderSeeder extends Seeder
{
    public function run(): void
    {
        $providers = [
            [
                'code'                 => 'sandbox',
                'name'                 => 'Sandbox (Testing)',
                'driver'               => 'sandbox',
                'logo'                 => 'general/static/plugins/sandbox-recharge.svg',
                'description'          => 'Local sandbox driver. Use it to verify wallet flows without contacting any real provider.',
                'status'               => true,
                'is_default'           => true,
                'supported_countries'  => null,
                'supported_currencies' => null,
                'fee_fixed'            => 0,
                'fee_percent'          => 0,
                'min_amount'           => 10,
                'max_amount'           => 10000,
                'config'               => [],
                'order'                => 1,
            ],
            [
                'code'                 => 'http',
                'name'                 => 'Generic HTTP API',
                'driver'               => 'http',
                'logo'                 => 'general/static/plugins/http-recharge.svg',
                'description'          => 'Bring your own provider that exposes a REST endpoint and bearer-token auth.',
                'status'               => false,
                'is_default'           => false,
                'supported_countries'  => null,
                'supported_currencies' => null,
                'fee_fixed'            => 0,
                'fee_percent'          => 0,
                'min_amount'           => 10,
                'max_amount'           => 10000,
                'config'               => [],
                'order'                => 2,
            ],
            [
                'code'                 => 'reloadly',
                'name'                 => 'Reloadly (Global Airtime)',
                'driver'               => 'reloadly',
                'logo'                 => 'general/static/plugins/reloadly-recharge.svg',
                'description'          => 'Global airtime aggregator covering 180+ countries. Supports both sandbox and production environments.',
                'status'               => false,
                'is_default'           => false,
                'supported_countries'  => null,
                'supported_currencies' => null,
                'fee_fixed'            => 0,
                'fee_percent'          => 2,
                'min_amount'           => 1,
                'max_amount'           => 50000,
                'config'               => [
                    'use_local_amount' => true,
                    'default_country'  => 'BD',
                ],
                'order' => 3,
            ],
        ];

        foreach ($providers as $payload) {
            $pluginId = Plugin::query()
                ->mobileRecharge()
                ->where('code', $payload['code'])
                ->value('id');

            MobileRechargeProvider::query()->updateOrCreate(
                ['code' => $payload['code']],
                array_merge($payload, ['plugin_id' => $pluginId]),
            );
        }
    }
}
