<?php

namespace Database\Seeders;

use App\Models\Plugin;
use Illuminate\Database\Seeder;

/**
 * Seed mobile recharge driver credentials into the existing `plugins`
 * table (type = "mobile_recharge"). Only API credentials live here; the
 * matching business rules (fee, limits, default flag) live in
 * `mobile_recharge_providers` seeded by MobileRechargeProviderSeeder.
 */
class MobileRechargePluginSeeder extends Seeder
{
    public function run(): void
    {
        $plugins = [
            [
                'name'        => 'Sandbox (Testing)',
                'code'        => 'sandbox',
                'logo'        => 'general/static/plugins/sandbox-recharge.svg',
                'description' => 'Local sandbox driver. Use it to verify wallet flows without contacting any real provider.',
                'status'      => 1,
                'credentials' => [
                    'sandbox_status' => 'completed',
                ],
            ],
            [
                'name'        => 'Generic HTTP API',
                'code'        => 'http',
                'logo'        => 'general/static/plugins/http-recharge.svg',
                'description' => 'Bring your own provider that exposes a REST endpoint and bearer-token auth.',
                'status'      => 0,
                'credentials' => [
                    'base_url' => null,
                    'endpoint' => '/recharges',
                    'token'    => null,
                    'timeout'  => 15,
                ],
            ],
            [
                'name'        => 'Reloadly (Global Airtime)',
                'code'        => 'reloadly',
                'logo'        => 'general/static/plugins/reloadly-recharge.svg',
                'description' => 'Global airtime aggregator covering 180+ countries. Supports both sandbox and production environments.',
                'status'      => 0,
                'credentials' => [
                    'client_id'     => null,
                    'client_secret' => null,
                    'sandbox'       => true,
                    'timeout'       => 20,
                ],
            ],
        ];

        foreach ($plugins as $payload) {
            Plugin::query()->updateOrCreate(
                ['code' => $payload['code']],
                [
                    'type'        => Plugin::TYPE_MOBILE_RECHARGE,
                    'name'        => $payload['name'],
                    'logo'        => $payload['logo'],
                    'description' => $payload['description'],
                    'credentials' => json_encode($payload['credentials']),
                    'status'      => $payload['status'],
                ],
            );
        }
    }
}
