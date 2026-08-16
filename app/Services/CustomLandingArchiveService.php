<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;
use ZipArchive;

class CustomLandingArchiveService
{
    /**
     * @var list<string>
     */
    private const ALLOWED_EXTENSIONS = [
        'css',
        'gif',
        'html',
        'ico',
        'jpeg',
        'jpg',
        'js',
        'json',
        'mjs',
        'otf',
        'png',
        'svg',
        'ttf',
        'txt',
        'webmanifest',
        'webp',
        'woff',
        'woff2',
    ];

    private const MAX_FILES = 150;

    private const MAX_EXTRACTED_BYTES = 26214400;

    private const MAX_DEPTH = 6;

    public function __construct(private readonly CustomLandingHtmlCompiler $htmlCompiler) {}

    /**
     * @return array{path: string, file_count: int, total_size: int, source_checksum: string}
     */
    public function prepare(UploadedFile $zipFile, string $folder): array
    {
        $archivePath = $zipFile->getRealPath();

        if (! is_string($archivePath)) {
            throw ValidationException::withMessages([
                'zipFile' => __('The uploaded ZIP file could not be read.'),
            ]);
        }

        $workingPath = storage_path('app/custom-landings/tmp/'.$folder.'-'.Str::random(10));

        File::ensureDirectoryExists($workingPath, 0755, true);

        try {
            $stats = $this->extractArchive($archivePath, $workingPath, $folder);
        } catch (Throwable $e) {
            $this->discard($workingPath);

            throw $e;
        }

        return [
            'path'            => $workingPath,
            'file_count'      => $stats['file_count'],
            'total_size'      => $stats['total_size'],
            'source_checksum' => hash_file('sha256', $archivePath) ?: '',
        ];
    }

    public function publishPreparedDirectory(string $preparedPath, string $targetPath): void
    {
        if (! File::isDirectory($preparedPath)) {
            throw new RuntimeException('Prepared custom landing directory is missing.');
        }

        File::ensureDirectoryExists(dirname($targetPath), 0755, true);

        $backupPath = null;

        if (File::isDirectory($targetPath)) {
            $backupPath = dirname($targetPath).DIRECTORY_SEPARATOR.'.'.basename($targetPath).'-backup-'.Str::random(10);
            File::moveDirectory($targetPath, $backupPath, true);
        }

        try {
            File::moveDirectory($preparedPath, $targetPath, true);

            if ($backupPath !== null) {
                File::deleteDirectory($backupPath);
            }
        } catch (Throwable $e) {
            if ($backupPath !== null && File::isDirectory($backupPath) && ! File::isDirectory($targetPath)) {
                File::moveDirectory($backupPath, $targetPath, true);
            }

            throw $e;
        } finally {
            $this->discard($preparedPath);
        }
    }

    public function discard(?string $path): void
    {
        if (is_string($path) && File::isDirectory($path)) {
            File::deleteDirectory($path);
        }
    }

