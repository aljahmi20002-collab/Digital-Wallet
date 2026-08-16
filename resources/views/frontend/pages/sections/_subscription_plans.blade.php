@push('styles')
    <link rel="stylesheet" href="{{ asset('frontend/css/lp-subscription-plans.css?v=' . config('app.version')) }}">
@endpush

@php
    use App\Enums\BillingCycle;
    use App\Models\SubscriptionPlan;

    $lpPlans = SubscriptionPlan::query()
        ->where('status', true)
        ->orderBy('sort_order')
        ->with(['features', 'prices'])
        ->get();

    $lpFeatured = $lpPlans->firstWhere('is_featured', true);
    $lpOthers   = $lpPlans->where('is_featured', false)->values();

    // Featured plan goes in the centre slot
    if ($lpFeatured && $lpOthers->count() >= 2) {
        $lpOrdered = [$lpOthers[0], $lpFeatured, $lpOthers[1]];
    } elseif ($lpFeatured && $lpOthers->count() === 1) {
        $lpOrdered = [$lpOthers[0], $lpFeatured];
    } else {
        $lpOrdered = $lpPlans->all();
    }

    $locale = $locale ?? app()->getLocale();

    $lpEyebrow = !empty($data['subheading'][$locale]) ? $data['subheading'][$locale] : __('Simple, transparent pricing');
    $lpHeading = !empty($data['heading'][$locale])    ? $data['heading'][$locale]    : __('Pick the plan that powers your wallet');
    $lpDesc    = !empty($data['description'][$locale]) ? $data['description'][$locale] : __('All plans include a DigiKash wallet. Upgrade anytime — no hidden fees, no lock-in.');

    $allPrices             = $lpPlans->flatMap->prices;
    $maxHalfYearlyDiscount = $allPrices->where('billing_cycle', BillingCycle::HalfYearly)->max('discount');
    $maxYearlyDiscount     = $allPrices->where('billing_cycle', BillingCycle::Yearly)->max('discount');

    $currencyCode = siteCurrency('code');
@endphp

