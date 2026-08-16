<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Adds provider-universal fields so a single cardholder profile can
 * satisfy issuance from any virtual-card provider (StroWallet,
 * Bitnob, Stripe, Marqeta, Adyen, Lithic, Galileo, etc.) without
 * forcing the user to re-enter data per provider.
 *
 * All columns are nullable and additive — no existing data is touched
 * and no downstream code path becomes invalid.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cardholders', function (Blueprint $table) {
            // Identity
            $table->string('title', 12)->nullable()->after('user_id');           // Mr / Ms / Dr / Mx
            $table->string('middle_name')->nullable()->after('first_name');
            $table->string('nationality', 10)->nullable()->after('dob');         // ISO-2 country code
            $table->string('place_of_birth')->nullable()->after('nationality');

            // Phone + E.164
            $table->string('phone_country_code', 8)->nullable()->after('mobile');

            // Government ID (used by Bitnob, Adyen, Lithic, etc.)
            $table->string('id_type', 32)->nullable()->after('country');         // passport / national_id / drivers_license / residence_permit
            $table->string('id_number')->nullable()->after('id_type');
            $table->string('id_issue_country', 10)->nullable()->after('id_number');
            $table->date('id_issue_date')->nullable()->after('id_issue_country');
            $table->date('id_expiry')->nullable()->after('id_issue_date');

            // Tax / fiscal
            $table->string('tax_id')->nullable()->after('id_expiry');            // SSN / ITIN / PAN / TIN
            $table->string('tax_country', 10)->nullable()->after('tax_id');

            // Employment + AML
            $table->string('occupation')->nullable()->after('tax_country');
            $table->string('employer')->nullable()->after('occupation');
            $table->decimal('annual_income', 14, 2)->nullable()->after('employer');
            $table->string('source_of_funds')->nullable()->after('annual_income'); // salary / business / investment / other

            // Compliance flags
            $table->boolean('pep_flag')->default(false)->after('source_of_funds');
            $table->boolean('sanctions_flag')->default(false)->after('pep_flag');
        });

        Schema::table('businesses', function (Blueprint $table) {
            $table->string('trading_name')->nullable()->after('business_name');     // DBA
            $table->date('incorporation_date')->nullable()->after('business_type');
            $table->string('incorporation_country', 10)->nullable()->after('incorporation_date');
            $table->string('industry')->nullable()->after('incorporation_country');
            $table->string('mcc_code', 8)->nullable()->after('industry');           // Merchant Category Code
            $table->string('website_url')->nullable()->after('mcc_code');
            $table->string('phone_country_code', 8)->nullable()->after('contact_phone');
            $table->json('beneficial_owners')->nullable()->after('documents');      // [{name, dob, ownership_pct, country, id_type, id_number}]
        });
    }

    public function down(): void
    {
        Schema::table('cardholders', function (Blueprint $table) {
            $table->dropColumn([
                'title', 'middle_name', 'nationality', 'place_of_birth',
                'phone_country_code',
                'id_type', 'id_number', 'id_issue_country', 'id_issue_date', 'id_expiry',
                'tax_id', 'tax_country',
                'occupation', 'employer', 'annual_income', 'source_of_funds',
                'pep_flag', 'sanctions_flag',
            ]);
        });

        Schema::table('businesses', function (Blueprint $table) {
            $table->dropColumn([
                'trading_name', 'incorporation_date', 'incorporation_country',
                'industry', 'mcc_code', 'website_url',
                'phone_country_code', 'beneficial_owners',
            ]);
        });
    }
};
