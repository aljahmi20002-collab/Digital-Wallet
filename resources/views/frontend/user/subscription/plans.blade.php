@extends('frontend.layouts.user.index')
@section('title', __('Subscription Plans'))

@push('styles')
    <link rel="stylesheet" href="{{ asset('frontend/css/subscription-plans.css?v=' . config('app.version')) }}">
@endpush

@section('content')

@php
    $currencyCode = siteCurrency('code');
@endphp

<div class="row">
    <div class="col-12">
        <div class="card single-form-card">

            <x-user-feature-header
                :title="__('Subscription Plans')"
                :subtitle="__('Simple pricing, no surprises. Upgrade or cancel anytime.')"
                icon="fas fa-layer-group"
            >
                @if($current)
                    <a href="{{ route('user.subscription.current') }}" class="btn btn-light-primary btn-sm">
                        <i class="fa-solid fa-circle-check"></i> {{ __('My Subscription') }}
                    </a>
                @endif
                <a href="{{ route('user.subscription.history') }}" class="btn btn-light-secondary btn-sm">
                    <i class="fa-solid fa-clock-rotate-left"></i> {{ __('History') }}
                </a>
            </x-user-feature-header>

            <div class="card-body">

                {{-- Billing cycle selector --}}
                <div class="sp-toggle-wrap" style="animation:sp-fadeUp .28s both">
                    <div class="sp-toggle" id="billingToggle" role="group" aria-label="{{ __('Billing cycle') }}">
                        <button class="sp-toggle__btn active" data-cycle="monthly" type="button">
                            {{ __('Monthly') }}
                        </button>
                        <button class="sp-toggle__btn" data-cycle="half_yearly" type="button">
                            {{ __('6 Months') }}
                            @if($maxHalfYearlyDiscount)
                                <span class="sp-toggle__save">-{{ $maxHalfYearlyDiscount }}%</span>
                            @endif
                        </button>
                        <button class="sp-toggle__btn" data-cycle="yearly" type="button">
                            {{ __('Annual') }}
                            @if($maxYearlyDiscount)
                                <span class="sp-toggle__save">-{{ $maxYearlyDiscount }}%</span>
                            @endif
                        </button>
                    </div>
                </div>

                {{-- Contextual billing hint --}}
                @if($maxYearlyDiscount)
                    <p class="sp-billing-hint" style="animation:sp-fadeUp .28s .06s both">
                        <i class="fas fa-tag"></i>
                        {{ __('Save up to :d% when billed annually — your best value.', ['d' => $maxYearlyDiscount]) }}
                    </p>
                @endif

                {{-- Plans --}}
                @if($plans->isEmpty())
                    <x-user-not-found
                        :title="__('No subscription plans available')"
                        :message="__('Subscription plans are not available at the moment. Please check back later.')"
                        icon="fa-box-open"
                    />
                @else
                    <div class="sp-grid" id="planGrid">
                        @foreach($plans as $idx => $plan)
                            @php
                                $isFeatured = $plan->is_featured;
                                $isCurrent  = $current
                                    && $current->subscription_plan_id === $plan->id
                                    && $current->isActive();

                                $prices    = $plan->prices->keyBy(
                                    fn($p) => $p->billing_cycle instanceof \App\Enums\BillingCycle
                                        ? $p->billing_cycle->value
                                        : (string) $p->billing_cycle
                                );
                                $monthlyRow = $prices['monthly']     ?? null;
                                $halfRow    = $prices['half_yearly'] ?? null;
                                $yearlyRow  = $prices['yearly']      ?? null;

                                $monthlyAmt = (float) ($monthlyRow?->price ?? 0);
                                $halfAmt    = (float) ($halfRow?->price    ?? 0);
                                $yearlyAmt  = (float) ($yearlyRow?->price  ?? 0);
                                $isFree     = $monthlyAmt <= 0;

                                $halfPerMo = $halfAmt  > 0 ? round($halfAmt  / 6,  2) : 0;
                                $yearPerMo = $yearlyAmt > 0 ? round($yearlyAmt / 12, 2) : 0;

                                $halfNote = $halfRow?->discount
                                    ? __(':d% off — :c:a billed every 6 months', ['d' => $halfRow->discount,   'c' => $currencyCode, 'a' => number_format($halfAmt,   2)])
                                    : ($halfAmt   > 0 ? __(':c:a billed every 6 months', ['c' => $currencyCode, 'a' => number_format($halfAmt,   2)]) : '');
                                $yearNote = $yearlyRow?->discount
                                    ? __(':d% off — :c:a billed yearly', ['d' => $yearlyRow->discount, 'c' => $currencyCode, 'a' => number_format($yearlyAmt, 2)])
                                    : ($yearlyAmt > 0 ? __(':c:a billed yearly', ['c' => $currencyCode, 'a' => number_format($yearlyAmt, 2)]) : '');

                                // Icon per tier
                                $planIcon = match(true) {
                                    $isFree     => 'fas fa-seedling',
                                    $isFeatured => 'fas fa-crown',
                                    default     => 'fas fa-rocket',
                                };

                                // Badge
                                if ($isCurrent) {
                                    $badgeVariant = 'current';
                                    $badgeText    = __('Current Plan');
                                    $badgeCheck   = true;
                                } elseif ($isFeatured) {
                                    $badgeVariant = 'featured';
                                    $badgeText    = $plan->plan_badge ?: __('Most Popular');
                                    $badgeCheck   = false;
                                } elseif ($plan->plan_badge) {
                                    $badgeVariant = 'pro';
                                    $badgeText    = $plan->plan_badge;
                                    $badgeCheck   = false;
                                } elseif ($isFree) {
                                    $badgeVariant = 'free';
                                    $badgeText    = __('Free Forever');
                                    $badgeCheck   = false;
                                } else {
                                    $badgeVariant = null;
                                    $badgeText    = null;
                                    $badgeCheck   = false;
                                }

                                // CTA label
                                if ($isFree) {
                                    $ctaLabel = __('Get Started — Free');
                                } elseif ($plan->trial_days > 0) {
                                    $ctaLabel = __('Start :d-Day Free Trial', ['d' => $plan->trial_days]);
                                } else {
                                    $ctaLabel = __('Subscribe Now');
                                }
                            @endphp

                            {{-- Wrapper holds animation; badge is sibling of <article> --}}
                            <div class="sp-card-wrap" style="animation:sp-fadeUp .4s {{ $idx * 80 }}ms ease both">

                                @if($badgeText)
                                    <div class="sp-card__badge-wrap">
                                        <span class="sp-badge sp-badge--{{ $badgeVariant }}">
                                            @if($badgeCheck)
                                                <svg width="7" height="7" viewBox="0 0 7 7" fill="none" aria-hidden="true">
                                                    <path d="M1 3.5L2.8 5.5L6 1.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                            @elseif($isFeatured)
                                                <svg width="8" height="8" viewBox="0 0 9 9" fill="currentColor" aria-hidden="true">
                                                    <path d="M4.5 1 5.6 3.5 8.2 3.8 6.3 5.6 6.8 8.2 4.5 6.9 2.2 8.2l.5-2.6L.8 3.8l2.6-.3z"/>
                                                </svg>
                                            @endif
                                            {{ $badgeText }}
                                        </span>
                                    </div>
                                @endif

                                <article class="sp-card {{ $isFeatured ? 'sp-card--featured' : '' }} {{ $isCurrent ? 'sp-card--current' : '' }}">

                                    {{-- Plan identity --}}
                                    <div class="sp-card__top">
                                        <div class="sp-card__header">
                                            <div class="sp-card__icon">
                                                <i class="{{ $planIcon }}"></i>
                                            </div>
                                            <div class="sp-card__identity">
                                                <div class="sp-card__name">{{ $plan->name }}</div>
                                                <div class="sp-card__tagline">
                                                    {{ $plan->description ?: __('Access to core platform features.') }}
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Pricing --}}
                                        <div class="sp-card__price-row">
                                            @if($isFree)
                                                <span class="sp-card__amount sp-card__amount--free js-plan-amount" data-free="true">
                                                    {{ __('Free') }}
                                                </span>
                                                <span class="sp-card__period">{{ __('forever') }}</span>
                                            @else
                                                <span class="sp-card__currency">{{ $currencyCode }}</span>
                                                <span class="sp-card__amount js-plan-amount"
                                                      data-monthly="{{ number_format($monthlyAmt, 2) }}"
                                                      data-half_yearly="{{ number_format($halfPerMo, 2) }}"
                                                      data-yearly="{{ number_format($yearPerMo, 2) }}">
                                                    {{ number_format($monthlyAmt, 2) }}
                                                </span>
                                                <span class="sp-card__period">/{{ __('mo') }}</span>
                                            @endif
                                        </div>

                                        <div class="sp-card__billing-note js-plan-note"
                                             data-monthly=""
                                             data-half_yearly="{{ $halfNote }}"
                                             data-yearly="{{ $yearNote }}">
                                            &nbsp;
                                        </div>

                                        {{-- Trial / grace pills --}}
                                        @if($plan->trial_days > 0 || $plan->grace_days > 0)
                                            <div class="sp-card__meta-pills">
                                                @if($plan->trial_days > 0)
                                                    <span class="sp-pill sp-pill--trial">
                                                        <i class="fas fa-gift"></i>
                                                        {{ $plan->trial_days }}-{{ __('day free trial') }}
                                                    </span>
                                                @endif
                                                @if($plan->grace_days > 0)
                                                    <span class="sp-pill sp-pill--grace">
                                                        {{ $plan->grace_days }}-{{ __('day grace period') }}
                                                    </span>
                                                @endif
                                            </div>
                                        @endif
                                    </div>

                                    <div class="sp-card__divider"></div>

                                    {{-- Features --}}
                                    <div class="sp-card__body">
                                        @if($plan->features->isNotEmpty())
                                            <p class="sp-card__feat-heading">{{ __("What's included") }}</p>
                                            <ul class="sp-card__feature-list">
                                                @foreach($plan->features as $feature)
                                                    <li class="sp-card__feature-item">
                                                        <span class="sp-card__feat-check">
                                                            <i class="fas fa-check"></i>
                                                        </span>
                                                        <span class="sp-card__feat-label">{{ $feature->feature_label }}</span>
                                                        @if(! $feature->isToggle())
                                                            <span class="sp-pill sp-pill--value {{ $isFeatured ? 'sp-pill--value-inv' : '' }}">
                                                                {{ $feature->isUnlimited() ? __('Unlimited') : $feature->feature_value }}
                                                            </span>
                                                        @endif
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    </div>

                                    {{-- Call to action --}}
                                    <div class="sp-card__cta">
                                        @if($isCurrent)
                                            <a href="{{ route('user.subscription.current') }}"
                                               class="sp-card__btn sp-card__btn--active">
                                                <i class="fas fa-check-circle"></i>
                                                {{ __('Active Plan') }}
                                            </a>
                                        @else
                                            @php
                                                // Prorated charge / credit per cycle for switch CTAs.
                                                $planProration = $proration[$plan->id] ?? null;
                                                $isSwitch = (bool) ($current && $current->isActive());
                                                $isUpgrade = $isSwitch && (int) $plan->sort_order > (int) $current->plan->sort_order;
                                                $isDowngrade = $isSwitch && (int) $plan->sort_order < (int) $current->plan->sort_order;

                                                if ($isUpgrade) {
                                                    $switchLabel = __('Upgrade to :name', ['name' => $plan->name]);
                                                } elseif ($isDowngrade) {
                                                    $switchLabel = __('Switch to :name', ['name' => $plan->name]);
                                                } else {
                                                    $switchLabel = $ctaLabel;
                                                }

                                                $protoData = $planProration ? [
                                                    'monthly_charge'      => $planProration['monthly']['charge']      ?? 0,
                                                    'monthly_credit'      => $planProration['monthly']['credit']      ?? 0,
                                                    'monthly_full'        => $planProration['monthly']['new_plan_price'] ?? 0,
                                                    'half_yearly_charge'  => $planProration['half_yearly']['charge']  ?? 0,
                                                    'half_yearly_credit'  => $planProration['half_yearly']['credit']  ?? 0,
                                                    'half_yearly_full'    => $planProration['half_yearly']['new_plan_price'] ?? 0,
                                                    'yearly_charge'       => $planProration['yearly']['charge']       ?? 0,
                                                    'yearly_credit'       => $planProration['yearly']['credit']       ?? 0,
                                                    'yearly_full'         => $planProration['yearly']['new_plan_price'] ?? 0,
                                                    'remaining_days'      => $planProration['monthly']['remaining_days'] ?? 0,
                                                ] : null;
                                            @endphp

                                            <a href="{{ route('user.subscription.checkout', ['plan' => $plan->slug, 'cycle' => 'monthly']) }}"
                                               class="sp-card__btn {{ $isFeatured ? 'sp-card__btn--featured' : 'sp-card__btn--default' }} js-checkout-link"
                                               data-base="{{ route('user.subscription.checkout', ['plan' => $plan->slug, 'cycle' => 'monthly']) }}"
                                               data-slug="{{ $plan->slug }}">
                                                {{ $isSwitch ? $switchLabel : $ctaLabel }}
                                                <i class="fas fa-arrow-right"></i>
                                            </a>

                                            @if($isSwitch && $planProration && ($planProration['monthly']['credit'] ?? 0) > 0)
                                                <div class="sp-card__switch-note">
                                                    <i class="fas fa-info-circle"></i>
                                                    {{ __('Prorated — :days days credit applied', ['days' => $planProration['monthly']['remaining_days']]) }}
                                                </div>
                                            @endif
                                        @endif
                                    </div>

                                </article>
                            </div>{{-- /.sp-card-wrap --}}
                        @endforeach
                    </div>
                @endif

                <p class="sp-footer-note" style="animation:sp-fadeUp .4s .3s both">
                    <i class="fas fa-lock"></i>
                    {{ __('256-bit encryption · PCI-DSS compliant · Cancel anytime · No contracts') }}
                </p>

            </div>{{-- /.card-body --}}
        </div>{{-- /.single-form-card --}}
    </div>
