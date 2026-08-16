@push('styles')
    <link rel="stylesheet" href="{{ asset('frontend/css/lp-wallet-earn.css?v=' . config('app.version')) }}">
@endpush

@php
    use App\Models\WalletEarnPlan;

    $earnPlans = WalletEarnPlan::query()
        ->active()
        ->orderBy('sort_order')
        ->with('currency')
        ->get();

    $locale = $locale ?? app()->getLocale();

    $earnEyebrow = !empty($data['subheading'][$locale]) ? $data['subheading'][$locale] : __('Grow your money');
    $earnHeading = !empty($data['heading'][$locale])    ? $data['heading'][$locale]    : __('Put your wallet to work');
    $earnDesc    = !empty($data['description'][$locale]) ? $data['description'][$locale] : __('Stake your funds and earn guaranteed returns. Flexible terms, transparent payouts, zero surprises.');
@endphp

<section class="lp-earn" id="wallet-earn-plans">

    {{-- Background orbs --}}
    <div class="lp-earn__bg" aria-hidden="true">
        <div class="lp-earn__orb lp-earn__orb--a"></div>
        <div class="lp-earn__orb lp-earn__orb--b"></div>
    </div>

    {{-- Section header --}}
    <div class="lp-earn__header">
        <div class="container">
            <div class="lp-earn__eyebrow wow fadeInUp" data-wow-delay=".1s">
                <i class="fas fa-chart-line"></i>
                {{ $earnEyebrow }}
            </div>
            <h2 class="lp-earn__title wow fadeInUp" data-wow-delay=".2s">
                {!! nl2br(e($earnHeading)) !!}
            </h2>
            <p class="lp-earn__sub wow fadeInUp" data-wow-delay=".3s">{{ $earnDesc }}</p>
        </div>
    </div>

    {{-- Plans grid --}}
    <div class="container">
        @if($earnPlans->isEmpty())
            <p class="text-center text-muted py-5">{{ __('No earning plans available at the moment.') }}</p>
        @else
            <div class="lp-earn__grid">
                @foreach($earnPlans as $earnIdx => $earnPlan)
                    @php
                        $isFeatured    = $earnPlan->is_featured;
                        $badgeLabel    = $earnPlan->planBadgeLabel();
                        $currencyLabel = $earnPlan->currency_id ? $earnPlan->currency->code : __('All Wallets');
                    @endphp

                    {{-- card-wrap holds the badge outside overflow:hidden --}}
                    <div class="lp-earn__card-wrap wow fadeInUp" data-wow-delay="{{ $earnIdx * 100 }}ms">

                        {{-- Badge (positioned above the card) --}}
                        @if($badgeLabel)
                            <div class="lp-earn__badge-wrap">
                                <span class="lp-earn__badge {{ $isFeatured ? 'lp-earn__badge--amber' : 'lp-earn__badge--blue' }}">
                                    {{ $badgeLabel }}
                                </span>
                            </div>
                        @endif

                        <article class="lp-earn__card {{ $isFeatured ? 'lp-earn__card--featured' : '' }}">

                            {{-- Top section: icon + name + rate + pills --}}
                            <div class="lp-earn__card-top">

                                <div class="lp-earn__card-header">
                                    <div class="lp-earn__card-icon">
                                        @if($earnPlan->icon)
                                            <img src="{{ asset($earnPlan->icon) }}" alt="{{ $earnPlan->name }}" width="22" height="22" style="object-fit:contain" loading="lazy">
                                        @else
                                            <x-icon name="trending-up" height="18" width="18"/>
                                        @endif
                                    </div>
                                    <div class="lp-earn__card-title-wrap">
                                        <div class="lp-earn__card-name">{{ $earnPlan->name }}</div>
                                        <div class="lp-earn__card-tagline">
                                            {{ $earnPlan->description ?: __('Earn scheduled rewards while your principal stays locked.') }}
                                        </div>
                                    </div>
                                </div>

                                {{-- Rate --}}
                                <div class="lp-earn__rate">
                                    <span class="lp-earn__rate-value">{{ number_format((float) $earnPlan->profit_rate, 2) }}</span>
                                    <span class="lp-earn__rate-pct">%</span>
                                    <span class="lp-earn__rate-label">{{ $earnPlan->profit_type->label() }}</span>
                                </div>

                                {{-- Pills --}}
                                <div class="lp-earn__pills">
                                    <span class="lp-earn__pill">{{ $currencyLabel }}</span>
                                    <span class="lp-earn__pill lp-earn__pill--muted">{{ $earnPlan->durationLabel() }}</span>
                                    <span class="lp-earn__pill lp-earn__pill--muted">{{ $earnPlan->payout_frequency->label() }}</span>
                                </div>

                            </div>

                            {{-- Divider --}}
                            <div class="lp-earn__divider"></div>

                            {{-- Body: details + bullets --}}
                            <div class="lp-earn__card-body">

                                <div class="lp-earn__details">
                                    <div class="lp-earn__detail">
                                        <i class="fas fa-lock"></i>
                                        <span class="lp-earn__detail-val">{{ $earnPlan->amountRangeLabel() }}</span>
                                        @if($earnPlan->currency_id)
                                            <span class="lp-earn__detail-sub">{{ $earnPlan->currency->code }}</span>
                                        @endif
                                    </div>
                                    <div class="lp-earn__detail">
                                        <i class="fas fa-sync"></i>
                                        <span class="lp-earn__detail-val">{{ $earnPlan->payout_frequency->label() }}</span>
                                        <span class="lp-earn__detail-sub">{{ __('payout') }}</span>
                                    </div>
                                    <div class="lp-earn__detail">
                                        <i class="fas fa-shield-alt"></i>
                                        <span class="lp-earn__detail-val">{{ $earnPlan->return_principal ? __('Returned') : __('Not returned') }}</span>
                                        <span class="lp-earn__detail-sub">{{ __('principal') }}</span>
                                    </div>
                                    <div class="lp-earn__detail">
                                        <i class="fas fa-bolt"></i>
                                        <span class="lp-earn__detail-val">{{ $earnPlan->auto_approve ? __('Auto') : __('Manual') }}</span>
                                        <span class="lp-earn__detail-sub">{{ __('approval') }}</span>
                                    </div>
                                </div>

                                <div class="lp-earn__bullets">
                                    <div class="lp-earn__bullet">
                                        <span class="lp-earn__bullet-check"><i class="fas fa-check"></i></span>
                                        {{ $earnPlan->currency_id ? $earnPlan->currency->code . ' ' . __('only') : __('All currencies') }}
                                    </div>
                                    <div class="lp-earn__bullet">
                                        <span class="lp-earn__bullet-check"><i class="fas fa-check"></i></span>
                                        {{ $earnPlan->auto_approve ? __('Auto-approved') : __('Manual review') }}
                                    </div>
                                    <div class="lp-earn__bullet">
                                        <span class="lp-earn__bullet-check"><i class="fas fa-check"></i></span>
                                        {{ $earnPlan->return_principal ? __('Principal returned') : __('Principal locked') }}
                                    </div>
                                </div>

                            </div>

                            {{-- CTA --}}
                            <div class="lp-earn__card-cta">
                                <a href="{{ route('user.wallet-earn.plans') }}" class="lp-earn__btn">
                                    {{ __('Start Earning') }} <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>

                        </article>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

</section>
