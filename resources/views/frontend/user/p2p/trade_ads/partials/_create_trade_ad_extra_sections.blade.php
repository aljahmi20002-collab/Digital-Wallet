@php
    $isEditing = isset($offer);
    $currentOfferSide = strtoupper((string) old('side', $isEditing ? $offer->side->value : 'BUY'));
    $isSellOfferSide = $currentOfferSide === 'SELL';
    $selectedPaymentMethodIds = collect(old('payment_method_ids', $isEditing ? ($offerPaymentMethodIds ?? []) : []))
        ->map(fn ($id) => (int) $id)
        ->filter(fn ($id) => $id > 0)
        ->all();
    $p2pCountries = \App\Support\P2PPaymentMethodManager::countryOptions(collect($methods ?? []));
    $savedPaymentMethodIds = collect($paymentAccounts ?? collect())
        ->pluck('payment_method_id')
        ->map(fn ($id) => (int) $id)
        ->unique()
        ->values()
        ->all();
    $orderedMethods = collect($methods ?? collect())
        ->sortBy(function ($method) use ($savedPaymentMethodIds, $userCountryCode) {
            $methodId = (int) ($method->id ?? 0);
            $countryCode = strtoupper((string) ($method->country ?? ''));
            $isLocal = !empty($userCountryCode) && $countryCode === strtoupper((string) $userCountryCode);
            $isGlobal = $countryCode === '';
            $sortOrder = (int) ($method->sort_order ?? 0);
            $methodName = mb_strtolower(trim((string) ($method->name ?? '')));

            return [
                $isLocal ? 0 : ($isGlobal ? 1 : 2),
                in_array($methodId, $savedPaymentMethodIds, true) ? 0 : 1,
                $sortOrder,
                $methodName,
                $methodId,
            ];
        })
        ->values();
@endphp

