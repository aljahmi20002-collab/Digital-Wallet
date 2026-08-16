<?php

namespace App\Services;

use App\Exceptions\NotifyErrorException;
use App\Models\ProjectLicense;
use App\Models\ProjectUpdate;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PDO;
use ZipArchive;

class ProjectUpdaterService
{
    public function __construct(private readonly ProjectUpdateServerClient $client) {}

    /**
     * @return array<string, mixed>
     */
    public function overview(): array
    {
        return [
            'license' => ProjectLicense::current(),
            'latest'  => ProjectUpdate::query()->latest('checked_at')->latest('id')->first(),
            'history' => ProjectUpdate::query()->latest('created_at')->limit(10)->get(),
            'checks'  => $this->environmentChecks(),
        ];
    }

    public function activate(string $purchaseCode): ProjectLicense
    {
        $data    = $this->client->activate($purchaseCode);
        $license = $data['license'] ?? $data;

        if (! is_array($license)) {
            throw new NotifyErrorException(__('The license activation response was invalid.'));
        }

        return ProjectLicense::query()->updateOrCreate(
            [
                'product_slug' => config('project_updater.product_slug'),
                'domain'       => request()->getHost(),
            ],
            [
                'item_id'         => (string) ($license['item_id'] ?? config('project_updater.item_id')),
                'purchase_code'   => $purchaseCode,
                'license_token'   => $license['token']           ?? $license['license_token'] ?? null,
                'buyer_username'  => $license['buyer']           ?? $license['buyer_username'] ?? null,
                'status'          => $license['status']          ?? 'active',
                'support_until'   => $license['supported_until'] ?? $license['support_until'] ?? null,
                'activated_at'    => now(),
                'last_checked_at' => now(),
                'metadata'        => $license,
            ]
        );
    }

    public function checkForUpdates(): ProjectUpdate
    {
        $license = ProjectLicense::current();

        if (! $license?->isActive()) {
            throw new NotifyErrorException(__('Activate the project license before checking for updates.'));
        }

        $data   = $this->client->check($license->license_token);
        $update = $data['update'] ?? $data;

        if (! is_array($update)) {
            throw new NotifyErrorException(__('The update check response was invalid.'));
        }

        $license->forceFill(['last_checked_at' => now()])->save();

        return ProjectUpdate::query()->updateOrCreate(
            [
                'version' => (string) ($update['version'] ?? config('app.version')),
                'channel' => (string) ($update['channel'] ?? config('project_updater.channel')),
            ],
            [
                'status' => version_compare((string) ($update['version'] ?? config('app.version')), (string) config('app.version'), '>')
                    ? 'available'
                    : 'current',
                'package_url'   => $update['package_url']  ?? null,
                'checksum'      => $update['checksum']     ?? null,
                'signature'     => $update['signature']    ?? null,
                'changelog'     => $update['changelog']    ?? [],
                'requirements'  => $update['requirements'] ?? [],
                'release_date'  => $update['release_date'] ?? null,
                'checked_at'    => now(),
                'metadata'      => $update,
                'error_message' => null,
            ]
        );
    }

