<?php

namespace App\Support;

use App\Constants\CurrencyType;
use App\Exceptions\NotifyErrorException;
use App\Models\Admin;
use App\Models\Currency;
use App\Models\Language;
use App\Models\Setting;
use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use PDO;
use Spatie\Permission\Models\Role;
use Throwable;

class InstallationManager
{
    private const LEGACY_DEFAULT_ADMIN_EMAIL = 'admin@coevs.com';

    public const DEFAULT_ADMIN_PREFIX = 'admin';

    public const DEFAULT_CURRENCY_CODE = 'USD';

    /**
     * Curated list of fiat currencies offered during installation. Keys are
     * the ISO 4217 codes and the values describe how the currency is
     * presented (name, symbol, ISO-3166 flag code used by the storefront).
     *
     * @return array<string, array{name: string, symbol: string, flag: string}>
     */
    public static function currencyCatalog(): array
    {
        return [
            'USD' => ['name' => 'US Dollar',           'symbol' => '$',   'flag' => 'us'],
            'EUR' => ['name' => 'Euro',                'symbol' => '€',   'flag' => 'eu'],
            'GBP' => ['name' => 'British Pound',       'symbol' => '£',   'flag' => 'gb'],
            'JPY' => ['name' => 'Japanese Yen',        'symbol' => '¥',   'flag' => 'jp'],
            'AUD' => ['name' => 'Australian Dollar',   'symbol' => 'A$',  'flag' => 'au'],
            'CAD' => ['name' => 'Canadian Dollar',     'symbol' => 'C$',  'flag' => 'ca'],
            'CHF' => ['name' => 'Swiss Franc',         'symbol' => 'CHF', 'flag' => 'ch'],
            'CNY' => ['name' => 'Chinese Yuan',        'symbol' => '¥',   'flag' => 'cn'],
            'SGD' => ['name' => 'Singapore Dollar',    'symbol' => 'S$',  'flag' => 'sg'],
            'INR' => ['name' => 'Indian Rupee',        'symbol' => '₹',   'flag' => 'in'],
            'BDT' => ['name' => 'Bangladeshi Taka',    'symbol' => '৳',  'flag' => 'bd'],
            'PKR' => ['name' => 'Pakistani Rupee',     'symbol' => '₨',   'flag' => 'pk'],
            'LKR' => ['name' => 'Sri Lankan Rupee',    'symbol' => 'Rs',  'flag' => 'lk'],
            'NPR' => ['name' => 'Nepalese Rupee',      'symbol' => 'रू', 'flag' => 'np'],
            'IDR' => ['name' => 'Indonesian Rupiah',   'symbol' => 'Rp',  'flag' => 'id'],
            'MYR' => ['name' => 'Malaysian Ringgit',   'symbol' => 'RM',  'flag' => 'my'],
            'THB' => ['name' => 'Thai Baht',           'symbol' => '฿',   'flag' => 'th'],
            'PHP' => ['name' => 'Philippine Peso',     'symbol' => '₱',   'flag' => 'ph'],
            'VND' => ['name' => 'Vietnamese Dong',     'symbol' => '₫',   'flag' => 'vn'],
            'KRW' => ['name' => 'South Korean Won',    'symbol' => '₩',   'flag' => 'kr'],
            'TRY' => ['name' => 'Turkish Lira',        'symbol' => '₺',   'flag' => 'tr'],
            'AED' => ['name' => 'UAE Dirham',          'symbol' => 'AED', 'flag' => 'ae'],
            'SAR' => ['name' => 'Saudi Riyal',         'symbol' => 'SAR', 'flag' => 'sa'],
            'EGP' => ['name' => 'Egyptian Pound',      'symbol' => 'E£',  'flag' => 'eg'],
            'NGN' => ['name' => 'Nigerian Naira',      'symbol' => '₦',   'flag' => 'ng'],
            'KES' => ['name' => 'Kenyan Shilling',     'symbol' => 'KSh', 'flag' => 'ke'],
            'ZAR' => ['name' => 'South African Rand',  'symbol' => 'R',   'flag' => 'za'],
            'GHS' => ['name' => 'Ghanaian Cedi',       'symbol' => '₵',   'flag' => 'gh'],
            'BRL' => ['name' => 'Brazilian Real',      'symbol' => 'R$',  'flag' => 'br'],
            'MXN' => ['name' => 'Mexican Peso',        'symbol' => 'Mex$', 'flag' => 'mx'],
        ];
    }