<div class="p2p-offer-section" id="p2pPaymentMethodsSection">
    <div class="p2p-offer-section__head">
        <div>
            <h6 class="p2p-offer-section__title" id="p2pPaymentMethodsTitle">
                {{ $isSellOfferSide ? __('Accepted Payment Methods') : __('Preferred / Available Payment Methods') }}
            </h6>
            <p class="p2p-offer-section__subtitle" id="p2pPaymentMethodsSubtitle">
                {{ $isSellOfferSide
                    ? __('Choose which of your saved payment accounts buyers can pay to.')
                    : __('Select the payment methods you can use to send payment from your saved accounts.') }}
            </p>
        </div>
        <a href="{{ route('user.p2p.payment-accounts.index') }}" class="btn btn-light-primary btn-sm p2p-btn-xs">
            <i class="fas fa-wallet me-1"></i> @lang('Manage Accounts')
        </a>
    </div>
    <div class="p2p-offer-methods">
        @if(collect($paymentAccounts ?? collect())->isEmpty())
            <div class="alert alert-warning border-0 mb-3">
                <div class="fw-semibold mb-1">@lang('No saved payment accounts found')</div>
                <div class="small">@lang('Add your own payment accounts first. Trade ads can only use payment methods that you have already saved in your profile.')</div>
            </div>
        @endif

        <div class="p2p-method-toolbar mb-3">
            <div class="p2p-method-toolbar__summary">
                <div class="p2p-method-toolbar__label">@lang('Method Filters')</div>
                <div class="p2p-method-toolbar__stats">
                    <span class="p2p-method-toolbar__stat">
                        <span class="fw-semibold" id="p2pPaymentMethodsReadyCount">{{ count($savedPaymentMethodIds) }}</span>
                        @lang('ready')
                    </span>
                    <span class="p2p-method-toolbar__stat p2p-method-toolbar__stat--active">
                        <span class="fw-semibold" id="p2pPaymentMethodsSelectedCount">{{ count($selectedPaymentMethodIds) }}</span>
                        @lang('selected')
                    </span>
                </div>
            </div>

            <div class="p2p-method-toolbar__filters">
                <div class="input-group input-group-sm p2p-method-toolbar__country">
                    <span class="input-group-text"><i class="fas fa-globe"></i></span>
                    <select id="p2pPaymentMethodsCountry" class="form-select form-select-sm">
                        <option value="">@lang('All Countries')</option>
                        @foreach($p2pCountries as $c)
                            <option value="{{ $c }}">{{ getCountryDisplayLabel((string) $c, false) ?? $c }}</option>
                        @endforeach
                        <option value="__NONE__">@lang('Other / Not specified')</option>
                    </select>
                </div>

                <div class="input-group input-group-sm p2p-method-toolbar__search">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" id="p2pPaymentMethodsSearch" class="form-control form-control-sm" placeholder="@lang('Search payment methods')">
                </div>
            </div>
        </div>

        <div class="p2p-offer-methods__list">
            <div class="p2p-method-grid" id="p2pPaymentMethodsList">
                @foreach($orderedMethods as $m)
                    @php
                        $methodEnabled = in_array((int) $m->id, $savedPaymentMethodIds, true);
                        $countryCode = strtoupper((string) ($m->country ?? ''));
                        $countryLabel = $countryCode !== '' ? (getCountryDisplayLabel($countryCode, false) ?? $countryCode) : __('Global');
                        $countryFlag = $countryCode !== '' ? getCountryFlagEmoji($countryCode) : '';
                        $isLocalPriority = !empty($userCountryCode) && $countryCode === strtoupper((string) $userCountryCode);
                        $addAccountUrl = route('user.p2p.payment-accounts.index', [
                            'create' => 1,
                            'payment_method_id' => (int) $m->id,
                        ]);
                    @endphp
                    <div
                        class="js-p2p-payment-method-item"
                        data-country="{{ $countryCode }}"
                        data-ready="{{ $methodEnabled ? '1' : '0' }}"
                        data-search="{{ \Illuminate\Support\Str::lower(trim($m->name . ' ' . $countryLabel)) }}"
                    >
                        <label class="p2p-method-card {{ $methodEnabled ? '' : 'p2p-method-card--disabled' }}" for="p2p_pm_{{ $m->id }}">
                            <input
                                class="p2p-pm-check"
                                type="checkbox"
                                name="payment_method_ids[]"
                                value="{{ $m->id }}"
                                id="p2p_pm_{{ $m->id }}"
                                @checked(in_array((int) $m->id, $selectedPaymentMethodIds, true))
                                @disabled(!$methodEnabled)
                            >
                            <span class="p2p-method-card__inner">
                                <span class="p2p-method-card__check"><i class="fas fa-check"></i></span>
                                <span class="p2p-method-card__brand">
                                    @if(!empty($m->logo))
                                        <img src="{{ asset('storage/' . ltrim((string) $m->logo, '/')) }}" alt="{{ $m->name }}" loading="lazy">
                                    @else
                                        <span>{{ strtoupper(substr((string) $m->name, 0, 1)) }}</span>
                                    @endif
                                </span>
                                <span class="p2p-method-card__content">
                                    <span class="p2p-method-card__top">
                                        <span class="p2p-method-card__title-group">
                                            <span class="p2p-method-card__name">{{ $m->name }}</span>
                                            <span class="p2p-method-card__tags">
                                                <span class="p2p-method-card__tag">
                                                    {{ trim(($countryFlag !== '' ? $countryFlag . ' ' : '') . $countryLabel) }}
                                                </span>
                                                @if($isLocalPriority)
                                                    <span class="p2p-method-card__tag p2p-method-card__tag--success">@lang('Local')</span>
                                                @endif
                                            </span>
                                        </span>
                                        @if($methodEnabled)
                                            <span class="p2p-method-card__status is-ready">
                                                @lang('Ready')
                                            </span>
                                        @else
                                            <a href="{{ $addAccountUrl }}" class="p2p-method-card__status p2p-method-card__status-link is-missing">
                                                @lang('Add Account')
                                            </a>
                                        @endif
                                    </span>
                                    <span class="p2p-method-card__hint">
                                        {{ $methodEnabled
                                            ? __('Use your saved account for this payment method.')
                                            : __('No saved account added yet.') }}
                                    </span>
                                </span>
                            </span>
                        </label>
                    </div>
                @endforeach
            </div>

            <div id="p2pPaymentMethodsEmptyState" class="alert alert-light border d-none mt-3 mb-0">
                <div class="fw-semibold mb-1">@lang('No payment methods match your filters')</div>
                <div class="small text-muted">@lang('Try a different country filter or search keyword.')</div>
            </div>
        </div>
        <small class="text-muted p2p-offer-muted" id="p2pPaymentMethodsHint">
            {{ $isSellOfferSide
                ? __('Choose which of your saved payment accounts buyers can pay to.')
                : __('Select the payment methods you can use to send payment. Sellers will see these before starting a trade.') }}
        </small>
    </div>