    public function install(ProjectUpdate $update, bool $includeSystemBackup = false): ProjectUpdate
    {
        if (! (bool) config('project_updater.install_enabled', true)) {
            throw new NotifyErrorException(__('Project update installation is disabled by configuration.'));
        }

        if (! $update->isInstallable()) {
            throw new NotifyErrorException(__('This update cannot be installed from its current status.'));
        }

        if (blank($update->package_url) || blank($update->checksum)) {
            throw new NotifyErrorException(__('The update package URL and checksum are required.'));
        }

        try {
            $packagePath = $this->downloadPackage($update);
            $this->verifyPackage($packagePath, (string) $update->checksum, $update->signature);

            $extractPath = $this->extractPackage($packagePath, $update);
            $manifest    = $this->readManifest($extractPath);
            $backupPath  = $this->backupManifestFiles($manifest, $update);

            if ($includeSystemBackup) {
                $this->createDatabaseAndStorageBackup($update, $backupPath);
            }

            Artisan::call('down');
            $this->copyManifestFiles($extractPath, $manifest);
            Artisan::call('migrate', ['--force' => true]);
            Artisan::call('optimize:clear');

            $update->forceFill([
                'status'        => 'installed',
                'package_path'  => $packagePath,
                'backup_path'   => $backupPath,
                'installed_at'  => now(),
                'error_message' => null,
            ])->save();
        } catch (\Throwable $e) {
            $update->forceFill([
                'status'        => 'failed',
                'error_message' => $e->getMessage(),
            ])->save();

            throw $e instanceof NotifyErrorException
                ? $e
                : new NotifyErrorException(__('Project update failed: :message', ['message' => $e->getMessage()]));
        } finally {
            if (app()->isDownForMaintenance()) {
                Artisan::call('up');
            }
        }

        return $update->refresh();
    }

    /**
     * @return array<int, array{label: string, status: bool, help: string}>
     */
    private function environmentChecks(): array
    {
        return [
            [
                'label'  => __('ZIP extension'),
                'status' => class_exists(ZipArchive::class),
                'help'   => __('Required to extract update packages.'),
            ],
            [
                'label'  => __('Storage writable'),
                'status' => File::isWritable(storage_path('app')),
                'help'   => __('Required to download packages and create backups.'),
            ],
            [
                'label'  => __('Updater server'),
                'status' => filled(config('project_updater.server_url')),
                'help'   => __('Configure PROJECT_UPDATER_SERVER_URL on production.'),
            ],
        ];
    }

    private function downloadPackage(ProjectUpdate $update): string
    {
        $body = $this->client->download((string) $update->package_url);
        $path = trim((string) config('project_updater.packages_path'), '/').'/'.$update->version.'-'.Str::uuid().'.zip';

        Storage::disk((string) config('project_updater.storage_disk'))->put($path, $body);

        return $path;
    }

    public function verifyPackage(string $packagePath, string $checksum, ?string $signature = null): void
    {
        $absolutePath = Storage::disk((string) config('project_updater.storage_disk'))->path($packagePath);
        $actual       = hash_file('sha256', $absolutePath);

        if (! hash_equals(strtolower($checksum), strtolower((string) $actual))) {
            throw new NotifyErrorException(__('Update package checksum verification failed.'));
        }

        $publicKey = config('project_updater.public_key');

        if (filled($publicKey) && filled($signature)) {
            $verified = openssl_verify($checksum, base64_decode((string) $signature, true) ?: '', (string) $publicKey, OPENSSL_ALGO_SHA256);

            if ($verified !== 1) {
                throw new NotifyErrorException(__('Update package signature verification failed.'));
            }
        }
    }

    private function extractPackage(string $packagePath, ProjectUpdate $update): string
    {
        if (! class_exists(ZipArchive::class)) {
            throw new NotifyErrorException(__('The PHP ZIP extension is required to install updates.'));
        }

        $disk                = Storage::disk((string) config('project_updater.storage_disk'));
        $archivePath         = $disk->path($packagePath);
        $extractPath         = trim((string) config('project_updater.extract_path'), '/').'/'.$update->version.'-'.Str::uuid();
        $absoluteExtractPath = $disk->path($extractPath);

        File::ensureDirectoryExists($absoluteExtractPath);

        $zip = new ZipArchive;

        if ($zip->open($archivePath) !== true) {
            throw new NotifyErrorException(__('The update package could not be opened.'));
        }

        $zip->extractTo($absoluteExtractPath);
        $zip->close();

        return $extractPath;
    }

