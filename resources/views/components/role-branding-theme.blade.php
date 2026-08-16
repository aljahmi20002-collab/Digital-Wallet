@php
    $fallbacks = [
        'admin' => [
            'primary' => '#4f46e5',
            'accent'  => '#22c55e',
        ],
        'user' => [
            'primary' => '#4663ee',
            'accent'  => '#17a86a',
        ],
        'merchant' => [
            'primary' => '#16a34a',
            'accent'  => '#14b8a6',
        ],
        'agent' => [
            'primary' => '#4f46e5',
            'accent'  => '#f59e0b',
        ],
    ];

    $normalizeHex = static function (mixed $value, string $fallback): string {
        $value = strtoupper(trim((string) $value));

        return preg_match('/^#[0-9A-F]{6}$/', $value) === 1 ? $value : strtoupper($fallback);
    };

    $rgb = static function (string $hex): string {
        $hex = ltrim($hex, '#');

        return collect(str_split($hex, 2))
            ->map(fn (string $value): int => hexdec($value))
            ->implode(', ');
    };

    $shade = static function (string $hex, int $percent): string {
        $hex = ltrim($hex, '#');
        $channels = collect(str_split($hex, 2))->map(fn (string $value): int => hexdec($value));
        $target = $percent < 0 ? 0 : 255;
        $factor = abs($percent) / 100;

        return '#'.$channels
            ->map(fn (int $channel): string => str_pad(dechex((int) round($channel + (($target - $channel) * $factor))), 2, '0', STR_PAD_LEFT))
            ->implode('');
    };

    $colors = collect($fallbacks)->mapWithKeys(function (array $fallback, string $role) use ($normalizeHex, $rgb, $shade): array {
        $primary = $role === 'admin'
            ? $normalizeHex($fallback['primary'], $fallback['primary'])
            : $normalizeHex(setting("{$role}_role_primary_color", $fallback['primary']), $fallback['primary']);
        $accent = $role === 'admin'
            ? $normalizeHex($fallback['accent'], $fallback['accent'])
            : $normalizeHex(setting("{$role}_role_accent_color", $fallback['accent']), $fallback['accent']);

        return [
            $role => [
                'primary'     => $primary,
                'primaryRgb'  => $rgb($primary),
                'primaryHover' => strtoupper($shade($primary, -12)),
                'primaryDeep' => strtoupper($shade($primary, -25)),
                'primarySoft' => strtoupper($shade($primary, 46)),
                'accent'      => $accent,
                'accentRgb'   => $rgb($accent),
            ],
        ];
    });
@endphp

