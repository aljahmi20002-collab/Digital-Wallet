@extends('backend.layouts.app')

@section('title', __('Manage Feature: :name', ['name' => $feature->label]))

@push('styles')
    <link rel="stylesheet" href="{{ asset('backend/css/feature-management.css') }}">
@endpush

@section('content')
    <div class="feature-mgmt-page feature-mgmt-page--edit">
        <form action="{{ route('admin.features.update', $feature) }}" method="POST" class="feature-mgmt-form">
            @csrf
            @method('PUT')

            {{-- Header --}}
            <div class="feature-mgmt-edit-header">
                <div class="feature-mgmt-edit-header__text">
                    <a href="{{ route('admin.features.index') }}" class="feature-mgmt-back">
                        <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                        <span>{{ __('Back') }}</span>
                    </a>

                    <div class="feature-mgmt-edit-header__title-row">
                        <h1 class="feature-mgmt-edit-header__title">{{ __($feature->label) }}</h1>
                        <span class="feature-mgmt-badge feature-mgmt-badge--{{ $feature->is_enabled ? 'success' : 'secondary' }}">
                            {{ $feature->is_enabled ? __('Active') : __('Disabled') }}
                        </span>
                        @if($feature->is_core)
                            <span class="feature-mgmt-badge feature-mgmt-badge--warning"
                                  title="{{ __('Disabling this feature affects business-critical flows.') }}">{{ __('Core') }}</span>
                        @endif
                    </div>

                    <p class="feature-mgmt-edit-header__subtitle">
                        <span class="feature-mgmt-edit-header__desc">{{ __($feature->description) }}</span>
                        <code>{{ $feature->key }}</code>
                    </p>
                </div>
            </div>

            {{-- Global toggle --}}
            <section class="feature-mgmt-card">
                <div class="feature-mgmt-card__header">
                    <h3 class="feature-mgmt-card__title">{{ __('Global Availability') }}</h3>
                    <p class="feature-mgmt-card__subtitle">
                        {{ __('Master switch - off hides this feature everywhere, regardless of panel rules below.') }}
                    </p>
                </div>
                <div class="feature-mgmt-card__body">
                    <label class="feature-mgmt-row">
                        <div>
                            <span class="feature-mgmt-row__label">{{ __('Enabled platform-wide') }}</span>
                            @if($feature->is_core)
                                <span class="feature-mgmt-row__hint feature-mgmt-row__hint--danger">
                                    {{ __('Core feature - disabling may break dependent flows.') }}
                                </span>
                            @else
                                <span class="feature-mgmt-row__hint">
                                    {{ __('Controls menu visibility, dashboard entry points, and protected routes for this feature.') }}
                                </span>
                            @endif
                        </div>
                        <div>
                            <input type="hidden" name="is_enabled" value="0">
                            <div class="form-check form-switch feature-mgmt-switch feature-mgmt-switch--lg">
                                <input class="form-check-input"
                                       type="checkbox"
                                       role="switch"
                                       name="is_enabled"
                                       value="1"
                                       {{ $feature->is_enabled ? 'checked' : '' }}>
                            </div>
                        </div>
                    </label>
                </div>
            </section>

            {{-- Per-panel rules --}}
            <section class="feature-mgmt-card">
                <div class="feature-mgmt-card__header">
                    <h3 class="feature-mgmt-card__title">{{ __('Panel Visibility & Access') }}</h3>
                    <p class="feature-mgmt-card__subtitle">
                        {{ __('Visibility controls menus and UI entry points. Access controls routes, actions, and APIs.') }}
                    </p>
                </div>

                <div class="feature-mgmt-card__body feature-mgmt-card__body--panels">
                    @foreach($rulesByPanel as $panel => $rule)
                        @php
                            $conditions = (array) $rule->conditions;
                            $countries  = implode(', ', (array) data_get($conditions, 'countries_allowed', []));
                            $requiresKyc = (bool) data_get($conditions, 'requires_kyc');
                            $requiresPhone = (bool) data_get($conditions, 'requires_phone');
                            $panelIcons = [
                                \App\Models\FeatureAccessRule::PANEL_USER => 'fa-user',
                                \App\Models\FeatureAccessRule::PANEL_MERCHANT => 'fa-store',
                                \App\Models\FeatureAccessRule::PANEL_AGENT => 'fa-user-tie',
                            ];
                            $enabledRules = collect([$rule->is_visible, $rule->is_accessible, $requiresKyc, $requiresPhone])->filter()->count();
                            $panelLabel = $panelOptions[$panel] ?? ucfirst($panel);
                        @endphp

                        <div class="feature-mgmt-panel feature-mgmt-panel--{{ $panel }}">
                            <header class="feature-mgmt-panel__header">
                                <div class="feature-mgmt-panel__identity">
                                    <span class="feature-mgmt-panel__icon" aria-hidden="true">
                                        <i class="fa-solid {{ $panelIcons[$panel] ?? 'fa-layer-group' }}"></i>
                                    </span>
                                    <div>
                                        <span class="feature-mgmt-panel__badge feature-mgmt-panel__badge--{{ $panel }}">
                                            {{ $panelLabel }}
                                        </span>
                                        <h4 class="feature-mgmt-panel__title">
                                            {{ __(':panel level controls', ['panel' => $panelLabel]) }}
                                        </h4>
                                    </div>
                                </div>

                                <div class="feature-mgmt-panel__meta" aria-label="{{ __('Panel rule summary') }}">
                                    <span class="feature-mgmt-panel__metric">
                                        <strong>{{ $enabledRules }}</strong>
                                        <span>{{ __('rules on') }}</span>
                                    </span>
                                    <span class="feature-mgmt-panel__scope {{ $countries === '' ? 'is-open' : 'is-limited' }}">
                                        <i class="fa-solid {{ $countries === '' ? 'fa-earth-americas' : 'fa-location-dot' }}" aria-hidden="true"></i>
                                        {{ $countries === '' ? __('All countries') : __('Limited') }}
                                    </span>
                                </div>
                            </header>

                            <div class="feature-mgmt-panel__status-strip">
                                <span class="feature-mgmt-status-pill {{ $rule->is_visible ? 'is-on' : 'is-off' }}">
                                    <i class="fa-solid {{ $rule->is_visible ? 'fa-eye' : 'fa-eye-slash' }}" aria-hidden="true"></i>
                                    {{ $rule->is_visible ? __('Visible') : __('Hidden') }}
                                </span>
                                <span class="feature-mgmt-status-pill {{ $rule->is_accessible ? 'is-on' : 'is-off' }}">
                                    <i class="fa-solid {{ $rule->is_accessible ? 'fa-unlock-keyhole' : 'fa-lock' }}" aria-hidden="true"></i>
                                    {{ $rule->is_accessible ? __('Accessible') : __('Blocked') }}
                                </span>
                                <span class="feature-mgmt-status-pill {{ $requiresKyc ? 'is-warning' : 'is-muted' }}">
                                    <i class="fa-solid {{ $requiresKyc ? 'fa-id-card' : 'fa-circle-check' }}" aria-hidden="true"></i>
                                    {{ $requiresKyc ? __('KYC required') : __('No KYC gate') }}
                                </span>
                                <span class="feature-mgmt-status-pill {{ $requiresPhone ? 'is-warning' : 'is-muted' }}">
                                    <i class="fa-solid {{ $requiresPhone ? 'fa-comment-sms' : 'fa-circle-check' }}" aria-hidden="true"></i>
                                    {{ $requiresPhone ? __('Phone required') : __('No phone gate') }}
                                </span>
                            </div>

                            <div class="feature-mgmt-panel__grid">
                                <label class="feature-mgmt-row feature-mgmt-row--panel">
                                    <div>
                                        <span class="feature-mgmt-row__label">
                                            <i class="fa-solid fa-eye feature-mgmt-row__icon" aria-hidden="true"></i>
                                            {{ __('Visible') }}
                                        </span>
                                        <span class="feature-mgmt-row__hint">{{ __('Show in menus and dashboards.') }}</span>
                                    </div>
                                    <div>
                                        <input type="hidden" name="panels[{{ $panel }}][is_visible]" value="0">
                                        <div class="form-check form-switch feature-mgmt-switch">
                                            <input class="form-check-input" type="checkbox" role="switch"
                                                   name="panels[{{ $panel }}][is_visible]" value="1"
                                                   {{ $rule->is_visible ? 'checked' : '' }}>
                                        </div>
                                    </div>
                                </label>

                                <label class="feature-mgmt-row feature-mgmt-row--panel">
                                    <div>
                                        <span class="feature-mgmt-row__label">
                                            <i class="fa-solid fa-unlock-keyhole feature-mgmt-row__icon" aria-hidden="true"></i>
                                            {{ __('Accessible') }}
                                        </span>
                                        <span class="feature-mgmt-row__hint">{{ __('Allow routes, actions, and APIs.') }}</span>
                                    </div>
                                    <div>
                                        <input type="hidden" name="panels[{{ $panel }}][is_accessible]" value="0">
                                        <div class="form-check form-switch feature-mgmt-switch">
                                            <input class="form-check-input" type="checkbox" role="switch"
                                                   name="panels[{{ $panel }}][is_accessible]" value="1"
                                                   {{ $rule->is_accessible ? 'checked' : '' }}>
                                        </div>
                                    </div>
                                </label>

                                <label class="feature-mgmt-row feature-mgmt-row--panel">
                                    <div>
                                        <span class="feature-mgmt-row__label">
                                            <i class="fa-solid fa-id-card feature-mgmt-row__icon" aria-hidden="true"></i>
                                            {{ __('KYC Required') }}
                                        </span>
                                        <span class="feature-mgmt-row__hint">{{ __('User must complete KYC first.') }}</span>
                                    </div>
                                    <div>
                                        <input type="hidden" name="panels[{{ $panel }}][requires_kyc]" value="0">
                                        <div class="form-check form-switch feature-mgmt-switch">
                                            <input class="form-check-input" type="checkbox" role="switch"
                                                   name="panels[{{ $panel }}][requires_kyc]" value="1"
                                                   {{ data_get($conditions, 'requires_kyc') ? 'checked' : '' }}>
                                        </div>
                                    </div>
                                </label>

                                <label class="feature-mgmt-row feature-mgmt-row--panel">
                                    <div>
                                        <span class="feature-mgmt-row__label">
                                            <i class="fa-solid fa-comment-sms feature-mgmt-row__icon" aria-hidden="true"></i>
                                            {{ __('Phone Required') }}
                                        </span>
                                        <span class="feature-mgmt-row__hint">{{ __('Verified phone needed.') }}</span>
                                    </div>
                                    <div>
                                        <input type="hidden" name="panels[{{ $panel }}][requires_phone]" value="0">
                                        <div class="form-check form-switch feature-mgmt-switch">
                                            <input class="form-check-input" type="checkbox" role="switch"
                                                   name="panels[{{ $panel }}][requires_phone]" value="1"
                                                   {{ $requiresPhone ? 'checked' : '' }}>
                                        </div>
                                    </div>
                                </label>

                                <div class="feature-mgmt-row feature-mgmt-row--panel feature-mgmt-row--full">
                                    <div>
                                        <span class="feature-mgmt-row__label">
                                            <i class="fa-solid fa-location-dot feature-mgmt-row__icon" aria-hidden="true"></i>
                                            {{ __('Allowed Countries') }}
                                        </span>
                                        <span class="feature-mgmt-row__hint">{{ __('Comma-separated ISO codes - empty allows every country.') }}</span>
                                    </div>
                                    <div class="feature-mgmt-row__control">
                                        <input type="text"
                                               name="panels[{{ $panel }}][countries_allowed]"
                                               class="form-control feature-mgmt-input"
                                               placeholder="{{ __('e.g. BD, IN, US') }}"
                                               value="{{ $countries }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            <div class="feature-mgmt-form-footer">
                <a href="{{ route('admin.features.index') }}" class="btn feature-mgmt-btn-secondary">{{ __('Cancel') }}</a>
                <button type="submit" class="btn feature-mgmt-btn-primary">
                    <x-icon name="complete" class="icon me-1"/>
                    {{ __('Save Changes') }}
                </button>
            </div>
        </form>
    </div>
@endsection