    /**
     * @return array{files: list<array{path: string}>}
     */
    private function readManifest(string $extractPath): array
    {
        $manifestPath = Storage::disk((string) config('project_updater.storage_disk'))->path($extractPath.'/manifest.json');

        if (! File::exists($manifestPath)) {
            throw new NotifyErrorException(__('The update package manifest is missing.'));
        }

        $manifest = json_decode((string) File::get($manifestPath), true);

        if (! is_array($manifest) || ! isset($manifest['files']) || ! is_array($manifest['files'])) {
            throw new NotifyErrorException(__('The update package manifest is invalid.'));
        }

        return $manifest;
    }

    /**
     * @param array{files: list<array{path: string}>} $manifest
     */
    private function backupManifestFiles(array $manifest, ProjectUpdate $update): string
    {
        $backupPath = trim((string) config('project_updater.backups_path'), '/').'/'.$update->version.'-'.now()->format('YmdHis');
        $backupRoot = Storage::disk((string) config('project_updater.storage_disk'))->path($backupPath);

        File::ensureDirectoryExists($backupRoot);

        foreach ($manifest['files'] as $file) {
            $relativePath = $this->safeRelativePath((string) ($file['path'] ?? ''));
            $source       = base_path($relativePath);

            if (File::exists($source)) {
                File::ensureDirectoryExists(dirname($backupRoot.'/'.$relativePath));
                File::copy($source, $backupRoot.'/'.$relativePath);
            }
        }

        File::put($backupRoot.'/manifest.json', json_encode($manifest, JSON_PRETTY_PRINT));

        return $backupPath;
    }

    public function createDatabaseAndStorageBackup(ProjectUpdate $update, ?string $backupPath = null): string
    {
        $backupPath ??= trim((string) config('project_updater.backups_path'), '/').'/'.$update->version.'-'.now()->format('YmdHis');
        $backupRoot = Storage::disk((string) config('project_updater.storage_disk'))->path($backupPath);

        File::ensureDirectoryExists($backupRoot);

        $this->writeDatabaseAndStorageBackup($backupRoot, $update->version);

        return $backupPath;
    }

    /**
     * @return array{path: string, name: string, backup_path: string}
     */
    public function createDownloadableRecoveryBackup(): array
    {
        $license = ProjectLicense::current();

        if (! $license?->isActive()) {
            throw new NotifyErrorException(__('Activate the project license before downloading a recovery backup.'));
        }

        $version    = (string) config('app.version');
        $stamp      = now()->format('YmdHis');
        $backupPath = trim((string) config('project_updater.backups_path'), '/').'/manual-'.$version.'-'.$stamp;
        $backupRoot = Storage::disk((string) config('project_updater.storage_disk'))->path($backupPath);

        File::ensureDirectoryExists($backupRoot);
        $this->writeDatabaseAndStorageBackup($backupRoot, $version);

        $fileName    = 'digikash-recovery-'.$version.'-'.$stamp.'.zip';
        $archivePath = trim((string) config('project_updater.backups_path'), '/').'/downloads/'.$fileName;
        $absoluteZip = Storage::disk((string) config('project_updater.storage_disk'))->path($archivePath);

        File::ensureDirectoryExists(dirname($absoluteZip));
        $this->archiveBackupDirectory($backupRoot, $absoluteZip);

        return [
            'path'        => $absoluteZip,
            'name'        => $fileName,
            'backup_path' => $backupPath,
        ];
    }

    private function writeDatabaseAndStorageBackup(string $backupRoot, string $version): void
    {
        $this->backupDatabase($backupRoot.'/database.sql');
        $this->backupStorageDirectory($backupRoot.'/storage.zip', $backupRoot);

        File::put($backupRoot.'/restore-note.txt', implode(PHP_EOL, [
            'Digikash update backup',
            'Version: '.$version,
            'Created: '.now()->toDateTimeString(),
            'database.sql contains a database dump.',
            'storage.zip contains the Laravel storage folder, excluding this backup directory.',
        ]));
    }

