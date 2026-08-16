@forelse($offers as $offer)
    @php
        $compactSeller = $compactSeller ?? false;
        $currency = $offer->wallet->currency->code;
        $assetKey = strtolower($currency);
        $currencyFlag = $offer->wallet->currency->flag ?? null;
        $isSelf = (int) $offer->user_id === (int) auth()->id();
        $user = $offer->user;
        $userName = $offer->user?->name ?? __('User');
        $initials = strtoupper(substr((string) $offer->user?->first_name, 0, 1) . substr((string) $offer->user?->last_name, 0, 1));
        $initials = $initials !== '' ? $initials : strtoupper(substr((string) $userName, 0, 1));
        $isBuy = $offer->side === \App\Enums\P2P\OrderSide::SELL;
        $btnLabel = ($isBuy ? __('Buy') : __('Sell')) . ' ' . $currency;
        $btnIcon = $isBuy ? 'fa-shopping-cart' : 'fa-tag';
        $actionModifier = $isBuy ? 'p2p-offer-action-btn--buy' : 'p2p-offer-action-btn--sell';
        $assetModifier = $isBuy ? match ($assetKey) {
            'btc' => 'p2p-offer-action-btn--asset-btc',
            'eth' => 'p2p-offer-action-btn--asset-eth',
            default => '',
        } : '';
        $decimals = (int) setting('site_decimal', 2);
        $stats = $sellerStats[(int) $offer->user_id] ?? null;
        $completedOrders = (int) ($stats['completed_orders'] ?? 0);
        $totalOrders = (int) ($stats['total_orders'] ?? 0);
        $completionRate = (float) ($stats['completion_rate'] ?? 0);
        $avgRating = $stats['avg_rating'] ?? null;
        $positiveFeedback = (int) ($stats['positive_feedback'] ?? 0);
        $isVerified = $user ? $user->isKycVerified() : false;
        $isTrusted = $totalOrders >= 10 && $completionRate >= 95 && (($avgRating !== null && $avgRating >= 4.5) || $positiveFeedback >= 10);
        $isHighVolume = $completedOrders >= 50;
        $fiatCurrency = (string) setting('site_currency', '');
        $fiatCurrency = $fiatCurrency !== '' ? $fiatCurrency : $currency;
        $availableText = $offer->max_amount !== null
            ? number_format((float) $offer->max_amount, $decimals)
            : __('Unlimited');
        $limitText = number_format((float) $offer->min_amount, $decimals) . ' - ' . ($offer->max_amount !== null ? number_format((float) $offer->max_amount, $decimals) : __('Unlimited'));
        $hasActiveOrders = (int) ($offer->active_orders_count ?? 0) > 0;
        $selfActionLabel = __('Manage Ad');

        $promotion = $offer->promotion;
        $isSponsored = $promotion
            && $promotion->status === \App\Enums\P2P\PromotionStatus::ACTIVE
            && $promotion->ends_at
            && $promotion->ends_at->isFuture();
        $promotionFeatures = (array) ($promotion?->package?->features ?? []);
        $hasHighlightedCard = $isSponsored && !empty($promotionFeatures['highlighted_card']);
        $hasFeaturedBadge = $isSponsored
            && (!empty($promotionFeatures['featured_badge']) || !empty($promotionFeatures['verified_badge']));
        $accentColor = strtoupper(trim((string) ($promotion?->package?->accent_color ?? '')));
        $hasAccentColor = in_array($accentColor, ['GOLD', 'BLUE', 'RED'], true);
        $accentCardClass = $hasAccentColor ? match ($accentColor) {
            'GOLD' => 'p2p-offer-card--accent-gold',
            'RED' => 'p2p-offer-card--accent-red',
            default => 'p2p-offer-card--accent-blue',
        } : '';
        $hasSoftAccentBorder = $isSponsored && !$hasHighlightedCard && $hasAccentColor;
        $cardClass = implode(' ', array_filter([
            'p2p-offer-card',
            $compactSeller ? 'p2p-offer-card--profile' : null,
            $isSponsored ? 'p2p-offer-card--sponsored' : null,
            $hasHighlightedCard ? 'p2p-offer-card--highlighted' : null,
            $hasSoftAccentBorder ? 'p2p-offer-card--accent-soft' : null,
            $accentCardClass,
        ]));
    @endphp

    <div class="{{ $cardClass }}">
        <div class="p2p-offer-card__head">
            <div class="p2p-offer-card__seller {{ $compactSeller ? 'p2p-offer-card__seller--profile' : '' }}">
                @if($compactSeller)
                    <div class="p2p-offer-profile-context">
                        <span class="{{ $offer->side->badgeClass() }} p2p-offer-profile-context__side">{{ $offer->side->label() }}</span>
                        <div class="p2p-offer-profile-context__copy">
                            <strong class="p2p-offer-profile-context__title">{{ $btnLabel }}</strong>
                            <span class="p2p-offer-profile-context__meta">{{ (int) ($offer->payment_window_minutes ?? 0) }} @lang('min')</span>
                        </div>
                    </div>
                @else
                    <div class="p2p-advertiser">
                        @if($user && ! empty($user->avatar))
                            <div class="p2p-advertiser__avatar p2p-advertiser__avatar--img">
                                <img src="{{ asset($user->avatar_alt) }}" alt="{{ $userName }}" loading="lazy">
                            </div>
                        @else
                            <div class="p2p-advertiser__avatar">{{ $initials }}</div>
                        @endif
                        <div class="p2p-advertiser__meta">
                            <div class="p2p-advertiser__name-row">
                                <div class="p2p-advertiser__name">
                                    <a class="p2p-advertiser__name-link" href="{{ route('user.p2p.advertisers.show', $offer->user_id) }}">{{ $userName }}</a>
                                </div>
                                @if($isVerified)
                                    <span class="p2p-seller-badge p2p-seller-badge--verified p2p-seller-badge--verified-inline"><i class="fas fa-check-circle"></i>@lang('Verified')</span>
                                @endif
                            </div>

                            <div class="p2p-advertiser__stats">
                                {{ number_format($completionRate, 1) }}% @lang('completion')
                                <span class="p2p-dot">|</span>
                                {{ number_format($totalOrders) }} @lang('trades')
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <div class="p2p-offer-card__aside">
                <div class="p2p-offer-card__meta-row">
                    <span class="p2p-available-badge {{ $compactSeller ? 'p2p-available-badge--compact' : '' }}">
                        @unless($compactSeller)
                            <span class="p2p-available-badge__label">@lang('Available'):</span>
                        @endunless
                        <span class="p2p-available-badge__value">{{ $availableText }} {{ $currency }}</span>
                    </span>
                </div>

                <div class="p2p-offer-card__badges p2p-offer-card__promo-row">
                    @if(! $compactSeller && $isTrusted)
                        <span class="p2p-seller-badge p2p-seller-badge--trusted p2p-seller-badge--compact"><i class="fas fa-shield-alt"></i>@lang('Trusted')</span>
                    @endif
                    @if(! $compactSeller && $isHighVolume)
                        <span class="p2p-seller-badge p2p-seller-badge--volume p2p-seller-badge--compact"><i class="fas fa-bolt"></i>@lang('High Volume')</span>
                    @endif
                    @if($isSponsored)
                        <span class="p2p-offer-chip p2p-offer-chip--promoted"><i class="fas fa-bullhorn"></i>@lang('Promoted')</span>
                        @if($hasFeaturedBadge)
                            <span class="p2p-offer-chip p2p-offer-chip--featured"><i class="fas fa-star"></i>@lang('Featured')</span>
                        @endif
                    @endif
                </div>
            </div>
        </div>

        @php
            $pmMethods = $offer->paymentMethods->filter(fn ($m) => !empty($m?->name))->values();
            $pmNames = $pmMethods->pluck('name')->filter()->values();
            $pmAll = $pmNames->implode(', ');
            $pmJson = $pmMethods->map(function ($m) {
                return [
                    'id' => (int) $m->id,
                    'name' => (string) $m->name,
                    'logo' => !empty($m->logo) ? asset('storage/'.$m->logo) : null,
                    'instructions' => (string) ($m->instructions ?? ''),
                ];
            })->values();
            $advertiserUrl = route('user.p2p.advertisers.show', $offer->user_id);
            $paymentPreviewCount = $compactSeller ? 2 : 3;
        @endphp

        <div class="p2p-offer-body">
            <div class="p2p-offer-body__left">
                <div class="p2p-offer-price-line">
                    <span class="p2p-offer-price-base">
                        @if(!empty($currencyFlag))
                            <img class="p2p-asset-logo" src="{{ asset($currencyFlag) }}" alt="{{ $currency }}" loading="lazy">
                        @else
                            <span class="p2p-asset-icon p2p-asset-icon--{{ $assetKey }}">{{ strtoupper(substr($currency, 0, 1)) }}</span>
                        @endif
                        {{ $currency }}
                    </span>
                    <span class="p2p-offer-price-arrow">-&gt;</span>
                    <span class="p2p-offer-price-number">{{ number_format((float) $offer->price, $decimals) }}</span>
                    <span class="p2p-offer-price-fiat">{{ $fiatCurrency }}</span>
                    <span class="p2p-offer-price-per">@lang('per') {{ $currency }}</span>
                </div>

                @if($compactSeller)
                    <div class="p2p-offer-compact-meta">
                        <div class="p2p-offer-compact-meta__item">
                            <span class="p2p-offer-compact-meta__label">@lang('Range')</span>
                            <span class="p2p-offer-compact-meta__value">{{ $limitText }} {{ $currency }}</span>
                        </div>

                        <div class="p2p-offer-compact-meta__item">
                            <span class="p2p-offer-compact-meta__label">@lang('Methods')</span>
                            @if($pmNames->isNotEmpty())
                                <span class="p2p-payment-pills" tabindex="0" role="button" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="top" data-bs-container="body" data-bs-content="{{ $pmAll }}">
                                    @foreach($pmMethods->take($paymentPreviewCount) as $pm)
                                        <span class="p2p-payment-pill">
                                            @if(! empty($pm->logo))
                                                <img class="p2p-payment-pill__logo" src="{{ asset('storage/'.$pm->logo) }}" alt="{{ $pm->name }}" loading="lazy">
                                            @else
                                                <span class="p2p-payment-pill__fallback" aria-hidden="true">{{ strtoupper(substr((string) $pm->name, 0, 1)) }}</span>
                                            @endif
                                            <span class="p2p-payment-pill__text">{{ $pm->name }}</span>
                                        </span>
                                    @endforeach
                                    @if($pmNames->count() > $paymentPreviewCount)
                                        <span class="p2p-payment-pill">+{{ $pmNames->count() - $paymentPreviewCount }}</span>
                                    @endif
                                </span>
                            @else
                                <span class="text-muted">@lang('No payment methods')</span>
                            @endif
                        </div>
                    </div>
                @else
                    <div class="p2p-offer-limit">
                        <span class="p2p-offer-limit__label">@lang('Limit'):</span>
                        <span class="p2p-offer-limit__value">{{ $limitText }} {{ $currency }}</span>
                    </div>

                    <div class="p2p-offer-payment-row">
                        <span class="p2p-offer-payment__label">@lang('Payment'):</span>
                        @if($pmNames->isNotEmpty())
                            <span class="p2p-payment-pills" tabindex="0" role="button" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="top" data-bs-container="body" data-bs-content="{{ $pmAll }}">
                                @foreach($pmMethods->take($paymentPreviewCount) as $pm)
                                    <span class="p2p-payment-pill">
                                        @if(! empty($pm->logo))
                                            <img class="p2p-payment-pill__logo" src="{{ asset('storage/'.$pm->logo) }}" alt="{{ $pm->name }}" loading="lazy">
                                        @else
                                            <span class="p2p-payment-pill__fallback" aria-hidden="true">{{ strtoupper(substr((string) $pm->name, 0, 1)) }}</span>
                                        @endif
                                        <span class="p2p-payment-pill__text">{{ $pm->name }}</span>
                                    </span>
                                @endforeach
                                @if($pmNames->count() > $paymentPreviewCount)
                                    <span class="p2p-payment-pill">+{{ $pmNames->count() - $paymentPreviewCount }}</span>
                                @endif
                            </span>
                        @else
                            <span class="text-muted">@lang('No payment methods')</span>
                        @endif
                    </div>
                @endif
            </div>

            <div class="p2p-offer-body__right">
                <div class="p2p-offer-cta">
                    @if($isSelf)
                        <a href="{{ route('user.p2p.offers.edit', $offer) }}" class="btn btn-sm p2p-offer-action-btn p2p-offer-action-btn--lg p2p-offer-action-btn--self" title="@lang('Manage your trade ad')">
                            <i class="fas fa-sliders-h"></i>
                            <span class="p2p-offer-action-btn__text">{{ $selfActionLabel }}</span>
                        </a>
                        @if($hasActiveOrders)
                            <div class="p2p-operation-time p2p-operation-time--warning">
                                <i class="fas fa-circle-info"></i>
                                @lang('Active order running')
                            </div>
                        @elseif(! $compactSeller)
                            <div class="p2p-operation-time">
                                <i class="fas fa-user-gear"></i>
                                @lang('Manage tools')
                            </div>
                        @endif
                    @else
                        <button type="button"
                                class="btn btn-sm p2p-offer-action-btn p2p-offer-action-btn--lg {{ $actionModifier }} {{ $assetModifier }}"
                                data-bs-toggle="modal"
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
                                data-user-avatar="{{ $user && ! empty($user->avatar) ? asset($user->avatar_alt) : '' }}"
                                data-user-verified="{{ $isVerified ? '1' : '0' }}"
                                data-completion-rate="{{ number_format($completionRate, 1) }}"
                                data-total-trades="{{ $totalOrders }}"
                                data-payment-methods="{{ e($pmJson->toJson()) }}"
                                data-payment-window="{{ (int) ($offer->payment_window_minutes ?? 0) }}"
                                data-advertiser-url="{{ $advertiserUrl }}"
                                data-terms="{{ e(json_encode($offer->terms_text)) }}">
                            <i class="fas {{ $btnIcon }}"></i>
                            <span class="p2p-offer-action-btn__text">{{ $btnLabel }}</span>
                        </button>
                    @endif

                    @if(! $compactSeller)
                        <div class="p2p-operation-time">
                            <i class="far fa-clock"></i>
                            @lang('Est. time'):
                            ~{{ (int) ($offer->payment_window_minutes ?? 0) }} @lang('min')
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@empty
    <x-user-not-found
        :title="__('No trade ads found')"
        :message="__('Trade ads matching the current filters will appear here.')"
        :eyebrow="__('P2P marketplace')"
        icon="fa-store"
        class="p2p-empty"
    />
@endforelse
