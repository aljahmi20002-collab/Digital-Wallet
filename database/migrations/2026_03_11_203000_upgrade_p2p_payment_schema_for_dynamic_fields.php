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
        Schema::table('p2p_payment_methods', function (Blueprint $table) {
            if (! Schema::hasColumn('p2p_payment_methods', 'fields')) {
                $table->json('fields')->nullable()->after('instructions');
            }

            if (! Schema::hasColumn('p2p_payment_methods', 'sort_order')) {
                $table->unsignedInteger('sort_order')->default(0)->after('fields');
            }
        });

        Schema::table('p2p_payment_accounts', function (Blueprint $table) {
            if (! Schema::hasColumn('p2p_payment_accounts', 'label')) {
                $table->string('label')->nullable()->after('payment_method_id');
            }

            if (! Schema::hasColumn('p2p_payment_accounts', 'field_values')) {
                $table->json('field_values')->nullable()->after('instructions');
            }

            if (! Schema::hasColumn('p2p_payment_accounts', 'display_name')) {
                $table->string('display_name')->nullable()->after('field_values');
            }

            if (! Schema::hasColumn('p2p_payment_accounts', 'display_value')) {
                $table->string('display_value')->nullable()->after('display_name');
            }
        });

        $defaultFields = json_encode([
            [
                'key'        => 'account_name',
                'label'      => 'Account Name',
                'type'       => 'text',
                'required'   => true,
                'options'    => [],
                'sort_order' => 1,
            ],
            [
                'key'        => 'account_number',
                'label'      => 'Account Number',
                'type'       => 'text',
                'required'   => true,
                'options'    => [],
                'sort_order' => 2,
            ],
            [
                'key'        => 'instructions',
                'label'      => 'Instructions',
                'type'       => 'text',
                'required'   => false,
                'options'    => [],
                'sort_order' => 3,
            ],
        ], JSON_UNESCAPED_UNICODE);

        DB::table('p2p_payment_methods')
            ->orderBy('id')
            ->get(['id', 'fields', 'sort_order'])
            ->each(function (object $method) use ($defaultFields): void {
                $updates = [];

                if (empty($method->fields)) {
                    $updates['fields'] = $defaultFields;
                }

                if ((int) $method->sort_order === 0) {
                    $updates['sort_order'] = (int) $method->id;
                }

                if ($updates !== []) {
                    DB::table('p2p_payment_methods')->where('id', $method->id)->update($updates);
                }
            });

        DB::table('p2p_payment_accounts')
            ->orderBy('id')
            ->get(['id', 'account_name', 'account_number', 'instructions', 'label', 'field_values', 'display_name', 'display_value'])
            ->each(function (object $account): void {
                $fieldValues = [
                    'account_name'   => (string) ($account->account_name ?? ''),
                    'account_number' => (string) ($account->account_number ?? ''),
                    'instructions'   => (string) ($account->instructions ?? ''),
                ];

                $fieldValues = array_filter($fieldValues, static function ($value): bool {
                    return trim((string) $value) !== '';
                });

                $updates = [];

                if (empty($account->field_values) && $fieldValues !== []) {
                    $updates['field_values'] = json_encode($fieldValues, JSON_UNESCAPED_UNICODE);
                }

                if (empty($account->display_name) && ! empty($account->account_name)) {
                    $updates['display_name'] = (string) $account->account_name;
                }

                if (empty($account->display_value) && ! empty($account->account_number)) {
                    $updates['display_value'] = (string) $account->account_number;
                }

                if (empty($account->label) && ! empty($account->account_name)) {
                    $updates['label'] = (string) $account->account_name;
                }

                if ($updates !== []) {
                    DB::table('p2p_payment_accounts')->where('id', $account->id)->update($updates);
                }
            });
    }

    public function down(): void
    {
        Schema::table('p2p_payment_accounts', function (Blueprint $table) {
            if (Schema::hasColumn('p2p_payment_accounts', 'display_value')) {
                $table->dropColumn('display_value');
            }

            if (Schema::hasColumn('p2p_payment_accounts', 'display_name')) {
                $table->dropColumn('display_name');
            }

            if (Schema::hasColumn('p2p_payment_accounts', 'field_values')) {
                $table->dropColumn('field_values');
            }

            if (Schema::hasColumn('p2p_payment_accounts', 'label')) {
                $table->dropColumn('label');
            }
        });

        Schema::table('p2p_payment_methods', function (Blueprint $table) {
            if (Schema::hasColumn('p2p_payment_methods', 'sort_order')) {
                $table->dropColumn('sort_order');
            }

            if (Schema::hasColumn('p2p_payment_methods', 'fields')) {
                $table->dropColumn('fields');
            }
        });
    }
};