    /**
     * @return array{file_count: int, total_size: int}
     */
    private function extractArchive(string $archivePath, string $workingPath, string $folder): array
    {
        $zip    = new ZipArchive;
        $opened = $zip->open($archivePath);

        if ($opened !== true) {
            throw ValidationException::withMessages([
                'zipFile' => __('The ZIP file could not be opened.'),
            ]);
        }

        try {
            $entries = $this->inspectArchive($zip);

            foreach ($entries as $entry) {
                $destination = $workingPath.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $entry['name']);

                File::ensureDirectoryExists(dirname($destination), 0755, true);

                $sourceStream = $zip->getStream($entry['source']);

                if ($sourceStream === false) {
                    throw ValidationException::withMessages([
                        'zipFile' => __('The ZIP file contains a file that could not be read.'),
                    ]);
                }

                $destinationStream = fopen($destination, 'wb');

                if ($destinationStream === false) {
                    fclose($sourceStream);

                    throw new RuntimeException('Unable to write custom landing file.');
                }

                try {
                    stream_copy_to_stream($sourceStream, $destinationStream);
                } finally {
                    fclose($sourceStream);
                    fclose($destinationStream);
                }
            }

            $indexPath = $workingPath.DIRECTORY_SEPARATOR.'index.html';
            $content   = File::get($indexPath);

            File::replace($indexPath, $this->htmlCompiler->compileForPublish($content, $folder));

            return [
                'file_count' => count($entries),
                'total_size' => (int) array_sum(array_column($entries, 'size')),
            ];
        } finally {
            $zip->close();
        }
    }

    /**
     * @return list<array{name: string, source: string, size: int}>
     */
    private function inspectArchive(ZipArchive $zip): array
    {
        $entries      = [];
        $totalSize    = 0;
        $hasRootIndex = false;

        for ($index = 0; $index < $zip->numFiles; $index++) {
            $stat = $zip->statIndex($index);

            if ($stat === false || ! isset($stat['name'])) {
                throw ValidationException::withMessages([
                    'zipFile' => __('The ZIP file contains an unreadable entry.'),
                ]);
            }

            $entryName = $this->normalizeEntryName($stat['name']);

            if ($entryName === null) {
                continue;
            }

            $this->guardAgainstUnsafeEntry($zip, $index, $entryName);

            $entrySize = (int) ($stat['size'] ?? 0);
            $totalSize += $entrySize;

            if (count($entries) + 1 > self::MAX_FILES) {
                throw ValidationException::withMessages([
                    'zipFile' => __('The ZIP file contains too many files.'),
                ]);
            }

            if ($totalSize > self::MAX_EXTRACTED_BYTES) {
                throw ValidationException::withMessages([
                    'zipFile' => __('The ZIP file expands to more than :size MB.', [
                        'size' => self::MAX_EXTRACTED_BYTES / 1024 / 1024,
                    ]),
                ]);
            }

            if ($entryName === 'index.html') {
                $hasRootIndex = true;
            }

            $entries[] = [
                'name'   => $entryName,
                'source' => $stat['name'],
                'size'   => $entrySize,
            ];
        }

        if (! $hasRootIndex) {
            throw ValidationException::withMessages([
                'zipFile' => __('The ZIP file must contain index.html at the root level.'),
            ]);
        }

        return $entries;
    }

    private function normalizeEntryName(string $name): ?string
    {
        $name = str_replace('\\', '/', $name);

        if (str_starts_with($name, '/')) {
            throw ValidationException::withMessages([
                'zipFile' => __('The ZIP file contains an unsafe absolute path.'),
            ]);
        }

        $name = ltrim($name, '/');

        if ($name === '' || str_ends_with($name, '/')) {
            return null;
        }

        if (preg_match('/^[a-zA-Z]:/', $name) === 1) {
            throw ValidationException::withMessages([
                'zipFile' => __('The ZIP file contains an unsafe absolute path.'),
            ]);
        }

        return $name;
    }

    private function guardAgainstUnsafeEntry(ZipArchive $zip, int $index, string $name): void
    {
        $segments = explode('/', $name);

        if (count($segments) > self::MAX_DEPTH) {
            throw ValidationException::withMessages([
                'zipFile' => __('The ZIP file contains folders nested too deeply.'),
            ]);
        }

        foreach ($segments as $segment) {
            if ($segment === '' || $segment === '.' || $segment === '..' || str_starts_with($segment, '.')) {
                throw ValidationException::withMessages([
                    'zipFile' => __('The ZIP file contains an unsafe path.'),
                ]);
            }
        }

        $extension = Str::lower(pathinfo($name, PATHINFO_EXTENSION));

        if (! in_array($extension, self::ALLOWED_EXTENSIONS, true)) {
            throw ValidationException::withMessages([
                'zipFile' => __('The ZIP file contains an unsupported file type: :file', [
                    'file' => $name,
                ]),
            ]);
        }

        $this->guardAgainstSymlink($zip, $index);
    }

    private function guardAgainstSymlink(ZipArchive $zip, int $index): void
    {
        if (! method_exists($zip, 'getExternalAttributesIndex')) {
            return;
        }

        $operatingSystem = 0;
        $attributes      = 0;

        if (! $zip->getExternalAttributesIndex($index, $operatingSystem, $attributes)) {
            return;
        }

        if ($operatingSystem !== ZipArchive::OPSYS_UNIX) {
            return;
        }

        $fileType = ($attributes >> 16) & 0170000;

        if ($fileType === 0120000) {
            throw ValidationException::withMessages([
                'zipFile' => __('The ZIP file cannot contain symbolic links.'),
            ]);
        }
    }
}
