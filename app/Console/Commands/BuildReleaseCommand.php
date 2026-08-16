<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\Process\Process;
use Throwable;
use ZipArchive;

/**
 * Build a CodeCanyon-ready release zip in one command.
 *
 *   php artisan release:build 1.2.3
 *
 * What it does (in order):
 *   1. Slims vendor/ via `composer install --no-dev --optimize-autoloader`
 *      so the zip ships production deps only.
 *   2. Runs `php artisan optimize:clear` so cached config / routes /
 *      compiled views don't bleed into the buyer's first boot.
 *   3. Removes the public/storage symlink so the buyer's own installer
 *      recreates it pointing at THEIR storage directory.
 *   4. Generates a sanitised SQL dump (schema for every table, rows only
 *      for reference / system tables — tenant data stripped).
 *   5. Walks the project tree, skips every path matched by the exclude
 *      list, and writes the zip.
 *   6. Restores the developer environment: `composer install` (dev deps
 *      back) and `php artisan storage:link` (symlink back).
 *
 * Output: <project>/releases/<name>-v<version>.zip
 *
 * Flags:
 *   --dry-run        : Inspect what would ship, write nothing.
 *   --skip-prep      : Skip steps 1–3 (use if you already ran them).
 *   --skip-restore   : Leave the project in "prod state" after the build.
 *   --keep-existing  : Don't overwrite an existing zip with the same name.
 */
class BuildReleaseCommand extends Command
{
    protected $signature = 'release:build
        {version=1.0.0 : Version label appended to the zip filename}
        {--name=digikash : Base name for the release zip}
        {--dry-run : Show what would be included without writing a zip}
        {--keep-existing : Do not overwrite an existing zip with the same name}
        {--skip-prep : Skip composer install --no-dev / optimize:clear / unlink storage}
        {--skip-restore : Leave the project in production state (no composer install / storage:link)}';

    protected $description = 'Build a CodeCanyon-ready release zip with a sanitised database dump.';

    /** @var list<string> */
    private array $excludePaths = [];

    /** @var list<string> */
    private array $stripTableData = [];

    /** @var array<string, array<string, string>> */
    private array $sanitizeFiles = [];

    private bool $autoStripEnabled = true;

    /** @var list<string> */
    private array $autoStripColumns = [];

    /** @var list<string> */
    private array $autoStripNeverStrip = [];

    /** @var array{detected: int, manual_only: int, total: int} */
    private array $stripStats = ['detected' => 0, 'manual_only' => 0, 'total' => 0];

    private string $projectRoot;