    /**
     * @return array{installed: bool, can_install: bool, requirements: list<array{label: string, status: bool, help: string}>, writable: list<array{label: string, status: bool, help: string}>}
     */
    public function status(): array
    {
        $requirements = $this->requirements();
        $writable     = $this->writablePaths();

        return [
            'installed'    => $this->isInstalled(),
            'can_install'  => collect([...$requirements, ...$writable])->every(fn (array $check): bool => $check['status']),
            'requirements' => $requirements,
            'writable'     => $writable,
        ];
    }

    public function isInstalled(): bool
    {
        if (File::exists($this->lockPath())) {
            return true;
        }

        return $this->hasInstalledDatabase();
    }

    public function settingsTableAvailable(): bool
    {
        try {
            DB::connection()->getPdo();

            return Schema::hasTable('settings');
        } catch (Throwable) {
            return false;
        }
    }

    public function sessionTableAvailable(): bool
    {
        try {
            DB::connection()->getPdo();

            return Schema::hasTable('sessions');
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * @param  array<string, mixed>                                                                                                 $data
     * @return array{ok: bool, status: string, message: string, checks: list<array{label: string, status: string, detail: string}>}
     */
    public function testDatabaseConnection(array $data): array
    {
        $connection = (string) $data['db_connection'];

        if ($connection === 'sqlite') {
            try {
                $database = $this->prepareSqliteDatabase((string) $data['db_database']);
            } catch (Throwable $e) {
                throw new NotifyErrorException(__('SQLite database file could not be prepared. Check file and folder write permissions. Error: :message', ['message' => $e->getMessage()]));
            }

            return [
                'ok'      => true,
                'status'  => 'success',
                'message' => __('SQLite database file is ready for installation.'),
                'checks'  => [
                    [
                        'label'  => __('SQLite file'),
                        'status' => 'success',
                        'detail' => __('The database file is available at :path.', ['path' => $this->relativeDatabasePath($database)]),
                    ],
                    [
                        'label'  => __('Writable path'),
                        'status' => 'success',
                        'detail' => __('Laravel can write to the selected SQLite location.'),
                    ],
                ],
            ];
        }

        $database = trim((string) $data['db_database']);

        if ($database === '') {
            throw new NotifyErrorException(__('Database name is required.'));
        }

        $pdo = $this->mysqlServerPdo($data);

        try {
            $pdo->exec(sprintf(
                'CREATE DATABASE IF NOT EXISTS %s CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci',
                $this->quoteMysqlIdentifier($database)
            ));
        } catch (Throwable $e) {
            throw new NotifyErrorException(__('Database server login worked, but the database could not be created. Give the database user CREATE permission. Error: :message', ['message' => $e->getMessage()]));
        }

        $this->configureDatabase($data);
        $tableCount = $this->databaseTableCount();

        $readyForImport = $tableCount === 0;

        return [
            'ok'      => $readyForImport,
            'status'  => $readyForImport ? 'success' : 'warning',
            'message' => $readyForImport
                ? __('Database connection is ready. The installer can import DB/digitalwallet.sql.')
                : __('Database connection works, but this database already has :count tables. Use a new empty database before importing.', ['count' => $tableCount]),
            'checks' => [
                [
                    'label'  => __('Server login'),
                    'status' => 'success',
                    'detail' => __('Host, port, username, and password were accepted.'),
                ],
                [
                    'label'  => __('Create permission'),
                    'status' => 'success',
                    'detail' => __('The database exists or was created automatically.'),
                ],
                [
                    'label'  => __('Import readiness'),
                    'status' => $readyForImport ? 'success' : 'warning',
                    'detail' => $readyForImport
                        ? __('The selected database is empty and ready for the bundled SQL file.')
                        : __('Existing tables were found. Installation will stop to protect data.'),
                ],
            ],
        ];
    }

    /**
     * @param  array<string, mixed>                          $data
     * @return array{admin_url: string, admin_email: string}
     */
    public function install(array $data): array
    {
        if ($this->isInstalled()) {
            throw new NotifyErrorException(__('The application is already installed.'));
        }

        if (! $this->status()['can_install']) {
            throw new NotifyErrorException(__('Resolve the server requirement checks before installing.'));
        }

        $this->runStage(__('Clearing application caches'), fn () => Artisan::call('optimize:clear'));

        if ($this->usesSqlDumpImport((string) $data['db_connection'])) {
            $this->runStage(__('Preparing MySQL database'), fn () => $this->prepareMysqlDatabase($data));
            $this->runStage(__('Writing .env file'), fn () => $this->writeEnvironment($data));

            $dumpImported = (bool) $this->runStage(__('Importing DB/digitalwallet.sql'), fn () => $this->importSqlDump());

            // The bundled SQL dump is a point-in-time snapshot. Any migration
            // added to the codebase after that snapshot (e.g. wallet_earn_plans)
            // would otherwise leave the production DB short of tables. Always
            // run migrate so Laravel's migrations table picks up only the
            // entries that aren't already recorded in the dump.
            $this->runStage(__('Applying pending migrations'), fn () => Artisan::call('migrate', ['--force' => true]));

            // If the dump was missing/empty we never imported any reference
            // data — fall back to running the core seeders so settings,
            // payment gateways, features, etc. exist. All core seeders are
            // idempotent (firstOrCreate / updateOrCreate), so running them
            // after a partial dump would also be safe; we only opt-in here
            // to keep the happy path predictable.
            if (! $dumpImported) {
                $this->runStage(__('Seeding reference data'), fn () => $this->runSeeders((bool) ($data['seed_demo_data'] ?? false)));
            }
        } else {
            $this->runStage(__('Configuring database connection'), fn () => $this->configureDatabase($data));
            $this->runStage(__('Writing .env file'), fn () => $this->writeEnvironment($data));
            $this->runStage(__('Running migrations'), fn () => Artisan::call('migrate', ['--force' => true]));
            $this->runStage(__('Running seeders'), fn () => $this->runSeeders((bool) ($data['seed_demo_data'] ?? false)));
        }

        $this->runStage(__('Seeding default language'), fn () => $this->seedDefaultLanguage());
        $this->runStage(__('Seeding default currency'), fn () => $this->seedDefaultCurrency((string) ($data['default_currency_code'] ?? self::DEFAULT_CURRENCY_CODE)));
        $this->runStage(__('Seeding default settings'), fn () => $this->seedDefaultSettings(
            (string) $data['app_name'],
            (string) ($data['admin_prefix'] ?? self::DEFAULT_ADMIN_PREFIX),
        ));

        $admin = $this->runStage(__('Creating super admin account'), fn () => $this->createAdmin($data));
        $this->runStage(__('Writing installer lock file'), fn () => $this->markInstalled($data, $admin));

        // Create the public/storage symlink so logos, KYC docs and any
        // other uploaded media in storage/app/public become reachable via
        // /storage/* URLs. The release builder unlinks this on the seller
        // side — the buyer's install is where it needs to be put back.
        try {
            Artisan::call('storage:link', ['--force' => true]);
        } catch (Throwable $e) {
            Log::warning('storage:link failed during install: '.$e->getMessage());
        }

        $this->runStage(__('Finalising caches'), fn () => Artisan::call('optimize:clear'));

        return [
            'admin_url'   => url(trim((string) setting('admin_prefix', self::DEFAULT_ADMIN_PREFIX), '/').'/login'),
            'admin_email' => $admin->email,
        ];
    }

    /**
     * Execute one install stage and convert any unexpected throwable into a
     * NotifyErrorException with the stage label baked in. NotifyErrorException
     * already-thrown by the stage callback is re-raised as-is.
     *
     * @template TStageReturn
     *
     * @param  callable(): TStageReturn $callback
     * @return TStageReturn
     */
    private function runStage(string $label, callable $callback): mixed
    {
        try {
            return $callback();
        } catch (NotifyErrorException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::error(sprintf('Installer stage "%s" failed: %s', $label, $e->getMessage()), [
                'exception' => $e,
            ]);

            throw new NotifyErrorException(__(':label failed: :message', [
                'label'   => $label,
                'message' => $e->getMessage(),
            ]));
        }
    }

    /**
     * Platform-specific permission guidance shown next to the install error
     * banner. Each entry is a self-contained instruction the buyer can run
     * or follow without further context.
     *
     * @return list<string>
     */
    public function permissionGuidance(): array
    {
        return [
            __('Linux / VPS: run `sudo chown -R www-data:www-data storage bootstrap/cache public` then `sudo chmod -R 775 storage bootstrap/cache`. Replace `www-data` with your web server user (`nginx`, `apache`, or your cPanel user).'),
            __('cPanel / shared hosting: open File Manager, select `storage`, `bootstrap/cache`, and `public`, click Permissions, and set the value to 755 (recursive).'),
            __('Windows / IIS: grant the `IIS_IUSRS` and the AppPool identity Modify permission on `storage`, `bootstrap/cache`, `public`, and the `.env` file.'),
            __('Verify `open_basedir` and `disable_functions` in php.ini do not block `chmod`, `mkdir`, or `symlink`. Re-run the server check after applying the fix.'),
        ];
    }

    /**
     * @return list<array{label: string, status: bool, help: string}>
     */
    private function requirements(): array
    {
        $checks = [
            [
                'label'  => 'PHP 8.3+',
                'status' => PHP_VERSION_ID >= 80300,
                'help'   => 'Required by this Laravel application.',
            ],
        ];

        foreach (config('installer.required_extensions', []) as $label => $extension) {
            $checks[] = [
                'label'  => (string) $label,
                'status' => extension_loaded((string) $extension),
                'help'   => 'Required PHP extension.',
            ];
        }

        return $checks;
    }

    /**
     * @return list<array{label: string, status: bool, help: string}>
     */
    private function writablePaths(): array
    {
        $checks = [];

        foreach (config('installer.writable_paths', []) as $label => $path) {
            $path   = (string) $path;
            $result = $this->probeWritable($path);

            $checks[] = [
                'label'  => (string) $label,
                'status' => $result['ok'],
                'help'   => $result['ok'] ? $path : __(':path — :reason', ['path' => $path, 'reason' => $result['reason']]),
            ];
        }

        return $checks;
    }

    /**
     * Actually try writing a probe file in the target location. Some hosts
     * (open_basedir, suexec/FastCGI user mismatch, ACL, Windows IIS) make
     * `is_writable()` report true while real writes still fail, so the
     * installer needs to test with a real I/O round-trip.
     *
     * @return array{ok: bool, reason: string}
     */
    private function probeWritable(string $path): array
    {
        $directory = File::exists($path) && File::isDirectory($path) ? $path : dirname($path);

        if (! File::exists($directory)) {
            try {
                File::ensureDirectoryExists($directory, 0775);
            } catch (Throwable $e) {
                return ['ok' => false, 'reason' => __('directory missing and cannot be created (:message)', ['message' => $e->getMessage()])];
            }
        }

        if (File::exists($path) && File::isFile($path) && ! File::isWritable($path)) {
            $this->attemptChmod($path, 0664);

            if (! File::isWritable($path)) {
                return ['ok' => false, 'reason' => __('file is not writable by the web server user')];
            }
        }

        if (! File::isWritable($directory)) {
            $this->attemptChmod($directory, 0775);

            if (! File::isWritable($directory)) {
                return ['ok' => false, 'reason' => __('directory is not writable by the web server user')];
            }
        }

        $probe = rtrim($directory, '/\\').DIRECTORY_SEPARATOR.'.digitalwallet-probe-'.bin2hex(random_bytes(4));

        try {
            File::put($probe, 'probe');

            if (! File::exists($probe) || File::get($probe) !== 'probe') {
                return ['ok' => false, 'reason' => __('probe file could not be read back after writing')];
            }
        } catch (Throwable $e) {
            return ['ok' => false, 'reason' => __('write rejected by the host (:message)', ['message' => $e->getMessage()])];
        } finally {
            if (File::exists($probe)) {
                try {
                    File::delete($probe);
                } catch (Throwable) {
                    //
                }
            }
        }

        return ['ok' => true, 'reason' => ''];
    }

    private function attemptChmod(string $path, int $mode): void
    {
        try {
            @chmod($path, $mode);
        } catch (Throwable) {
            //
        }
    }

    private function hasInstalledDatabase(): bool
    {
        try {
            DB::connection()->getPdo();

            return Schema::hasTable('settings') && Schema::hasTable('admins');
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    private function configureDatabase(array $data): void
    {
        $connection = (string) $data['db_connection'];
        $database   = (string) $data['db_database'];

        if ($connection === 'sqlite') {
            $database = $this->prepareSqliteDatabase($database);
            Config::set('database.connections.sqlite.database', $database);
        } else {
            Config::set("database.connections.{$connection}.host", (string) $data['db_host']);
            Config::set("database.connections.{$connection}.port", (string) $data['db_port']);
            Config::set("database.connections.{$connection}.database", $database);
            Config::set("database.connections.{$connection}.username", (string) $data['db_username']);
            Config::set("database.connections.{$connection}.password", (string) ($data['db_password'] ?? ''));
        }

        Config::set('database.default', $connection);
        DB::purge($connection);
        DB::setDefaultConnection($connection);

        try {
            DB::connection($connection)->getPdo();
        } catch (Throwable $e) {
            throw new NotifyErrorException(__('Database connection failed: :message', ['message' => $e->getMessage()]));
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    private function prepareMysqlDatabase(array $data): void
    {
        $connection = (string) $data['db_connection'];

        if (! in_array($connection, ['mysql', 'mariadb'], true)) {
            return;
        }

        $database = trim((string) $data['db_database']);

        if ($database === '') {
            throw new NotifyErrorException(__('Database name is required.'));
        }

        $pdo = $this->mysqlServerPdo($data);

        try {
            $pdo->exec(sprintf(
                'CREATE DATABASE IF NOT EXISTS %s CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci',
                $this->quoteMysqlIdentifier($database)
            ));
        } catch (Throwable $e) {
            throw new NotifyErrorException(__('Database could not be created. Check that the database user has CREATE permission. Error: :message', ['message' => $e->getMessage()]));
        }

        $this->configureDatabase($data);

        if (! $this->databaseIsEmpty()) {
            throw new NotifyErrorException(__('The selected database already has tables. Use a new empty database name, or clear the database before importing the script.'));
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    private function mysqlServerPdo(array $data): PDO
    {
        $host     = (string) $data['db_host'];
        $port     = (string) $data['db_port'];
        $username = (string) $data['db_username'];
        $password = (string) ($data['db_password'] ?? '');

        try {
            return new PDO(
                "mysql:host={$host};port={$port};charset=utf8mb4",
                $username,
                $password,
                [
                    PDO::ATTR_ERRMODE                => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE     => PDO::FETCH_ASSOC,
                    PDO::MYSQL_ATTR_MULTI_STATEMENTS => false,
                ]
            );
        } catch (Throwable $e) {
            throw new NotifyErrorException(__('Database server connection failed. Check host, port, username, and password. Error: :message', ['message' => $e->getMessage()]));
        }
    }

    private function databaseIsEmpty(): bool
    {
        return $this->databaseTableCount() === 0;
    }

    private function databaseTableCount(): int
    {
        try {
            return count(Schema::getTables());
        } catch (Throwable $e) {
            throw new NotifyErrorException(__('Database was created, but tables could not be inspected: :message', ['message' => $e->getMessage()]));
        }
    }

    /**
     * Import the bundled SQL dump.
     *
     * Returns true when statements were imported, false when the dump file
     * is missing or empty. A missing dump is no longer fatal — the install
     * flow falls back to `migrate` + `seed` so the install can finish even
     * if the dump isn't shipped with the release.
     *
     * Any real import error (bad SQL, dropped connection) still throws a
     * NotifyErrorException so the buyer sees an actionable banner.
     */
    private function importSqlDump(): bool
    {
        $dumpPath = (string) config('installer.database_dump', base_path('DB/digitalwallet.sql'));

        if (! File::exists($dumpPath) || ! File::isReadable($dumpPath)) {
            Log::warning('Installer SQL dump missing or unreadable; falling back to migrations: '.$dumpPath);

            return false;
        }

        $sql = File::get($dumpPath);

        if (trim($sql) === '') {
            Log::warning('Installer SQL dump is empty; falling back to migrations: '.$dumpPath);

            return false;
        }

        try {
            DB::connection()->unprepared('SET FOREIGN_KEY_CHECKS=0');

            foreach ($this->splitSqlStatements($sql) as $statement) {
                if ($this->shouldSkipSqlImportStatement($statement)) {
                    continue;
                }

                DB::connection()->unprepared($statement);
            }

            DB::connection()->unprepared('SET FOREIGN_KEY_CHECKS=1');
        } catch (Throwable $e) {
            try {
                DB::connection()->unprepared('SET FOREIGN_KEY_CHECKS=1');
            } catch (Throwable) {
                //
            }

            throw new NotifyErrorException(__('Database import failed from DB/digitalwallet.sql: :message', ['message' => $e->getMessage()]));
        }

        return true;
    }

    /**
     * @return list<string>
     */
    private function splitSqlStatements(string $sql): array
    {
        $statements = [];
        $statement  = '';
        $length     = strlen($sql);
        $quote      = null;

        for ($i = 0; $i < $length; $i++) {
            $char = $sql[$i];
            $next = $sql[$i + 1] ?? '';

            if ($quote === null && $char === '-' && $next === '-' && $this->isSqlCommentStart($sql[$i + 2] ?? '')) {
                $i = $this->skipUntilNewline($sql, $i + 2);

                continue;
            }

            if ($quote === null && $char === '#') {
                $i = $this->skipUntilNewline($sql, $i);

                continue;
            }

            if ($quote === null && $char === '/' && $next === '*') {
                $i = $this->skipBlockComment($sql, $i + 2);

                continue;
            }

            if (($char === "'" || $char === '"' || $char === '`') && ! $this->isEscaped($sql, $i)) {
                $quote = $quote === $char ? null : ($quote ?? $char);
            }

            if ($char === ';' && $quote === null) {
                $trimmed = trim($statement);

                if ($trimmed !== '') {
                    $statements[] = $trimmed;
                }

                $statement = '';

                continue;
            }

            $statement .= $char;
        }

        $trimmed = trim($statement);

        if ($trimmed !== '') {
            $statements[] = $trimmed;
        }

        return $statements;
    }

    private function shouldSkipSqlImportStatement(string $statement): bool
    {
        $normalized = ltrim($statement);

        return preg_match('/^(CREATE\s+DATABASE|USE\s+|DELIMITER\b)/i', $normalized) === 1;
    }

    private function isSqlCommentStart(string $char): bool
    {
        return $char === '' || ctype_space($char);
    }

    private function skipUntilNewline(string $sql, int $offset): int
    {
        $newline = strpos($sql, "\n", $offset);

        return $newline === false ? strlen($sql) : $newline;
    }

    private function skipBlockComment(string $sql, int $offset): int
    {
        $end = strpos($sql, '*/', $offset);

        return $end === false ? strlen($sql) : $end + 1;
    }

    private function isEscaped(string $sql, int $index): bool
    {
        $slashes = 0;

        for ($i = $index - 1; $i >= 0 && $sql[$i] === '\\'; $i--) {
            $slashes++;
        }

        return $slashes % 2 === 1;
    }

    private function quoteMysqlIdentifier(string $identifier): string
    {
        return '`'.str_replace('`', '``', $identifier).'`';
    }

    private function usesSqlDumpImport(string $connection): bool
    {
        return in_array($connection, ['mysql', 'mariadb'], true);
    }

    private function prepareSqliteDatabase(string $database): string
    {
        if ($database === ':memory:') {
            return $database;
        }

        $database = $this->absolutePath($database);

        File::ensureDirectoryExists(dirname($database));

        if (! File::exists($database)) {
            File::put($database, '');
        }

        return $database;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function writeEnvironment(array $data): void
    {
        $connection = (string) $data['db_connection'];
        $database   = (string) $data['db_database'];
        $appKey     = $this->generateApplicationKey();

        if ($connection === 'sqlite' && $database !== ':memory:') {
            $database = $this->relativeDatabasePath($this->absolutePath($database));
        }

        $values = [
            'APP_NAME'                       => (string) $data['app_name'],
            'APP_ENV'                        => 'production',
            'APP_KEY'                        => $appKey,
            'APP_DEBUG'                      => 'false',
            'APP_URL'                        => rtrim((string) $data['app_url'], '/'),
            'DB_CONNECTION'                  => $connection,
            'DB_HOST'                        => (string) ($data['db_host'] ?? '127.0.0.1'),
            'DB_PORT'                        => (string) ($data['db_port'] ?? '3306'),
            'DB_DATABASE'                    => $database,
            'DB_USERNAME'                    => (string) ($data['db_username'] ?? ''),
            'DB_PASSWORD'                    => (string) ($data['db_password'] ?? ''),
            'SESSION_DRIVER'                 => 'file',
        ];

        Config::set('app.key', $appKey);
        app()->forgetInstance('encrypter');

        $envPath = base_path('.env');

        if (! File::exists($envPath)) {
            if (File::exists(base_path('.env.example'))) {
                File::copy(base_path('.env.example'), $envPath);
            } else {
                File::put($envPath, '');
            }
        }

        $content = File::get($envPath);

        foreach ($values as $key => $value) {
            $line    = $key.'='.$this->formatEnvironmentValue($value);
            $pattern = '/^'.preg_quote($key, '/').'=.*$/m';

            if (preg_match($pattern, $content) === 1) {
                $content = preg_replace($pattern, $line, $content) ?? $content;
            } else {
                $content = rtrim($content).PHP_EOL.$line.PHP_EOL;
            }
        }

        $finalContent = rtrim($content).PHP_EOL;
        $bytesWritten = File::put($envPath, $finalContent);

        if ($bytesWritten === false || ! File::exists($envPath) || str_contains(File::get($envPath), 'APP_KEY=') === false) {
            throw new NotifyErrorException(__('Could not write the .env file at :path. Grant the web server user write permission to this file and the project root, then try again.', ['path' => $envPath]));
        }
    }

    private function generateApplicationKey(): string
    {
        return 'base64:'.base64_encode(Encrypter::generateKey((string) config('app.cipher')));
    }

    private function formatEnvironmentValue(mixed $value): string
    {
        $value = (string) $value;

        if ($value === '') {
            return '""';
        }

        if (preg_match('/[\s#"\'=]/', $value) === 1) {
            return '"'.str_replace(['\\', '"'], ['\\\\', '\\"'], $value).'"';
        }

        return $value;
    }

    private function absolutePath(string $path): string
    {
        $path = str_replace('\\', '/', trim($path));

        if ($path === ':memory:' || preg_match('/^[A-Za-z]:\//', $path) === 1 || str_starts_with($path, '/')) {
            return $path;
        }

        return base_path($path);
    }

    private function relativeDatabasePath(string $path): string
    {
        $normalizedBase = str_replace('\\', '/', base_path());
        $normalizedPath = str_replace('\\', '/', $path);

        if (str_starts_with($normalizedPath, $normalizedBase.'/')) {
            return ltrim(substr($normalizedPath, strlen($normalizedBase)), '/');
        }

        return $path;
    }

    private function runSeeders(bool $includeDemoData): void
    {
        foreach (config('installer.core_seeders', []) as $seeder) {
            Artisan::call('db:seed', ['--class' => $seeder, '--force' => true]);
        }

        if (! $includeDemoData) {
            return;
        }

        foreach (config('installer.demo_seeders', []) as $seeder) {
            Artisan::call('db:seed', ['--class' => $seeder, '--force' => true]);
        }
    }

    private function seedDefaultLanguage(): void
    {
        Language::query()->firstOrCreate(
            ['code' => 'en'],
            [
                'flag'       => 'us',
                'name'       => 'English',
                'is_default' => true,
                'status'     => true,
            ]
        );
    }

    private function seedDefaultSettings(string $appName, string $adminPrefix): void
    {
        foreach (config('settings', []) as $section) {
            foreach ($section['elements'] ?? [] as $field) {
                $key = $field['key'] ?? null;

                if (! is_string($key) || Setting::has($key)) {
                    continue;
                }

                $value = $field['value'] ?? '';

                if (is_array($value)) {
                    continue;
                }

                Setting::add($key, $value, (string) ($field['data'] ?? 'string'));
            }
        }

        Setting::set('site_title', $appName, 'string');
        Setting::set('admin_prefix', $this->sanitizeAdminPrefix($adminPrefix), 'string');
        Setting::set('site_timezone', (string) config('app.timezone', 'UTC'), 'string');
        // Home redirect defaults to '/' so the public landing page is shown.
        // Previously this defaulted to the string 'login', which Laravel
        // resolved to '/login' — a URL that doesn't exist (the real user
        // login is at /user/login) — so every buyer landed on a 404 after
        // install.
        Setting::set('home_redirect', '/', 'string');
        Setting::flushCache();
    }

    private function sanitizeAdminPrefix(string $prefix): string
    {
        $prefix = strtolower(trim($prefix, " \t\n\r\0\x0B/"));

        if ($prefix === '' || preg_match('/^[a-z0-9][a-z0-9-]*[a-z0-9]$/', $prefix) !== 1) {
            return self::DEFAULT_ADMIN_PREFIX;
        }

        return $prefix;
    }

    /**
     * Persist the buyer's chosen currency as the application default.
     *
     * The bundled SQL dump and seeders may insert a baseline set of
     * currencies, so this method updates the existing record when one
     * matches and otherwise creates a minimal row from the curated
     * catalog. Every other currency is flipped out of the default flag
     * so only one stays active as the site currency.
     */
    private function seedDefaultCurrency(string $code): void
    {
        $code     = strtoupper(trim($code));
        $catalog  = self::currencyCatalog();
        $fallback = $catalog[self::DEFAULT_CURRENCY_CODE];
        $info     = $catalog[$code] ?? $fallback;

        if (! isset($catalog[$code])) {
            $code = self::DEFAULT_CURRENCY_CODE;
        }

        if (! Schema::hasTable('currencies')) {
            return;
        }

        Currency::query()->where('code', '!=', $code)->update(['default' => false]);

        Currency::query()->updateOrCreate(
            ['code' => $code],
            [
                'name'          => $info['name'],
                'symbol'        => $info['symbol'],
                'flag'          => $info['flag'],
                'type'          => CurrencyType::FIAT,
                'exchange_rate' => 1,
                'rate_live'     => false,
                'auto_wallet'   => true,
                'default'       => true,
                'status'        => true,
            ]
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    private function createAdmin(array $data): Admin
    {
        Role::query()->firstOrCreate(
            ['guard_name' => 'admin', 'name' => 'super-admin'],
            ['description' => 'Full platform access with every permission.']
        );

        $admin = Admin::query()->firstOrNew([
            'email' => (string) $data['admin_email'],
        ]);

        $admin->forceFill([
            'name'              => (string) $data['admin_name'],
            'email'             => (string) $data['admin_email'],
            'email_verified_at' => now(),
            'password'          => (string) $data['admin_password'],
            'status'            => true,
        ])->save();

        $admin->syncRoles(['super-admin']);

        $this->removeLegacyDefaultAdmin($admin);

        return $admin;
    }

    private function removeLegacyDefaultAdmin(Admin $currentAdmin): void
    {
        Admin::query()
            ->where('email', self::LEGACY_DEFAULT_ADMIN_EMAIL)
            ->whereKeyNot($currentAdmin->getKey())
            ->get()
            ->each(function (Admin $legacyAdmin): void {
                $legacyAdmin->syncRoles([]);
                $legacyAdmin->delete();
            });
    }

    /**
     * @param array<string, mixed> $data
     */
    private function markInstalled(array $data, Admin $admin): void
    {
        $lockPath = $this->lockPath();

        File::ensureDirectoryExists(dirname($lockPath));

        $payload = json_encode([
            'installed_at'   => now()->toIso8601String(),
            'app_name'       => (string) $data['app_name'],
            'app_url'        => (string) $data['app_url'],
            'admin_email'    => $admin->email,
            'db_connection'  => (string) $data['db_connection'],
        ], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);

        $bytesWritten = File::put($lockPath, $payload);

        if ($bytesWritten === false || ! File::exists($lockPath)) {
            throw new NotifyErrorException(__('Could not write the installer lock file at :path. Make the storage/app folder writable by the web server user.', ['path' => $lockPath]));
        }
    }

    private function lockPath(): string
    {
        return (string) config('installer.lock_file', storage_path('app/installed'));
    }
}
