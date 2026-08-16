@extends('backend.layouts.app')

@section('title', __('Project Updater'))

@push('styles')
    <link rel="stylesheet" href="{{ asset('backend/css/project-updater.css?v=' . config('app.version')) }}">
@endpush

@section('content')
    @php
        $licenseActive = $license?->isActive() ?? false;
        $latestStatus = $latest?->status ?? 'not_checked';
        $changelog = collect($latest?->changelog ?? []);
        $supportTone = $license?->supportTone() ?? 'secondary';
        $supportDaysRemaining = $license?->supportDaysRemaining();
        $supportExpired = $license && $supportDaysRemaining !== null && $supportDaysRemaining < 0;
        $supportEndingSoon = $license?->supportExpiresSoon() ?? false;
        $showAlert = ! $license || $supportExpired || $supportEndingSoon;
        $alertTitle = ! $license
            ? __('Activate your Digikash license')
            : ($supportExpired ? __('Envato support has expired') : __('Envato support is ending soon'));
        $alertMessage = ! $license
            ? __('Enter the Envato purchase code to connect this installation with your private update server.')
            : ($supportExpired
                ? __('You can still receive Digikash project updates for free. Extend Envato support if you need priority help, setup assistance, or service support.')
                : __('Your support period is close to ending. Updates remain lifetime free, but extending support keeps premium help available when you need it.'));

        $latestVersion = $latest?->version;
        $hasUpdate = $latest && $latest->status === 'available';
        $isCurrent = $latest && $latest->status === 'current';
        $checksTotal = count($checks ?? []);
        $checksPassed = collect($checks ?? [])->where('status', true)->count();
        $checksReady = $checksTotal > 0 && $checksPassed === $checksTotal;

        $licenseKpiTone = $licenseActive ? 'success' : ($license ? 'warning' : 'neutral');
        $supportKpiTone = match ($supportTone) {
            'success' => 'success',
            'warning' => 'warning',
            'danger' => 'danger',
            default => 'neutral',
        };
        $releaseKpiTone = $hasUpdate ? 'info' : ($isCurrent ? 'success' : 'neutral');
        $checksKpiTone = $checksReady ? 'success' : 'warning';

        $supportRemainingLabel = $license
            ? ($supportExpired
                ? __(':days days ago', ['days' => abs((int) $supportDaysRemaining)])
                : ($supportDaysRemaining !== null
                    ? __(':days days left', ['days' => (int) $supportDaysRemaining])
                    : __('Unknown')))
            : __('Not activated');
    @endphp

    <div class="project-updater">
        {{-- Hero ---------------------------------------------------------- --}}
        <header class="pu-hero">
            <div class="pu-hero__main">
                <h1 class="pu-hero__title">
                    <span class="pu-hero__icon"><i class="fas fa-download"></i></span>
                    {{ __('Project Updater') }}
                </h1>
                <p class="pu-hero__lead">
                    {{ __('Activate the Digikash license, check your private update server, verify packages, and install signed releases from one place.') }}
                </p>
            </div>
            <div class="pu-hero__aside">
                <div class="pu-version">
                    <span class="pu-version__icon"><i class="fas fa-code-branch"></i></span>
                    <span class="pu-version__label">{{ __('Installed') }}</span>
                    <strong class="pu-version__value">v{{ config('app.version') }}</strong>
                </div>
                <form action="{{ route('admin.app.updater.check') }}" method="POST">
                    @csrf
                    <button type="submit" class="pu-btn pu-btn--primary" @disabled(! $licenseActive)>
                        <i class="fas fa-rotate"></i>
                        {{ __('Check Update') }}
                    </button>
                </form>
            </div>
        </header>

        {{-- KPI Strip ----------------------------------------------------- --}}
        <section class="pu-kpi-grid" aria-label="{{ __('Updater status overview') }}">
            <div class="pu-kpi">
                <span class="pu-kpi__icon pu-kpi__icon--{{ $licenseKpiTone }}">
                    <i class="fas fa-check-circle"></i>
                </span>
                <div class="pu-kpi__body">
                    <span class="pu-kpi__label">{{ __('License') }}</span>
                    <strong class="pu-kpi__value">
                        {{ $licenseActive ? __('Active') : ($license ? __('Inactive') : __('Not Linked')) }}
                    </strong>
                    <span class="pu-kpi__hint">
                        {{ $license
                            ? ($license->domain ?: request()->getHost())
                            : __('Activate to enable updates') }}
                    </span>
                </div>
            </div>

            <div class="pu-kpi">
                <span class="pu-kpi__icon pu-kpi__icon--{{ $supportKpiTone }}">
                    <i class="fas fa-life-ring"></i>
                </span>
                <div class="pu-kpi__body">
                    <span class="pu-kpi__label">{{ __('Support') }}</span>
                    <strong class="pu-kpi__value">{{ $license?->supportStatusLabel() ?? __('Unknown') }}</strong>
                    <span class="pu-kpi__hint">{{ $supportRemainingLabel }}</span>
                </div>
            </div>

            <div class="pu-kpi">
                <span class="pu-kpi__icon pu-kpi__icon--{{ $releaseKpiTone }}">
                    <i class="fas fa-rocket"></i>
                </span>
                <div class="pu-kpi__body">
                    <span class="pu-kpi__label">{{ __('Latest Release') }}</span>
                    <strong class="pu-kpi__value">
                        {{ $latestVersion ? 'v' . $latestVersion : __('Not checked') }}
                    </strong>
                    <span class="pu-kpi__hint">
                        @if($hasUpdate)
                            {{ __('Update available') }}
                        @elseif($isCurrent)
                            {{ __('You are up to date') }}
                        @else
                            {{ __('Run a check to discover releases') }}
                        @endif
                    </span>
                </div>
            </div>

            <div class="pu-kpi">
                <span class="pu-kpi__icon pu-kpi__icon--{{ $checksKpiTone }}">
                    <i class="fas fa-tasks"></i>
                </span>
                <div class="pu-kpi__body">
                    <span class="pu-kpi__label">{{ __('Readiness') }}</span>
                    <strong class="pu-kpi__value">{{ $checksPassed }} / {{ $checksTotal }} {{ __('passing') }}</strong>
                    <span class="pu-kpi__hint">
                        {{ $checksReady ? __('Ready to install') : __('Resolve highlighted items') }}
                    </span>
                </div>
            </div>
        </section>

        {{-- Conditional alert (only when there's something to act on) ----- --}}
        @if($showAlert)
            <div class="pu-alert pu-alert--{{ $supportTone }}">
                <span class="pu-alert__icon">
                    <i class="fas {{ ! $license ? 'fa-key' : ($supportExpired ? 'fa-exclamation-circle' : 'fa-hourglass-half') }}"></i>
                </span>
                <div class="pu-alert__copy">
                    <strong>{{ $alertTitle }}</strong>
                    <p>{{ $alertMessage }}</p>
                </div>
            </div>
        @endif

        {{-- License + Release -------------------------------------------- --}}
        <div class="pu-grid pu-grid--2">
            <article class="pu-card">
                <header class="pu-card__head">
                    <div class="pu-card__head-main">
                        <span class="pu-card__head-icon"><i class="fas fa-id-card"></i></span>
                        <div>
                            <h2 class="pu-card__title">{{ __('License') }}</h2>
                            <p class="pu-card__subtitle">{{ __('Purchase verification stays on your private update server.') }}</p>
                        </div>
                    </div>
                    <span class="pu-pill pu-pill--{{ $licenseActive ? 'success' : 'warning' }}">
                        {{ $licenseActive ? __('Active') : __('Inactive') }}
                    </span>
                </header>

                @if($license)
                    <div class="pu-facts">
                        <div class="pu-fact">
                            <span class="pu-fact__label"><i class="fas fa-user"></i> {{ __('Buyer') }}</span>
                            <strong class="pu-fact__value">{{ $license->buyer_username ?: __('Unknown') }}</strong>
                        </div>
                        <div class="pu-fact">
                            <span class="pu-fact__label"><i class="fas fa-calendar-alt"></i> {{ __('Support Until') }}</span>
                            <strong class="pu-fact__value">
                                {{ $license->support_until?->format('M d, Y') ?? __('Unknown') }}
                            </strong>
                        </div>
                        <div class="pu-fact">
                            <span class="pu-fact__label"><i class="fas fa-clock"></i> {{ __('Last Checked') }}</span>
                            <strong class="pu-fact__value">{{ $license->last_checked_at?->diffForHumans() ?? __('Never') }}</strong>
                        </div>
                        <div class="pu-fact">
                            <span class="pu-fact__label"><i class="fas fa-infinity"></i> {{ __('Update Access') }}</span>
                            <strong class="pu-fact__value">{{ __('Lifetime Free') }}</strong>
                        </div>
                    </div>
                @endif

                @can('project-updater-manage')
                    <form action="{{ route('admin.app.updater.activate') }}" method="POST" class="pu-form">
                        @csrf
                        <label for="purchase_code" class="form-label">{{ __('Envato Purchase Code') }}</label>
                        <div class="input-group">
                            <input
                                id="purchase_code"
                                type="text"
                                name="purchase_code"
                                value="{{ old('purchase_code') }}"
                                class="form-control @error('purchase_code') is-invalid @enderror"
                                placeholder="00000000-0000-0000-0000-000000000000"
                                autocomplete="off"
                            >
                            <button type="submit" class="pu-btn pu-btn--dark">
                                <i class="fas fa-key"></i>
                                {{ __('Activate') }}
                            </button>
                            @error('purchase_code')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <small>{{ __('Envato support period is for premium service, setup help, and priority assistance only.') }}</small>
                    </form>
                @endcan
            </article>

            <article class="pu-card">
                <header class="pu-card__head">
                    <div class="pu-card__head-main">
                        <span class="pu-card__head-icon pu-card__head-icon--info"><i class="fas fa-rocket"></i></span>
                        <div>
                            <h2 class="pu-card__title">{{ __('Release') }}</h2>
                            <p class="pu-card__subtitle">{{ __('Latest version returned by your update server.') }}</p>
                        </div>
                    </div>
                    <span class="pu-pill pu-pill--{{ $latest?->statusTone() ?? 'secondary' }}">
                        {{ title(str_replace('_', ' ', $latestStatus)) }}
                    </span>
                </header>

                <div class="pu-release">
                    <div class="pu-release__row">
                        <div class="pu-release__col">
                            <span class="pu-release__col-label">
                                <i class="fas fa-check-circle"></i> {{ __('Current') }}
                            </span>
                            <strong>v{{ config('app.version') }}</strong>
                        </div>
                        <span class="pu-release__arrow"><i class="fas fa-arrow-right"></i></span>
                        <div class="pu-release__col pu-release__col--latest">
                            <span class="pu-release__col-label">
                                <i class="fas fa-rocket"></i> {{ __('Latest') }}
                            </span>
                            <strong>{{ $latestVersion ? 'v' . $latestVersion : '—' }}</strong>
                        </div>
                    </div>
                    <div class="pu-release__meta">
                        <div>
                            <i class="fas fa-signal"></i>
                            <span>{{ __('Channel') }}</span>
                            <strong>{{ $latest?->channel ?? config('project_updater.channel') }}</strong>
                        </div>
                        <div>
                            <i class="fas fa-calendar"></i>
                            <span>{{ __('Released') }}</span>
                            <strong>{{ $latest?->release_date?->format('M d, Y') ?? __('Unknown') }}</strong>
                        </div>
                    </div>
                </div>

                @can('project-updater-manage')
                    <div class="pu-download-backup pu-download-backup--warning">
                        <span class="pu-download-backup__icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </span>
                        <div>
                            <strong>{{ __('Important: download a local backup before updating') }}</strong>
                            <span>{{ __('Save the recovery ZIP on your computer before installing. If an update fails and no local backup was kept, restore may not be possible and support may be limited.') }}</span>
                        </div>
                        <form action="{{ route('admin.app.updater.backup.download') }}" method="POST">
                            @csrf
                            <button type="submit" class="pu-btn pu-btn--light" @disabled(! $licenseActive)>
                                <i class="fas fa-file-archive"></i>
                                {{ __('Download Backup') }}
                            </button>
                        </form>
                    </div>
                @endcan

                @if($hasUpdate)
                    @can('project-updater-manage')
                        <form action="{{ route('admin.app.updater.install', $latest) }}" method="POST" class="pu-install">
                            @csrf
                            <label class="pu-confirm">
                                <input type="checkbox" name="confirm_backup" value="1">
                                <span>{{ __('I understand a backup will be created before files are replaced.') }}</span>
                            </label>
                            <label class="pu-confirm pu-confirm--risk">
                                <input type="checkbox" name="confirm_local_backup" value="1">
                                <span>
                                    <strong>{{ __('I downloaded and saved a local recovery backup, or I accept the restore risk.') }}</strong>
                                    <small>{{ __('If the update causes a crash and no local backup was saved, recovery depends on the server backup and may require manual technical work.') }}</small>
                                </span>
                            </label>
                            <label class="pu-confirm pu-confirm--backup">
                                <input type="checkbox" name="backup_database_storage" value="1" checked>
                                <span>
                                    <strong>{{ __('Backup database and storage folder') }}</strong>
                                    <small>{{ __('Creates database.sql and storage.zip in the update backup folder before installation, so you can restore if something goes wrong.') }}</small>
                                </span>
                            </label>
                            <div class="pu-install-warning">
                                <i class="fas fa-exclamation-triangle"></i>
                                <span>{{ __('Before clicking Install Update, download and save the recovery ZIP locally. Without a local backup, crash recovery may be limited and complaints about missing backups cannot be accepted.') }}</span>
                            </div>
                            <button type="submit" class="pu-btn pu-btn--success">
                                <i class="fas fa-download"></i>
                                {{ __('Install Update') }}
                            </button>
                        </form>
                    @endcan
                @endif
            </article>
        </div>

        {{-- Readiness + Changelog ---------------------------------------- --}}
        <div class="pu-grid pu-grid--2">
            <article class="pu-card">
                <header class="pu-card__head">
                    <div class="pu-card__head-main">
                        <span class="pu-card__head-icon pu-card__head-icon--success"><i class="fas fa-tasks"></i></span>
                        <div>
                            <h2 class="pu-card__title">{{ __('Readiness') }}</h2>
                            <p class="pu-card__subtitle">{{ __('Server-side checks required before package install.') }}</p>
                        </div>
                    </div>
                    <span class="pu-pill pu-pill--{{ $checksKpiTone }}">
                        {{ $checksPassed }}/{{ $checksTotal }} {{ __('ready') }}
                    </span>
                </header>
                <ul class="pu-checks">
                    @foreach($checks as $check)
                        <li class="pu-checks__item pu-checks__item--{{ $check['status'] ? 'pass' : 'fail' }}">
                            <span class="pu-checks__icon">
                                <i class="fas {{ $check['status'] ? 'fa-check' : 'fa-exclamation' }}"></i>
                            </span>
                            <div class="pu-checks__body">
                                <strong>{{ $check['label'] }}</strong>
                                <span>{{ $check['help'] }}</span>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </article>

            <article class="pu-card">
                <header class="pu-card__head">
                    <div class="pu-card__head-main">
                        <span class="pu-card__head-icon pu-card__head-icon--accent"><i class="fas fa-code-branch"></i></span>
                        <div>
                            <h2 class="pu-card__title">{{ __('Changelog') }}</h2>
                            <p class="pu-card__subtitle">{{ __('What this release changes.') }}</p>
                        </div>
                    </div>
                    @if($changelog->isNotEmpty())
                        <span class="pu-pill pu-pill--secondary pu-pill--plain">
                            {{ trans_choice(':count entry|:count entries', $changelog->count(), ['count' => $changelog->count()]) }}
                        </span>
                    @endif
                </header>
                @if($changelog->isNotEmpty())
                    <ol class="pu-changelog">
                        @foreach($changelog->take(8) as $index => $line)
                            <li class="pu-changelog__item">
                                <span class="pu-changelog__bullet">{{ $index + 1 }}</span>
                                <span>{{ is_array($line) ? ($line['title'] ?? json_encode($line)) : $line }}</span>
                            </li>
                        @endforeach
                    </ol>
                @else
                    <x-admin-not-found
                        :title="__('No changelog yet')"
                        :message="__('Check for updates after activating the license.')"
                        icon="fa-code-branch"
                    />
                @endif
            </article>
        </div>

        {{-- History ------------------------------------------------------- --}}
        <article class="pu-card">
            <header class="pu-card__head">
                <div class="pu-card__head-main">
                    <span class="pu-card__head-icon pu-card__head-icon--neutral"><i class="fas fa-history"></i></span>
                    <div>
                        <h2 class="pu-card__title">{{ __('Update History') }}</h2>
                        <p class="pu-card__subtitle">{{ __('Recent checks, downloads, installs, and failures.') }}</p>
                    </div>
                </div>
                @if($history->isNotEmpty())
                    <span class="pu-pill pu-pill--secondary pu-pill--plain">
                        {{ trans_choice(':count entry|:count entries', $history->count(), ['count' => $history->count()]) }}
                    </span>
                @endif
            </header>

            @if($history->isNotEmpty())
                <div class="pu-table-wrap">
                    <table class="pu-table">
                        <thead>
                            <tr>
                                <th>{{ __('Version') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Checked') }}</th>
                                <th>{{ __('Installed') }}</th>
                                <th>{{ __('Backup') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($history as $update)
                                <tr>
                                    <td><span class="pu-table__version">v{{ $update->version }}</span></td>
                                    <td>
                                        <span class="pu-pill pu-pill--{{ $update->statusTone() }}">
                                            {{ title(str_replace('_', ' ', $update->status)) }}
                                        </span>
                                    </td>
                                    <td><span class="pu-table__time">{{ $update->checked_at?->diffForHumans() ?? '—' }}</span></td>
                                    <td><span class="pu-table__time">{{ $update->installed_at?->diffForHumans() ?? '—' }}</span></td>
                                    <td><span class="pu-table__path">{{ $update->backup_path ?: '—' }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <x-admin-not-found
                    :title="__('No update activity')"
                    :message="__('Activate the license and run your first update check.')"
                    icon="fa-history"
                />
            @endif
        </article>
    </div>
@endsection
