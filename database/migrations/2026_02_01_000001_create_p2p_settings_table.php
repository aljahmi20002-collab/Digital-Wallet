<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('p2p_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('enabled')->default(false);
            $table->decimal('maker_fee_pct', 8, 4)->default(0.2000);
            $table->decimal('taker_fee_pct', 8, 4)->default(0.4000);
            $table->unsignedInteger('order_expiry_minutes')->default(45);
            $table->unsignedInteger('dispute_window_minutes')->default(120);
            $table->decimal('min_amount', 18, 8)->default(1);
            $table->decimal('max_amount', 18, 8)->nullable();
            $table->string('allowed_countries')->nullable();
            $table->string('blocked_countries')->nullable();
            $table->timestamps();
        });

        $defaults = [
            'enabled'                => false,
            'maker_fee_pct'          => 0.2000,
            'taker_fee_pct'          => 0.4000,
            'order_expiry_minutes'   => 45,
            'dispute_window_minutes' => 120,
            'min_amount'             => 1,
            'max_amount'             => null,
            'allowed_countries'      => null,
            'blocked_countries'      => null,
        ];

        $old = [];
        if (Schema::hasTable('settings')) {
            $old = DB::table('settings')
                ->whereIn('key', [
                    'p2p_enabled',
                    'p2p_maker_fee_pct',
                    'p2p_taker_fee_pct',
                    'p2p_order_expiry_minutes',
                    'p2p_dispute_window_minutes',
                    'p2p_min_amount',
                    'p2p_max_amount',
                    'p2p_allowed_countries',
                    'p2p_blocked_countries',
                ])
                ->pluck('val', 'key')
                ->toArray();
        }

        $data = $defaults;
        if (isset($old['p2p_enabled'])) {
            $data['enabled'] = in_array(strtolower((string) $old['p2p_enabled']), ['1', 'true', 'yes', 'on'], true);
        }
        if (isset($old['p2p_maker_fee_pct'])) {
            $data['maker_fee_pct'] = (float) $old['p2p_maker_fee_pct'];
        }
        if (isset($old['p2p_taker_fee_pct'])) {
            $data['taker_fee_pct'] = (float) $old['p2p_taker_fee_pct'];
        }
        if (isset($old['p2p_order_expiry_minutes'])) {
            $data['order_expiry_minutes'] = (int) $old['p2p_order_expiry_minutes'];
        }
        if (isset($old['p2p_dispute_window_minutes'])) {
            $data['dispute_window_minutes'] = (int) $old['p2p_dispute_window_minutes'];
        }
        if (isset($old['p2p_min_amount'])) {
            $data['min_amount'] = (float) $old['p2p_min_amount'];
        }
        if (array_key_exists('p2p_max_amount', $old)) {
            $data['max_amount'] = $old['p2p_max_amount'] === '' ? null : (float) $old['p2p_max_amount'];
        }
        if (array_key_exists('p2p_allowed_countries', $old)) {
            $data['allowed_countries'] = $old['p2p_allowed_countries'] !== '' ? (string) $old['p2p_allowed_countries'] : null;
        }
        if (array_key_exists('p2p_blocked_countries', $old)) {
            $data['blocked_countries'] = $old['p2p_blocked_countries'] !== '' ? (string) $old['p2p_blocked_countries'] : null;
        }

        DB::table('p2p_settings')->insert(array_merge($data, [
            'created_at' => now(),
            'updated_at' => now(),
        ]));

        if (Schema::hasTable('settings')) {
            DB::table('settings')->whereIn('key', [
                'p2p_enabled',
                'p2p_maker_fee_pct',
                'p2p_taker_fee_pct',
                'p2p_order_expiry_minutes',
                'p2p_dispute_window_minutes',
                'p2p_min_amount',
                'p2p_max_amount',
                'p2p_allowed_countries',
                'p2p_blocked_countries',
            ])->delete();
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('p2p_settings');
    }
};
