@php
    $faviconPath = (string) setting('site_favicon');
    $faviconUrl = filter_var($faviconPath, FILTER_VALIDATE_URL) ? $faviconPath : asset($faviconPath);
    $faviconPathForType = parse_url($faviconPath, PHP_URL_PATH) ?: $faviconPath;
    $faviconExtension = strtolower(pathinfo($faviconPathForType, PATHINFO_EXTENSION));
    $faviconType = match ($faviconExtension) {
        'svg' => 'image/svg+xml',
        'png' => 'image/png',
        'jpg', 'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        default => 'image/x-icon',
    };

    $relativeFaviconPath = ltrim($faviconPathForType, '/');
    $faviconDiskPath = null;
    if (! filter_var($faviconPath, FILTER_VALIDATE_URL)) {
        $faviconDiskPath = str_starts_with($relativeFaviconPath, 'storage/')
            ? storage_path('app/public/'.substr($relativeFaviconPath, 8))
            : public_path($relativeFaviconPath);
    }

    $faviconVersion = $faviconDiskPath && is_file($faviconDiskPath)
        ? filemtime($faviconDiskPath)
        : config('app.version');
    $faviconUrlWithVersion = $faviconUrl.(str_contains($faviconUrl, '?') ? '&' : '?').'v='.$faviconVersion;
@endphp
<link rel="icon" href="{{ $faviconUrlWithVersion }}" type="{{ $faviconType }}" data-site-favicon>
<link rel="shortcut icon" href="{{ $faviconUrlWithVersion }}" type="{{ $faviconType }}" data-site-favicon>
<script>
    'use strict';
    (function () {
        var href = @json($faviconUrlWithVersion);
        var type = @json($faviconType);

        document.querySelectorAll('link[rel~="icon"]').forEach(function (link) {
            link.remove();
        });

        ['icon', 'shortcut icon'].forEach(function (rel) {
            var link = document.createElement('link');
            link.setAttribute('rel', rel);
            link.setAttribute('href', href);
            link.setAttribute('type', type);
            link.setAttribute('data-site-favicon', '');
            document.head.appendChild(link);
        });
    })();
</script>
