@extends('frontend.layouts.user.index')

@section('title', __('My Subscription'))

@push('styles')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('frontend/css/my-subscription.css?v=' . config('app.version')) }}">
@endpush

@section('content')
@php
    use App\Enums\BillingCycle;
    $currencyCode = $subscription?->currency_code ?? siteCurrency('code');
@endphp

<div class="row">
    <div class="col-12">
        <div class="single-form-card">
            <div class="card-main">

                @if(! $subscription)
                    {{-- ─────────── Empty state ─────────── --}}
                    <x-user-not-found
                        :eyebrow="__('No plan selected')"
                        :title="__('No active subscription')"
                        :message="__('Choose a plan to unlock premium features, higher limits, and full account access.')"
                        icon="fa-layer-group"
                        :action-url="route('user.subscription.plans')"
                        :action-label="__('Browse Plans')"
                        action-icon="fa-bolt"
                        :secure-label="__('Wallet checkout')"
                    />
                @else
                    @php
                        $plan         = $subscription->plan;
                        $billingCycle = $subscription->billing_cycle;
                        $billingKey   = $billingCycle?->value;
                        $isLifetime   = (bool) $billingCycle?->isLifetime();

                        $prices = collect($plan?->prices ?? [])->keyBy(
                            fn ($p) => $p->billing_cycle instanceof BillingCycle ? $p->billing_cycle->value : (string) $p->billing_cycle
                        );

                        $currentPrice = $billingKey ? (float) ($prices->get($billingKey)?->price ?? 0) : 0;
                        $monthlyPrice = (float) ($prices->get('monthly')?->price ?? 0);

                        $features     = collect($plan?->features ?? []);
                        $featureCount = $features->count();
                        $enabledCount = $features->filter(fn ($f) => ! $f->isToggle() || $f->isEnabled())->count();
                        $utilization  = $featureCount > 0 ? (int) round(($enabledCount / $featureCount) * 100) : 0;

                        $transactions  = $subscription->transactions;
                        $statusKey     = $subscription->status->value;
                        $isFree        = $currentPrice <= 0 && $monthlyPrice <= 0;
                        $isCancelled   = (bool) $subscription->cancelled_at;
                        $isTrial       = $statusKey === 'trial';
                        $startedAt     = $subscription->started_at;
                        $periodEnd     = $subscription->current_period_end;
                        $trialEndsAt   = $subscription->trial_ends_at;
                        $daysRemaining = $subscription->daysRemaining();
                        $memberDays    = $startedAt ? max(0, $startedAt->diffInDays(now())) : 0;

                        $statusPillClass = match($statusKey) {
                            'active' => 'ms-pill--active',
                            'trial'  => 'ms-pill--trial',
                            'grace'  => 'ms-pill--grace',
                            default  => 'ms-pill--neutral',
                        };
                        $statusLabel = strtoupper($subscription->status->label());

                        // Action visibility — derived once, used everywhere
                        $canRenew  = ! $isTrial && ! $isFree && ! $isLifetime && $subscription->isActive() && ! $isCancelled;
                        $canCancel = ! $isFree && $subscription->isActive() && ! $isCancelled;

                        // Hero meta — "next renewal" sub copy
                        $nextRenewalSub = match (true) {
                            $isLifetime              => __('Lifetime — no renewal'),
                            $isFree                  => __('Free plan — no renewal'),
                            $isTrial && $trialEndsAt => __('Trial ends in :n days', ['n' => max(0, (int) now()->diffInDays($trialEndsAt, false))]),
                            $isCancelled             => __('Cancelled — access until end of period'),
                            ! is_null($daysRemaining) => __('In :n days', ['n' => $daysRemaining]),
                            default                  => __('Pending'),
                        };
                        $nextRenewalDate = match (true) {
                            $isLifetime || $isFree   => '—',
                            $isTrial && $trialEndsAt => $trialEndsAt->format('M d'),
                            $periodEnd               => $periodEnd->format('M d'),
                            default                  => '—',
                        };

                        // Upgrade plan cost
                        $upgradeMonthlyPrice = $upgradePlan?->prices->firstWhere('billing_cycle', BillingCycle::Monthly)?->price ?? 0;
                        $upgradeFeatureDelta = $upgradePlan
                            ? max(0, $upgradePlan->features->count() - $featureCount)
                            : 0;
                    @endphp

                    <div class="ms-page">

                        {{-- ═══════════ HERO ═══════════ --}}
                        <section class="ms-hero">
                            <div class="ms-hero__dec">{{ strtoupper($plan->name) }}</div>
                            <div class="ms-hero__grid">
                                <div>
                                    <div class="ms-pill {{ $statusPillClass }}">
                                        <span class="ms-pill__dot"></span>
                                        {{ $statusLabel }} {{ __('PLAN') }}
                                    </div>
                                    <h2 class="ms-hero__title">
                                        {{ $plan->name }}
                                        @if($isFree)
                                            <small>/ {{ __('Free forever') }}</small>
                                        @elseif($billingCycle)
                                            <small>/ {{ $billingCycle->label() }}</small>
                                        @endif
                                    </h2>
                                    <p class="ms-hero__tagline">{{ $plan->description ?: __('Your active subscription on this account.') }}</p>

                                    {{-- Single capability tag — features count only --}}
                                    <div class="ms-tags">
                                        <span class="ms-tag">{{ trans_choice(':count feature included|:count features included', $featureCount, ['count' => $featureCount]) }}</span>
                                    </div>

                                    {{-- Smart hero actions — picks based on state --}}
                                    <div class="ms-hero__actions">
                                        @if($canRenew)
                                            <form method="POST" action="{{ route('user.subscription.renew', $subscription) }}"
                                                  onsubmit="return confirm('{{ __('Renew subscription? Your wallet will be charged :price.', ['price' => $currencyCode.' '.number_format($currentPrice, 2)]) }}')">
                                                @csrf
                                                <button type="submit" class="ms-btn ms-btn--bright">
                                                    <i class="fa-solid fa-rotate"></i>
                                                    {{ __('Renew now') }}
                                                </button>
                                            </form>
                                        @elseif($upgradePlan)
                                            <a href="{{ route('user.subscription.plans') }}" class="ms-btn ms-btn--bright">
                                                {{ __('Upgrade to') }} {{ $upgradePlan->name }}
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                                            </a>
                                        @endif
                                        <a href="{{ route('user.subscription.plans') }}" class="ms-btn ms-btn--line">{{ __('Compare all plans') }}</a>
                                    </div>
                                </div>

                                {{-- 4 distinct meta cards — cost, usage, age, renewal --}}
                                <div class="ms-hero__meta">
                                    <div class="ms-meta-card">
                                        <div class="ms-meta-card__lbl">{{ __('Plan cost') }}</div>
                                        <div class="ms-meta-card__val">
                                            {{ $currencyCode }} {{ number_format($currentPrice, 2) }}
                                            <span>/{{ $isLifetime ? __('once') : __('mo') }}</span>
                                        </div>
                                        <div class="ms-meta-card__sub">{{ $isFree ? __('No billing cycle') : ($billingCycle?->label() ?? __('Custom')) }}</div>
                                    </div>
                                    <div class="ms-meta-card">
                                        <div class="ms-meta-card__lbl">{{ __('Features active') }}</div>
                                        <div class="ms-meta-card__val">{{ $enabledCount }} <span>{{ __('of') }} {{ $featureCount }}</span></div>
                                        <div class="ms-meta-card__sub">{{ $utilization }}% {{ __('enabled') }}</div>
                                    </div>
                                    <div class="ms-meta-card">
                                        <div class="ms-meta-card__lbl">{{ __('Member since') }}</div>
                                        <div class="ms-meta-card__val">{{ $startedAt?->format('M d') ?? '—' }}</div>
                                        <div class="ms-meta-card__sub">{{ $startedAt?->format('Y') ?? '' }} · {{ trans_choice(':count day|:count days', $memberDays, ['count' => $memberDays]) }}</div>
                                    </div>
                                    <div class="ms-meta-card">
                                        <div class="ms-meta-card__lbl">{{ __('Next renewal') }}</div>
                                        <div class="ms-meta-card__val">{{ $nextRenewalDate }}</div>
                                        <div class="ms-meta-card__sub">{{ $nextRenewalSub }}</div>
                                    </div>
                                </div>
                            </div>
                        </section>

                        {{-- ═══════════ NOTICES ═══════════ --}}
                        @if($subscription->isActive() && $periodEnd && ! is_null($daysRemaining) && $daysRemaining <= 7 && ! $isLifetime && ! $isFree && ! $isCancelled)
                            <div class="ms-notice ms-notice--warning">
                                <i class="fa-solid fa-triangle-exclamation"></i>
                                <span>{{ __('Your subscription expires in :days days. Renew now to avoid interruption.', ['days' => $daysRemaining]) }}</span>
                            </div>
                        @endif
                        @if($isCancelled && $subscription->isActive())
                            <div class="ms-notice ms-notice--info">
                                <i class="fa-solid fa-circle-info"></i>
                                <span>{{ __('Cancelled — access continues until :date.', ['date' => $periodEnd?->format('d M Y') ?? __('period end')]) }}</span>
                            </div>
                        @endif
                        @if($isTrial && $trialEndsAt)
                            <div class="ms-notice ms-notice--info">
                                <i class="fa-solid fa-gift"></i>
                                <span>{{ __('Trial ends on :date. You will be charged :price after that.', ['date' => $trialEndsAt->format('d M Y'), 'price' => $currencyCode.' '.number_format($currentPrice, 2)]) }}</span>
                            </div>
                        @endif

                        {{-- ═══════════ ACCOUNT ACTIVITY STATS ═══════════ --}}
                        <div class="ms-stats">
                            <div class="ms-stat">
                                <div class="ms-stat__lbl">{{ __('Total deposits') }}</div>
                                <div class="ms-stat__val">{{ $currencyCode }} {{ number_format($stats['deposits'], 2) }}</div>
                                <div class="ms-stat__delta {{ $stats['deposits'] > 0 ? '' : 'ms-stat__delta--flat' }}">
                                    @if($stats['deposits'] > 0)
                                        <i class="fa-solid fa-arrow-up"></i> {{ __('Lifetime total') }}
                                    @else
                                        — {{ __('No deposits yet') }}
                                    @endif
                                </div>
                            </div>
                            <div class="ms-stat">
                                <div class="ms-stat__lbl">{{ __('Money sent') }}</div>
                                <div class="ms-stat__val">{{ $currencyCode }} {{ number_format($stats['sent'], 2) }}</div>
                                <div class="ms-stat__delta {{ $stats['sent'] > 0 ? '' : 'ms-stat__delta--flat' }}">
                                    @if($stats['sent'] > 0)
                                        <i class="fa-solid fa-arrow-up"></i> {{ __('Lifetime total') }}
                                    @else
                                        — {{ __('No transfers yet') }}
                                    @endif
                                </div>
                            </div>
                            <div class="ms-stat">
                                <div class="ms-stat__lbl">{{ __('Referrals') }}</div>
                                <div class="ms-stat__val">{{ $stats['referrals'] }}</div>
                                <div class="ms-stat__delta ms-stat__delta--flat">{{ __('Total invited') }}</div>
                            </div>
                            <div class="ms-stat">
                                <div class="ms-stat__lbl">{{ __('Vouchers redeemed') }}</div>
                                <div class="ms-stat__val">{{ $stats['vouchers'] }}</div>
                                <div class="ms-stat__delta ms-stat__delta--flat">{{ __('Lifetime total') }}</div>
                            </div>
                        </div>

                        {{-- ═══════════ PLAN ACCESS + BILLING ═══════════ --}}
                        <div class="ms-grid-2">

                            {{-- Plan access — real usage meters --}}
                            <div class="ms-card">
                                <div class="ms-card__head">
                                    <div>
                                        <h3>{{ __('Plan usage') }}</h3>
                                        <div class="ms-card__sub">{{ __('Live usage against the limits on your plan.') }}</div>
                                    </div>
                                </div>

                                @php
                                    // Build the rendered list — only features that have a usage entry
                                    $usageFeatures = $features->filter(fn ($f) => isset($usage[$f->feature_key]))->values();

                                    $iconMap = [
                                        'deposit_money'             => 'fa-arrow-down',
                                        'withdraw_money'            => 'fa-arrow-up',
                                        'send_money'                => 'fa-paper-plane',
                                        'request_money'             => 'fa-hand-holding-usd',
                                        'exchange_money'            => 'fa-sync-alt',
                                        'vouchers'                  => 'fa-tag',
                                        'transaction_history'       => 'fa-history',
                                        'two_factor_auth'           => 'fa-shield-alt',
                                        'push_notifications'        => 'fa-bell',
                                        'p2p_marketplace'           => 'fa-handshake',
                                        'virtual_card'              => 'fa-credit-card',
                                        'virtual_card_limit'        => 'fa-credit-card',
                                        'virtual_cards'             => 'fa-credit-card',
                                        'wallet_earn'               => 'fa-piggy-bank',
                                        'wallet_balance_cap'        => 'fa-wallet',
                                        'referral_program'          => 'fa-users',
                                        'payment_link'              => 'fa-link',
                                        'bank_transfer'             => 'fa-university',
                                        'api_access'                => 'fa-code',
                                        'support_priority'          => 'fa-headset',
                                        'daily_transaction_limit'   => 'fa-tachometer-alt',
                                        'monthly_transaction_limit' => 'fa-chart-line',
                                        'monthly_withdraw_limit'    => 'fa-money-bill',
                                        'monthly_send_limit'        => 'fa-paper-plane',
                                        'p2p_ad_limit'              => 'fa-bullhorn',
                                    ];
                                @endphp

                                @if($usageFeatures->isEmpty())
                                    <x-user-not-found
                                        class="mt-2"
                                        :title="__('No measurable limits')"
                                        :message="__('This plan does not have usage-based limits to track yet.')"
                                        icon="fa-list-check"
                                    />
                                @else
                                    <div class="ms-meters">
                                        @foreach($usageFeatures as $feature)
                                            @php
                                                $u = $usage[$feature->feature_key];

                                                $palettes  = ['blue', 'green', 'lav', 'amber', 'rose'];
                                                $palette   = $palettes[$loop->index % count($palettes)];
                                                $iconCls   = $iconMap[strtolower((string) $feature->feature_key)] ?? ($u['is_unlimited'] ? 'fa-infinity' : 'fa-bolt');

                                                // Format used / limit values consistently
                                                $isInt   = ! $u['is_currency'];
                                                $usedFmt = $u['is_currency']
                                                    ? $currencyCode.' '.number_format($u['used'], 2)
                                                    : number_format($u['used'], $isInt && fmod($u['used'], 1) === 0.0 ? 0 : 2);

                                                $limitFmt = $u['is_unlimited']
                                                    ? __('Unlimited')
                                                    : ($u['is_currency']
                                                        ? $currencyCode.' '.number_format((float) $u['limit'], 2)
                                                        : number_format((float) $u['limit'], 0));

                                                // Highlight when usage is at or near the cap
                                                $isOver  = ! $u['is_unlimited'] && $u['percentage'] >= 100;
                                                $isWarn  = ! $u['is_unlimited'] && $u['percentage'] >= 80 && ! $isOver;
                                                if ($isOver) { $palette = 'rose'; }
                                                elseif ($isWarn) { $palette = 'amber'; }
                                            @endphp
                                            <div class="ms-meter-row">
                                                <div class="ms-meter-row__top">
                                                    <div class="ms-meter-name">
                                                        <span class="ms-meter-icn ms-icn-{{ $palette }}">
                                                            <i class="fa-solid {{ $iconCls }}"></i>
                                                        </span>
                                                        <span class="ms-meter-name__txt">{{ $feature->feature_label }}</span>
                                                        @if($u['reset_label'])
                                                            <span class="ms-meter-reset">· {{ $u['reset_label'] }}</span>
                                                        @endif
                                                    </div>
                                                    <div class="ms-meter-val">
                                                        <b>{{ $usedFmt }}</b>
                                                        <span class="ms-meter-sep">{{ __('of') }}</span>
                                                        <span class="ms-meter-cap">{{ $limitFmt }}</span>
                                                    </div>
                                                </div>
                                                <div class="ms-bar ms-bar--{{ $palette }}">
                                                    <i style="width: {{ $u['percentage'] }}%"></i>
                                                </div>
                                                @if($isOver)
                                                    <div class="ms-meter-foot ms-meter-foot--over">
                                                        <i class="fa-solid fa-triangle-exclamation"></i>
                                                        {{ __('Limit reached for this period') }}
                                                    </div>
                                                @elseif($isWarn)
                                                    <div class="ms-meter-foot ms-meter-foot--warn">
                                                        <i class="fa-solid fa-circle-exclamation"></i>
                                                        {{ __(':p% used — close to your cap', ['p' => $u['percentage']]) }}
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            {{-- Billing summary — payment-only focus --}}
                            <div class="ms-card">
                                <div class="ms-card__head">
                                    <div>
                                        <h3>{{ __('Billing summary') }}</h3>
                                        <div class="ms-card__sub">
                                            @if($isFree)
                                                {{ __('No payment scheduled') }}
                                            @elseif($isLifetime)
                                                {{ __('One-time charge — no renewals') }}
                                            @else
                                                {{ __('Charged from your wallet each cycle') }}
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="ms-billing-amount">
                                    {{ $currencyCode }} {{ number_format($currentPrice, 2) }}<small>/ {{ $isLifetime ? __('once') : __('mo') }}</small>
                                </div>
                                <div class="ms-next-date">
                                    <i class="fa-solid fa-calendar"></i>
                                    @if($isFree)
                                        {{ __('Always free') }}
                                    @elseif($isLifetime)
                                        {{ __('No renewal') }}
                                    @elseif($isCancelled && $periodEnd)
                                        {{ __('Access ends') }} {{ $periodEnd->format('d M Y') }}
                                    @elseif($periodEnd)
                                        {{ __('Renews on') }} {{ $periodEnd->format('d M Y') }}
                                    @else
                                        {{ __('Pending') }}
                                    @endif
                                </div>

                                <div class="ms-divider"></div>

                                <div class="ms-section-lbl">{{ __('Payment method') }}</div>
                                <div class="ms-method">
                                    <div class="ms-card-thumb"></div>
                                    <div class="ms-method__info">
                                        <div class="ms-method__title">{{ __('Wallet balance') }} <span>· {{ __('default') }}</span></div>
                                        <div class="ms-method__sub">{{ __('Charges are taken from your default wallet') }}</div>
                                    </div>
                                </div>

                                <div class="ms-divider"></div>

                                <div class="ms-section-lbl">{{ __('Plan summary') }}</div>
                                <div class="ms-summary-row">
                                    <span>{{ __('Auto-renew') }}</span>
                                    <b>{{ $subscription->auto_renew ? __('Enabled') : __('Disabled') }}</b>
                                </div>
                                @if($plan->trial_days > 0)
                                    <div class="ms-summary-row">
                                        <span>{{ __('Trial period') }}</span>
                                        <b>{{ $plan->trial_days }} {{ __('days') }}</b>
                                    </div>
                                @endif
                                @if($plan->grace_days > 0)
                                    <div class="ms-summary-row">
                                        <span>{{ __('Grace period') }}</span>
                                        <b>{{ $plan->grace_days }} {{ __('days') }}</b>
                                    </div>
                                @endif
                                <div class="ms-summary-row">
                                    <span>{{ __('Started') }}</span>
                                    <b>{{ $startedAt?->format('d M Y') ?? '—' }}</b>
                                </div>
                            </div>
                        </div>

                        {{-- ═══════════ UPGRADE STRIP ═══════════ --}}
                        @if($upgradePlan && ! $isCancelled)
                            <div class="ms-upgrade-strip">
                                <div class="ms-us__left">
                                    <div class="ms-us__icon">
                                        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                    </div>
                                    <div class="ms-us__text">
                                        <h4>
                                            @if($upgradeFeatureDelta > 0)
                                                {{ __('Unlock') }} <b>{{ $upgradeFeatureDelta }} {{ trans_choice('more feature|more features', $upgradeFeatureDelta) }}</b>
                                                {{ __('on') }} {{ $upgradePlan->name }}
                                                @if($upgradeMonthlyPrice > 0)
                                                    {{ __('for :price/mo', ['price' => $currencyCode.' '.number_format($upgradeMonthlyPrice, 2)]) }}
                                                @endif
                                            @else
                                                {{ __('Step up to') }} <b>{{ $upgradePlan->name }}</b>
                                                @if($upgradeMonthlyPrice > 0)
                                                    {{ __('for :price/mo', ['price' => $currencyCode.' '.number_format($upgradeMonthlyPrice, 2)]) }}
                                                @endif
                                            @endif
                                        </h4>
                                        <p>{{ $upgradePlan->description ?: __('Higher limits, more features, priority support — all included.') }}</p>
                                    </div>
                                </div>
                                <div class="ms-us__actions">
                                    <a href="{{ route('user.subscription.plans') }}" class="ms-btn ms-btn--primary">
                                        {{ __('Upgrade to') }} {{ $upgradePlan->name }}
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                                    </a>
                                </div>
                            </div>
                        @endif

                        {{-- ═══════════ BILLING HISTORY ═══════════ --}}
                        <div class="ms-card mb-3">
                            <div class="ms-card__head">
                                <div>
                                    <h3>{{ __('Billing history') }}</h3>
                                    <div class="ms-card__sub">{{ __('Recent invoices for this subscription') }}</div>
                                </div>
                                <a href="{{ route('user.subscription.history') }}" class="ms-btn ms-btn--ghost ms-btn--sm">
                                    <i class="fa-solid fa-clock-rotate-left"></i> {{ __('View all') }}
                                </a>
                            </div>

                            @if($transactions->isEmpty())
                                <x-user-not-found
                                    class="mt-2"
                                    :title="__('No payment records yet')"
                                    :message="__('Subscription payment records will appear here after a paid activation or renewal.')"
                                    icon="fa-receipt"
                                />
                            @else
                                <div class="ms-table-wrap">
                                    <table class="ms-inv-table">
                                        <thead>
                                            <tr>
                                                <th style="width:24%;">{{ __('Invoice') }}</th>
                                                <th style="width:32%;">{{ __('Description') }}</th>
                                                <th>{{ __('Date') }}</th>
                                                <th>{{ __('Amount') }}</th>
                                                <th>{{ __('Status') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($transactions as $trx)
                                                @php
                                                    $statusClass = match (strtolower((string) $trx->status)) {
                                                        'completed', 'success', 'paid' => 'ms-s-paid',
                                                        'pending', 'processing'        => 'ms-s-pending',
                                                        'failed', 'cancelled'          => 'ms-s-failed',
                                                        'refunded'                     => 'ms-s-free',
                                                        default                        => 'ms-s-free',
                                                    };
                                                    $invoiceId = $trx->trx_id
                                                        ? 'INV-'.strtoupper(substr($trx->trx_id, 0, 12))
                                                        : 'DK-'.$trx->created_at->format('Ymd').'-'.str_pad($trx->id, 3, '0', STR_PAD_LEFT);
                                                    $typeLabel = ucfirst((string) $trx->type);
                                                @endphp
                                                <tr>
                                                    <td><span class="ms-inv-id">{{ $invoiceId }}</span></td>
                                                    <td>{{ $plan->name }} · {{ $typeLabel }}</td>
                                                    <td>{{ $trx->created_at->format('d M Y') }}</td>
                                                    <td>
                                                        @if((float) $trx->amount > 0)
                                                            {{ $trx->currency_code }} {{ number_format($trx->amount, 2) }}
                                                        @else
                                                            {{ __('Free') }}
                                                        @endif
                                                    </td>
                                                    <td><span class="ms-inv-status {{ $statusClass }}">● {{ ucfirst((string) $trx->status) }}</span></td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>

                        {{-- ═══════════ DANGER ZONE ═══════════ --}}
                        @if($canCancel)
                            <div class="ms-danger-zone">
                                <div class="ms-dz__text">
                                    <h4>{{ __('Cancel subscription') }}</h4>
                                    <p>{{ __('Your access continues until :date, then the plan ends.', ['date' => $periodEnd?->format('d M Y') ?? __('period end')]) }}</p>
                                </div>
                                <div class="ms-dz__actions">
                                    <form method="POST" action="{{ route('user.subscription.cancel', $subscription) }}"
                                          onsubmit="return confirm('{{ __('Cancel your subscription? Access continues until the end of the current period.') }}')">
                                        @csrf
                                        <button type="submit" class="ms-btn ms-btn--danger ms-btn--sm">
                                            <i class="fa-solid fa-ban"></i> {{ __('Cancel subscription') }}
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endif

                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection
