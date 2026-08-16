@extends('frontend.layouts.user.index')
@section('title', __('My Trade Ads'))
@section('content')
{{-- My Trade Ads: status overview, promotion state, and quick lifecycle actions for owned trade ads --}}
@php
    $summaryCards = [
        [
            'label' => __('Total Ads'),
            'value' => (int) ($summary['total_offers'] ?? 0),
            'icon' => 'fa-layer-group',
            'tone' => 'primary',
        ],
        [
            'label' => __('Live Ads'),
            'value' => (int) ($summary['active_offers'] ?? 0),
            'icon' => 'fa-check-circle',
            'tone' => 'success',
        ],
        [
            'label' => __('Promoted Ads'),
            'value' => (int) ($summary['promoted_offers'] ?? 0),
            'icon' => 'fa-bullhorn',
            'tone' => 'warning',
        ],
        [
            'label' => __('Open Trades'),
            'value' => (int) ($summary['open_orders'] ?? 0),
            'icon' => 'fa-sync-alt',
            'tone' => 'info',
        ],
        [
            'label' => __('Completed Trades'),
            'value' => (int) ($summary['completed_orders'] ?? 0),
            'icon' => 'fa-check-double',
            'tone' => 'success-soft',
        ],
    ];
@endphp
<div class="single-form-card p2p-ui">
    <x-user-feature-header
        :title="__('My Trade Ads')"
        :subtitle="__('Track and manage your trade advertisements all from one place.')"
        icon="fas fa-store"
    >
        <a href="{{ route('user.p2p.orders.index') }}" class="btn btn-light-primary btn-sm">
            <i class="fas fa-exchange-alt"></i> @lang('My Orders')
        </a>
        <a href="{{ route('user.p2p.offers.index') }}" class="btn btn-light-primary btn-sm">
            <i class="fas fa-store"></i> @lang('Marketplace')
        </a>
        <a href="{{ route('user.p2p.offers.create') }}" class="btn btn-light-success btn-sm">
            <i class="fas fa-plus"></i> @lang('New Ad')
        </a>
    </x-user-feature-header>
    <div class="card-main p2p-card-main">
        <div class="p2p-my-ads-summary">
            @foreach($summaryCards as $card)
                <div class="p2p-summary-card p2p-summary-card--{{ $card['tone'] }}">
                    <span class="p2p-summary-card__icon"><i class="fas {{ $card['icon'] }}"></i></span>
                    <div class="p2p-summary-card__content">
                        <span class="p2p-summary-card__label">{{ $card['label'] }}</span>
                        <strong class="p2p-summary-card__value">{{ number_format($card['value']) }}</strong>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="p2p-offers-panel">
            <div class="p2p-offers-panel__head">
                <h6 class="p2p-offers-panel__title">@lang('My Trade Ads')</h6>
                <div class="p2p-offers-panel__toolbar">
                    <form method="GET" action="{{ route('user.p2p.offers.my') }}" class="p2p-my-ads-toolbar">
                        <div class="p2p-filter-field p2p-filter-field--search">
                            <div class="p2p-filter-control">
                                <i class="fas fa-search"></i>
                                <input type="text" name="search" value="{{ $search ?? '' }}" class="form-control form-control-sm" placeholder="@lang('Search ads...')">
                            </div>
                        </div>

                        <div class="p2p-filter-field">
                            <div class="p2p-filter-control">
                                <i class="fas fa-circle-dot"></i>
                                <select name="status" class="form-select form-select-sm">
                                    <option value="">@lang('Active')</option>
                                    <option value="ACTIVE" @selected(($status ?? '') === 'ACTIVE')>@lang('Active')</option>
                                    <option value="PAUSED" @selected(($status ?? '') === 'PAUSED')>@lang('Paused')</option>
                                    <option value="DISABLED" @selected(($status ?? '') === 'DISABLED')>@lang('Disabled')</option>
                                    <option value="PROMOTED" @selected(($status ?? '') === 'PROMOTED')>@lang('Promoted')</option>
                                </select>
                            </div>
                        </div>

                        <div class="p2p-filter-field">
                            <div class="p2p-filter-control">
                                <i class="fas fa-sort-amount-down"></i>
                                <select name="sort" class="form-select form-select-sm">
                                    <option value="recent" @selected(($sort ?? 'recent') === 'recent')>@lang('Price High to Low')</option>
                                    <option value="recent" @selected(($sort ?? 'recent') === 'recent')>@lang('Recently Updated')</option>
                                    <option value="oldest" @selected(($sort ?? '') === 'oldest')>@lang('Oldest')</option>
                                    <option value="price_low" @selected(($sort ?? '') === 'price_low')>@lang('Price Low to High')</option>
                                    <option value="price_high" @selected(($sort ?? '') === 'price_high')>@lang('Price High to Low')</option>
                                    <option value="most_orders" @selected(($sort ?? '') === 'most_orders')>@lang('Most Orders')</option>
                                </select>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-success btn-sm p2p-search-solid">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>

                    <button type="button" class="btn btn-light-primary btn-sm p2p-my-ads-toolbar__view-toggle" title="@lang('Toggle view')">
                        <i class="fas fa-th"></i>
                    </button>
                </div>
            </div>

            {{-- Owned trade ad cards --}}
            <div class="p2p-offers-panel__body p2p-my-ads-list">
                @forelse($offers as $offer)
                    @php
                        $currency = $offer->wallet->currency->code;
                        $decimals = (int) setting('site_decimal', 2);
                        $fiatCurrency = siteCurrency('code') ?: $currency;
                        $priceText = number_format((float) $offer->price, $decimals);
                        $limitText = number_format((float) $offer->min_amount, $decimals) . ' - ' . ($offer->max_amount !== null ? number_format((float) $offer->max_amount, $decimals) : __('∞'));
                        $paymentMethods = $offer->paymentMethods->filter(fn ($method) => !empty($method?->name))->pluck('name')->values();
                        $paymentMethodsText = $paymentMethods->take(2)->implode(', ');
                        $hasOpenOrders = (int) ($offer->active_orders_count ?? 0) > 0;
                        $isDisabled = $offer->status === \App\Enums\P2P\OfferStatus::DISABLED;
                        $promotion = $offer->promotion;
                        $isPromoted = $promotion
                            && $promotion->status === \App\Enums\P2P\PromotionStatus::ACTIVE
                            && $promotion->ends_at
                            && $promotion->ends_at->isFuture();
                        $visibilityText = $offer->status === \App\Enums\P2P\OfferStatus::ACTIVE
                            ? __('Visible in Marketplace')
                            : ($offer->status === \App\Enums\P2P\OfferStatus::PAUSED ? __('Paused from Marketplace') : __('Not Available'));
                        $readinessText = $hasOpenOrders
                            ? __('Has open trade activity')
                            : ($isDisabled ? __('Requires system attention') : __('Ready to receive trade requests'));
                    @endphp

                    <div class="p2p-my-ad-card">
                        <div class="p2p-my-ad-card__accent"></div>
                        <div class="p2p-my-ad-card__body">
                            <div class="p2p-my-ad-card__main">
                                <div class="p2p-my-ad-card__title-row">
                                    <h6 class="p2p-my-ad-card__title">#{{ $offer->id }} · {{ $offer->side->label() }} {{ $currency }}</h6>
                                    <div class="p2p-my-ad-card__badges">
                                        <span class="{{ $offer->status->badgeClass() }}">{{ $offer->status->label() }}</span>
                                        @if($isPromoted)
                                            <span class="badge rounded-pill bg-warning-subtle text-warning border border-warning-subtle"><i class="fas fa-bullhorn me-1"></i>@lang('Promoted')</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="p2p-my-ad-card__metrics">
                                    <div class="p2p-my-ad-card__metric">
                                        <span class="p2p-my-ad-card__metric-icon"><i class="fas fa-tag"></i></span>
                                        <div>
                                            <span class="p2p-my-ad-card__metric-label">@lang('Rate')</span>
                                            <strong class="p2p-my-ad-card__metric-value">{{ $priceText }} {{ $fiatCurrency }}</strong>
                                        </div>
                                    </div>

                                    <div class="p2p-my-ad-card__metric">
                                        <span class="p2p-my-ad-card__metric-icon"><i class="fas fa-wallet"></i></span>
                                        <div>
                                            <span class="p2p-my-ad-card__metric-label">@lang('Limits')</span>
                                            <strong class="p2p-my-ad-card__metric-value">{{ $limitText }}</strong>
                                        </div>
                                    </div>
                                </div>

                                <div class="p2p-my-ad-card__meta-line">
                                    <span><i class="fas fa-clock"></i>@lang('Updated') {{ $offer->updated_at->diffForHumans() }}.</span>
                                    <span><i class="fas fa-circle-check"></i>{{ $visibilityText }}</span>
                                    <span><i class="fas fa-bolt"></i>{{ $readinessText }}</span>
                                </div>
                            </div>

                            <div class="p2p-my-ad-card__cta">
                                <a href="{{ route('user.p2p.offers.edit', $offer) }}" class="btn btn-light-success btn-sm p2p-my-ad-card__manage-btn">
                                    <i class="fas fa-sliders-h me-1"></i> @lang('Manage Ad')
                                    <i class="fas fa-chevron-right p2p-my-ad-card__manage-icon"></i>
                                </a>
                            </div>
                        </div>

                    </div>
                @empty
                    <div class="p2p-my-ads-empty text-center">
                        <div class="p2p-my-ads-empty__icon"><i class="fas fa-store"></i></div>
                        <h6 class="mb-2">@lang('No trade ads yet')</h6>
                        <p class="text-muted mb-3">@lang('Create your first buy or sell ad to start receiving trade requests in the marketplace.')</p>
                        <div class="d-flex flex-wrap justify-content-center gap-2">
                            <a href="{{ route('user.p2p.offers.create') }}" class="btn btn-light-success btn-sm">
                                <i class="fas fa-plus me-1"></i> @lang('Create Trade Ad')
                            </a>
                            <a href="{{ route('user.p2p.offers.index') }}" class="btn btn-light-primary btn-sm">
                                <i class="fas fa-store me-1"></i> @lang('Browse Marketplace')
                            </a>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>

        @if($offers->hasPages())
            {{ $offers->links() }}
        @endif
    </div>
</div>
@endsection
