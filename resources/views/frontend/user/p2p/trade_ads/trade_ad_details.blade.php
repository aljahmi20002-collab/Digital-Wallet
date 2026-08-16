@extends('frontend.layouts.user.index')
@section('title', __('Trade Ad #').$offer->id)
@section('content')
{{-- Trade Ad Details: trade terms, seller stats, reviews, and a quick trade-start modal --}}
<div class="single-form-card p2p-ui">
    <div class="card-main p2p-card-main">
        @php
            $promotion = $offer->promotion;
            $isSponsored = $promotion
                && $promotion->status === \App\Enums\P2P\PromotionStatus::ACTIVE
                && $promotion->ends_at
                && $promotion->ends_at->isFuture();
            $promotionFeatures = (array) ($promotion?->package?->features ?? []);
            $hasFeaturedBadge = $isSponsored
                && (!empty($promotionFeatures['featured_badge']) || !empty($promotionFeatures['verified_badge']));
            $currency = $offer->wallet->currency->code;
            $isBuy = $offer->side === \App\Enums\P2P\OrderSide::SELL;
            $btnLabel = ($isBuy ? __('Buy') : __('Sell')) . ' ' . $currency;
            $btnIcon = $isBuy ? 'fa-shopping-cart' : 'fa-tag';
            $actionModifier = $isBuy ? 'p2p-offer-action-btn--buy' : 'p2p-offer-action-btn--sell';
            $decimals = (int) setting('site_decimal', 2);
            $fiatCurrency = (string) setting('site_currency', '');
            $fiatCurrency = $fiatCurrency !== '' ? $fiatCurrency : $currency;
            $userName = $offer->user?->name ?? __('User');
            $initials = strtoupper(substr((string) $offer->user?->first_name, 0, 1) . substr((string) $offer->user?->last_name, 0, 1));
            $initials = $initials !== '' ? $initials : strtoupper(substr((string) $userName, 0, 1));
            $isVerified = $offer->user ? $offer->user->isKycVerified() : false;
            $availableText = $offer->max_amount !== null ? number_format((float) $offer->max_amount, $decimals) : __('∞');
            $limitText = number_format((float) $offer->min_amount, $decimals) . ' - ' . ($offer->max_amount !== null ? number_format((float) $offer->max_amount, $decimals) : __('∞'));
            $pmJson = $offer->paymentMethods->filter(fn ($pm) => !empty($pm?->name))->map(function ($pm) {
                return [
                    'id' => (int) $pm->id,
                    'name' => (string) $pm->name,
                    'logo' => !empty($pm->logo) ? asset('storage/' . $pm->logo) : null,
                    'instructions' => (string) ($pm->instructions ?? ''),
                ];
            })->values();
            $advertiserUrl = route('user.p2p.advertisers.show', $offer->user_id);
            $statusDescription = match ($offer->status) {
                \App\Enums\P2P\OfferStatus::ACTIVE => __('Visible in the marketplace and ready for new trade requests.'),
                \App\Enums\P2P\OfferStatus::PAUSED => __('Temporarily hidden until you resume it from your ad controls.'),
                default => __('Unavailable for promotion or status changes until restored by the system.'),
            };
            $ownerCanEdit = $isOwner && $offer->status !== \App\Enums\P2P\OfferStatus::DISABLED && $openOrders === 0;
            $ownerCanDelete = $isOwner && $openOrders === 0;
            $paymentMethods = $offer->paymentMethods->filter(fn ($pm) => !empty($pm?->name))->values();
            $promotionPackage = trim((string) ($promotion?->package?->name ?? ''));
        @endphp

        <x-user-feature-header
            :title="__('Trade Ad #').$offer->id"
            :subtitle="$statusDescription"
            icon="fas fa-store"
        >
            @if($isOwner)
                @if($ownerCanEdit)
                    <a href="{{ route('user.p2p.offers.edit', $offer) }}" class="btn btn-light-primary btn-sm p2p-btn-xs">
                        <i class="fas fa-sliders-h"></i> @lang('Manage Ad')
                    </a>
                @endif
                @if($offer->status !== \App\Enums\P2P\OfferStatus::DISABLED)
                    <a href="{{ route('user.p2p.offers.promotion.show', $offer) }}" class="btn btn-light-warning btn-sm p2p-btn-xs">
                        <i class="fas fa-bullhorn"></i> {{ $isSponsored ? __('Extend Promotion') : __('Promote Ad') }}
                    </a>
                    <form method="POST" action="{{ route('user.p2p.offers.toggle', $offer) }}" class="d-inline-flex">
                        @csrf
                        <button type="submit" class="btn btn-light-primary btn-sm p2p-btn-xs" @disabled(! $canManageLifecycle) title="{{ $openOrders > 0 ? __('Open trades must be finished before changing this ad status.') : '' }}">
                            @if($offer->status === \App\Enums\P2P\OfferStatus::ACTIVE)
                                <i class="fas fa-pause"></i> {{ __('Pause') }}
                            @else
                                <i class="fas fa-play"></i> {{ __('Resume') }}
                            @endif
                        </button>
                    </form>
                @endif
                <form method="POST" action="{{ route('user.p2p.offers.destroy', $offer) }}" class="d-inline-flex" onsubmit="return confirm(@json(__('Are you sure you want to delete this trade ad?')))">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-light-danger btn-sm p2p-btn-xs" @disabled(! $ownerCanDelete) title="{{ $openOrders > 0 ? __('Open trades must be finished before deleting this ad.') : '' }}">
                        <i class="fas fa-trash"></i> @lang('Delete')
                    </button>
                </form>
                <a href="{{ route('user.p2p.offers.my') }}" class="btn btn-light-primary btn-sm p2p-btn-xs">
                    <i class="fas fa-arrow-left"></i> @lang('Back to My Ads')
                </a>
            @else
                <a href="{{ route('user.p2p.offers.index', ['side' => $offer->side->value]) }}" class="btn btn-light-primary btn-sm p2p-btn-xs">
                    <i class="fas fa-arrow-left"></i> @lang('Back to Marketplace')
                </a>
            @endif
        </x-user-feature-header>

        <div class="p2p-ad-details-badges mb-3">
            <span class="{{ $offer->side->badgeClass() }}">{{ $offer->side->label() }}</span>
            <span class="{{ $offer->status->badgeClass() }}">{{ $offer->status->label() }}</span>
            @if($isSponsored)
                <span class="badge rounded-pill bg-warning-subtle text-warning border border-warning-subtle"><i class="fas fa-bullhorn me-1"></i>@lang('Promoted')</span>
            @endif
            @if($hasFeaturedBadge)
                <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle"><i class="fas fa-star me-1"></i>@lang('Featured')</span>
            @endif
            @if($openOrders > 0)
                <span class="badge rounded-pill bg-info-subtle text-info border border-info-subtle"><i class="fas fa-sync-alt me-1"></i>{{ number_format($openOrders) }} @lang('Open')</span>
            @endif
        </div>

        <div class="p2p-ad-details-summary">
            <div class="p2p-ad-summary-card">
                <span class="p2p-ad-summary-card__label">@lang('Price')</span>
                <strong class="p2p-ad-summary-card__value">{{ number_format((float) $offer->price, $decimals) }} {{ $fiatCurrency }}</strong>
                <span class="p2p-ad-summary-card__hint">@lang('Per') {{ $currency }}</span>
            </div>
            <div class="p2p-ad-summary-card">
                <span class="p2p-ad-summary-card__label">@lang('Trade Limit')</span>
                <strong class="p2p-ad-summary-card__value">{{ $limitText }}</strong>
                <span class="p2p-ad-summary-card__hint">@lang('Available') {{ $availableText }} {{ $currency }}</span>
            </div>
            <div class="p2p-ad-summary-card">
                <span class="p2p-ad-summary-card__label">@lang('Payment Window')</span>
                <strong class="p2p-ad-summary-card__value">{{ (int) ($offer->payment_window_minutes ?? 0) }} @lang('minutes')</strong>
                <span class="p2p-ad-summary-card__hint">@lang('Buyer payment deadline')</span>
            </div>
            <div class="p2p-ad-summary-card">
                <span class="p2p-ad-summary-card__label">@lang('Order Activity')</span>
                <strong class="p2p-ad-summary-card__value">{{ number_format($totalOrders) }} @lang('total')</strong>
                <span class="p2p-ad-summary-card__hint">{{ number_format($completedOrders) }} @lang('completed') · {{ number_format($openOrders) }} @lang('open')</span>
            </div>
            <div class="p2p-ad-summary-card">
                <span class="p2p-ad-summary-card__label">@lang('Completion')</span>
                <strong class="p2p-ad-summary-card__value">{{ number_format($completionRate, 1) }}%</strong>
                <span class="p2p-ad-summary-card__hint">@lang('Based on closed orders')</span>
            </div>
            <div class="p2p-ad-summary-card">
                <span class="p2p-ad-summary-card__label">@lang('Disputes')</span>
                <strong class="p2p-ad-summary-card__value">{{ number_format($disputedOrders) }}</strong>
                <span class="p2p-ad-summary-card__hint">{{ number_format($positiveCnt) }} @lang('positive') · {{ number_format($negativeCnt) }} @lang('negative')</span>
            </div>
        </div>

        @if($isOwner && $openOrders > 0)
            <div class="p2p-ad-inline-alert p2p-ad-inline-alert--warning">
                <i class="fas fa-circle-info"></i>
                <span>@lang('This ad currently has open trades. Editing, pausing, and deleting remain restricted until those orders are completed, cancelled, expired, or resolved.') </span>
            </div>
        @endif

        <div class="row g-3">
            <div class="col-xl-8">
                <div class="card p2p-ad-panel h-100">
                    <div class="card-header fw-bold">@lang('Ad Setup')</div>
                    <div class="card-body">
                        <div class="p2p-ad-info-grid">
                            <div class="p2p-ad-info-item">
                                <span class="p2p-ad-info-item__label">@lang('Currency')</span>
                                <strong class="p2p-ad-info-item__value">{{ $currency }}</strong>
                            </div>
                            <div class="p2p-ad-info-item">
                                <span class="p2p-ad-info-item__label">@lang('Updated')</span>
                                <strong class="p2p-ad-info-item__value">{{ $offer->updated_at->format('Y-m-d H:i') }}</strong>
                            </div>
                            <div class="p2p-ad-info-item">
                                <span class="p2p-ad-info-item__label">@lang('Minimum')</span>
                                <strong class="p2p-ad-info-item__value">{{ number_format((float) $offer->min_amount, $decimals) }}</strong>
                            </div>
                            <div class="p2p-ad-info-item">
                                <span class="p2p-ad-info-item__label">@lang('Maximum')</span>
                                <strong class="p2p-ad-info-item__value">{{ $offer->max_amount !== null ? number_format((float) $offer->max_amount, $decimals) : __('∞') }}</strong>
                            </div>
                        </div>

                        <div class="p2p-ad-section-block">
                            <span class="p2p-ad-section-block__label">@lang('Payment Methods')</span>
                            @if($paymentMethods->isNotEmpty())
                                <div class="p2p-ad-payment-list">
                                    @foreach($paymentMethods as $pm)
                                        <div class="p2p-ad-payment-item">
                                            @if(!empty($pm->logo))
                                                <img src="{{ asset('storage/' . $pm->logo) }}" alt="{{ $pm->name }}" loading="lazy">
                                            @else
                                                <span class="p2p-ad-payment-item__fallback">{{ strtoupper(substr((string) $pm->name, 0, 1)) }}</span>
                                            @endif
                                            <div>
                                                <strong>{{ $pm->name }}</strong>
                                                @if(!empty($pm->instructions))
                                                    <span>{{ $pm->instructions }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="p2p-ad-empty">@lang('No payment methods linked to this ad.')</div>
                            @endif
                        </div>

                        <div class="p2p-ad-section-block">
                            <span class="p2p-ad-section-block__label">@lang('Terms & Instructions')</span>
                            @if(filled($offer->terms_text))
                                <div class="p2p-ad-terms-box">{{ $offer->terms_text }}</div>
                            @else
                                <div class="p2p-ad-empty">@lang('No additional trade instructions provided.')</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="card p2p-ad-panel mb-3">
                    <div class="card-header fw-bold">@lang('Promotion & Health')</div>
                    <div class="card-body d-flex flex-column gap-3">
                        <div class="p2p-ad-status-box">
                            <strong>{{ $promotionPackage !== '' ? $promotionPackage : ($isSponsored ? __('Promotion active') : __('Standard placement')) }}</strong>
                            @if($isSponsored && $promotion?->ends_at)
                                <span>@lang('Runs until') {{ $promotion->ends_at->format('Y-m-d H:i') }}</span>
                            @elseif($isOwner && $offer->status !== \App\Enums\P2P\OfferStatus::DISABLED)
                                <span>@lang('Promote this ad to improve visibility and placement priority.') </span>
                            @else
                                <span>@lang('This ad is currently not using promoted placement.') </span>
                            @endif
                        </div>

                        <div class="p2p-ad-health-list">
                            <div class="p2p-ad-health-item">
                                <span>@lang('Open Orders')</span>
                                <strong>{{ number_format($openOrders) }}</strong>
                            </div>
                            <div class="p2p-ad-health-item">
                                <span>@lang('Completed Orders')</span>
                                <strong>{{ number_format($completedOrders) }}</strong>
                            </div>
                            <div class="p2p-ad-health-item">
                                <span>@lang('Disputed Orders')</span>
                                <strong>{{ number_format($disputedOrders) }}</strong>
                            </div>
                            <div class="p2p-ad-health-item">
                                <span>@lang('Feedback Signal')</span>
                                <strong>{{ number_format($positiveCnt) }}/{{ number_format($negativeCnt) }}</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card p2p-ad-panel h-100">
                    <div class="card-header fw-bold">{{ $isOwner ? __('Ad Actions') : __('Start Trade') }}</div>
                    <div class="card-body">
                        @if($isOwner)
                            <div class="p2p-ad-empty">
                                @lang('This detailed page now holds all operational data for your ad. Use the actions above to edit, promote, pause, resume, or remove it when no open trades exist.')
                            </div>
                        @elseif($offer->side === \App\Enums\P2P\OrderSide::SELL)
                            <button type="button" class="btn btn-sm p2p-offer-action-btn {{ $actionModifier }} p2p-btn-xs w-100" data-bs-toggle="modal"
                                    data-bs-target="#p2pOrderModal"
                                    data-offer-id="{{ $offer->id }}"
                                    data-side="{{ $offer->side->value }}"
                                    data-min="{{ $offer->min_amount }}"
                                    data-max="{{ $offer->max_amount ?? '' }}"
                                    data-price="{{ $offer->price }}"
                                    data-currency="{{ $currency }}"
                                    data-fiat-currency="{{ $fiatCurrency }}"
                                    data-action="{{ $isBuy ? 'buy' : 'sell' }}"
                                    data-action-label="{{ $btnLabel }}"
                                    data-available-text="{{ $availableText }}"
                                    data-limit-text="{{ $limitText }}"
                                    data-user-name="{{ $userName }}"
                                    data-user-initials="{{ $initials }}"
                                    data-user-avatar="{{ $offer->user && !empty($offer->user->avatar) ? asset($offer->user->avatar_alt) : '' }}"
                                    data-user-verified="{{ $isVerified ? '1' : '0' }}"
                                    data-completion-rate="{{ number_format($completionRate, 1) }}"
                                    data-total-trades="{{ $totalOrders }}"
                                    data-payment-methods="{{ e($pmJson->toJson()) }}"
                                    data-payment-window="{{ (int) ($offer->payment_window_minutes ?? 0) }}"
                                    data-advertiser-url="{{ $advertiserUrl }}"
                                    data-terms="{{ e(json_encode($offer->terms_text)) }}">
                                <i class="fas {{ $btnIcon }} me-1"></i> {{ $btnLabel }}
                            </button>
                        @else
                            <button type="button" class="btn btn-sm p2p-offer-action-btn {{ $actionModifier }} p2p-btn-xs w-100" data-bs-toggle="modal"
                                    data-bs-target="#p2pOrderModal"
                                    data-offer-id="{{ $offer->id }}"
                                    data-side="{{ $offer->side->value }}"
                                    data-min="{{ $offer->min_amount }}"
                                    data-max="{{ $offer->max_amount ?? '' }}"
                                    data-price="{{ $offer->price }}"
                                    data-currency="{{ $currency }}"
                                    data-fiat-currency="{{ $fiatCurrency }}"
                                    data-action="{{ $isBuy ? 'buy' : 'sell' }}"
                                    data-action-label="{{ $btnLabel }}"
                                    data-available-text="{{ $availableText }}"
                                    data-limit-text="{{ $limitText }}"
                                    data-user-name="{{ $userName }}"
                                    data-user-initials="{{ $initials }}"
                                    data-user-avatar="{{ $offer->user && !empty($offer->user->avatar) ? asset($offer->user->avatar_alt) : '' }}"
                                    data-user-verified="{{ $isVerified ? '1' : '0' }}"
                                    data-completion-rate="{{ number_format($completionRate, 1) }}"
                                    data-total-trades="{{ $totalOrders }}"
                                    data-payment-methods="{{ e($pmJson->toJson()) }}"
                                    data-payment-window="{{ (int) ($offer->payment_window_minutes ?? 0) }}"
                                    data-advertiser-url="{{ $advertiserUrl }}"
                                    data-terms="{{ e(json_encode($offer->terms_text)) }}">
                                <i class="fas {{ $btnIcon }} me-1"></i> {{ $btnLabel }}
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="card mt-3 p2p-ad-panel">
            <div class="card-header fw-bold">@lang('Recent Reviews')</div>
            <div class="card-body">
                @forelse($feedbacks as $fb)
                    <div class="p2p-ad-review-item">
                        <div>
                            <div class="fw-bold">{{ $fb->user->name ?? __('User') }}</div>
                            <div class="small text-muted">{{ $fb->created_at->diffForHumans() }}</div>
                            <div class="p2p-ad-review-item__comment">{{ $fb->comment }}</div>
                        </div>
                        <div class="ms-3">
                            <span class="badge {{ $fb->rating >= 4 ? 'bg-success' : ($fb->rating <= 2 ? 'bg-danger' : 'bg-secondary') }}">{{ $fb->rating }}/5</span>
                        </div>
                    </div>
                @empty
                    <div class="p2p-ad-empty">@lang('No reviews yet')</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

@push('scripts')
    @include('frontend.user.p2p.trade_ads.partials._marketplace_scripts')
@endpush
@include('frontend.user.p2p.trade_ads.partials._marketplace_order_modal')
@endsection
