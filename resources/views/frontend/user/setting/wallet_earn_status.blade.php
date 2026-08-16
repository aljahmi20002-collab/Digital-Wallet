@extends('frontend.user.setting.index')
@section('title', __('My Wallet Earn'))

@section('user_setting_content')
    @if($walletEarnSummary['active_count'] > 0)
        <section class="settings-current-plan settings-current-plan--earn">
            <div class="settings-current-plan__hero">
                <span class="settings-current-plan__icon" aria-hidden="true">
                    <i class="fas fa-chart-line"></i>
                </span>
                <div class="settings-current-plan__copy">
                    <span class="settings-current-plan__eyebrow">{{ __('Current active Wallet Earn') }}</span>
                    <h5 class="settings-current-plan__title">
                        {{ trans_choice(':count active position|:count active positions', $walletEarnSummary['active_count'], ['count' => $walletEarnSummary['active_count']]) }}
                    </h5>
                    <p class="settings-current-plan__text">
                        {{ __('Only active Wallet Earn positions are shown here to keep this settings view focused.') }}
                    </p>
                </div>
                <span class="settings-current-plan__badge settings-current-plan__badge--success">
                    <i class="fas fa-circle-check" aria-hidden="true"></i>
                    {{ __('Active') }}
                </span>
            </div>

            <div class="settings-current-plan__metrics">
                <div class="settings-current-plan__metric">
                    <span>{{ __('Principal') }}</span>
                    <strong>{{ number_format($walletEarnSummary['principal_amount'], 2) }}</strong>
                </div>
                <div class="settings-current-plan__metric">
                    <span>{{ __('Expected Profit') }}</span>
                    <strong>{{ number_format($walletEarnSummary['expected_profit'], 2) }}</strong>
                </div>
                <div class="settings-current-plan__metric">
                    <span>{{ __('Paid Profit') }}</span>
                    <strong>{{ number_format($walletEarnSummary['paid_profit'], 2) }}</strong>
                </div>
                <div class="settings-current-plan__metric">
                    <span>{{ __('Next Payout') }}</span>
                    <strong>{{ $walletEarnSummary['next_payout_at']?->format('d M Y') ?? __('Not scheduled') }}</strong>
                </div>
            </div>

            <div class="settings-active-list" aria-label="{{ __('Active Wallet Earn positions') }}">
                @foreach($activeStakes->take(4) as $stake)
                    <article class="settings-active-list__item">
                        <span class="settings-active-list__icon" aria-hidden="true">
                            <i class="fas fa-coins"></i>
                        </span>
                        <div class="settings-active-list__body">
                            <h6>{{ $stake->plan_name }}</h6>
                            <p>
                                {{ number_format((float) $stake->principal_amount, 2) }} {{ $stake->currency?->code ?? siteCurrency() }}
                                <span aria-hidden="true">&middot;</span>
                                {{ $stake->profit_rate }}{{ $stake->profit_type?->value === 'percentage' ? '%' : '' }}
                            </p>
                        </div>
                        <div class="settings-active-list__meta">
                            <strong>{{ $stake->matures_at?->format('d M Y') ?? __('Open') }}</strong>
                            <span>{{ __('Maturity') }}</span>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="settings-current-plan__footer">
                <span>
                    <i class="fas fa-filter" aria-hidden="true"></i>
                    {{ __('Inactive, completed, and pending stakes are hidden from this settings summary.') }}
                </span>
                <a href="{{ route('user.wallet-earn.stakes', ['status' => 'active']) }}" class="settings-current-plan__link">
                    {{ __('Open active stakes') }}
                    <i class="fas fa-arrow-right" aria-hidden="true"></i>
                </a>
            </div>
        </section>
    @else
        <x-user-not-found
            :title="__('No active Wallet Earn position')"
            :message="__('You do not have an active Wallet Earn stake right now. Active positions will appear here once approved or auto-started.')"
            icon="fa-chart-line"
            :action-url="route('user.wallet-earn.plans')"
            :action-label="__('View Earn Plans')"
            action-icon="fa-arrow-right"
        />
    @endif
@endsection
