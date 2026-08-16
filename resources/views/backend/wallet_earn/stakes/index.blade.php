@extends('backend.wallet_earn.layout')

@section('title', __('Wallet Earn'))
@section('wallet_earn_title', __('Earn Dashboard'))
@section('wallet_earn_icon', 'trending-up')
@section('wallet_earn_subtitle', __('Monitor staking volume, pending reviews, active stakes, and paid rewards.'))

@section('wallet_earn_content')
    <div class="we-kpi-grid mb-3">
        <div class="we-kpi">
            <span class="we-kpi__icon"><x-icon name="lock" height="20" width="20"/></span>
            <div>
                <div class="we-kpi__label">@lang('Total Staked')</div>
                <div class="we-kpi__value">{{ number_format((float) $metrics['total_staked'], (int) setting('site_decimal', 2)) }}</div>
            </div>
        </div>
        <div class="we-kpi">
            <span class="we-kpi__icon we-kpi__icon--success"><x-icon name="running" height="20" width="20"/></span>
            <div>
                <div class="we-kpi__label">@lang('Active Stakes')</div>
                <div class="we-kpi__value">{{ $metrics['active'] }}</div>
            </div>
        </div>
        <div class="we-kpi">
            <span class="we-kpi__icon we-kpi__icon--warning"><x-icon name="clock" height="20" width="20"/></span>
            <div>
                <div class="we-kpi__label">@lang('Pending Review')</div>
                <div class="we-kpi__value">{{ $metrics['pending'] }}</div>
            </div>
        </div>
        <div class="we-kpi">
            <span class="we-kpi__icon we-kpi__icon--primary"><x-icon name="reward" height="20" width="20"/></span>
            <div>
                <div class="we-kpi__label">@lang('Rewards Paid')</div>
                <div class="we-kpi__value">{{ number_format((float) $metrics['rewards_paid'], (int) setting('site_decimal', 2)) }}</div>
            </div>
        </div>
    </div>

    <form method="GET" action="{{ route('admin.wallet-earn.index') }}" class="we-filter mb-3">
        <div class="we-filter__row">
            <div class="we-filter__field we-filter__field--user">
                <label class="form-label">@lang('User')</label>
                <input type="text" name="user" value="{{ request('user') }}" class="form-control" placeholder="{{ __('Email, username, or name') }}">
            </div>
            <div class="we-filter__field">
                <label class="form-label">@lang('Status')</label>
                <select name="status" class="form-select">
                    <option value="">@lang('All')</option>
                    @foreach($statuses as $value => $label)
                        <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="we-filter__field">
                <label class="form-label">@lang('Currency')</label>
                <select name="currency_id" class="form-select">
                    <option value="">@lang('All')</option>
                    @foreach($currencies as $currency)
                        <option value="{{ $currency->id }}" @selected((string) request('currency_id') === (string) $currency->id)>{{ $currency->code }}</option>
                    @endforeach
                </select>
            </div>
            <div class="we-filter__field">
                <label class="form-label">@lang('Plan')</label>
                <select name="plan_id" class="form-select">
                    <option value="">@lang('All')</option>
                    @foreach($plans as $plan)
                        <option value="{{ $plan->id }}" @selected((string) request('plan_id') === (string) $plan->id)>{{ $plan->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="we-filter__actions">
                <button type="submit" class="btn btn-primary">@lang('Filter')</button>
                <a href="{{ route('admin.wallet-earn.index') }}" class="btn btn-light">@lang('Reset')</a>
            </div>
        </div>
    </form>

    <div class="we-table-card">
        <div class="table-responsive">
            <table class="we-table">
                <thead>
                    <tr>
                        <th>@lang('User')</th>
                        <th>@lang('Plan')</th>
                        <th class="text-end">@lang('Principal')</th>
                        <th class="text-end">@lang('Paid Profit')</th>
                        <th>@lang('Schedule')</th>
                        <th>@lang('Status')</th>
                        <th class="text-end">@lang('Action')</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stakes as $stake)
                        <tr>
                            <td>
                                <div class="we-user">
                                    <span class="we-avatar">{{ strtoupper(substr($stake->user->first_name ?? 'U', 0, 1)) }}</span>
                                    <div>
                                        <div class="fw-semibold">{{ $stake->user->name }}</div>
                                        <div class="we-muted">{{ $stake->user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $stake->plan_name }}</div>
                                <div class="we-muted">{{ $stake->currency->code }} &middot; {{ $stake->payout_frequency->label() }}</div>
                            </td>
                            <td class="text-end fw-semibold">{{ number_format((float) $stake->principal_amount, (int) setting('site_decimal', 2)) }} {{ $stake->currency->code }}</td>
                            <td class="text-end">{{ number_format((float) $stake->paid_profit, (int) setting('site_decimal', 2)) }} {{ $stake->currency->code }}</td>
                            <td>
                                <div class="we-muted">@lang('Next')</div>
                                <div>{{ $stake->next_payout_at?->format('d M Y, h:i A') ?? __('Not scheduled') }}</div>
                            </td>
                            <td>
                                <span class="we-pill we-pill--{{ $stake->status->color() }}">{{ $stake->status->label() }}</span>
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-2">
                                    <a href="{{ route('admin.wallet-earn.stakes.show', $stake) }}" class="btn btn-outline-primary btn-sm">@lang('View')</a>
                                    @can('wallet-earn-manage')
                                        @if($stake->status === \App\Enums\WalletEarnStatus::Pending)
                                            <form action="{{ route('admin.wallet-earn.stakes.approve', $stake) }}" method="POST">
                                                @csrf
                                                <button class="btn btn-outline-success btn-sm">@lang('Approve')</button>
                                            </form>
                                        @endif
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <x-admin-not-found
                                    :title="__('No Wallet Earn stakes found')"
                                    :message="__('Wallet Earn stakes matching the current filters will appear here.')"
                                    icon="fa-lock"
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($stakes->hasPages())
            <div class="we-table-card__footer">
                {{ $stakes->links() }}
            </div>
        @endif
    </div>
@endsection
