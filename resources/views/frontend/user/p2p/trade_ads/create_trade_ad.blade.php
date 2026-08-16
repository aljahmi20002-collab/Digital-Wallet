@extends('frontend.layouts.user.index')
@section('title', isset($offer) ? __('Manage Trade Ad') : __('Create Trade Ad'))
@section('content')
    @php
        $isEditing = isset($offer);
        $offerPaymentMethodIds = $isEditing ? $offer->paymentMethods->pluck('id')->map(fn ($id) => (int) $id)->all() : [];
        $managePromotion = $isEditing ? $offer->promotion : null;
        $isManagePromoted = $managePromotion
            && $managePromotion->status === \App\Enums\P2P\PromotionStatus::ACTIVE
            && $managePromotion->ends_at
            && $managePromotion->ends_at->isFuture();
        $manageDecimals = (int) setting('site_decimal', 2);
        $manageCurrency = $isEditing ? ($offer->wallet->currency->code ?? siteCurrency('code')) : siteCurrency('code');
        $manageFiatCurrency = siteCurrency('code') ?: $manageCurrency;
        $managePriceText = $isEditing ? number_format((float) $offer->price, $manageDecimals) . ' ' . $manageFiatCurrency : null;
        $manageLimitText = $isEditing
            ? number_format((float) $offer->min_amount, $manageDecimals) . ' - ' . ($offer->max_amount !== null ? number_format((float) $offer->max_amount, $manageDecimals) : __('∞'))
            : null;
        $managePaymentMethodsCount = $isEditing ? $offer->paymentMethods->count() : 0;
        $manageCanEditTradeAd = ! $isEditing || ($canEditTradeAd ?? false);
        $manageCanManageLifecycle = $isEditing && ($canManageLifecycle ?? false);
        $manageCanDelete = $isEditing && (int) ($openOrders ?? 0) === 0;
    @endphp
    {{-- Trade Ad Builder: wallet, pricing, payment methods, terms, and optional promotion plan --}}
    <div class="p2p-manage-shell">
        @if($isEditing)
            <div class="p2p-manage-hero">
                <div class="p2p-manage-hero__top">
                    <div>
                        <span class="p2p-manage-hero__eyebrow">
                            <i class="fas fa-sliders-h"></i>
                            @lang('Trade Ad Management')
                        </span>
                        <h5 class="p2p-manage-hero__title mb-0">@lang('Manage Trade Ad #'){{ $offer->id }}</h5>
                        <p class="p2p-manage-hero__subtitle">@lang('Use this page to update ad details, review trade health, and handle lifecycle actions from one place.')</p>
                    </div>

                    <div class="p2p-manage-hero__actions">
                        <a href="{{ route('user.p2p.offers.my') }}" class="btn btn-light-primary btn-sm p2p-btn-xs">
                            <i class="fas fa-arrow-left me-1"></i> @lang('My Trade Ads')
                        </a>
                        <a href="{{ route('user.p2p.offers.show', $offer) }}" class="btn btn-light-primary btn-sm p2p-btn-xs">
                            <i class="fas fa-eye me-1"></i> @lang('Public View')
                        </a>
                        @if($offer->status !== \App\Enums\P2P\OfferStatus::DISABLED)
                            <a href="{{ route('user.p2p.offers.promotion.show', $offer) }}" class="btn btn-light-warning btn-sm p2p-btn-xs">
                                <i class="fas fa-bullhorn me-1"></i> {{ $isManagePromoted ? __('Extend Promotion') : __('Promote Ad') }}
                            </a>
                            <form method="POST" action="{{ route('user.p2p.offers.toggle', $offer) }}">
                                @csrf
                                <button type="submit" class="btn btn-light-primary btn-sm p2p-btn-xs" @disabled(! $manageCanManageLifecycle) title="{{ (int) ($openOrders ?? 0) > 0 ? __('Open trades must be finished before changing this ad status.') : '' }}">
                                    @if($offer->status === \App\Enums\P2P\OfferStatus::ACTIVE)
                                        <i class="fas fa-pause me-1"></i> {{ __('Pause') }}
                                    @else
                                        <i class="fas fa-play me-1"></i> {{ __('Resume') }}
                                    @endif
                                </button>
                            </form>
                        @endif
                        <form method="POST" action="{{ route('user.p2p.offers.destroy', $offer) }}" onsubmit="return confirm(@json(__('Are you sure you want to delete this trade ad?')))">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-light-danger btn-sm p2p-btn-xs" @disabled(! $manageCanDelete) title="{{ (int) ($openOrders ?? 0) > 0 ? __('Open trades must be finished before deleting this ad.') : '' }}">
                                <i class="fas fa-trash me-1"></i> @lang('Delete')
                            </button>
                        </form>
                    </div>
                </div>

                <div class="p2p-manage-hero__stats">
                    <div class="p2p-manage-stat">
                        <span class="p2p-manage-stat__label">@lang('Status')</span>
                        <span class="p2p-manage-stat__value">{{ $offer->status->label() }}</span>
                        <span class="p2p-manage-stat__hint">{{ $isManagePromoted ? __('Promotion active') : __('Standard placement') }}</span>
                    </div>
                    <div class="p2p-manage-stat">
                        <span class="p2p-manage-stat__label">@lang('Rate')</span>
                        <span class="p2p-manage-stat__value">{{ $managePriceText }}</span>
                        <span class="p2p-manage-stat__hint">@lang('Per') {{ $manageCurrency }}</span>
                    </div>
                    <div class="p2p-manage-stat">
                        <span class="p2p-manage-stat__label">@lang('Limits')</span>
                        <span class="p2p-manage-stat__value">{{ $manageLimitText }}</span>
                        <span class="p2p-manage-stat__hint">{{ number_format($managePaymentMethodsCount) }} @lang('payment methods linked')</span>
                    </div>
                    <div class="p2p-manage-stat">
                        <span class="p2p-manage-stat__label">@lang('Order Health')</span>
                        <span class="p2p-manage-stat__value">{{ number_format((int) ($completedOrders ?? 0)) }}/{{ number_format((int) ($totalOrders ?? 0)) }}</span>
                        <span class="p2p-manage-stat__hint">@lang('Completed vs total orders')</span>
                    </div>
                    <div class="p2p-manage-stat">
                        <span class="p2p-manage-stat__label">@lang('Open Activity')</span>
                        <span class="p2p-manage-stat__value">{{ number_format((int) ($openOrders ?? 0)) }}</span>
                        <span class="p2p-manage-stat__hint">{{ number_format((float) ($completionRate ?? 0), 1) }}% @lang('completion rate')</span>
                    </div>
                </div>

                @if(! $manageCanEditTradeAd)
                    <div class="p2p-manage-alert p2p-manage-alert--warning">
                        <i class="fas fa-circle-info"></i>
                        <span>
                            @if($offer->status === \App\Enums\P2P\OfferStatus::DISABLED)
                                @lang('This ad is currently disabled, so editing is locked until it becomes available again.')
                            @else
                                @lang('This ad has open trades. You can review and manage lifecycle actions here, but editing the offer form stays locked until those trades are resolved.')
                            @endif
                        </span>
                    </div>
                @else
                    <div class="p2p-manage-alert p2p-manage-alert--info">
                        <i class="fas fa-shield-alt"></i>
                        <span>@lang('All key actions now live on this page. Review health above, update the offer below, and manage promotion or lifecycle actions from the action bar.') </span>
                    </div>
                @endif
            </div>
        @endif

        <div class="single-form-card">
        <x-user-feature-header
            :title="$isEditing ? __('Manage Trade Ad Settings') : __('Create Trade Ad')"
            :subtitle="$isEditing ? __('Update pricing, limits, payment methods, and terms from the same editing workspace.') : __('Build a new listing with wallet, pricing, payment method, and terms controls.')"
            icon="fas fa-sliders-h"
            compact
        >
            <a href="{{ route('user.p2p.offers.my') }}" class="btn btn-light-success btn-sm p2p-btn-xs p2p-offer-card__link">
                <i class="fas fa-list"></i> {{ $isEditing ? __('Back to My Trade Ads') : __('My Trade Ads') }}
            </a>
        </x-user-feature-header>

        <div class="card-main p2p-card-main p2p-offer-card__body">
            <form method="POST" action="{{ $isEditing ? route('user.p2p.offers.update', $offer) : route('user.p2p.offers.store') }}">
                @csrf
                @if($isEditing)
                    @method('PUT')
                @endif
                <fieldset class="p2p-offer-builder-fields" @disabled($isEditing && ! $manageCanEditTradeAd)>
                <div class="p2p-offer-section">
                    <div class="p2p-offer-section__head">
                        <div>
                            <h6 class="p2p-offer-section__title">@lang('Ad Details')</h6>
                            <p class="p2p-offer-section__subtitle">{{ $isEditing ? __('Update editable details for this ad from your management workspace') : __('Set your wallet, trade side, price, and trading limits') }}</p>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-12 col-md-6 col-xl-4">
                            <label class="form-label">@lang('Wallet')</label>
                            <select name="wallet_id" id="p2pOfferWallet" class="form-select form-select-sm" required>
                                @foreach($wallets as $w)
                                    <option value="{{ $w->id }}" data-currency="{{ $w->currency?->code ?? siteCurrency('code') }}" @selected((string) old('wallet_id', $isEditing ? $offer->wallet_id : '') === (string) $w->id)>{{ $w->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-6 col-xl-4">
                            <label class="form-label">@lang('Side')</label>
                            <select name="side" class="form-select form-select-sm" required>
                                <option value="BUY" @selected(old('side', $isEditing ? $offer->side->value : 'BUY') === 'BUY')>@lang('Buy')</option>
                                <option value="SELL" @selected(old('side', $isEditing ? $offer->side->value : 'BUY') === 'SELL')>@lang('Sell')</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-6 col-xl-4">
                            <label class="form-label">@lang('Price')</label>
                            <div class="input-group input-group-sm">
                                <input type="text" inputmode="decimal" name="price" value="{{ old('price', $isEditing ? $offer->price : '') }}" class="form-control form-control-sm" oninput="this.value = validateDouble(this.value)" required>
                                <span class="input-group-text" id="p2pAmountCurrencyPrice">{{ siteCurrency('code') }}</span>
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-xl-4">
                            <label class="form-label">@lang('Min Amount')</label>
                            <div class="input-group input-group-sm">
                                <input type="text" inputmode="decimal" name="min_amount" value="{{ old('min_amount', $isEditing ? $offer->min_amount : '') }}" class="form-control form-control-sm" oninput="this.value = validateDouble(this.value)" required>
                                <span class="input-group-text" id="p2pAmountCurrencyMin">{{ siteCurrency('code') }}</span>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-xl-4">
                            <label class="form-label">@lang('Max Amount')</label>
                            <div class="input-group input-group-sm">
                                <input type="text" inputmode="decimal" name="max_amount" value="{{ old('max_amount', $isEditing ? $offer->max_amount : '') }}" class="form-control form-control-sm" oninput="this.value = validateDouble(this.value)">
                                <span class="input-group-text" id="p2pAmountCurrencyMax">{{ siteCurrency('code') }}</span>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-xl-4">
                            <label class="form-label">@lang('Payment Window')</label>
                            <div class="input-group input-group-sm">
                                <input type="text" inputmode="numeric" name="payment_window_minutes" class="form-control form-control-sm" value="{{ old('payment_window_minutes', $isEditing ? (int) ($offer->payment_window_minutes ?? setting('p2p_order_expiry_minutes', 45)) : (int) setting('p2p_order_expiry_minutes', 45)) }}" oninput="this.value = validateDouble(this.value)">
                                <span class="input-group-text">@lang('minutes')</span>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="p2p-offer-hint">
                                <i class="fas fa-info-circle"></i>
                                <span>@lang('Min amount must be less than or equal to max amount.')</span>
                            </div>
                        </div>
                    </div>
                </div>

                @include('frontend.user.p2p.trade_ads.partials._create_trade_ad_extra_sections')
                </fieldset>
            </form>
        </div>
    </div>
    </div>
@endsection

{{-- Trade Ad Builder Script: form state, promotion preview, Summernote, and helper interactions --}}
@push('scripts')
    @include('frontend.user.p2p.trade_ads.partials._create_trade_ad_scripts')
@endpush