    public function handle(): int
    {
        // Walking vendor/ trees easily exceeds the default 128M when
        // collecting paths. Bump it for this command only.
        @ini_set('memory_limit', '512M');
        @set_time_limit(0);

        $this->projectRoot         = base_path();
        $this->excludePaths        = (array) config('release.exclude_paths', []);
        $this->stripTableData      = (array) config('release.strip_table_data', []);
        $this->sanitizeFiles       = $this->normaliseSanitizeFiles((array) config('release.sanitize_files', []));
        $this->autoStripEnabled    = (bool) config('release.auto_strip.enabled', true);
        $this->autoStripColumns    = (array) config('release.auto_strip.tenant_columns', []);
        $this->autoStripNeverStrip = (array) config('release.auto_strip.never_strip', []);

        $version     = (string) $this->argument('version');
        $name        = (string) $this->option('name');
        $isDryRun    = (bool) $this->option('dry-run');
        $keep        = (bool) $this->option('keep-existing');
        $skipPrep    = (bool) $this->option('skip-prep');
        $skipRestore = (bool) $this->option('skip-restore');
        $outputDir   = (string) config('release.output_dir', $this->projectRoot.'/releases');
        $sqlInZip    = (string) config('release.sql_dump_path', 'DB/digikash.sql');
        $zipName     = sprintf('%s-v%s.zip', $name, $version);
        $zipPath     = rtrim($outputDir, '/\\').DIRECTORY_SEPARATOR.$zipName;

        $this->components->info('Digikash release builder');
        $this->table(['Setting', 'Value'], [
            ['Version',  $version],
            ['Output',   $isDryRun ? '(dry-run)' : $zipPath],
            ['Project',  $this->projectRoot],
            ['SQL path', $sqlInZip],
            ['Prep',     $skipPrep ? 'skipped' : 'composer no-dev · optimize:clear · unlink storage'],
            ['Restore',  $skipRestore ? 'skipped' : 'composer install · storage:link'],
        ]);

        if ($this->hasUncommittedChanges()) {
            $this->components->warn('Working tree has uncommitted changes — those will end up in the zip.');
            if (! $this->confirm('Continue anyway?', true)) {
                return self::FAILURE;
            }
        }

        File::ensureDirectoryExists($outputDir);

        if (! $isDryRun && ! $keep && file_exists($zipPath)) {
            File::delete($zipPath);
        }
        if (! $isDryRun && $keep && file_exists($zipPath)) {
            $this->components->error("Zip already exists at {$zipPath}.");

            return self::FAILURE;
        }

        $needRestore = false;
        $exitCode    = self::SUCCESS;

        try {
            // ── PREP ──────────────────────────────────────────────────
            // (skipped for --dry-run, since we don't need vendor changes
            // just to walk the tree)
            if (! $isDryRun && ! $skipPrep) {
                $this->newLine();
                $this->components->info('Preparing project for clean release');

                if (! $this->runProcess(
                    'Composer install (--no-dev --optimize-autoloader)',
                    [...$this->composerBinary(), 'install', '--no-dev', '--optimize-autoloader', '--no-interaction'],
                    600
                )) {
                    $this->components->error('composer install --no-dev failed. Aborting build.');

                    return self::FAILURE;
                }
                $needRestore = true;

                $this->runProcess(
                    'Laravel optimize:clear',
                    [PHP_BINARY, 'artisan', 'optimize:clear'],
                    60
                );

                $this->components->task('Unlinking public/storage symlink', fn () => $this->unlinkPublicStorage());

                $this->newLine();
            }

            $exitCode = $this->buildArtefact(
                $zipPath,
                $sqlInZip,
                $isDryRun
            );
        } finally {
            // ── RESTORE ──────────────────────────────────────────────
            if ($needRestore && ! $skipRestore) {
                $this->newLine();
                $this->components->info('Restoring developer environment');

                $this->runProcess(
                    'Composer install (with dev deps)',
                    [...$this->composerBinary(), 'install', '--no-interaction'],
                    600
                );

                $this->runProcess(
                    'Re-linking public/storage',
                    [PHP_BINARY, 'artisan', 'storage:link'],
                    30
                );
            }
        }

        return $exitCode;
    }

    /**
     * Generates the SQL + writes the zip (or counts files for --dry-run).
     */
    private function buildArtefact(string $zipPath, string $sqlInZip, bool $isDryRun): int
    {

        // 1. Build the sanitised SQL into memory (small — <1 MB).
        $sql = null;
        $this->components->task('Generating sanitised SQL dump', function () use (&$sql) {
            $sql = $this->buildSanitisedSql();
        });

        $this->components->info(sprintf(
            ' → SQL dump: %s (%d tables · %d stripped — %d auto-detected, %d manual-only)',
            $this->formatBytes(strlen($sql ?? '')),
            $this->countTables(),
            $this->stripStats['total'],
            $this->stripStats['detected'],
            $this->stripStats['manual_only']
        ));

        // 2. Stream the project tree into the zip (or count, if dry-run).
        $sqlInZipNorm = strtolower(str_replace('\\', '/', ltrim($sqlInZip, '/\\')));

        $zip = null;
        if (! $isDryRun) {
            $zip    = new ZipArchive;
            $opened = $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
            if ($opened !== true) {
                $this->components->error("Could not open zip at {$zipPath} (code {$opened}).");

                return self::FAILURE;
            }
        }

        $totals    = ['count' => 0, 'bytes' => 0];
        $byTopDir  = [];
        $taskLabel = $isDryRun ? 'Inspecting project tree' : 'Writing zip archive';

        $this->components->task($taskLabel, function () use ($zip, $sqlInZipNorm, &$totals, &$byTopDir, $isDryRun): void {
            foreach ($this->streamShippableFiles() as $relative => $absolute) {
                // Skip the dev SQL — we replace it with the sanitised one.
                if (strtolower(str_replace('\\', '/', $relative)) === $sqlInZipNorm) {
                    continue;
                }

                $size = filesize($absolute) ?: 0;
                $totals['count']++;
                $totals['bytes'] += $size;

                if ($isDryRun) {
                    $top = explode('/', str_replace('\\', '/', $relative))[0];
                    $byTopDir[$top] ??= ['count' => 0, 'bytes' => 0];
                    $byTopDir[$top]['count']++;
                    $byTopDir[$top]['bytes'] += $size;

                    continue;
                }

                $sanitised = $this->sanitiseContentFor($relative, $absolute);
                if ($sanitised !== null) {
                    $zip->addFromString($relative, $sanitised);

                    continue;
                }

                $zip->addFile($absolute, $relative);
            }
        });

        $this->components->info(sprintf(' → Files: %d (%s)', $totals['count'], $this->formatBytes($totals['bytes'])));

        if ($isDryRun) {
            $rows = [];
            ksort($byTopDir);
            foreach ($byTopDir as $top => $info) {
                $rows[] = [$top, $info['count'], $this->formatBytes($info['bytes'])];
            }
            $this->table(['Top-level entry', 'Files', 'Size'], $rows);

            return self::SUCCESS;
        }

        // 3. Overlay the clean SQL dump + keep runtime cache dirs alive.
        //    Every dir on this list is required for a fresh Laravel install
        //    to boot — they were excluded above so the buyer's own runtime
        //    state stays clean, but the directory itself must exist.
        $zip->addFromString($sqlInZip, $sql);
        $runtimeDirs = [
            'bootstrap/cache',
            'storage/app',
            'storage/app/public',
            'storage/app/private',
            'storage/framework/cache/data',
            'storage/framework/sessions',
            'storage/framework/views',
            'storage/logs',
        ];
        foreach ($runtimeDirs as $runtimeDir) {
            $zip->addEmptyDir($runtimeDir);
            $zip->addFromString($runtimeDir.'/.gitkeep', '');
        }

        $zip->close();

        $finalSize = file_exists($zipPath) ? filesize($zipPath) : 0;
        $this->components->info(sprintf('Built %s (%s)', $zipPath, $this->formatBytes($finalSize)));

        return self::SUCCESS;
    }

