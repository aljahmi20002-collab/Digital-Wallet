@extends('frontend.user.setting.index')
@section('title', __('Security Center'))

@section('user_setting_content')
    @php
        $isStrong = $securityScore >= 100;
        $statusModifier = $isStrong ? 'enabled' : 'disabled';
        $completedChecks = $securityChecks->where('complete', true)->count();
    @endphp

    @include('frontend.user.setting.partials._security_tabs')

    <section class="settings-status-card settings-status-card--{{ $statusModifier }} security-center-status security-center-hero mb-3">
        <div class="settings-status-card__icon" aria-hidden="true">
            <x-icon name="user-security" class="settings-icon-svg" height="20" width="20" />
        </div>
        <div class="settings-status-card__body">
            <h6 class="settings-status-card__title">
                {{ $isStrong ? __('Core wallet security is complete') : __('Core wallet security needs attention') }}
            </h6>
            <p class="settings-status-card__text">
                {{ __('Complete email verification, authenticator 2FA, and wallet PIN protection for safer wallet access.') }}
            </p>
        </div>
        <span class="settings-badge {{ $isStrong ? 'settings-badge--success' : 'settings-badge--info' }}">
            <x-icon name="chart-up" class="settings-icon-svg" height="14" width="14" />
            {{ __(':score% Ready', ['score' => $securityScore]) }}
        </span>
    </section>

    <div class="security-readiness-strip mb-3">
        <div class="security-readiness-strip__item">
            <span class="security-readiness-strip__label">{{ __('Protection readiness') }}</span>
            <strong class="security-readiness-strip__value">{{ __(':completed of :total checks active', [
                'completed' => $completedChecks,
                'total' => $securityChecks->count(),
            ]) }}</strong>
        </div>
        <div class="security-readiness-strip__item">
            <span class="security-readiness-strip__label">{{ __('Next best action') }}</span>
            <strong class="security-readiness-strip__value">
                {{ $nextSecurityCheck['label'] ?? __('All core controls are active') }}
            </strong>
        </div>
        <div class="security-readiness-strip__meter" aria-label="{{ __('Security readiness :score percent', ['score' => $securityScore]) }}">
            <span style="width: {{ $securityScore }}%"></span>
        </div>
    </div>

    <div class="row g-3 security-access-grid">
        <div class="col-lg-4 order-lg-2">
            <aside class="settings-tips security-center-summary">
                <h6 class="settings-tips__title">
                    <x-icon name="lock" class="settings-icon-svg" height="16" width="16" />
                    {{ __('Current Access') }}
                </h6>
                <dl class="security-current-list">
                    <div>
                        <dt>{{ __('Current IP') }}</dt>
                        <dd>{{ $currentIpAddress }}</dd>
                    </div>
                    <div>
                        <dt>{{ __('Known IPs') }}</dt>
                        <dd>{{ $knownIpCount }}</dd>
                    </div>
                    <div>
                        <dt>{{ __('2FA') }}</dt>
                        <dd>{{ auth()->user()->two_factor_enabled ? __('Enabled') : __('Disabled') }}</dd>
                    </div>
                    <div>
                        <dt>{{ __('Wallet PIN') }}</dt>
                        <dd>{{ auth()->user()->hasWalletPin() ? __('Set') : __('Not Set') }}</dd>
                    </div>
                </dl>
            </aside>
        </div>

        <div class="col-lg-8 order-lg-1">
            <section class="settings-section security-session-card">
                <header class="settings-section__header">
                    <div>
                        <h6 class="settings-section__title">{{ __('Sign Out Other Browser Sessions') }}</h6>
                        <p class="settings-section__subtitle">
                            {{ __('Keep this device signed in while removing account access from other browsers and devices.') }}
                        </p>
                    </div>
                    <span class="settings-badge settings-badge--info">
                        <x-icon name="lock" class="settings-icon-svg" height="14" width="14" />
                        {{ __('Password Required') }}
                    </span>
                </header>

                <div class="settings-section__body">
                    @if($errors->has('password'))
                        <div class="settings-inline-alert settings-inline-alert--danger mb-3" role="alert">
                            <span class="settings-inline-alert__icon" aria-hidden="true">
                                <i class="fas fa-triangle-exclamation"></i>
                            </span>
                            <div class="settings-inline-alert__body">
                                <strong class="settings-inline-alert__title">{{ __('Session action failed') }}</strong>
                                <span class="settings-inline-alert__text">{{ $errors->first('password') }}</span>
                            </div>
                        </div>
                    @endif

                    <form action="{{ route('user.settings.security.logout-other-sessions') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="logout-sessions-password" class="form-label">{{ __('Current Password') }}</label>
                            <input type="password"
                                   id="logout-sessions-password"
                                   name="password"
                                   class="form-control"
                                   placeholder="{{ __('Enter your current password') }}"
                                   autocomplete="current-password"
                                   required>
                        </div>
                        <button type="submit" class="btn btn-danger security-center-session-button">
                            <span class="security-center-session-button__icon" aria-hidden="true">
                                <i class="fa-solid fa-right-from-bracket"></i>
                            </span>
                            <span>{{ __('Sign Out Other Sessions') }}</span>
                        </button>
                    </form>
                </div>
            </section>
        </div>
    </div>

    <section class="settings-section security-activity-section">
        <header class="settings-section__header">
            <div>
                <h6 class="settings-section__title">{{ __('Recent Sign-In Activity') }}</h6>
                <p class="settings-section__subtitle">
                    {{ __('Review recent successful sign-ins and contact support if anything looks unfamiliar.') }}
                </p>
            </div>
            @if($recentLoginActivities->total() > 0)
                <span class="settings-badge settings-badge--info">
                    <x-icon name="history" class="settings-icon-svg" height="14" width="14" />
                    {{ trans_choice(':count unique IP|:count unique IPs', $recentLoginActivities->total(), ['count' => $recentLoginActivities->total()]) }}
                </span>
            @endif
        </header>

        <div class="settings-section__body">
            @if($recentLoginActivities->isEmpty())
                <x-user-not-found
                    :title="__('No sign-in activity yet')"
                    :message="__('Your successful sign-ins will appear here after your next login.')"
                    icon="fa-history"
                />
            @else
                <div class="security-activity-list">
                    @foreach($recentLoginActivities as $activity)
                        <article class="security-activity-item">
                            <span class="security-activity-item__icon" aria-hidden="true">
                                <x-icon name="web" class="security-icon-svg" height="18" width="18" />
                            </span>
                            <div class="security-activity-item__body">
                                <h6 class="security-activity-item__title">
                                    {{ $activity->browser ?: __('Unknown Browser') }}
                                    <span>{{ $activity->platform ?: __('Unknown Platform') }}</span>
                                </h6>
                                <p class="security-activity-item__meta">
                                    {{ $activity->ip_address ?: __('Unknown IP') }}
                                    @if($activity->country)
                                        <span aria-hidden="true">&middot;</span> {{ $activity->country }}
                                    @endif
                                    @if($activity->device)
                                        <span aria-hidden="true">&middot;</span> {{ $activity->device }}
                                    @endif
                                </p>
                            </div>
                            <time class="security-activity-item__time" datetime="{{ $activity->login_at?->toIso8601String() }}">
                                {{ $activity->login_at?->diffForHumans() ?? __('Unknown') }}
                            </time>
                        </article>
                    @endforeach
                </div>

                @if($recentLoginActivities->hasPages())
                    <nav class="security-activity-pagination security-activity-pagination--compact" aria-label="{{ __('Recent sign-in activity pagination') }}">
                        <span class="security-activity-pagination__summary">
                            {{ __(':from-:to of :total', [
                                'from' => $recentLoginActivities->firstItem(),
                                'to' => $recentLoginActivities->lastItem(),
                                'total' => $recentLoginActivities->total(),
                            ]) }}
                        </span>
                        <div class="security-activity-pagination__actions">
                            @if($recentLoginActivities->onFirstPage())
                                <span class="security-activity-page-link is-disabled" aria-disabled="true">
                                    <i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
                                    {{ __('Previous') }}
                                </span>
                            @else
                                <a class="security-activity-page-link" href="{{ $recentLoginActivities->previousPageUrl() }}" rel="prev">
                                    <i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
                                    {{ __('Previous') }}
                                </a>
                            @endif

                            <span class="security-activity-page-link is-current" aria-current="page">
                                {{ __('Page :current of :last', [
                                    'current' => $recentLoginActivities->currentPage(),
                                    'last' => $recentLoginActivities->lastPage(),
                                ]) }}
                            </span>

                            @if($recentLoginActivities->hasMorePages())
                                <a class="security-activity-page-link" href="{{ $recentLoginActivities->nextPageUrl() }}" rel="next">
                                    {{ __('Next') }}
                                    <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
                                </a>
                            @else
                                <span class="security-activity-page-link is-disabled" aria-disabled="true">
                                    {{ __('Next') }}
                                    <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
                                </span>
                            @endif
                        </div>
                    </nav>
                @endif
            @endif
        </div>
    </section>
@endsection
