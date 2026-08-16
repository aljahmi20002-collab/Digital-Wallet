@extends('frontend.layouts.user.index')
@section('title', __('Promote Trade Ad #').$offer->id)
@section('content')
{{-- Trade Ad Promotion Screen: plan selection, wallet selection, live quote, and summary preview --}}
@php
    $promotion = $offer->promotion;
    $decimals = (int) setting('site_decimal', 2);
    $isActive = $promotion
        && $promotion->status === \App\Enums\P2P\PromotionStatus::ACTIVE
        && $promotion->ends_at
        && $promotion->ends_at->isFuture();
@endphp

<div class="single-form-card p2p-ui p2p-promo-page">
    <x-user-feature-header
        :title="__('Promote Trade Ad #').$offer->id"
        :subtitle="__('Boost visibility with a promotion plan and wallet-funded checkout flow.')"
        icon="fas fa-bullhorn"
    >
        <a href="{{ route('user.p2p.offers.my') }}" class="btn btn-light-primary btn-sm p2p-btn-xs">
            <i class="fas fa-arrow-left"></i> @lang('Back')
        </a>
    </x-user-feature-header>
    <div class="card-main p2p-card-main">
        @if($isActive)
            <div class="alert alert-info d-flex align-items-start gap-2">
                <i class="fas fa-info-circle mt-1"></i>
                <div>
                    <div class="fw-semibold">@lang('This trade ad is currently promoted')</div>
                    <div class="small">@lang('Current expiry:') <strong>{{ $promotion->ends_at->format('Y-m-d H:i') }}</strong></div>
                    <div class="small text-muted">@lang('Buying another plan will extend the promotion time.') </div>
                </div>
            </div>
        @else
            <div class="alert alert-light">
                <div class="fw-semibold">@lang('Boost your trade ad')</div>
                <div class="small text-muted">@lang('Choose a plan and pay from your selected wallet. Price will be auto-converted to wallet currency.') </div>
            </div>
        @endif

        <form method="POST" action="{{ route('user.p2p.offers.promotion.purchase', $offer) }}" id="p2pPromotionPurchaseForm">
            @csrf

            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <label class="form-label">@lang('Promotion Plan')</label>
                    <select class="form-select form-select-sm" name="package_id" id="p2pPromotionPackage" required>
                        <option value="">@lang('Select plan')</option>
                        @foreach($packages as $pkg)
                            @php
                                $baseCurrency = (string) ($pkg->base_currency ?: siteCurrency());
                                $basePrice = (float) $pkg->effectiveBasePrice();
                                $pkgAppliesTo = strtoupper(trim((string) ($pkg->applies_to ?? 'BOTH')));
                                $pkgFeatures = (array) ($pkg->features ?? []);
                                $pkgAllowedCategories = (array) ($pkg->allowed_categories ?? []);
                                $durationText = (int) $pkg->duration_minutes >= 60
                                    ? (round(((int) $pkg->duration_minutes) / 60, 2) . ' ' . __('hours'))
                                    : ((int) $pkg->duration_minutes . ' ' . __('minutes'));
                            @endphp
                            <option value="{{ $pkg->id }}" @selected((string) old('package_id') === (string) $pkg->id)
                                data-base-currency="{{ $baseCurrency }}"
                                data-base-price="{{ number_format((float) $basePrice, $decimals, '.', '') }}"
                                data-duration="{{ (int) $pkg->duration_minutes }}"
                                data-applies-to="{{ $pkgAppliesTo }}"
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
                    @error('package_id')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label">@lang('Pay From Wallet')</label>
                    <select class="form-select form-select-sm" name="wallet_id" id="p2pPromotionWallet" required>
                        <option value="">@lang('Select wallet')</option>
                        @foreach($wallets as $w)
                            <option value="{{ $w->id }}" @selected((string) old('wallet_id') === (string) $w->id)
                                data-currency="{{ $w->currency->code }}"
                                data-balance="{{ (float) $w->balance }}"
                            >
                                {{ $w->currency->code }} - @lang('Balance'): {{ number_format((float) $w->balance, $decimals) }}
                            </option>
                        @endforeach
                    </select>
                    @error('wallet_id')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <div class="card p2p-promo-preview">
                        <div class="card-header fw-semibold">@lang('Promotion Summary')</div>
                        <div class="card-body">
                            <div id="p2pPromotionEmpty" class="p2p-promo-empty">@lang('Select a plan to see details')</div>

                            <div class="p2p-promo-meta" id="p2pPromotionMeta">
                                <div class="p2p-promo-meta__item" id="p2pPromotionBasePriceItem">
                                    <small>@lang('Base Price')</small>
                                    <strong id="p2pPromotionBasePrice">-</strong>
                                </div>
                                <div class="p2p-promo-meta__item" id="p2pPromotionPayAmountItem">
                                    <small>@lang('You Pay')</small>
                                    <strong id="p2pPromotionPayAmount">-</strong>
                                </div>
                                <div class="p2p-promo-meta__item" id="p2pPromotionDurationItem">
                                    <small>@lang('Duration')</small>
                                    <strong id="p2pPromotionDuration">-</strong>
                                </div>
                                <div class="p2p-promo-meta__item" id="p2pPromotionAppliesToItem">
                                    <small>@lang('Applies To')</small>
                                    <strong id="p2pPromotionAppliesTo">-</strong>
                                </div>
                                <div class="p2p-promo-meta__item" id="p2pPromotionPriorityItem">
                                    <small>@lang('Search Priority')</small>
                                    <strong id="p2pPromotionPriority">-</strong>
                                </div>
                            </div>

                            <div id="p2pPromotionFeaturesSection" class="p2p-promo-summary__group">
                                <div class="small text-muted mb-1">@lang('Enabled Features')</div>
                                <div id="p2pPromotionFeatures"></div>
                            </div>

                            <div id="p2pPromotionRulesSection" class="p2p-promo-summary__group">
                                <div class="small text-muted mb-1">@lang('Other Rules')</div>
                                <div id="p2pPromotionRules" class="small text-muted">-</div>
                            </div>

                            <div class="mt-2 small p2p-promo-summary__group" id="p2pPromotionQuoteHintWrap">
                                <div id="p2pPromotionQuoteHint"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 d-flex gap-2 justify-content-end">
                    <a href="{{ route('user.p2p.offers.my') }}" class="btn btn-light-secondary btn-sm">@lang('Cancel')</a>
                    <button type="submit" class="btn btn-base btn-sm submit-btn" id="p2pPromotionSubmit" disabled>
                        <i class="fas fa-bullhorn me-1"></i> @lang('Promote Trade Ad')
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

{{-- Promotion Script: selected plan preview, wallet quote refresh, and submit state control --}}
@push('scripts')
    @include('frontend.user.p2p.trade_ads.partials._promote_trade_ad_scripts')
@endpush