    private function backupDatabase(string $targetPath): void
    {
        File::ensureDirectoryExists(dirname($targetPath));

        $connection = DB::connection();
        $driver     = $connection->getDriverName();
        $pdo        = $connection->getPdo();
        $tables     = $this->databaseTables($driver);
        $handle     = fopen($targetPath, 'wb');

        if ($handle === false) {
            throw new NotifyErrorException(__('The database backup file could not be created.'));
        }

        fwrite($handle, '-- Digikash database backup'.PHP_EOL);
        fwrite($handle, '-- Generated at: '.now()->toDateTimeString().PHP_EOL.PHP_EOL);

        if ($driver === 'mysql') {
            fwrite($handle, 'SET FOREIGN_KEY_CHECKS=0;'.PHP_EOL.PHP_EOL);
        }

        foreach ($tables as $table) {
            $quotedTable = $this->quoteIdentifier($table, $driver);

            fwrite($handle, 'DROP TABLE IF EXISTS '.$quotedTable.';'.PHP_EOL);
            fwrite($handle, $this->createTableStatement($table, $driver).';'.PHP_EOL.PHP_EOL);
            $this->writeTableRows($handle, $pdo, $table, $driver);
            fwrite($handle, PHP_EOL);
        }

        if ($driver === 'mysql') {
            fwrite($handle, 'SET FOREIGN_KEY_CHECKS=1;'.PHP_EOL);
        }

        fclose($handle);
    }

    /**
     * @return list<string>
     */
    private function databaseTables(string $driver): array
    {
        if ($driver === 'sqlite') {
            return collect(DB::select("SELECT name FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite_%' ORDER BY name"))
                ->map(fn (object $row): string => (string) $row->name)
                ->values()
                ->all();
        }

        if ($driver === 'mysql') {
            return collect(DB::select("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'"))
                ->map(fn (object $row): string => (string) array_values((array) $row)[0])
                ->values()
                ->all();
        }

        throw new NotifyErrorException(__('Database backup is not supported for the active database driver: :driver', ['driver' => $driver]));
    }

    private function createTableStatement(string $table, string $driver): string
    {
        if ($driver === 'sqlite') {
            $row = DB::selectOne("SELECT sql FROM sqlite_master WHERE type = 'table' AND name = ?", [$table]);

            return (string) ($row->sql ?? '');
        }

        $row = DB::selectOne('SHOW CREATE TABLE '.$this->quoteIdentifier($table, $driver));

        return (string) (array_values((array) $row)[1] ?? '');
    }

    /**
     * @param resource $handle
     */
    private function writeTableRows(mixed $handle, PDO $pdo, string $table, string $driver): void
    {
        $statement = $pdo->query('SELECT * FROM '.$this->quoteIdentifier($table, $driver));

        if ($statement === false) {
            throw new NotifyErrorException(__('The database table could not be read for backup: :table', ['table' => $table]));
        }

        while (($row = $statement->fetch(PDO::FETCH_ASSOC)) !== false) {
            $columns = array_map(fn (string $column): string => $this->quoteIdentifier($column, $driver), array_keys($row));
            $values  = array_map(fn (mixed $value): string => $this->sqlValue($pdo, $value), array_values($row));

            fwrite($handle, 'INSERT INTO '.$this->quoteIdentifier($table, $driver).' ('.implode(', ', $columns).') VALUES ('.implode(', ', $values).');'.PHP_EOL);
        }
    }

