@extends('backend.layouts.app')

@section('title', __('Feature Management'))

@push('styles')
    <link rel="stylesheet" href="{{ asset('backend/css/feature-management.css') }}">
@endpush

@section('content')
    @php
        $enabledPercent = $summary['total'] > 0 ? (int) round(($summary['enabled'] / $summary['total']) * 100) : 0;
        $enabledProgressStep = max(0, min(100, (int) round($enabledPercent / 5) * 5));
    @endphp

    <div class="feature-mgmt-page">

        {{-- Hero: compact header + inline stat summary --}}
        <section class="feature-mgmt-hero">
            <div class="feature-mgmt-hero__intro">
                <span class="feature-mgmt-hero__eyebrow">{{ __('System Config') }}</span>
                <h1 class="feature-mgmt-hero__title">{{ __('Feature Management') }}</h1>
                <p class="feature-mgmt-hero__subtitle">
                    {{ __('Govern what is visible and accessible across user, merchant, and agent panels from one place.') }}
                </p>
                <div class="feature-mgmt-hero__progress feature-mgmt-progress--{{ $enabledProgressStep }}"
                     role="progressbar"
                     aria-label="{{ __('Enabled feature progress') }}"
                     aria-valuemin="0"
                     aria-valuemax="100"
                     aria-valuenow="{{ $enabledPercent }}">
                    <span></span>
                </div>
            </div>

            <div class="feature-mgmt-hero__stats">
                <div class="feature-mgmt-stat">
                    <span class="feature-mgmt-stat__dot feature-mgmt-stat__dot--success"></span>
                    <div>
                        <span class="feature-mgmt-stat__label">{{ __('Enabled') }}</span>
                        <span class="feature-mgmt-stat__value">{{ $summary['enabled'] }}<span class="feature-mgmt-stat__denom">/{{ $summary['total'] }}</span></span>
                    </div>
                </div>
                <div class="feature-mgmt-stat">
                    <span class="feature-mgmt-stat__dot feature-mgmt-stat__dot--muted"></span>
                    <div>
                        <span class="feature-mgmt-stat__label">{{ __('Disabled') }}</span>
                        <span class="feature-mgmt-stat__value">{{ $summary['disabled'] }}</span>
                    </div>
                </div>
                <div class="feature-mgmt-stat">
                    <span class="feature-mgmt-stat__dot feature-mgmt-stat__dot--warning"></span>
                    <div>
                        <span class="feature-mgmt-stat__label">{{ __('Core') }}</span>
                        <span class="feature-mgmt-stat__value">{{ $summary['core'] }}</span>
                    </div>
                </div>
                <div class="feature-mgmt-stat">
                    <span class="feature-mgmt-stat__dot feature-mgmt-stat__dot--role"></span>
                    <div>
                        <span class="feature-mgmt-stat__label">{{ __('Role') }}</span>
                        <span class="feature-mgmt-stat__value">{{ $summary['roles'] }}</span>
                    </div>
                </div>
            </div>
        </section>

        {{-- Toolbar: search only, heading stays subtle --}}
        <div class="feature-mgmt-toolbar">
            <div>
                <h2 class="feature-mgmt-toolbar__title">{{ __('Feature Catalog') }}</h2>
                <p class="feature-mgmt-toolbar__subtitle">{{ __('Flat feature switches with panel visibility at a glance.') }}</p>
            </div>

            <div class="feature-mgmt-search">
                <i class="fa-solid fa-magnifying-glass feature-mgmt-search__icon" aria-hidden="true"></i>
                <input type="text"
                       id="featureSearchInput"
                       class="feature-mgmt-search__input"
                       placeholder="{{ __('Search features...') }}"
                       autocomplete="off">
            </div>
        </div>

        <section class="feature-mgmt-catalog-panel">
            <header class="feature-mgmt-catalog-panel__header">
                <div>
                    <span class="feature-mgmt-catalog-panel__eyebrow">{{ __('Unified Controls') }}</span>
                    <h3 class="feature-mgmt-catalog-panel__title">{{ __('All Feature Controls') }}</h3>
                </div>
                <span class="feature-mgmt-catalog-panel__count">
                    {{ trans_choice('{1} :count feature|[2,*] :count features', $features->count(), ['count' => $features->count()]) }}
                </span>
            </header>

            @forelse($features as $feature)
                @php
                    $isEnabled     = (bool) $feature->is_enabled;
                    $isRoleControl = config("feature_catalog.features.{$feature->key}.manage_mode") === 'role_toggle';
                    $featureIconMap = [
                        'deposit_money'       => 'fa-wallet',
                        'withdraw_money'      => 'fa-arrow-up',
                        'send_money'          => 'fa-paper-plane',
                        'request_money'       => 'fa-hand-holding-dollar',
                        'exchange_money'      => 'fa-right-left',
                        'wallet_earn'         => 'fa-chart-line',
                        'mobile_recharge'     => 'fa-mobile-screen-button',
                        'bank_transfer'       => 'fa-building-columns',
                        'payment_link'        => 'fa-link',
                        'merchant_payment'    => 'fa-store',
                        'agent_program'       => 'fa-user-tie',
                        'subscription_system' => 'fa-layer-group',
                        'p2p_marketplace'     => 'fa-handshake',
                        'virtual_card'        => 'fa-credit-card',
                        'referral_program'    => 'fa-share-nodes',
                        'user_ranks'          => 'fa-award',
                        'vouchers'            => 'fa-gift',
                    ];
                    $featureIcon = $featureIconMap[$feature->key] ?? 'fa-sliders';
                    $declaredPanels = (array) config("feature_catalog.features.{$feature->key}.panels", []);
                    $rulePanels = $feature->accessRules->pluck('panel')->all();
                    $defaultVisiblePanels = collect($declaredPanels)
                        ->filter(fn ($visible, $panel): bool => (bool) $visible && ! in_array($panel, $rulePanels, true))
                        ->keys();
                    $panelsVisible = $feature->accessRules
                        ->where('is_visible', true)
                        ->pluck('panel')
                        ->merge($defaultVisiblePanels)
                        ->intersect(\App\Models\FeatureAccessRule::PANELS)
                        ->unique()
                        ->values();
                @endphp

                @if($loop->first)
                    <div class="row g-3 feature-mgmt-grid feature-mgmt-grid--flat">
                @endif

                <div class="col-12 col-xl-6 feature-mgmt-col"
                     data-feature-search="{{ strtolower($feature->key.' '.$feature->label.' '.$feature->description.' '.$feature->category) }}">
                    <article class="feature-mgmt-item feature-mgmt-item--flat {{ $isRoleControl ? 'feature-mgmt-role-card' : '' }} {{ $isEnabled ? '' : 'is-disabled' }}"
                             title="{{ $feature->key }}">
                        <div class="feature-mgmt-item__main">
                            <div class="feature-mgmt-item__head">
                                <div class="feature-mgmt-item__title-wrap">
                                    <h4 class="feature-mgmt-item__title">
                                        <span class="feature-mgmt-title-icon" aria-hidden="true">
                                            <i class="fa-solid {{ $featureIcon }}"></i>
                                        </span>
                                        <span class="feature-mgmt-item__title-text">{{ __($feature->label) }}</span>
                                        @if($isRoleControl)
                                            <span class="feature-mgmt-badge feature-mgmt-badge--role">{{ __('Role switch') }}</span>
                                        @endif
                                        @if($feature->is_core)
                                            <span class="feature-mgmt-badge feature-mgmt-badge--warning"
                                                  title="{{ __('Disabling this feature affects business-critical flows.') }}">{{ __('Core') }}</span>
                                        @endif
                                    </h4>
                                </div>
                                <form action="{{ route('admin.features.toggle', $feature) }}"
                                      method="POST"
                                      class="feature-mgmt-toggle-form feature-mgmt-card-switch-form"
                                      data-feature-label="{{ $feature->label }}"
                                      @if($isRoleControl) data-feature-kind="role" @endif
                                      data-is-core="{{ $feature->is_core ? '1' : '0' }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="is_enabled" value="0">
                                    <label class="feature-mgmt-card-switch {{ $isEnabled ? 'is-on' : 'is-off' }}"
                                           aria-label="{{ __('Toggle :feature', ['feature' => __($feature->label)]) }}">
                                        <span class="feature-mgmt-card-switch__status">
                                            <span class="feature-mgmt-card-switch__dot" aria-hidden="true"></span>
                                            <span class="feature-mgmt-card-switch__text">{{ $isEnabled ? __('Active') : __('Off') }}</span>
                                        </span>
                                        <span class="form-check form-switch feature-mgmt-switch feature-mgmt-switch--card">
                                            <input class="form-check-input feature-mgmt-switch__input"
                                                   type="checkbox"
                                                   role="switch"
                                                   name="is_enabled"
                                                   value="1"
                                                   {{ $isEnabled ? 'checked' : '' }}>
                                        </span>
                                    </label>
                                </form>
                            </div>

                            <p class="feature-mgmt-item__description">{{ __($feature->description) }}</p>

                            <div class="feature-mgmt-item__panels">
                                @if($isRoleControl)
                                    <span class="feature-mgmt-panel-chip feature-mgmt-panel-chip--role">
                                        <i class="fa-solid fa-lock" aria-hidden="true"></i>
                                        {{ __('Enable / disable only') }}
                                    </span>
                                @else
                                    @forelse($panelsVisible as $panel)
                                        <span class="feature-mgmt-panel-chip">{{ ucfirst($panel) }}</span>
                                    @empty
                                        <span class="feature-mgmt-panel-chip feature-mgmt-panel-chip--muted">{{ __('No panel') }}</span>
                                    @endforelse
                                @endif
                            </div>
                        </div>

                        @unless($isRoleControl)
                            <div class="feature-mgmt-item__actions">
                                <a href="{{ route('admin.features.edit', $feature) }}"
                                   class="feature-mgmt-manage-btn"
                                   title="{{ __('Manage') }}">
                                    <span class="feature-mgmt-manage-btn__icon-wrap" aria-hidden="true">
                                        <i class="fa-solid fa-gear feature-mgmt-manage-btn__icon"></i>
                                    </span>
                                    {{ __('Manage') }}
                                </a>
                            </div>
                        @endunless
                    </article>
                </div>

                @if($loop->last)
                    </div>
                @endif
            @empty
                <x-admin-not-found
                    :title="__('No features registered yet')"
                    :message="__('Run the feature seeder or update the feature catalog to register platform controls.')"
                    icon="fa-sliders"
                />
            @endforelse
        </section>

        <x-admin-not-found
            id="featureSearchEmpty"
            hidden
            :title="__('No matching features')"
            :message="__('Try a different keyword.')"
            icon="fa-magnifying-glass"
        />
    </div>

    {{-- Confirmation modal (re-used by every inline toggle) --}}
    <div class="feature-mgmt-modal-backdrop" id="featureConfirmBackdrop"></div>
    <div class="feature-mgmt-modal" id="featureConfirmModal" aria-hidden="true">
        <div class="feature-mgmt-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="featureConfirmTitle">
            <div class="feature-mgmt-modal__glow feature-mgmt-modal__glow--primary" aria-hidden="true"></div>
            <div class="feature-mgmt-modal__glow feature-mgmt-modal__glow--danger" aria-hidden="true"></div>

            <button type="button" class="feature-mgmt-modal__close" data-feature-dismiss aria-label="{{ __('Close') }}">
                <i class="fa-solid fa-xmark"></i>
            </button>

            <div class="feature-mgmt-modal__header">
                <div class="feature-mgmt-modal__icon" aria-hidden="true">
                    <i class="fa-solid fa-triangle-exclamation feature-mgmt-modal__icon-glyph feature-mgmt-modal__icon-glyph--danger"></i>
                    <i class="fa-solid fa-bolt feature-mgmt-modal__icon-glyph feature-mgmt-modal__icon-glyph--success"></i>
                </div>
                <span class="feature-mgmt-modal__eyebrow" id="featureConfirmEyebrow">{{ __('Feature Control') }}</span>
            </div>

            <div class="feature-mgmt-modal__body">
                <h3 class="feature-mgmt-modal__title" id="featureConfirmTitle">{{ __('Disable feature?') }}</h3>
                <p class="feature-mgmt-modal__text" id="featureConfirmText">
                    {{ __('This action updates the feature status for configured panels.') }}
                </p>

                <div class="feature-mgmt-modal__impact">
                    <span class="feature-mgmt-modal__impact-dot" aria-hidden="true"></span>
                    <div class="feature-mgmt-modal__impact-copy">
                        <span class="feature-mgmt-modal__impact-label">{{ __('Effect') }}</span>
                        <span id="featureConfirmImpact">{{ __('Menus, widgets, and guarded routes will follow this switch immediately.') }}</span>
                    </div>
                </div>
            </div>

            <div class="feature-mgmt-modal__actions">
                <button type="button" class="feature-mgmt-btn feature-mgmt-btn--ghost" data-feature-dismiss>
                    <i class="fa-solid fa-xmark feature-mgmt-btn__icon" aria-hidden="true"></i>
                    {{ __('Cancel') }}
                </button>
                <button type="button" class="feature-mgmt-btn feature-mgmt-btn--primary" id="featureConfirmSubmit">
                    <i class="fa-solid fa-check feature-mgmt-btn__icon" id="featureConfirmSubmitIcon" aria-hidden="true"></i>
                    <span id="featureConfirmSubmitLabel">{{ __('Confirm') }}</span>
                </button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('backend/js/feature-management.js') }}"></script>
@endpush