    /**
     * Build the SQL dump string. Schema for every table, data only for
     * tables NOT listed in config('release.strip_table_data').
     *
     * Supports MySQL (production) + SQLite (tests run on :memory: sqlite).
     */
    private function buildSanitisedSql(): string
    {
        $driver = DB::connection()->getDriverName();
        $tables = $this->listTables($driver);

        $autoDetected = $this->autoDetectTenantTables($tables);
        $stripSet     = array_values(array_unique([...$this->stripTableData, ...$autoDetected]));

        $this->stripStats = [
            'detected'    => count($autoDetected),
            'manual_only' => count(array_diff($this->stripTableData, $autoDetected)),
            'total'       => count($stripSet),
        ];

        $strip = array_flip($stripSet);

        $sql = "-- Digikash release SQL dump\n";
        $sql .= '-- Generated: '.now()->toIso8601String()."\n";
        $sql .= '-- Tables: total='.count($tables).', data-stripped='.count($strip);
        $sql .= ' (auto-detected='.$this->stripStats['detected'].', manual-only='.$this->stripStats['manual_only'].")\n";
        $sql .= "-- Source driver: {$driver}\n\n";

        if ($driver === 'mysql') {
            $sql .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
            $sql .= "SET FOREIGN_KEY_CHECKS = 0;\n";
            $sql .= "START TRANSACTION;\n";
            $sql .= "SET time_zone = \"+00:00\";\n";
            $sql .= "SET NAMES utf8mb4;\n\n";
        }

        foreach ($tables as $table) {
            $sql .= $this->dumpTableSchema($driver, $table);
            if (! isset($strip[$table])) {
                $sql .= $this->dumpTableData($table);
            } else {
                $sql .= "-- ↪ data stripped (tenant / transient table)\n\n";
            }
        }

        if ($driver === 'mysql') {
            $sql .= "SET FOREIGN_KEY_CHECKS = 1;\n";
            $sql .= "COMMIT;\n";
        }

        return $sql;
    }

    /**
     * Inspect each table's columns and return those that have at least one
     * "tenant FK" column configured in release.auto_strip.tenant_columns.
     *
     * Tables listed in release.auto_strip.never_strip are exempt — useful
     * for admin-configured rules tables that happen to carry an `agent_id`
     * or `user_id` column but should still ship their data.
     *
     * @param  list<string> $tables
     * @return list<string>
     */
    private function autoDetectTenantTables(array $tables): array
    {
        if (! $this->autoStripEnabled || $this->autoStripColumns === []) {
            return [];
        }

        $exempt   = array_flip($this->autoStripNeverStrip);
        $detected = [];

        foreach ($tables as $table) {
            if (isset($exempt[$table])) {
                continue;
            }

            try {
                $columns = Schema::getColumnListing($table);
            } catch (Throwable) {
                continue;
            }

            if (array_intersect($columns, $this->autoStripColumns) !== []) {
                $detected[] = $table;
            }
        }

        return $detected;
    }