    private function sqlValue(PDO $pdo, mixed $value): string
    {
        if ($value === null) {
            return 'NULL';
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        return (string) $pdo->quote((string) $value);
    }

    private function quoteIdentifier(string $identifier, string $driver): string
    {
        $quote = $driver === 'mysql' ? '`' : '"';

        return $quote.str_replace($quote, $quote.$quote, $identifier).$quote;
    }

    private function backupStorageDirectory(string $targetPath, string $backupRoot): void
    {
        if (! class_exists(ZipArchive::class)) {
            throw new NotifyErrorException(__('The PHP ZIP extension is required to create the storage backup.'));
        }

        $zip = new ZipArchive;

        if ($zip->open($targetPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new NotifyErrorException(__('The storage backup archive could not be created.'));
        }

        $sourceRoot    = $this->normalizedPath(storage_path());
        $excludedRoots = collect([
            $backupRoot,
            $this->storageDiskPath((string) config('project_updater.backups_path')),
            $this->storageDiskPath((string) config('project_updater.packages_path')),
            $this->storageDiskPath((string) config('project_updater.extract_path')),
        ])
            ->filter()
            ->map(fn (string $path): string => $this->normalizedPath($path))
            ->values()
            ->all();

        foreach (File::allFiles(storage_path(), true) as $file) {
            $path = $this->normalizedPath((string) $file->getRealPath());

            if ($this->pathStartsWithAny($path, $excludedRoots)) {
                continue;
            }

            $relativePath = ltrim(Str::after($path, $sourceRoot), '/');

            if ($relativePath !== '') {
                $zip->addFile($path, $relativePath);
            }
        }

        $zip->close();
    }

    private function archiveBackupDirectory(string $backupRoot, string $targetPath): void
    {
        if (! class_exists(ZipArchive::class)) {
            throw new NotifyErrorException(__('The PHP ZIP extension is required to create the downloadable backup.'));
        }

        $zip = new ZipArchive;

        if ($zip->open($targetPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new NotifyErrorException(__('The downloadable backup archive could not be created.'));
        }

        $sourceRoot = $this->normalizedPath($backupRoot);

        foreach (File::allFiles($backupRoot, true) as $file) {
            $path         = $this->normalizedPath((string) $file->getRealPath());
            $relativePath = ltrim(Str::after($path, $sourceRoot), '/');

            if ($relativePath !== '') {
                $zip->addFile($path, $relativePath);
            }
        }

        $zip->close();
    }

    private function storageDiskPath(string $path): string
    {
        return Storage::disk((string) config('project_updater.storage_disk'))->path(trim($path, '/'));
    }

    /**
     * @param list<string> $roots
     */
    private function pathStartsWithAny(string $path, array $roots): bool
    {
        foreach ($roots as $root) {
            if ($path === $root || str_starts_with($path, $root.'/')) {
                return true;
            }
        }

        return false;
    }

    private function normalizedPath(string $path): string
    {
        return rtrim(str_replace('\\', '/', $path), '/');
    }

    /**
     * @param array{files: list<array{path: string}>} $manifest
     */
    private function copyManifestFiles(string $extractPath, array $manifest): void
    {
        $disk = Storage::disk((string) config('project_updater.storage_disk'));

        foreach ($manifest['files'] as $file) {
            $relativePath = $this->safeRelativePath((string) ($file['path'] ?? ''));
            $source       = $disk->path($extractPath.'/'.$relativePath);
            $target       = base_path($relativePath);

            if (! File::exists($source)) {
                throw new NotifyErrorException(__('Update file missing from package: :path', ['path' => $relativePath]));
            }

            File::ensureDirectoryExists(dirname($target));
            File::copy($source, $target);
        }
    }

    private function safeRelativePath(string $path): string
    {
        $path = trim(str_replace('\\', '/', $path), '/');

        if ($path === '' || str_contains($path, '../')) {
            throw new NotifyErrorException(__('Update package contains an unsafe file path.'));
        }

        foreach (config('project_updater.protected_paths', []) as $protectedPath) {
            $protectedPath = trim((string) $protectedPath, '/');

            if ($path === $protectedPath || str_starts_with($path, $protectedPath.'/')) {
                throw new NotifyErrorException(__('Update package tried to modify a protected path: :path', ['path' => $path]));
            }
        }

        return $path;
    }
}
