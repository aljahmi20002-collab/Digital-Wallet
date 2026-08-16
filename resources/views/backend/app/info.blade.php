@extends('backend.layouts.app')
@section('title', __('App Information'))
@section('content')
    @php
        $formatLabel = static fn (string $key): string => title(str_replace('_', ' ', $key));
        $isProtected = static fn (mixed $value): bool => str_contains((string) $value, 'Hidden');
        $displayValue = static fn (mixed $value): string => $isProtected($value) ? __('Protected') : (string) $value;
        $valueTone = static function (string $key, mixed $value) use ($isProtected): string {
            $value = (string) $value;

            if ($isProtected($value)) {
                return 'status-warning';
            }

            if (($key === 'demo_mode' && $value === 'Disabled') || $value === 'Enabled' || $value === 'Yes' || $value === 'Set') {
                return 'status-success';
            }

            if (($key === 'demo_mode' && $value === 'Enabled') || $value === 'No' || $value === 'Not Set') {
                return $key === 'demo_mode' ? 'status-warning' : 'status-danger';
            }

            return 'status-info';
        };

        $isDemoMode = (bool) config('app.demo', false);

        $metricCards = [
            [
                'label' => __('System Mode'),
                'value' => $isDemoMode ? __('Demo') : __('Live'),
                'icon' => $isDemoMode ? 'fas fa-eye-slash' : 'fas fa-bolt',
                'tone' => $isDemoMode ? 'warning' : 'success',
                'hint' => $isDemoMode ? __('Sandbox preview') : __('Production ready'),
            ],
            [
                'label' => __('Environment'),
                'value' => $appInfo['environment'] ?? app()->environment(),
                'icon' => 'fas fa-layer-group',
                'tone' => 'primary',
                'hint' => __('Active runtime tier'),
            ],
            [
                'label' => __('PHP Runtime'),
                'value' => $phpInfo['php_version'] ?? phpversion(),
                'icon' => 'fab fa-php',
                'tone' => 'info',
                'hint' => __('Engine version'),
            ],
            [
                'label' => __('Security'),
                'value' => ($securityInfo['app_key_set'] ?? null) === 'Set' ? __('Hardened') : __('Review'),
                'icon' => 'fas fa-shield-alt',
                'tone' => ($securityInfo['app_key_set'] ?? null) === 'Set' ? 'success' : 'danger',
                'hint' => __('App key & TLS posture'),
            ],
        ];

        $quickActions = [
            [
                'label' => __('Control Panel'),
                'icon' => 'fas fa-th-large',
                'route' => 'admin.app.control-panel',
                'tone' => 'primary',
            ],
            [
                'label' => __('Optimize App'),
                'icon' => 'fas fa-bolt',
                'route' => 'admin.app.optimize',
                'tone' => 'success',
            ],
            [
                'label' => __('Clear Cache'),
                'icon' => 'fas fa-broom',
                'route' => 'admin.app.clear-cache',
                'tone' => 'warning',
            ],
            [
                'label' => __('Style Manager'),
                'icon' => 'fas fa-palette',
                'route' => 'admin.app.style-manager',
                'tone' => 'info',
            ],
        ];

        $sections = [
            [
                'title' => __('Application Details'),
                'subtitle' => __('Core runtime and localization setup'),
                'icon' => 'fas fa-cubes',
                'scheme' => 'scheme-primary',
                'items' => $appInfo,
                'labels' => [
                    'app_version' => ['icon' => 'fas fa-code-branch', 'label' => __('Version')],
                    'laravel_version' => ['icon' => 'fab fa-laravel', 'label' => __('Laravel Framework')],
                    'php_version' => ['icon' => 'fab fa-php', 'label' => __('PHP Runtime')],
                    'environment' => ['icon' => 'fas fa-layer-group', 'label' => __('Environment')],
                    'timezone' => ['icon' => 'fas fa-clock', 'label' => __('Timezone')],
                    'locale' => ['icon' => 'fas fa-language', 'label' => __('Locale')],
                    'debug_mode' => ['icon' => 'fas fa-bug', 'label' => __('Debug Mode')],
                ],
            ],
            [
                'title' => __('Security Configuration'),
                'subtitle' => __('Application protection status'),
                'icon' => 'fas fa-shield-alt',
                'scheme' => 'scheme-success',
                'items' => $securityInfo,
                'labels' => [
                    'app_key_set' => ['icon' => 'fas fa-key', 'label' => __('Application Key')],
                    'https_enabled' => ['icon' => 'fas fa-lock', 'label' => __('HTTPS Security')],
                    'csrf_protection' => ['icon' => 'fas fa-shield-virus', 'label' => __('CSRF Protection')],
                    'demo_mode' => ['icon' => 'fas fa-eye-slash', 'label' => __('Demo Mode')],
                ],
            ],
            [
                'title' => __('Server Infrastructure'),
                'subtitle' => __('Host and platform diagnostics'),
                'icon' => 'fas fa-server',
                'scheme' => 'scheme-info',
                'items' => $serverInfo,
                'labels' => [
                    'server_software' => ['icon' => 'fas fa-server', 'label' => __('Server Software')],
                    'server_ip' => ['icon' => 'fas fa-network-wired', 'label' => __('IP Address')],
                    'host_name' => ['icon' => 'fas fa-server', 'label' => __('Hostname')],
                    'server_os' => ['icon' => 'fab fa-linux', 'label' => __('Operating System')],
                    'server_architecture' => ['icon' => 'fas fa-microchip', 'label' => __('Architecture')],
                    'web_server' => ['icon' => 'fas fa-globe', 'label' => __('Web Server')],
                ],
            ],
            [
                'title' => __('Database System'),
                'subtitle' => __('Connection and engine visibility'),
                'icon' => 'fas fa-database',
                'scheme' => 'scheme-dark',
                'items' => $databaseInfo,
                'labels' => [
                    'database_connection' => ['icon' => 'fas fa-plug', 'label' => __('Connection Type')],
                    'database_version' => ['icon' => 'fas fa-database', 'label' => __('Database Version')],
                ],
            ],
            [
                'title' => __('PHP Runtime'),
                'subtitle' => __('Limits, extensions, and upload capacity'),
                'icon' => 'fab fa-php',
                'scheme' => 'scheme-warning',
                'items' => $phpInfo,
                'labels' => [
                    'php_version' => ['icon' => 'fab fa-php', 'label' => __('PHP Version')],
                    'php_extensions' => ['icon' => 'fas fa-puzzle-piece', 'label' => __('Extensions Count')],
                    'memory_limit' => ['icon' => 'fas fa-memory', 'label' => __('Memory Limit')],
                    'max_execution_time' => ['icon' => 'fas fa-clock', 'label' => __('Max Execution')],
                    'upload_max_filesize' => ['icon' => 'fas fa-upload', 'label' => __('Upload Limit')],
                    'post_max_size' => ['icon' => 'fas fa-file-upload', 'label' => __('Post Max Size')],
                ],
            ],
            [
                'title' => __('Storage & Cache'),
                'subtitle' => __('Drivers used by background services'),
                'icon' => 'fas fa-hdd',
                'scheme' => 'scheme-secondary',
                'items' => $storageInfo,
                'labels' => [
                    'storage_driver' => ['icon' => 'fas fa-folder', 'label' => __('Storage Driver')],
                    'cache_driver' => ['icon' => 'fas fa-rocket', 'label' => __('Cache Driver')],
                    'queue_driver' => ['icon' => 'fas fa-tasks', 'label' => __('Queue Driver')],
                    'session_driver' => ['icon' => 'fas fa-cookie-bite', 'label' => __('Session Driver')],
                ],
            ],
        ];
    @endphp

    <div class="enterprise-container app-info-page">
        <div class="container-fluid">
            <div class="enterprise-header">
                <div class="enterprise-header__aurora" aria-hidden="true">
                    <span class="enterprise-header__orb enterprise-header__orb--one"></span>
                    <span class="enterprise-header__orb enterprise-header__orb--two"></span>
                    <span class="enterprise-header__orb enterprise-header__orb--three"></span>
                </div>

                <div class="enterprise-header__main">
                    <div class="enterprise-brand">
                        <div class="enterprise-brand__mark">
                            <img src="{{ asset(setting('logo')) }}" alt="{{ setting('site_title', config('app.name')) }}" class="stat-logo" loading="lazy">
                        </div>
                        <div class="enterprise-brand__content">
                            <span class="enterprise-eyebrow">
                                <i class="fas fa-circle-nodes"></i>
                                {{ __('System Overview') }}
                            </span>
                            <h1>{{ __('Application Information') }}</h1>
                            <p>{{ __('Real-time enterprise diagnostics, environment posture, and platform telemetry — curated in a single command center.') }}</p>
                        </div>
                    </div>
                    <div class="enterprise-header__aside">
                        <div class="enterprise-system-state enterprise-system-state--{{ $isDemoMode ? 'warning' : 'success' }}">
                            <span class="enterprise-system-state__pulse"></span>
                            <i class="{{ $isDemoMode ? 'fas fa-eye-slash' : 'fas fa-circle-check' }}"></i>
                            <span>{{ $isDemoMode ? __('Demo Mode') : __('Live System') }}</span>
                        </div>
                        <div class="enterprise-clock" title="{{ now()->format('M d, Y H:i') }}">
                            <i class="far fa-clock"></i>
                            <span>{{ now()->format('M d, Y · H:i') }}</span>
                        </div>
                    </div>
                </div>

                <div class="enterprise-stats">
                    @foreach($metricCards as $metric)
                        <div class="enterprise-stat-card enterprise-stat-card--{{ $metric['tone'] }}">
                            <div class="enterprise-stat-card__icon">
                                <i class="{{ $metric['icon'] }}"></i>
                            </div>
                            <div class="enterprise-stat-card__body">
                                <span>{{ $metric['label'] }}</span>
                                <strong title="{{ $metric['value'] }}">{{ $metric['value'] }}</strong>
                                <small>{{ $metric['hint'] }}</small>
                            </div>
                            <span class="enterprise-stat-card__chip">
                                <span class="enterprise-stat-card__dot"></span>
                            </span>
                        </div>
                    @endforeach
                </div>

                <div class="enterprise-actions" role="group" aria-label="{{ __('Quick administrative actions') }}">
                    <span class="enterprise-actions__label">
                        <i class="fas fa-wand-magic-sparkles"></i>
                        {{ __('Quick Actions') }}
                    </span>
                    <div class="enterprise-actions__list">
                        @foreach($quickActions as $action)
                            @if(\Illuminate\Support\Facades\Route::has($action['route']))
                                <a href="{{ route($action['route']) }}" class="enterprise-action-btn enterprise-action-btn--{{ $action['tone'] }}">
                                    <i class="{{ $action['icon'] }}"></i>
                                    <span>{{ $action['label'] }}</span>
                                    <i class="fas fa-arrow-right enterprise-action-btn__arrow"></i>
                                </a>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>

            @if($isDemoMode)
                <div class="demo-alert">
                    <div class="demo-alert__icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <div class="demo-alert__body">
                        <h6>{{ __('Demo Mode Active') }}</h6>
                        <p>{{ __('Sensitive system information is masked for security compliance. Disable demo mode in environment settings to expose full diagnostics.') }}</p>
                    </div>
                    <span class="demo-alert__badge">{{ __('Protected') }}</span>
                </div>
            @endif

            <div class="enterprise-grid">
                @foreach($sections as $section)
                    <div class="enterprise-card {{ $section['scheme'] }}">
                        <div class="enterprise-card-header">
                            <div class="card-header-content">
                                <div class="card-icon">
                                    <i class="{{ $section['icon'] }}"></i>
                                </div>
                                <div class="card-header-text">
                                    <h2>{{ $section['title'] }}</h2>
                                    <p>{{ $section['subtitle'] }}</p>
                                </div>
                            </div>
                            <span class="enterprise-card-count">
                                <i class="fas fa-list-ul"></i>
                                {{ count($section['items']) }}
                            </span>
                        </div>
                        <div class="enterprise-card-body">
                            @foreach($section['items'] as $key => $value)
                                @php
                                    $label = $section['labels'][$key] ?? ['icon' => 'fas fa-cog', 'label' => $formatLabel($key)];
                                    $shownValue = $displayValue($value);
                                    $tone = $valueTone($key, $value);
                                @endphp
                                <div class="enterprise-item">
                                    <div class="item-label">
                                        <i class="{{ $label['icon'] }}"></i>
                                        <span>{{ $label['label'] }}</span>
                                    </div>
                                    <div class="item-value {{ $tone }}" title="{{ $shownValue }}">
                                        <span class="item-value__dot"></span>
                                        {{ Str::limit($shownValue, 34) }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="enterprise-footer">
                <div class="enterprise-footer__content">
                    <i class="fas fa-shield-alt"></i>
                    <span>{{ __('System Diagnostics & Health Monitoring') }}</span>
                    <span class="enterprise-footer__dot"></span>
                    <small>{{ __('Last Updated') }}: {{ now()->format('M d, Y H:i') }}</small>
                    <span class="enterprise-footer__dot"></span>
                    <small>{{ __('Powered by') }} <strong>{{ setting('site_title', config('app.name')) }}</strong></small>
                </div>
            </div>
        </div>
    </div>
@endsection