    /**
     * @return list<string>
     */
    private function listTables(string $driver): array
    {
        if ($driver === 'sqlite') {
            $rows = DB::select("SELECT name FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite_%' ORDER BY name");

            return array_map(fn ($r) => $r->name, $rows);
        }

        return collect(DB::select('SHOW TABLES'))
            ->map(fn ($row) => (array) $row)
            ->map(fn (array $row) => array_values($row)[0])
            ->all();
    }

    private function dumpTableSchema(string $driver, string $table): string
    {
        if ($driver === 'sqlite') {
            $row    = DB::selectOne('SELECT sql FROM sqlite_master WHERE type = ? AND name = ?', ['table', $table]);
            $create = $row?->sql ?? '';
        } else {
            $row     = (array) DB::select("SHOW CREATE TABLE `{$table}`")[0];
            $columns = array_change_key_case($row, CASE_LOWER);
            $create  = $columns['create table'] ?? reset($columns);
        }

        $out = "\n-- ----------------------------------------------------------\n";
        $out .= "-- Table: {$table}\n";
        $out .= "-- ----------------------------------------------------------\n\n";
        $out .= "DROP TABLE IF EXISTS `{$table}`;\n";
        $out .= $create.";\n\n";

        return $out;
    }

    private function dumpTableData(string $table): string
    {
        $rows = DB::table($table)->get();
        if ($rows->isEmpty()) {
            return "-- (no data)\n\n";
        }

        $columns = array_keys((array) $rows->first());
        $colList = '`'.implode('`, `', $columns).'`';

        $out = "INSERT INTO `{$table}` ({$colList}) VALUES\n";

        $valueRows = [];
        foreach ($rows as $row) {
            $values = [];
            foreach ((array) $row as $value) {
                $values[] = $this->quoteValue($value);
            }
            $valueRows[] = '('.implode(', ', $values).')';
        }

        $out .= implode(",\n", $valueRows).";\n\n";

        return $out;
    }

