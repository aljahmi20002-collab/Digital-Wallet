@extends('frontend.user.setting.index')
@section('title', __('My Subscription'))

@section('user_setting_content')
    @if($subscription)
        @php
            $plan = $subscription->plan;
            $status = $subscription->status;
            $daysRemaining = $subscription->daysRemaining();
            $renewalLabel = $subscription->billing_cycle?->isLifetime()
                ? __('Lifetime access')
                : ($subscription->current_period_end?->format('d M Y') ?? __('Not scheduled'));
        @endphp

        <section class="settings-current-plan settings-current-plan--subscription">
            <div class="settings-current-plan__hero">
                <span class="settings-current-plan__icon" aria-hidden="true">
                    <i class="fas fa-layer-group"></i>
                </span>
                <div class="settings-current-plan__copy">
                    <span class="settings-current-plan__eyebrow">{{ __('Current active plan') }}</span>
                    <h5 class="settings-current-plan__title">{{ $plan?->name ?? __('Active Subscription') }}</h5>
                    <p class="settings-current-plan__text">{{ $plan?->description ?? __('Your account currently has an active subscription.') }}</p>
                </div>
                <span class="settings-current-plan__badge settings-current-plan__badge--success">
                    <i class="fas fa-circle-check" aria-hidden="true"></i>
                    {{ $status?->label() ?? __('Active') }}
                </span>
            </div>

            <div class="settings-current-plan__metrics">
                <div class="settings-current-plan__metric">
                    <span>{{ __('Billing') }}</span>
                    <strong>{{ $subscription->billing_cycle?->label() ?? __('Current') }}</strong>
                </div>
                <div class="settings-current-plan__metric">
                    <span>{{ __('Paid') }}</span>
                    <strong>{{ number_format((float) $subscription->amount_paid, 2) }} {{ $subscription->currency_code }}</strong>
                </div>
                <div class="settings-current-plan__metric">
                    <span>{{ __('Renews / Ends') }}</span>
                    <strong>{{ $renewalLabel }}</strong>
                </div>
                <div class="settings-current-plan__metric">
                    <span>{{ __('Remaining') }}</span>
                    <strong>{{ $daysRemaining === null ? __('Unlimited') : trans_choice(':count day|:count days', $daysRemaining, ['count' => $daysRemaining]) }}</strong>
                </div>
            </div>

            @if($plan?->features?->isNotEmpty())
                <div class="settings-current-plan__features">
                    @foreach($plan->features->take(4) as $feature)
                        <span>
                            <i class="fas fa-check" aria-hidden="true"></i>
                            {{ $feature->feature_label }}
                        </span>
                    @endforeach
                </div>
            @endif

            <div class="settings-current-plan__footer">
                <span>
                    <i class="fas fa-shield-check" aria-hidden="true"></i>
                    {{ __('This summary only shows your currently active plan.') }}
                </span>
                <a href="{{ route('user.subscription.current') }}" class="settings-current-plan__link">
                    {{ __('Open full subscription') }}
                    <i class="fas fa-arrow-right" aria-hidden="true"></i>
                </a>
            </div>
        </section>
    @else
        <x-user-not-found
            :title="__('No active subscription')"
            :message="__('You do not have an active subscription right now. Choose a plan when you are ready to unlock subscription features.')"
            icon="fa-layer-group"
            :action-url="route('user.subscription.plans')"
            :action-label="__('Browse Plans')"
            action-icon="fa-arrow-right"
        />
    @endif
@endsection
