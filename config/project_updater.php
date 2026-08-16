<?php

return [
    'version' => '2.0',

    'product_slug' => env('PROJECT_UPDATER_PRODUCT_SLUG', 'digikash'),
    'item_id' => env('PROJECT_UPDATER_ENVATO_ITEM_ID', '58275561'),
    'channel' => env('PROJECT_UPDATER_CHANNEL', 'stable'),

    'server_url' => env('PROJECT_UPDATER_SERVER_URL', 'https://updates.coevs.com'),
    'timeout' => (int)env('PROJECT_UPDATER_TIMEOUT', 20),
    'retries' => (int)env('PROJECT_UPDATER_RETRIES', 2),

    'install_enabled' => (bool)env('PROJECT_UPDATER_INSTALL_ENABLED', true),
    'public_key' => env('PROJECT_UPDATER_PUBLIC_KEY') ?: <<<'PUBLIC_KEY'
-----BEGIN PUBLIC KEY-----
MIICIjANBgkqhkiG9w0BAQEFAAOCAg8AMIICCgKCAgEAiAVubkVTZdeDI9aIIMSP
7ZtVe6IVoj2748ppHXeLEtJQF8ffn1+hFb56/9Fjk9CQR0R3PvFlPH4+RfRqT8Tp
m24mx6BgtiYh8oX+cCy8zYeyFPt3AR6lLuOtayXwoVJ1UeiveedWDDV4kYiXUpu8
vk/yPpPWl/hZ2pxTGPneTvGz+Jp2Ci+JteMuhXSw4mbDQWQz0XvPoI8AK/Yjihuk
ojI4j3Q2kVnGuia19WKDdmpV56akZK9HFRuGsoeOd/Z8NPK7/P2eKY5H3Q5xCstN
7S8i6W04w+zZ6ZadtXCfB5fcOnjROjuzZSGtsj2RJe37DbcjSSIk2oxRsHCBRhlm
Bv8lOdc7cMDRwlndBfYU/WYa7My6AzDoTW2XITtIe13DYRxJHdkmFqCgCZT5PCMZ
UYJykt/ERoRVbaVpxAa8GOR1YxaJCSmmDukv3RQRI/gre+C3VSPuq+CZNIgPmtMD
Ap04YND9EN7CDccqYLm7ehX6/H5kQPdNOkrYP18kYl/tiL9AC0SQpDftAIoTx9lV
mm1+5VSDXEIbUSADN3bfN+1aMlZ1MeBK8jqyf7o1gwYJsxglTwfOIoAZvxB1xlhe
w5Z+u32Nc+ZR0QS3cdxSUOQuSk3iHnYa75t9SwkiR6lZZOQP2gMWnaIjOkgqeWBr
A4hPkyT44cLR64OkGZZf+jECAwEAAQ==
-----END PUBLIC KEY-----
PUBLIC_KEY,

    'storage_disk' => env('PROJECT_UPDATER_STORAGE_DISK', 'local'),
    'packages_path' => env('PROJECT_UPDATER_PACKAGES_PATH', 'updates/packages'),
    'extract_path' => env('PROJECT_UPDATER_EXTRACT_PATH', 'updates/extracted'),
    'backups_path' => env('PROJECT_UPDATER_BACKUPS_PATH', 'updates/backups'),

    'protected_paths' => [
        '.env',
        'storage',
        'vendor',
        'node_modules',
        '.git',
        '.ai',
        '.aiassistant',
        '.claude',
        '.codex',
        '.mcp.json',
        '_ide_helper.php',
        'AGENTS.md',
        'boost.json',
        'CLAUDE.md',
        'herd.yml',

    ],
];
