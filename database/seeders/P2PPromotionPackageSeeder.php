<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\P2P\PromotionPackage;
use Illuminate\Database\Seeder;

class P2PPromotionPackageSeeder extends Seeder
{
    public function run(): void
    {
        $baseCurrency = (string) (siteCurrency('code') ?: config('app.default_currency', 'USD'));
        $baseCurrency = strtoupper(trim($baseCurrency));

        $packages = [
            [
                'name'               => 'Starter Boost',
                'price'              => 2.50,
                'base_currency'      => $baseCurrency,
                'duration_minutes'   => 720,
                'sort_order'         => 1,
                'visibility'         => 'PUBLIC',
                'billing_type'       => 'FIXED',
                'daily_price'        => null,
                'per_trade_fee'      => null,
                'auto_renew_allowed' => false,
                'features'           => [
                    'featured_listing' => true,
                    'highlighted_card' => true,
                ],
                'accent_color'                  => 'BLUE',
                'search_priority'               => 15,
                'applies_to'                    => 'BOTH',
                'allowed_categories'            => ['ALL'],
                'max_active_per_user'           => 1,
                'max_impressions'               => 3500,
                'cooldown_after_expiry_minutes' => 180,
                'status'                        => true,
            ],
            [
                'name'               => 'Buyer Priority',
                'price'              => 3.90,
                'base_currency'      => $baseCurrency,
                'duration_minutes'   => 1440,
                'sort_order'         => 2,
                'visibility'         => 'PUBLIC',
                'billing_type'       => 'FIXED',
                'daily_price'        => null,
                'per_trade_fee'      => null,
                'auto_renew_allowed' => true,
                'features'           => [
                    'featured_listing'      => true,
                    'search_priority_boost' => true,
                    'featured_badge'        => true,
                ],
                'accent_color'                  => 'BLUE',
                'search_priority'               => 28,
                'applies_to'                    => 'BUY',
                'allowed_categories'            => ['CRYPTO', 'LOCAL_PAYMENT'],
                'max_active_per_user'           => 2,
                'max_impressions'               => 7000,
                'cooldown_after_expiry_minutes' => 120,
                'status'                        => true,
            ],
            [
                'name'               => 'Seller Spotlight',
                'price'              => 4.80,
                'base_currency'      => $baseCurrency,
                'duration_minutes'   => 1440,
                'sort_order'         => 3,
                'visibility'         => 'PUBLIC',
                'billing_type'       => 'FIXED',
                'daily_price'        => null,
                'per_trade_fee'      => null,
                'auto_renew_allowed' => true,
                'features'           => [
                    'highlighted_card' => true,
                    'featured_badge'   => true,
                ],
                'accent_color'                  => 'GOLD',
                'search_priority'               => 40,
                'applies_to'                    => 'SELL',
                'allowed_categories'            => ['CRYPTO', 'GIFT_CARD'],
                'max_active_per_user'           => 2,
                'max_impressions'               => 9000,
                'cooldown_after_expiry_minutes' => 90,
                'status'                        => true,
            ],
            [
                'name'               => 'Marketplace Daily Reach',
                'price'              => 0.0,
                'base_currency'      => $baseCurrency,
                'duration_minutes'   => 4320,
                'sort_order'         => 4,
                'visibility'         => 'PUBLIC',
                'billing_type'       => 'DAILY_PRICE',
                'daily_price'        => 3.25,
                'per_trade_fee'      => null,
                'auto_renew_allowed' => true,
                'features'           => [
                    'featured_listing'      => true,
                    'highlighted_card'      => true,
                    'search_priority_boost' => true,
                ],
                'accent_color'                  => 'BLUE',
                'search_priority'               => 52,
                'applies_to'                    => 'BOTH',
                'allowed_categories'            => ['ALL'],
                'max_active_per_user'           => 3,
                'max_impressions'               => 18000,
                'cooldown_after_expiry_minutes' => 60,
                'status'                        => true,
            ],
            [
                'name'               => 'High Volume Trader Fee Saver',
                'price'              => 0.0,
                'base_currency'      => $baseCurrency,
                'duration_minutes'   => 10080,
                'sort_order'         => 5,
                'visibility'         => 'PUBLIC',
                'billing_type'       => 'PER_TRADE_FEE',
                'daily_price'        => null,
                'per_trade_fee'      => 0.35,
                'auto_renew_allowed' => true,
                'features'           => [
                    'search_priority_boost' => true,
                    'featured_badge'        => true,
                ],
                'accent_color'                  => 'RED',
                'search_priority'               => 65,
                'applies_to'                    => 'BOTH',
                'allowed_categories'            => ['CRYPTO', 'LOCAL_PAYMENT', 'GIFT_CARD'],
                'max_active_per_user'           => 5,
                'max_impressions'               => 30000,
                'cooldown_after_expiry_minutes' => 30,
                'status'                        => true,
            ],
            [
                'name'               => 'Elite Marketplace Dominance',
                'price'              => 14.90,
                'base_currency'      => $baseCurrency,
                'duration_minutes'   => 4320,
                'sort_order'         => 6,
                'visibility'         => 'PUBLIC',
                'billing_type'       => 'FIXED',
                'daily_price'        => null,
                'per_trade_fee'      => null,
                'auto_renew_allowed' => true,
                'features'           => [
                    'featured_listing'      => true,
                    'highlighted_card'      => true,
                    'search_priority_boost' => true,
                    'featured_badge'        => true,
                ],
                'accent_color'                  => 'GOLD',
                'search_priority'               => 95,
                'applies_to'                    => 'BOTH',
                'allowed_categories'            => ['ALL'],
                'max_active_per_user'           => 2,
                'max_impressions'               => 75000,
                'cooldown_after_expiry_minutes' => 15,
                'status'                        => true,
            ],
        ];

        foreach ($packages as $package) {
            PromotionPackage::query()->updateOrCreate(
                ['name' => $package['name']],
                $package
            );
        }
    }
}
