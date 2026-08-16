<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('p2p_promotion_packages', function (Blueprint $table) {
            $table->string('visibility', 20)->default('PUBLIC')->after('sort_order');
            $table->string('billing_type', 20)->default('FIXED')->after('visibility');
            $table->decimal('daily_price', 24, 8)->nullable()->after('billing_type');
            $table->decimal('per_trade_fee', 24, 8)->nullable()->after('daily_price');
            $table->boolean('auto_renew_allowed')->default(false)->after('per_trade_fee');

            $table->json('features')->nullable()->after('auto_renew_allowed');
            $table->string('accent_color', 20)->nullable()->after('features');
            $table->unsignedInteger('search_priority')->default(0)->after('accent_color');

            $table->string('applies_to', 10)->default('BOTH')->after('search_priority');
            $table->json('allowed_categories')->nullable()->after('applies_to');

            $table->unsignedInteger('max_active_per_user')->nullable()->after('allowed_categories');
            $table->unsignedBigInteger('max_impressions')->nullable()->after('max_active_per_user');
            $table->unsignedInteger('cooldown_after_expiry_minutes')->nullable()->after('max_impressions');

            $table->string('refund_policy', 40)->default('NON_REFUNDABLE')->after('cooldown_after_expiry_minutes');

            $table->index(['status', 'visibility', 'sort_order'], 'p2p_promo_pkg_status_visibility_sort');
            $table->index(['applies_to'], 'p2p_promo_pkg_applies_to');
        });
    }

    public function down(): void
    {
        Schema::table('p2p_promotion_packages', function (Blueprint $table) {
            $table->dropIndex('p2p_promo_pkg_status_visibility_sort');
            $table->dropIndex('p2p_promo_pkg_applies_to');

            $table->dropColumn([
                'visibility',
                'billing_type',
                'daily_price',
                'per_trade_fee',
                'auto_renew_allowed',
                'features',
                'accent_color',
                'search_priority',
                'applies_to',
                'allowed_categories',
                'max_active_per_user',
                'max_impressions',
                'cooldown_after_expiry_minutes',
                'refund_policy',
            ]);
        });
    }
};