</div>

<div class="p2p-offer-section">
    <div class="p2p-offer-section__head">
        <div>
            <h6 class="p2p-offer-section__title">@lang('Terms & Instructions')</h6>
            <p class="p2p-offer-section__subtitle">@lang('Add payment rules, timing notes, and trade instructions.')</p>
        </div>
    </div>
    <textarea name="terms" class="form-control  p2p-terms-textarea" rows="6" placeholder="@lang('Add your terms, instructions, allowed time, etc.')">{{ old('terms', $isEditing ? $offer->terms_text : '') }}</textarea>
</div>

@if(!$isEditing)
<div class="p2p-offer-section">
    <div class="p2p-offer-section__head">
        <div>
            <h6 class="p2p-offer-section__title">@lang('Ad Promotion (Optional)')</h6>
            <p class="p2p-offer-section__subtitle">@lang('Boost your trade ad right after publishing with a promotion plan')</p>
        </div>
    </div>

    @php
        $decimals = (int) setting('site_decimal', 2);
        $canPromote = isset($packages) && $packages->isNotEmpty();
        $promoteChecked = (string) old('promote_now', '0') === '1';
    @endphp

    <div class="row g-3">
        <div class="col-12">
            <div class="form-check form-switch">
                <input type="hidden" name="promote_now" value="0">
                <input class="form-check-input" type="checkbox" id="p2pPromoteNow" name="promote_now" value="1" {{ $promoteChecked ? 'checked' : '' }} {{ $canPromote ? '' : 'disabled' }}>
                <label class="form-check-label" for="p2pPromoteNow">@lang('Promote this trade ad now')</label>
            </div>
            @if(!$canPromote)
                <div class="small text-muted mt-1">@lang('No promotion plans are available right now.')</div>
            @endif
        </div>

        <div class="col-12 col-md-6" id="p2pPromotionPackageWrap">
            <label class="form-label">@lang('Promotion Plan')</label>
            <select class="form-select form-select-sm" name="promotion_package_id" id="p2pPromotionPackage">
                <option value="">@lang('Select plan')</option>
                @foreach(($packages ?? collect()) as $pkg)
                    @php
                        $baseCurrency = (string) ($pkg->base_currency ?: siteCurrency('code'));
                        $basePrice = (float) $pkg->effectiveBasePrice();
                        $pkgAppliesTo = strtoupper(trim((string) ($pkg->applies_to ?? 'BOTH')));
                        $pkgFeatures = (array) ($pkg->features ?? []);
                        $pkgAllowedCategories = (array) ($pkg->allowed_categories ?? []);
                        $durationText = (int) $pkg->duration_minutes >= 60
                            ? (rtrim(rtrim(number_format(((int) $pkg->duration_minutes) / 60, 2, '.', ''), '0'), '.') . ' ' . __('hours'))
                            : ((int) $pkg->duration_minutes . ' ' . __('minutes'));
                    @endphp
                    <option
                        value="{{ $pkg->id }}"
                        @selected((string) old('promotion_package_id') === (string) $pkg->id)
                        data-base-currency="{{ $baseCurrency }}"
                        data-base-price="{{ number_format((float) $basePrice, $decimals, '.', '') }}"
                        data-duration="{{ (int) $pkg->duration_minutes }}"
                        data-applies-to="{{ $pkgAppliesTo }}"
                        data-accent-color="{{ strtoupper((string) ($pkg->accent_color ?? '')) }}"
                        data-search-priority="{{ (int) ($pkg->search_priority ?? 0) }}"
                        data-auto-renew="{{ (int) ($pkg->auto_renew_allowed ?? 0) }}"
                        data-allowed-categories="{{ implode(',', $pkgAllowedCategories) }}"
                        data-featured-listing="{{ !empty($pkgFeatures['featured_listing']) ? '1' : '0' }}"
                        data-highlighted-card="{{ !empty($pkgFeatures['highlighted_card']) ? '1' : '0' }}"
                        data-search-priority-boost="{{ !empty($pkgFeatures['search_priority_boost']) ? '1' : '0' }}"
                        data-featured-badge="{{ (!empty($pkgFeatures['featured_badge']) || !empty($pkgFeatures['verified_badge'])) ? '1' : '0' }}"
                    >
                        {{ $pkg->name }} - {{ number_format((float) $basePrice, $decimals) }} {{ $baseCurrency }} ({{ $durationText }})
                    </option>
                @endforeach
            </select>
            @error('promotion_package_id')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
            <div id="p2pPromotionPackageHint" class="p2p-promo-package-hint text-muted"></div>
        </div>

        <div class="col-12 col-md-6" id="p2pPromotionWalletWrap">
            <label class="form-label">@lang('Pay From Wallet')</label>
            <select class="form-select form-select-sm" name="promotion_wallet_id" id="p2pPromotionWallet">
                <option value="">@lang('Select wallet')</option>
                @foreach(($wallets ?? collect()) as $w)
                    <option value="{{ $w->id }}" @selected((string) old('promotion_wallet_id') === (string) $w->id)>
                        {{ $w->currency?->code ?? '' }} - @lang('Balance'): {{ number_format((float) $w->balance, $decimals) }}
                    </option>
                @endforeach
            </select>
            @error('promotion_wallet_id')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-12" id="p2pPromotionPreviewWrap">
            <div class="p2p-promo-preview">
                <div class="p2p-promo-preview__title">@lang('Plan Preview')</div>

                <div id="p2pPromotionPreviewEmpty" class="p2p-promo-empty">@lang('Select a plan to see the details')</div>

                <div class="p2p-promo-meta mb-2" id="p2pPromotionPreviewMeta">
                    <div class="p2p-promo-meta__item" id="p2pPromotionPreviewPriceItem">
                        <small>@lang('Base Price')</small>
                        <strong id="p2pPromotionPreviewPrice">-</strong>
                    </div>
                    <div class="p2p-promo-meta__item" id="p2pPromotionPreviewDurationItem">
                        <small>@lang('Duration')</small>
                        <strong id="p2pPromotionPreviewDuration">-</strong>
                    </div>
                    <div class="p2p-promo-meta__item" id="p2pPromotionPreviewSideItem">
                        <small>@lang('Offer Side')</small>
                        <strong id="p2pPromotionPreviewSide">-</strong>
                    </div>
                    <div class="p2p-promo-meta__item" id="p2pPromotionPreviewPriorityItem">
                        <small>@lang('Search Priority')</small>
                        <strong id="p2pPromotionPreviewPriority">-</strong>
                    </div>
                </div>

                <div id="p2pPromotionPreviewFeaturesSection" class="p2p-promo-summary__group">
                    <div class="p2p-promo-group-title">@lang('Included Features')</div>
                    <div id="p2pPromotionPreviewFeatures" class="p2p-promo-features"></div>
                </div>

                <div id="p2pPromotionPreviewRulesSection" class="p2p-promo-rules">
                    <div id="p2pPromotionPreviewRules" class="p2p-promo-rules__list"></div>
                </div>
            </div>
        </div>
    </div>
</div>

@endif

<div class="p2p-offer-footer">
    <div class="p2p-offer-footer__note">
        <i class="fas fa-lock"></i>
        <span>@lang('Your funds will be held in escrow until an order is created')</span>
    </div>
    <div class="p2p-offer-footer__actions">
        <a href="{{ route('user.p2p.offers.my') }}" class="btn btn-light-success btn-sm">
            <i class="fas fa-arrow-left me-1"></i> {{ $isEditing ? __('Back to My Trade Ads') : __('Cancel') }}
        </a>
        <button class="btn btn-base btn-sm submit-btn fw-normal" type="submit">
            <i class="fas fa-check-circle me-1"></i>
            {{ $isEditing ? (($manageCanEditTradeAd ?? false) ? __('Save Changes') : __('Editing Locked')) : __('Publish Offer') }}
        </button>
    </div>
</div>