<section class="lp-sub-plans" id="subscription-plans">

    {{-- Background orbs --}}
    <div class="lp-sub-plans__bg" aria-hidden="true">
        <div class="lp-sub-plans__orb lp-sub-plans__orb--a"></div>
        <div class="lp-sub-plans__orb lp-sub-plans__orb--b"></div>
        <div class="lp-sub-plans__orb lp-sub-plans__orb--c"></div>
    </div>

    {{-- Section header --}}
    <div class="lp-sub-plans__header">
        <div class="container">
            <div class="lp-sub-plans__eyebrow wow fadeInUp" data-wow-delay=".1s">
                <svg width="12" height="12" viewBox="0 0 12 12" fill="none"><path d="M6 1L7.5 4.1L11 4.6L8.5 7L9.1 10.5L6 8.9L2.9 10.5L3.5 7L1 4.6L4.5 4.1L6 1Z" fill="#4663EE" stroke="#4663EE" stroke-width=".8" stroke-linejoin="round"/></svg>
                {{ $lpEyebrow }}
            </div>
            <h2 class="lp-sub-plans__title wow fadeInUp" data-wow-delay=".2s">
                {!! nl2br(e($lpHeading)) !!}
            </h2>
            <p class="lp-sub-plans__sub wow fadeInUp" data-wow-delay=".3s">{{ $lpDesc }}</p>

            {{-- 3-cycle billing toggle --}}
            <div class="lp-sub-plans__toggle wow fadeInUp" data-wow-delay=".35s" id="lpBillingToggle" role="group" aria-label="{{ __('Billing cycle') }}">
                <button class="lp-sub-plans__toggle-btn active" data-cycle="monthly" type="button">
                    {{ __('Monthly') }}
                </button>
                <button class="lp-sub-plans__toggle-btn" data-cycle="half_yearly" type="button">
                    {{ __('6 Months') }}
                    @if($maxHalfYearlyDiscount)
                        <span class="lp-sub-plans__toggle-save">-{{ $maxHalfYearlyDiscount }}%</span>
                    @endif
                </button>
                <button class="lp-sub-plans__toggle-btn" data-cycle="yearly" type="button">
                    {{ __('Annual') }}
                    @if($maxYearlyDiscount)
                        <span class="lp-sub-plans__toggle-save">-{{ $maxYearlyDiscount }}%</span>
                    @endif
                </button>
            </div>
        </div>
    </div>

    {{-- Plans grid --}}
    <div class="container">
        <div class="lp-sub-plans__grid" id="lpPlansGrid">

            @foreach($lpOrdered as $lpIdx => $lpPlan)
                @php
                    $isFeatured = $lpPlan->is_featured;

                    $prices     = $lpPlan->prices->keyBy(
                        fn($p) => $p->billing_cycle instanceof BillingCycle
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

                    $halfPerMo = $halfAmt   > 0 ? round($halfAmt  / 6,  2) : 0;
                    $yearPerMo = $yearlyAmt > 0 ? round($yearlyAmt / 12, 2) : 0;

                    $halfNote = $halfRow?->discount
                        ? __(':d% off — :c:a billed every 6 months', ['d' => $halfRow->discount, 'c' => $currencyCode, 'a' => number_format($halfAmt, 2)])
                        : ($halfAmt > 0 ? __(':c:a billed every 6 months', ['c' => $currencyCode, 'a' => number_format($halfAmt, 2)]) : '');
                    $yearNote = $yearlyRow?->discount
                        ? __(':d% off — :c:a billed yearly', ['d' => $yearlyRow->discount, 'c' => $currencyCode, 'a' => number_format($yearlyAmt, 2)])
                        : ($yearlyAmt > 0 ? __(':c:a billed yearly', ['c' => $currencyCode, 'a' => number_format($yearlyAmt, 2)]) : '');

                    $planIcon = match(true) {
                        $isFree     => 'fas fa-seedling',
                        $isFeatured => 'fas fa-crown',
                        default     => 'fas fa-rocket',
                    };

                    if ($isFree) {
                        $badgeVariant = 'free';
                        $badgeText    = $lpPlan->plan_badge ?: __('Free Forever');
                    } elseif ($isFeatured) {
                        $badgeVariant = 'featured';
                        $badgeText    = $lpPlan->plan_badge ?: __('Most Popular');
                    } elseif ($lpPlan->plan_badge) {
                        $badgeVariant = 'pro';
                        $badgeText    = $lpPlan->plan_badge;
                    } else {
                        $badgeVariant = null;
                        $badgeText    = null;
                    }

                    if ($isFree) {
                        $ctaLabel = __('Get Started — Free');
                    } elseif ($lpPlan->trial_days > 0) {
                        $ctaLabel = __('Start :d-Day Free Trial', ['d' => $lpPlan->trial_days]);
                    } else {
                        $ctaLabel = __('Get :name', ['name' => $lpPlan->name]);
                    }
                @endphp

                <div class="lp-sub-plans__card-wrap wow fadeInUp" data-wow-delay="{{ ($lpIdx * 100) }}ms"
                     id="lp-plan-{{ $lpPlan->slug }}">

                    <div class="lp-sub-plans__card {{ $isFeatured ? 'lp-sub-plans__card--featured' : '' }}">

                        @if($isFeatured)
                            <div class="lp-sub-plans__card-shine" aria-hidden="true"></div>
                        @endif

                        {{-- Plan identity --}}
                        <div class="lp-sub-plans__card-top">
                            <div class="lp-sub-plans__card-header">
                                <div class="lp-sub-plans__card-icon">
                                    <i class="{{ $planIcon }}"></i>
                                </div>
                                <div>
                                    @if($badgeText)
                                        <span class="lp-sub-plans__badge lp-sub-plans__badge--{{ $badgeVariant }}">
                                            @if($isFeatured)
                                                <svg width="9" height="9" viewBox="0 0 9 9" fill="none"><path d="M4.5 1L5.7 3.6L8.5 4.1L6.5 6L7 8.8L4.5 7.5L2 8.8L2.5 6L.5 4.1L3.3 3.6L4.5 1Z" fill="currentColor" stroke="currentColor" stroke-width=".5" stroke-linejoin="round"/></svg>
                                            @endif
                                            {{ $badgeText }}
                                        </span>
                                    @endif
                                    <div class="lp-sub-plans__card-name">{{ $lpPlan->name }}</div>
                                </div>
                            </div>
                            <div class="lp-sub-plans__card-tagline">
                                {{ $lpPlan->description ?: __('Access to core platform features.') }}
                            </div>
                        </div>

                        {{-- Price --}}
                        <div class="lp-sub-plans__price-row">
                            @if($isFree)
                                <span class="lp-sub-plans__card-amount lp-sub-plans__card-amount--free js-lp-amount" data-free="true">
                                    {{ __('Free') }}
                                </span>
                                <span class="lp-sub-plans__price-period">{{ __('forever') }}</span>
                            @else
                                <span class="lp-sub-plans__price-currency">{{ $currencyCode }}</span>
                                <span class="lp-sub-plans__card-amount js-lp-amount"
                                      data-monthly="{{ number_format($monthlyAmt, 2) }}"
                                      data-half_yearly="{{ number_format($halfPerMo, 2) }}"
                                      data-yearly="{{ number_format($yearPerMo, 2) }}">
                                    {{ number_format($monthlyAmt, 2) }}
                                </span>
                                <span class="lp-sub-plans__price-period">/{{ __('mo') }}</span>
                            @endif
                        </div>

                        <div class="lp-sub-plans__billing-note js-lp-note"
                             data-monthly=""
                             data-half_yearly="{{ $halfNote }}"
                             data-yearly="{{ $yearNote }}">
                            &nbsp;
                        </div>

                        {{-- Trial pill --}}
                        @if($lpPlan->trial_days > 0)
                            <div class="lp-sub-plans__trial-pill">
                                <i class="fas fa-gift"></i>
                                {{ $lpPlan->trial_days }}-{{ __('day free trial') }}
                            </div>
                        @endif

                        {{-- CTA --}}
                        <a href="{{ route('user.subscription.plans') }}"
                           class="lp-sub-plans__cta {{ $isFeatured ? 'lp-sub-plans__cta--featured' : 'lp-sub-plans__cta--default' }}">
                            {{ $ctaLabel }}
                            @if(! $isFree)
                                <svg width="13" height="13" viewBox="0 0 13 13" fill="none"><path d="M2.5 6.5H10.5M7.5 3.5L10.5 6.5L7.5 9.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            @endif
                        </a>

                        <div class="lp-sub-plans__divider"></div>

                        {{-- Features --}}
                        @if($lpPlan->features->isNotEmpty())
                            <div class="lp-sub-plans__feat-heading">{{ __("What's included") }}</div>
                            <ul class="lp-sub-plans__features">
                                @foreach($lpPlan->features as $lpFeat)
                                    <li class="lp-sub-plans__feature">
                                        <span class="lp-sub-plans__feat-icon">
                                            <svg width="10" height="10" viewBox="0 0 10 10" fill="none"><path d="M2 5.5L4 7.5L8 3" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                        </span>
                                        <span class="lp-sub-plans__feat-label">{{ $lpFeat->feature_label }}</span>
                                        @if(! $lpFeat->isToggle())
                                            <span class="lp-sub-plans__limit-pill">
                                                {{ $lpFeat->isUnlimited() ? __('Unlimited') : $lpFeat->feature_value }}
                                            </span>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        @endif

                    </div>
                </div>
            @endforeach

        </div>
    </div>

</section>

@push('scripts')
<script>
"use strict";
(function () {
    var toggle = document.getElementById('lpBillingToggle');
    var grid   = document.getElementById('lpPlansGrid');
    if (!toggle || !grid) { return; }

    function updateCycle(cycle) {
        toggle.querySelectorAll('.lp-sub-plans__toggle-btn').forEach(function (btn) {
            btn.classList.toggle('active', btn.dataset.cycle === cycle);
        });

        grid.querySelectorAll('.lp-sub-plans__card').forEach(function (card) {
            var amountEl = card.querySelector('.js-lp-amount');
            var noteEl   = card.querySelector('.js-lp-note');

            if (amountEl && !amountEl.dataset.free) {
                var val = amountEl.getAttribute('data-' + cycle)
                       || amountEl.getAttribute('data-' + cycle.replace('_', ''));
                if (val) { amountEl.textContent = val; }
            }

            if (noteEl) {
                var note = noteEl.getAttribute('data-' + cycle) || '';
                noteEl.textContent = note || ' ';
                noteEl.classList.toggle('is-visible', Boolean(note));
            }
        });
    }

    toggle.addEventListener('click', function (e) {
        var btn = e.target.closest('.lp-sub-plans__toggle-btn');
        if (btn && btn.dataset.cycle) { updateCycle(btn.dataset.cycle); }
    });
}());
</script>
@endpush
