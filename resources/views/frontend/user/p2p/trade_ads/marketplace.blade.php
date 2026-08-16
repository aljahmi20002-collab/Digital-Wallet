@extends('frontend.layouts.user.index')
@section('title', __('P2P Marketplace'))
@section('content')
{{-- Marketplace Screen: listing filters, trade ads, CTA cards, and trade order modal --}}
<div class="single-form-card p2p-ui">
    <x-user-feature-header
        :title="__('P2P Marketplace')"
        :subtitle="__('Browse live trade ads and launch your own listing from the same workspace.')"
        icon="fas fa-store"
    >
        <a href="{{ route('user.p2p.offers.create') }}" class="btn btn-light-success btn-sm">
            <i class="fas fa-plus"></i> <span class="p2p-new-listing-text">@lang('Create Trade Ad')</span>
        </a>
    </x-user-feature-header>
    <div class="card-main p2p-card-main">

    @php
        $side = request('side');
        $sideEnum = \App\Enums\P2P\OrderSide::tryFrom($side);
        $activeBuyTab  = $sideEnum === \App\Enums\P2P\OrderSide::SELL || $sideEnum === null;
        $activeSellTab = $sideEnum === \App\Enums\P2P\OrderSide::BUY;
        $advancedOpen = request()->filled('country') || request()->filled('sort');
    @endphp

    {{-- Header: Title + Tabs + New Listing (single row) --}}
    <div class="p2p-header-row">
        <div class="p2p-header-left">
            <ul class="nav nav-pills p2p-header-tabs">
                <li class="nav-item">
                    <a class="nav-link p2p-tab-buy {{ $activeBuyTab ? 'active' : '' }}" href="{{ route('user.p2p.offers.index', array_filter([
                        'side' => \App\Enums\P2P\OrderSide::SELL->value,
                        'amount_min' => request('amount_min'),
                        'amount_max' => request('amount_max'),
                        'currency' => request('currency'),
                        'payment_method_id' => request('payment_method_id'),
                        'country' => request('country'),
                        'sort' => request('sort'),
                    ])) }}">
                        <i class="fas fa-shopping-cart me-1"></i> @lang('Buy Crypto')
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link p2p-tab-sell {{ $activeSellTab ? 'active' : '' }}" href="{{ route('user.p2p.offers.index', array_filter([
                        'side' => \App\Enums\P2P\OrderSide::BUY->value,
                        'amount_min' => request('amount_min'),
                        'amount_max' => request('amount_max'),
                        'currency' => request('currency'),
                        'payment_method_id' => request('payment_method_id'),
                        'country' => request('country'),
                        'sort' => request('sort'),
                    ])) }}">
                        <i class="fas fa-tag me-1"></i> @lang('Sell Crypto')
                    </a>
                </li>
            </ul>
        </div>
    </div>

    {{-- Resume Banner --}}
    <div id="p2pResumeBanner" class="p2p-resume-banner d-none" role="status" aria-live="polite">
        <div class="p2p-resume-left">
            <span class="p2p-resume-icon-wrap">
                <i class="fas fa-clock-rotate-left p2p-resume-icon"></i>
            </span>
            <div class="p2p-resume-content">
                <div class="p2p-resume-title-row">
                    <span class="p2p-resume-title">@lang('Resume last trade')</span>
                    <span id="p2pResumeStatus" class="p2p-resume-status d-none"></span>
                </div>
                <div class="p2p-resume-order-row">
                    <strong id="p2pResumeOrderText">@lang('Order') #</strong>
                    <span id="p2pResumeUpdated" class="p2p-resume-updated d-none"></span>
                </div>
                <div id="p2pResumeMeta" class="p2p-resume-meta"></div>
            </div>
        </div>
        <a id="p2pResumeLink" href="#" class="btn btn-light-primary btn-sm p2p-btn-xs p2p-resume-link">
            <i class="fas fa-sync-alt me-1"></i> @lang('Resume Trade')
            <i class="fas fa-chevron-right ms-1"></i>
        </a>
    </div>

    {{-- Filters (collapsible on mobile) --}}
    <form method="GET" action="{{ route('user.p2p.offers.index') }}" class="p2p-filterbar">
        <input type="hidden" name="side" value="{{ $activeBuyTab ? \App\Enums\P2P\OrderSide::SELL->value : \App\Enums\P2P\OrderSide::BUY->value }}">
        <div class="p2p-filter-toggle collapsed" data-bs-toggle="collapse" data-bs-target="#p2pFilterCollapse" aria-expanded="false">
            <span><i class="fas fa-sliders-h me-2"></i>@lang('Filters')</span>
            <i class="fas fa-chevron-down p2p-filter-chevron"></i>
        </div>
        <div class="collapse" id="p2pFilterCollapse">
        <div class="p2p-filter-row p2p-filter-row--compact">
            <div class="p2p-filter-field">
                <label class="form-label visually-hidden">@lang('Currency')</label>
                <div class="p2p-filter-control">
                    <i class="fas fa-coins"></i>
                    <select name="currency" class="form-select form-select-sm">
                        <option value="">@lang('Select Currency')</option>
                        @foreach($currencyOptions as $code)
                            <option value="{{ $code }}" @selected(request('currency')===$code)>{{ $code }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="p2p-filter-field">
                <label class="form-label visually-hidden">@lang('Payment Method')</label>
                <div class="p2p-filter-control">
                    <i class="fas fa-credit-card"></i>
                    <select name="payment_method_id" class="form-select form-select-sm">
                        <option value="">@lang('Payment Method')</option>
                        @foreach($methods as $m)
                            <option value="{{ $m->id }}" @selected((string)request('payment_method_id') === (string)$m->id)>{{ $m->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="p2p-filter-field p2p-filter-field--range">
                <label class="form-label visually-hidden">@lang('Amount Range')</label>
                <div class="p2p-filter-control p2p-filter-control--range">
                    <i class="fas fa-money-bill-wave"></i>
                    <input type="text"
                           inputmode="decimal"
                           name="amount_min"
                           value="{{ request('amount_min') }}"
                           class="form-control form-control-sm p2p-range-input"
                           placeholder="@lang('Min')"
                           oninput="this.value = validateDouble(this.value)">
                    <span class="p2p-range-sep">–</span>
                    <input type="text"
                           inputmode="decimal"
                           name="amount_max"
                           value="{{ request('amount_max') }}"
                           class="form-control form-control-sm p2p-range-input p2p-range-input--max"
                           placeholder="@lang('Max')"
                           oninput="this.value = validateDouble(this.value)">
                </div>
            </div>
            <div class="p2p-filter-field p2p-filter-field--submit">
                <div class="p2p-filter-submit-group">
                    <button type="submit" class="btn btn-success btn-sm p2p-search-solid">
                        <i class="fas fa-search"></i> @lang('Search') <i class="fas fa-arrow-right ms-1"></i>
                    </button>
                    <button type="button"
                            class="p2p-advanced-icon {{ $advancedOpen ? 'active' : '' }}"
                            data-bs-toggle="collapse"
                            data-bs-target="#p2pAdvancedFilters"
                            aria-expanded="{{ $advancedOpen ? 'true' : 'false' }}"
                            aria-controls="p2pAdvancedFilters"
                            title="@lang('Advanced Filters')"
                            aria-label="@lang('Advanced Filters')">
                        <i class="fas fa-sliders-h"></i>
                        <span class="visually-hidden">@lang('Advanced Filters')</span>
                    </button>
                </div>
            </div>
        </div>

        <div class="collapse {{ $advancedOpen ? 'show' : '' }}" id="p2pAdvancedFilters">
            <div class="p2p-filter-row p2p-filter-row--advanced">
                <div class="p2p-filter-field">
                    <label class="form-label visually-hidden">@lang('Region')</label>
                    <div class="p2p-filter-control">
                        <i class="fas fa-globe"></i>
                        <select name="country" class="form-select form-select-sm">
                            <option value="">@lang('Region')</option>
                            @foreach($countryOptions as $country)
                                <option value="{{ $country }}" @selected(request('country')===$country)>{{ getCountryDisplayLabel($country) ?? $country }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="p2p-filter-field">
                    <label class="form-label visually-hidden">@lang('Price')</label>
                    <div class="p2p-filter-control">
                        <i class="fas fa-sort-amount-down-alt"></i>
                        <select name="sort" class="form-select form-select-sm">
                            <option value="">@lang('Best')</option>
                            <option value="price_asc" @selected(request('sort')==='price_asc')>@lang('Lowest')</option>
                            <option value="price_desc" @selected(request('sort')==='price_desc')>@lang('Highest')</option>
                        </select>
                    </div>
                </div>
                <div class="p2p-filter-field p2p-filter-field--submit">
                    <a href="{{ route('user.p2p.offers.index', array_filter(['side' => ($activeBuyTab ? \App\Enums\P2P\OrderSide::SELL->value : \App\Enums\P2P\OrderSide::BUY->value)])) }}" class="btn btn-light-primary btn-sm w-100">
                        <i class="fas fa-redo-alt me-1"></i> @lang('Reset')
                    </a>
                </div>
            </div>
        </div>
        </div>{{-- /collapse --}}
    </form>

    <div class="p2p-offers-panel p2p-offers-panel--marketplace">
        <div id="p2pOffersList"
             class="p2p-offers-panel__body"
             data-current-page="{{ $offers->currentPage() }}"
             data-last-page="{{ $offers->lastPage() }}"
             data-next-page="{{ $offers->hasMorePages() ? $offers->currentPage() + 1 : 0 }}">
            <div id="p2pOffersItems">
                @include('frontend.user.p2p.trade_ads.partials._trade_ad_rows', ['offers' => $offers, 'sellerStats' => $sellerStats])
            </div>
            <div id="p2pOffersLoader" class="p2p-offers-panel__status d-none">
                <i class="fas fa-spinner fa-spin me-1"></i> @lang('Loading more trade ads...')
            </div>
            <div id="p2pOffersEnd" class="p2p-offers-panel__status d-none">
                @lang('You have reached the end of the available trade ads.')
            </div>
        </div>
    </div>

    <div id="p2pOffersPagination" class="mt-3">
        {{ $offers->links() }}
    </div>

    </div>
</div>

@include('frontend.user.p2p.trade_ads.partials._marketplace_order_modal')

{{-- Marketplace Script: resume state, modal population, payment selection, and total calculations --}}
@push('scripts')
    @include('frontend.user.p2p.trade_ads.partials._marketplace_scripts')
@endpush
@endsection
