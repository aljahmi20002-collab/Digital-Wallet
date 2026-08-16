@extends('backend.subscription.layout')

@section('title', __('Subscription Details'))
@section('sub_title', __('Subscription #:id', ['id' => $subscription->id]))
@section('sub_icon', 'eye')
@section('sub_subtitle', $subscription->user->name . ' - ' . $subscription->plan->name)

@section('sub_action')
    <a href="{{ route('admin.subscription.user-subscriptions.index') }}"
       class="btn btn-light d-inline-flex align-items-center gap-1">
        <x-icon name="back" height="18" width="18"/>
        @lang('Back to List')
    </a>
@endsection

@section('sub_content')
    @php
        $user = $subscription->user;
        $plan = $subscription->plan;
        $billingCycleLabel = $subscription->billing_cycle?->label() ?? __('Custom');
        $isLifetime = (bool) $subscription->billing_cycle?->isLifetime();
        $amountPaid = $subscription->currency_code.' '.number_format($subscription->amount_paid, 2);
        $transactionsTotal = $subscription->transactions->sum('amount');
        $transactionsTotalFormatted = $subscription->currency_code.' '.number_format($transactionsTotal, 2);
        $daysRemaining = $subscription->daysRemaining();
        $periodStart = $subscription->current_period_start?->format('d M Y');
        $periodEnd = $subscription->current_period_end?->format('d M Y');
        $periodLabel = $isLifetime
            ? __('Lifetime access')
            : ($periodEnd ? __('Ends :date', ['date' => $periodEnd]) : __('No period set'));
        $periodMeta = $periodStart && $periodEnd
            ? __(':start to :end', ['start' => $periodStart, 'end' => $periodEnd])
            : __('Period details unavailable');
        $userInitials = collect(explode(' ', trim($user->name)))
            ->filter()
            ->take(2)
            ->map(fn (string $name): string => mb_substr($name, 0, 1))
            ->implode('') ?: 'U';
    @endphp

    <div class="sa-subscription-show">
        <div class="sa-subscription-hero mb-4">
            <div class="sa-subscription-hero__main">
                <div class="sa-subscription-hero__eyebrow">
                    <span class="sa-subscription-hero__icon">
                        <x-icon name="subscription" height="20" width="20"/>
                    </span>
                    @lang('Subscription Control')
                </div>
                <h2 class="sa-subscription-hero__title">{{ $plan->name }}</h2>
                <div class="sa-subscription-hero__meta">
                    <span>{{ $user->name }}</span>
                    <span>{{ $billingCycleLabel }}</span>
                    <span>{{ __('ID #:id', ['id' => $subscription->id]) }}</span>
                </div>
            </div>

            <div class="sa-subscription-hero__aside">
                <span class="sa-pill sa-pill--{{ $subscription->status->badgeColor() }} sa-pill--xl">
                    <x-icon :name="$subscription->status->icon()" height="14" width="14"/>
                    {{ $subscription->status->label() }}
                </span>

                @can('subscription-manage')
                    <div class="sa-action-stack">
                        @if(! $subscription->isActive())
                            <form method="POST" action="{{ route('admin.subscription.user-subscriptions.activate', $subscription) }}">
                                @csrf
                                <button type="submit" class="btn btn-success btn-sm d-inline-flex align-items-center gap-1">
                                    <x-icon name="check" height="14" width="14"/>
                                    @lang('Activate')
                                </button>
                            </form>
                        @endif

                        @if($subscription->isActive())
                            <form method="POST" action="{{ route('admin.subscription.user-subscriptions.cancel', $subscription) }}"
                                  onsubmit="return confirm('{{ __('Cancel this subscription?') }}')">
                                @csrf
                                <button type="submit" class="btn btn-outline-danger btn-sm d-inline-flex align-items-center gap-1">
                                    <x-icon name="close" height="14" width="14"/>
                                    @lang('Cancel')
                                </button>
                            </form>
                        @endif
                    </div>
                @endcan
            </div>
        </div>

        <div class="sa-show-stats mb-4">
            <div class="sa-show-stat">
                <span class="sa-show-stat__icon sa-show-stat__icon--primary">
                    <x-icon name="wallet" height="20" width="20"/>
                </span>
                <span class="sa-show-stat__label">@lang('Amount Paid')</span>
                <strong class="sa-show-stat__value">{{ $amountPaid }}</strong>
            </div>
            <div class="sa-show-stat">
                <span class="sa-show-stat__icon sa-show-stat__icon--info">
                    <x-icon name="calendar" height="20" width="20"/>
                </span>
                <span class="sa-show-stat__label">@lang('Billing Cycle')</span>
                <strong class="sa-show-stat__value">{{ $billingCycleLabel }}</strong>
            </div>
            <div class="sa-show-stat">
                <span class="sa-show-stat__icon sa-show-stat__icon--success">
                    <x-icon name="clock" height="20" width="20"/>
                </span>
                <span class="sa-show-stat__label">@lang('Access Window')</span>
                <strong class="sa-show-stat__value">
                    @if($isLifetime)
                        @lang('Lifetime')
                    @elseif(! is_null($daysRemaining))
                        {{ trans_choice(':count day left|:count days left', $daysRemaining, ['count' => $daysRemaining]) }}
                    @else
                        @lang('Pending')
                    @endif
                </strong>
            </div>
            <div class="sa-show-stat">
                <span class="sa-show-stat__icon sa-show-stat__icon--warning">
                    <x-icon name="transaction-2" height="20" width="20"/>
                </span>
                <span class="sa-show-stat__label">@lang('Payment Total')</span>
                <strong class="sa-show-stat__value">{{ $transactionsTotalFormatted }}</strong>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-xl-8">
                <div class="sa-card mb-4">
                    <div class="sa-card__head">
                        <div>
                            <span class="sa-card__title">@lang('Subscription Overview')</span>
                            <div class="sa-card__subtitle">@lang('Lifecycle, billing, and renewal state')</div>
                        </div>
                        <span class="sa-pill sa-pill--{{ $subscription->auto_renew ? 'success' : 'secondary' }}">
                            {{ $subscription->auto_renew ? __('Auto-renew on') : __('Auto-renew off') }}
                        </span>
                    </div>
                    <div class="sa-card__body">
                        <div class="sa-detail-grid">
                            <div class="sa-detail-item">
                                <span class="sa-detail-item__icon"><x-icon name="plan" height="18" width="18"/></span>
                                <span class="sa-detail-item__label">@lang('Plan')</span>
                                <strong class="sa-detail-item__value">{{ $plan->name }}</strong>
                                <span class="sa-detail-item__meta">{{ $billingCycleLabel }}</span>
                            </div>
                            <div class="sa-detail-item">
                                <span class="sa-detail-item__icon"><x-icon name="money" height="18" width="18"/></span>
                                <span class="sa-detail-item__label">@lang('Amount Paid')</span>
                                <strong class="sa-detail-item__value">{{ $amountPaid }}</strong>
                                <span class="sa-detail-item__meta">{{ __('Recorded on subscription') }}</span>
                            </div>
                            <div class="sa-detail-item">
                                <span class="sa-detail-item__icon"><x-icon name="calendar" height="18" width="18"/></span>
                                <span class="sa-detail-item__label">@lang('Started')</span>
                                <strong class="sa-detail-item__value">{{ $subscription->started_at?->format('d M Y, H:i') ?? __('Not started') }}</strong>
                                <span class="sa-detail-item__meta">{{ $subscription->created_at->format('d M Y, H:i') }}</span>
                            </div>
                            <div class="sa-detail-item">
                                <span class="sa-detail-item__icon"><x-icon name="schedule" height="18" width="18"/></span>
                                <span class="sa-detail-item__label">@lang('Current Period')</span>
                                <strong class="sa-detail-item__value">{{ $periodLabel }}</strong>
                                <span class="sa-detail-item__meta">{{ $periodMeta }}</span>
                            </div>
                            <div class="sa-detail-item">
                                <span class="sa-detail-item__icon"><x-icon name="notification" height="18" width="18"/></span>
                                <span class="sa-detail-item__label">@lang('Trial Ends')</span>
                                <strong class="sa-detail-item__value">{{ $subscription->trial_ends_at?->format('d M Y, H:i') ?? __('No trial') }}</strong>
                                <span class="sa-detail-item__meta">{{ __('Trial status tracking') }}</span>
                            </div>
                            <div class="sa-detail-item">
                                <span class="sa-detail-item__icon"><x-icon name="shield" height="18" width="18"/></span>
                                <span class="sa-detail-item__label">@lang('Grace Period Ends')</span>
                                <strong class="sa-detail-item__value">{{ $subscription->grace_ends_at?->format('d M Y, H:i') ?? __('No grace period') }}</strong>
                                <span class="sa-detail-item__meta">{{ __('Post-expiry access window') }}</span>
                            </div>
                            <div class="sa-detail-item">
                                <span class="sa-detail-item__icon"><x-icon name="wallet-payment" height="18" width="18"/></span>
                                <span class="sa-detail-item__label">@lang('Wallet Reference')</span>
                                <strong class="sa-detail-item__value">{{ $subscription->wallet_reference ?? __('Not available') }}</strong>
                                <span class="sa-detail-item__meta">{{ __('Payment ledger reference') }}</span>
                            </div>
                            <div class="sa-detail-item">
                                <span class="sa-detail-item__icon"><x-icon name="close" height="18" width="18"/></span>
                                <span class="sa-detail-item__label">@lang('Cancelled At')</span>
                                <strong class="sa-detail-item__value">{{ $subscription->cancelled_at?->format('d M Y, H:i') ?? __('Not cancelled') }}</strong>
                                <span class="sa-detail-item__meta">
                                    {{ $subscription->cancelled_by_admin ? __('Cancelled by admin') : __('No admin cancellation') }}
                                </span>
                            </div>
                        </div>

                        @if($subscription->notes)
                            <div class="sa-note-box mt-3">
                                <span class="sa-note-box__label">@lang('Admin Notes')</span>
                                <p>{{ $subscription->notes }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="sa-card">
                    <div class="sa-card__head">
                        <div>
                            <span class="sa-card__title">@lang('Payment History')</span>
                            <div class="sa-card__subtitle">
                                {{ trans_choice(':count transaction recorded|:count transactions recorded', $subscription->transactions->count(), ['count' => $subscription->transactions->count()]) }}
                            </div>
                        </div>
                        <span class="sa-pill sa-pill--primary">{{ $transactionsTotalFormatted }}</span>
                    </div>
                    <div class="table-responsive">
                        <table class="sa-table sa-payment-table">
                            <thead>
                                <tr>
                                    <th>@lang('Type')</th>
                                    <th>@lang('Amount')</th>
                                    <th>@lang('TRX ID')</th>
                                    <th>@lang('Status')</th>
                                    <th>@lang('Date')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($subscription->transactions as $trx)
                                    @php
                                        $transactionStatusColor = match ($trx->status) {
                                            'completed', 'success', 'paid' => 'success',
                                            'pending', 'processing' => 'warning',
                                            'failed', 'cancelled', 'refunded' => 'danger',
                                            default => 'secondary',
                                        };
                                    @endphp
                                    <tr>
                                        <td>
                                            <span class="sa-pill sa-pill--secondary">{{ __(ucfirst($trx->type)) }}</span>
                                        </td>
                                        <td>
                                            <span class="sa-payment-amount">{{ $trx->currency_code }} {{ number_format($trx->amount, 2) }}</span>
                                        </td>
                                        <td>
                                            <span class="sa-copy-code">{{ $trx->trx_id ?? __('Manual record') }}</span>
                                        </td>
                                        <td>
                                            <span class="sa-pill sa-pill--{{ $transactionStatusColor }}">{{ __(ucfirst($trx->status)) }}</span>
                                        </td>
                                        <td class="sa-nowrap sa-muted">{{ $trx->created_at->format('d M Y, H:i') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5">
                                            <x-admin-not-found
                                                :title="__('No payment records')"
                                                :message="__('Subscription payment records will appear here once payments are made.')"
                                                icon="fa-receipt"
                                            />
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="sa-card mb-4">
                    <div class="sa-card__head">
                        <span class="sa-card__title">@lang('Subscriber')</span>
                        <span class="sa-pill sa-pill--info">{{ '@'.$user->username }}</span>
                    </div>
                    <div class="sa-card__body">
                        <div class="sa-user-profile">
                            <div class="sa-user-profile__avatar">{{ $userInitials }}</div>
                            <div class="sa-user-profile__content">
                                <strong>{{ $user->name }}</strong>
                                <span>{{ $user->email }}</span>
                            </div>
                        </div>

                        <div class="sa-side-list mt-3">
                            <div class="sa-side-list__row">
                                <span>@lang('Username')</span>
                                <strong>{{ '@'.$user->username }}</strong>
                            </div>
                            <div class="sa-side-list__row">
                                <span>@lang('Joined')</span>
                                <strong>{{ $user->created_at?->format('d M Y') ?? __('N/A') }}</strong>
                            </div>
                            <div class="sa-side-list__row">
                                <span>@lang('Transactions')</span>
                                <strong>{{ $subscription->transactions->count() }}</strong>
                            </div>
                        </div>

                        <a href="{{ route('admin.user.manage', $user->username) }}"
                           class="btn btn-outline-primary btn-sm w-100 mt-3 d-inline-flex align-items-center justify-content-center gap-1">
                            <x-icon name="eye" height="14" width="14"/>
                            @lang('View Profile')
                        </a>
                    </div>
                </div>

                <div class="sa-card">
                    <div class="sa-card__head">
                        <div>
                            <span class="sa-card__title">@lang('Plan Features')</span>
                            <div class="sa-card__subtitle">{{ $plan->features->count() }} @lang('configured')</div>
                        </div>
                        @if($plan->is_featured)
                            <span class="sa-pill sa-pill--warning">
                                <x-icon name="star" height="13" width="13"/>
                                @lang('Featured')
                            </span>
                        @endif
                    </div>
                    <div class="sa-card__body">
                        <div class="sa-feature-list">
                            @forelse($plan->features as $feature)
                                <div class="sa-feature-list__item">
                                    <span class="sa-feature-list__icon">
                                        <x-icon name="{{ $feature->isToggle() ? 'check' : 'grid-check' }}" height="16" width="16"/>
                                    </span>
                                    <span class="sa-feature-list__content">
                                        <strong>{{ $feature->feature_label }}</strong>
                                        @if($feature->reset_cycle)
                                            <small>{{ __('Resets :cycle', ['cycle' => $feature->reset_cycle]) }}</small>
                                        @else
                                            <small>{{ __(ucfirst($feature->feature_type)) }}</small>
                                        @endif
                                    </span>
                                    <span class="sa-feature-list__value">
                                        @if($feature->isUnlimited())
                                            <span class="sa-pill sa-pill--success">@lang('Unlimited')</span>
                                        @elseif($feature->isToggle())
                                            <span class="sa-pill {{ $feature->isEnabled() ? 'sa-pill--success' : 'sa-pill--danger' }}">
                                                {{ $feature->isEnabled() ? __('On') : __('Off') }}
                                            </span>
                                        @else
                                            {{ $feature->feature_value }}
                                        @endif
                                    </span>
                                </div>
                            @empty
                                <x-admin-not-found
                                    :title="__('No features configured')"
                                    :message="__('Plan features will appear here once they are added to this subscription plan.')"
                                    icon="fa-list-check"
                                />
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
