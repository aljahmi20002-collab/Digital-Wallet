<?php

namespace App\Traits;

use DOMDocument;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

trait FileManageTrait
{
    /**
     * Upload an image (optionally resize) with support for temp upload.
     */
    public function uploadImage(UploadedFile $file, ?string $old = null, bool $resizeImage = false, ?int $width = null, ?int $height = null, bool $isTemp = false): string
    {
        $allowedExtensions = ['jpeg', 'jpg', 'png', 'gif', 'webp', 'svg', 'ico'];

        return $this->handleUpload($file, $old, 'images', $allowedExtensions, $resizeImage, $width, $height, $isTemp);
    }

    /**
     * Upload a general file.
     */
    public function uploadFile(UploadedFile $file, ?string $old = null, bool $isTemp = false): string
    {
        $allowedExtensions = [
            'zip', 'rar', 'pdf', 'doc', 'docx', 'txt', 'csv', 'xml', 'json',
            'ppt', 'pptx', 'ods', 'odt', 'xls', 'xlsx', 'png', 'jpg', 'gif', 'svg', 'webp',
        ];

        return $this->handleUpload($file, $old, 'files', $allowedExtensions, false, null, null, $isTemp);
    }

    /**
     * Delete a file from storage.
     */
    public function delete(?string $path): void
    {
        if (blank($path)) {
            return;
        }

        $path = ltrim($path, '/');
        if (str_starts_with($path, 'storage/')) {
            $path = substr($path, 8);
        }

        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    public function deleteSummernoteImages(array|string|null $content): void
    {
        if (is_array($content)) {
            foreach ($content as $html) {
                $this->deleteImagesFromHtml($html);
            }
        } elseif (is_string($content)) {
            $this->deleteImagesFromHtml($content);
        }
    }

    /**
     * Handle the upload process.
     */
    private function handleUpload(
        UploadedFile $file,
        ?string $old,
        string $uploadRoot,
        array $allowedExtensions,
        bool $resizeImage = false,
        ?int $width = null,
        ?int $height = null,
        bool $isTemp = false
    ): string {
        $extension = strtolower($file->getClientOriginalExtension());

        // Max size validation (dynamic)
        $maxSizeMB = setting('max_upload_size', 5);
        if ($file->getSize() > $maxSizeMB * 1024 * 1024 || ! in_array($extension, $allowedExtensions, true)) {
            abort(406, 'Invalid file. Max: '.$maxSizeMB.'MB. Allowed: '.implode(', ', $allowedExtensions));
        }

        // Delete old if exists
        if ($old) {
            $this->delete($old);
        }

        // Upload Path
        $dateFolder = now()->format('Y/m/d');
        $path       = $isTemp ? "{$uploadRoot}/temp/{$dateFolder}" : "{$uploadRoot}/{$dateFolder}";

        // File Name
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $fileName     = now()->format('Ymd_His').'_'.Str::slug($originalName, '_').'_'.Str::random(4).'.'.$extension;
        $storagePath  = "{$path}/{$fileName}";

        if ($resizeImage && in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            $manager = new ImageManager(new Driver);
            $image   = $manager->read($file->getRealPath());

            if ($width) {
                $image->scale(width: $width, height: $height);
            }

            Storage::disk('public')->put($storagePath, (string) $image->encode());
        } elseif ($extension === 'svg') {
            // SVGs can contain <script> tags and on* event handlers that
            // execute JS when the image is rendered. Sanitise before storing
            // so a malicious avatar/KYC doc can't pop a popup in admin's
            // browser when listed.
            Storage::disk('public')->put($storagePath, $this->sanitiseSvg(file_get_contents($file->getRealPath()) ?: ''));
        } else {
            $file->storeAs($path, $fileName, 'public');
        }

        return $storagePath;
    }

    /**
     * Strip <script> tags, javascript: URLs, and on* event handlers from
     * SVG content so it's safe to inline via <img> / object / direct GET.
     * Conservative — drops every element we don't recognise as part of
     * the standard SVG drawing vocabulary.
     */
    private function sanitiseSvg(string $svg): string
    {
        if ($svg === '') {
            return $svg;
        }

        // Remove <script> blocks entirely.
        $clean = preg_replace('#<\s*script\b[^>]*>.*?<\s*/\s*script\s*>#is', '', $svg);
        $clean = preg_replace('#<\s*script\b[^>]*/?>#is', '', (string) $clean);

        // Strip on* event handler attributes (onclick, onload, onmouseover, ...).
        $clean = preg_replace('#\s+on[a-z]+\s*=\s*"[^"]*"#i', '', (string) $clean);
        $clean = preg_replace("#\s+on[a-z]+\s*=\s*'[^']*'#i", '', (string) $clean);
        $clean = preg_replace('#\s+on[a-z]+\s*=\s*[^\s>]+#i', '', (string) $clean);

        // Neutralise javascript:/data:/vbscript: in href/xlink:href.
        $clean = preg_replace('#((?:xlink:)?href)\s*=\s*([\'"])(?:javascript|data|vbscript|file)\s*:[^\'"]*\2#i', '$1=$2#$2', (string) $clean);

        // Drop <foreignObject> — can embed HTML inside SVG.
        $clean = preg_replace('#<\s*foreignObject\b[^>]*>.*?<\s*/\s*foreignObject\s*>#is', '', (string) $clean);

        return (string) $clean;
    }

    /**
     * Parse a HTML content and delete embedded images from storage.
     */
    private function deleteImagesFromHtml(?string $html): void
    {
        if (blank($html)) {
            return;
        }

        $dom = new DOMDocument;
        libxml_use_internal_errors(true);
        $dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
        libxml_clear_errors();

        $images = $dom->getElementsByTagName('img');

        foreach ($images as $img) {
            $src = $img->getAttribute('src');

            if (str_contains($src, asset('storage/'))) {
                $relativePath = str_replace(asset('storage/').'/', '', $src);

                // Delete the image from storage
                $this->delete($relativePath);
            }
        }
    }
}