    private function quoteValue(mixed $value): string
    {
        if ($value === null) {
            return 'NULL';
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        // Use DB connection's quoting so escaping is correct for the driver.
        return DB::connection()->getPdo()->quote((string) $value);
    }

    private function countTables(): int
    {
        return count($this->listTables(DB::connection()->getDriverName()));
    }

    /**
     * Yield every shippable file as `relativePath => absolutePath`.
     *
     * Implemented as a generator so we never hold more than one entry's
     * worth of state at a time — vendor/ alone has tens of thousands of
     * files and buffering them as SplFileInfo objects blew past 128MB.
     *
     * @return \Generator<string, string>
     */
    private function streamShippableFiles(): \Generator
    {
        yield from $this->walk($this->projectRoot, '');
    }

    /**
     * @return \Generator<string, string>
     */
    private function walk(string $absoluteDir, string $relativeDir): \Generator
    {
        $handle = @opendir($absoluteDir);
        if ($handle === false) {
            return;
        }

        try {
            while (($entry = readdir($handle)) !== false) {
                if ($entry === '.' || $entry === '..') {
                    continue;
                }

                $relative = $relativeDir === '' ? $entry : $relativeDir.'/'.$entry;
                $absolute = $absoluteDir.DIRECTORY_SEPARATOR.$entry;

                if ($this->isExcluded($relative)) {
                    continue;
                }

                if (is_dir($absolute) && ! is_link($absolute)) {
                    yield from $this->walk($absolute, $relative);

                    continue;
                }

                if (is_file($absolute)) {
                    yield $relative => $absolute;
                }
            }
        } finally {
            closedir($handle);
        }
    }

    /**
     * Normalise the sanitize_files map so keys can be matched against
     * project-relative paths regardless of how the developer wrote them
     * (Windows backslashes, leading slashes, mixed case).
     *
     * @param  array<string, mixed>                 $config
     * @return array<string, array<string, string>>
     */
    private function normaliseSanitizeFiles(array $config): array
    {
        $normalised = [];

        foreach ($config as $path => $rules) {
            if (! is_string($path) || ! is_array($rules)) {
                continue;
            }

            $key = strtolower(str_replace('\\', '/', ltrim($path, '/\\')));

            $normalised[$key] = array_map('strval', $rules);
        }

        return $normalised;
    }

    /**
     * Apply the configured regex rewrites to a file if it's on the
     * sanitize list. Returns the rewritten content, or null when the
     * file should be shipped unchanged.
     */
    private function sanitiseContentFor(string $relative, string $absolute): ?string
    {
        if ($this->sanitizeFiles === []) {
            return null;
        }

        $key = strtolower(str_replace('\\', '/', $relative));

        if (! isset($this->sanitizeFiles[$key])) {
            return null;
        }

        $content = @file_get_contents($absolute);

        if ($content === false) {
            return null;
        }

        foreach ($this->sanitizeFiles[$key] as $pattern => $replacement) {
            $result = preg_replace($pattern, $replacement, $content);

            if ($result === null) {
                throw new \RuntimeException("Sanitize pattern '{$pattern}' failed for {$relative}.");
            }

            $content = $result;
        }

        return $content;
    }

    /**
     * Decide whether a project-relative path matches any exclude pattern.
     *
     * Rules:
     *   • exact match (e.g. ".env")
     *   • directory prefix match (e.g. "tests/" matches "tests/Feature/Foo.php")
     *   • basename match (e.g. "node_modules" matches any nested
     *     "some/path/node_modules" or files inside it)
     *   • fnmatch glob (e.g. "*.log")
     */
    private function isExcluded(string $relative): bool
    {
        $relative = str_replace('\\', '/', $relative);

        foreach ($this->excludePaths as $pattern) {
            $pattern = str_replace('\\', '/', trim($pattern, '/'));

            if ($pattern === '') {
                continue;
            }
            if ($relative === $pattern) {
                return true;
            }
            if (str_starts_with($relative.'/', $pattern.'/')) {
                return true;
            }
            // Match any nested directory of that name (e.g. "node_modules"
            // inside any subfolder).
            if (str_contains('/'.$relative, '/'.$pattern.'/') || str_ends_with($relative, '/'.$pattern)) {
                return true;
            }
            // Glob fallback (e.g. "*.log", "*.bak").
            if ((str_contains($pattern, '*') || str_contains($pattern, '?'))
                && (fnmatch($pattern, $relative) || fnmatch($pattern, basename($relative)))
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Run an external command via Symfony Process, displaying a Laravel
     * "task" spinner. Captures STDOUT/STDERR and only spills them to the
     * console on failure.
     *
     * @param list<string> $command
     */
    private function runProcess(string $label, array $command, int $timeoutSeconds): bool
    {
        $ok = false;
        $this->components->task($label, function () use ($command, $timeoutSeconds, &$ok): bool {
            try {
                $process = new Process($command, $this->projectRoot, env: null, input: null, timeout: $timeoutSeconds);
                $process->run();
                $ok = $process->isSuccessful();
                if (! $ok) {
                    $this->newLine();
                    $this->line(trim($process->getErrorOutput() ?: $process->getOutput()));
                }

                return $ok;
            } catch (Throwable $e) {
                $this->newLine();
                $this->line($e->getMessage());

                return false;
            }
        });

        return $ok;
    }

    /**
     * Locate a usable composer entry point as an arg-vector.
     * Project-local composer.phar wins (deterministic version);
     * otherwise rely on PATH (composer / composer.bat on Windows).
     *
     * @return list<string>
     */
    private function composerBinary(): array
    {
        $phar = $this->projectRoot.DIRECTORY_SEPARATOR.'composer.phar';
        if (is_file($phar)) {
            return [PHP_BINARY, $phar];
        }

        return ['composer'];
    }

    /**
     * Remove the public/storage symlink so the buyer's installer can
     * re-create it pointing at their own storage. Handles both POSIX
     * symlinks and Windows directory junctions.
     */
    private function unlinkPublicStorage(): bool
    {
        $path = public_path('storage');

        if (! file_exists($path) && ! is_link($path)) {
            return true;
        }
        if (is_link($path)) {
            return @unlink($path);
        }
        if (is_dir($path)) {
            return @rmdir($path);
        }

        return @unlink($path);
    }

    private function hasUncommittedChanges(): bool
    {
        if (! is_dir($this->projectRoot.'/.git')) {
            return false;
        }

        $cwd = getcwd();
        chdir($this->projectRoot);
        $output = @shell_exec('git status --porcelain 2>&1');
        if ($cwd !== false) {
            chdir($cwd);
        }

        return is_string($output) && trim($output) !== '';
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i     = 0;
        $size  = (float) $bytes;
        while ($size >= 1024 && $i < count($units) - 1) {
            $size /= 1024;
            $i++;
        }

        return sprintf('%.1f %s', $size, $units[$i]);
    }
}