<style data-role-branding-theme>
    :root,
    [data-coreui-theme="light"],
    [data-theme="light"] {
        @foreach($colors as $role => $palette)
            --role-{{ $role }}-primary: {{ $palette['primary'] }};
            --role-{{ $role }}-primary-rgb: {{ $palette['primaryRgb'] }};
            --role-{{ $role }}-primary-hover: {{ $palette['primaryHover'] }};
            --role-{{ $role }}-primary-deep: {{ $palette['primaryDeep'] }};
            --role-{{ $role }}-primary-soft: {{ $palette['primarySoft'] }};
            --role-{{ $role }}-accent: {{ $palette['accent'] }};
            --role-{{ $role }}-accent-rgb: {{ $palette['accentRgb'] }};
        @endforeach
    }

    :root,
    [data-coreui-theme="light"] {
        --color-primary: var(--role-admin-primary);
        --color-primary-rgb: var(--role-admin-primary-rgb);
        --color-primary-hover: var(--role-admin-primary-hover);
        --color-primary-deep: var(--role-admin-primary-deep);
        --color-primary-soft: rgba(var(--role-admin-primary-rgb), 0.1);
        --color-primary-subtle: var(--role-admin-primary-soft);
        --color-accent: var(--role-admin-accent);
        --color-accent-rgb: var(--role-admin-accent-rgb);
        --color-accent-soft: rgba(var(--role-admin-accent-rgb), 0.12);
        --color-success: var(--role-admin-accent);
        --color-success-rgb: var(--role-admin-accent-rgb);
        --color-success-soft: rgba(var(--role-admin-accent-rgb), 0.12);
    }

    :root,
    [data-theme="light"] {
        --front-color-primary: var(--role-user-primary);
        --front-color-primary-rgb: var(--role-user-primary-rgb);
        --front-color-primary-hover: var(--role-user-primary-hover);
        --front-color-primary-dark: var(--role-user-primary-deep);
        --front-color-accent: var(--role-user-accent);
        --front-color-accent-rgb: var(--role-user-accent-rgb);
        --front-color-user: var(--role-user-primary);
        --front-color-user-rgb: var(--role-user-primary-rgb);
        --front-color-user-hover: var(--role-user-primary-hover);
        --front-color-user-dark: var(--role-user-primary-deep);
        --front-color-user-soft: rgba(var(--role-user-primary-rgb), 0.12);
        --front-color-user-accent: var(--role-user-accent);
        --front-color-user-accent-rgb: var(--role-user-accent-rgb);
        --front-gradient-user: linear-gradient(135deg, var(--role-user-primary) 0%, var(--role-user-accent) 100%);
        --front-gradient-user-soft: linear-gradient(135deg, rgba(var(--role-user-primary-rgb), 0.14) 0%, rgba(var(--role-user-accent-rgb), 0.14) 100%);
        --front-shadow-user: 0 12px 30px -10px rgba(var(--role-user-primary-rgb), 0.45);
        --front-color-merchant: var(--role-merchant-primary);
        --front-color-merchant-rgb: var(--role-merchant-primary-rgb);
        --front-color-merchant-hover: var(--role-merchant-primary-hover);
        --front-color-merchant-dark: var(--role-merchant-primary-deep);
        --front-color-merchant-soft: rgba(var(--role-merchant-primary-rgb), 0.12);
        --front-color-merchant-soft-border: rgba(var(--role-merchant-primary-rgb), 0.24);
        --front-color-merchant-accent: var(--role-merchant-accent);
        --front-color-merchant-accent-rgb: var(--role-merchant-accent-rgb);
        --front-color-success: var(--role-merchant-primary);
        --front-color-success-rgb: var(--role-merchant-primary-rgb);
        --front-gradient-merchant: linear-gradient(135deg, var(--role-merchant-primary) 0%, var(--role-merchant-accent) 100%);
        --front-gradient-merchant-soft: linear-gradient(135deg, rgba(var(--role-merchant-primary-rgb), 0.14) 0%, rgba(var(--role-merchant-accent-rgb), 0.14) 100%);
        --front-shadow-merchant: 0 12px 30px -10px rgba(var(--role-merchant-primary-rgb), 0.42);
        --front-color-agent: var(--role-agent-primary);
        --front-color-agent-rgb: var(--role-agent-primary-rgb);
        --front-color-agent-hover: var(--role-agent-primary-hover);
        --front-color-agent-dark: var(--role-agent-primary-deep);
        --front-color-agent-soft: rgba(var(--role-agent-primary-rgb), 0.12);
        --front-color-agent-soft-border: rgba(var(--role-agent-primary-rgb), 0.24);
        --front-color-agent-accent: var(--role-agent-accent);
        --front-color-agent-accent-rgb: var(--role-agent-accent-rgb);
        --front-gradient-agent: linear-gradient(135deg, var(--role-agent-primary) 0%, var(--role-agent-accent) 100%);
        --front-gradient-agent-soft: linear-gradient(135deg, rgba(var(--role-agent-primary-rgb), 0.14) 0%, rgba(var(--role-agent-accent-rgb), 0.14) 100%);
        --front-shadow-agent: 0 12px 30px -10px rgba(var(--role-agent-primary-rgb), 0.45);
    }

    body.dashboard-role-user {
        --main-color: var(--role-user-primary);
        --main-color-2: var(--role-user-accent);
        --front-color-primary: var(--role-user-primary);
        --front-color-primary-rgb: var(--role-user-primary-rgb);
        --front-color-primary-hover: var(--role-user-primary-hover);
        --front-color-primary-dark: var(--role-user-primary-deep);
        --front-color-accent: var(--role-user-accent);
        --front-color-accent-rgb: var(--role-user-accent-rgb);
    }

    body.dashboard-role-merchant {
        --main-color: var(--role-merchant-primary);
        --main-color-2: var(--role-merchant-accent);
        --front-color-primary: var(--role-merchant-primary);
        --front-color-primary-rgb: var(--role-merchant-primary-rgb);
        --front-color-primary-hover: var(--role-merchant-primary-hover);
        --front-color-primary-dark: var(--role-merchant-primary-deep);
        --front-color-accent: var(--role-merchant-accent);
        --front-color-accent-rgb: var(--role-merchant-accent-rgb);
    }

    body.dashboard-role-agent {
        --main-color: var(--role-agent-primary);
        --main-color-2: var(--role-agent-accent);
        --front-color-primary: var(--role-agent-primary);
        --front-color-primary-rgb: var(--role-agent-primary-rgb);
        --front-color-primary-hover: var(--role-agent-primary-hover);
        --front-color-primary-dark: var(--role-agent-primary-deep);
        --front-color-accent: var(--role-agent-accent);
        --front-color-accent-rgb: var(--role-agent-accent-rgb);
    }
</style>