</div>

@push('scripts')
<script>
"use strict";
(function () {
    const toggle = document.getElementById('billingToggle');
    const grid   = document.getElementById('planGrid');
    if (!toggle || !grid) { return; }

    function updateCycle(cycle) {
        toggle.querySelectorAll('.sp-toggle__btn').forEach(function (btn) {
            btn.classList.toggle('active', btn.dataset.cycle === cycle);
        });

        grid.querySelectorAll('.sp-card').forEach(function (card) {
            const amountEl    = card.querySelector('.js-plan-amount');
            const noteEl      = card.querySelector('.js-plan-note');
            const cycleInputs = card.querySelectorAll('.js-billing-cycle');

            if (amountEl && !amountEl.dataset.free) {
                const val = amountEl.getAttribute('data-' + cycle)
                         || amountEl.getAttribute('data-' + cycle.replace('_', ''));
                if (val) { amountEl.textContent = val; }
            }

            if (noteEl) {
                const note = noteEl.getAttribute('data-' + cycle) || '';
                noteEl.textContent = note || ' ';
                noteEl.classList.toggle('is-visible', Boolean(note));
            }

            cycleInputs.forEach(function (inp) { inp.value = cycle; });
        });
    }

    toggle.addEventListener('click', function (e) {
        const btn = e.target.closest('.sp-toggle__btn');
        if (btn && btn.dataset.cycle) { updateCycle(btn.dataset.cycle); }
    });

    // Keep checkout links in sync with the active billing-cycle selector.
    function syncCheckoutLinks(cycle) {
        grid.querySelectorAll('.js-checkout-link').forEach(function (link) {
            const base = link.dataset.base || link.getAttribute('href');
            // Replace the trailing /{cycle} segment with the current selection.
            link.href = base.replace(/\/[^/]+$/, '/' + cycle);
        });
    }

    // Initial sync + re-sync when the billing-cycle toggle is changed.
    syncCheckoutLinks('monthly');
    toggle.addEventListener('click', function (e) {
        const btn = e.target.closest('.sp-toggle__btn');
        if (btn && btn.dataset.cycle) { syncCheckoutLinks(btn.dataset.cycle); }
    });
}());
</script>
@endpush

@endsection
