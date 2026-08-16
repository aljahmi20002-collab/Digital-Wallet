<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * MySQL/MariaDB do not support partial unique indexes (i.e. unique
 * scoped to `deleted_at IS NULL`). The original migration declared
 * `unique(user_id, payment_method_id)` against the full table, which
 * collides whenever a soft-deleted account exists and the same user
 * tries to re-add the same payment method.
 *
 * The Laravel pattern is to drop the DB-level unique constraint and
 * rely on the FormRequest validator (`Rule::unique(...)->whereNull(
 * 'deleted_at')`), which is already in place inside
 * `App\Http\Controllers\Frontend\P2P\PaymentAccountController::
 * validatedAccountPayload`. The non-unique composite index is kept
 * for query performance.
 *
 * This migration is idempotent — `up()` and `down()` both inspect
 * `information_schema` first so re-running it on a database that
 * already has the desired state is a no-op instead of a fatal
 * "Duplicate key name" / "missing index" error.
 */
return new class extends Migration
{
    private const TABLE = 'p2p_payment_accounts';

    private const UNIQUE_NAME = 'p2p_payment_accounts_user_id_payment_method_id_unique';

    private const COMPOSITE_INDEX = 'p2p_payment_accounts_user_id_payment_method_id_index';

    public function up(): void
    {
        if ($this->indexExists(self::UNIQUE_NAME)) {
            Schema::table(self::TABLE, function (Blueprint $table): void {
                $table->dropUnique(self::UNIQUE_NAME);
            });
        }

        if (! $this->indexExists(self::COMPOSITE_INDEX)) {
            Schema::table(self::TABLE, function (Blueprint $table): void {
                $table->index(['user_id', 'payment_method_id'], self::COMPOSITE_INDEX);
            });
        }
    }

    public function down(): void
    {
        if ($this->indexExists(self::COMPOSITE_INDEX)) {
            Schema::table(self::TABLE, function (Blueprint $table): void {
                $table->dropIndex(self::COMPOSITE_INDEX);
            });
        }

        if (! $this->indexExists(self::UNIQUE_NAME)) {
            Schema::table(self::TABLE, function (Blueprint $table): void {
                $table->unique(['user_id', 'payment_method_id'], self::UNIQUE_NAME);
            });
        }
    }

    /**
     * Driver-aware index lookup. Falls back to a "best effort" check
     * when the connection isn't MySQL (e.g. SQLite test runs).
     */
    private function indexExists(string $name): bool
    {
        $connection = Schema::getConnection();
        $driver     = $connection->getDriverName();

        if ($driver === 'mysql' || $driver === 'mariadb') {
            $rows = $connection->select(
                'SELECT INDEX_NAME FROM information_schema.STATISTICS
                 WHERE TABLE_SCHEMA = DATABASE()
                   AND TABLE_NAME   = ?
                   AND INDEX_NAME   = ?
                 LIMIT 1',
                [self::TABLE, $name]
            );

            return ! empty($rows);
        }

        if ($driver === 'sqlite') {
            $rows = $connection->select(
                "SELECT name FROM sqlite_master WHERE type='index' AND tbl_name = ? AND name = ? LIMIT 1",
                [self::TABLE, $name]
            );

            return ! empty($rows);
        }

        if ($driver === 'pgsql') {
            $rows = $connection->select(
                'SELECT indexname FROM pg_indexes WHERE tablename = ? AND indexname = ? LIMIT 1',
                [self::TABLE, $name]
            );

            return ! empty($rows);
        }

        // Unknown driver — assume it does not exist, the closure-level
        // try/catch in legacy migrations will absorb a redundant create.
        return false;
    }
};
