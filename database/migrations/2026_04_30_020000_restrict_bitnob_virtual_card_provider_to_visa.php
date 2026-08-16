<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('virtual_card_providers')
            ->where('code', 'bitnob')
            ->update([
                'brand'                => 'Visa',
                'supported_networks'   => json_encode(['visa']),
                'supported_currencies' => json_encode(['USD']),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('virtual_card_providers')
            ->where('code', 'bitnob')
            ->update([
                'brand'                => 'Multi',
                'supported_networks'   => json_encode(['mastercard', 'visa']),
                'supported_currencies' => json_encode(['USD', 'NGN', 'KES', 'GHS']),
            ]);
    }
};
