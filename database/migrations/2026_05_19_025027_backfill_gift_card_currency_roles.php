<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $voucherRoles = DB::table('currency_roles')
            ->where('role_name', 'voucher')
            ->get();

        foreach ($voucherRoles as $voucher) {
            $exists = DB::table('currency_roles')
                ->where('currency_id', $voucher->currency_id)
                ->where('role_name', 'gift_card')
                ->exists();

            if ($exists) {
                continue;
            }

            DB::table('currency_roles')->insert([
                'currency_id' => $voucher->currency_id,
                'role_name'   => 'gift_card',
                'min_limit'   => $voucher->min_limit,
                'max_limit'   => $voucher->max_limit,
                'fee_type'    => $voucher->fee_type ?: 'percent',
                'fee'         => $voucher->fee ?? 0,
                'is_active'   => $voucher->is_active,
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);
        }
    }

    public function down(): void
    {
        DB::table('currency_roles')->where('role_name', 'gift_card')->delete();
    }
};
