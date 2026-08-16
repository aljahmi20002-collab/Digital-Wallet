@extends('backend.wallet_earn.layout')

@section('title', __('Wallet Earn Stake'))
@section('wallet_earn_title', __('Stake Details'))
@section('wallet_earn_icon', 'lock')
@section('wallet_earn_subtitle', __('Review principal, schedule, reward history, and admin actions.'))

@section('wallet_earn_action')
    <a href="{{ route('admin.wallet-earn.index') }}" class="btn btn-light">@lang('Back')</a>
@endsection

@section('wallet_earn_content')
    <div class="row g-3">
        <div class="col-xl-8">
            <div class="we-card">
                <div class="we-card__head">
                    <div>
                        <h2 class="we-card__title">{{ $stake->plan_name }}</h2>
                        <p class="we-card__subtitle">{{ $stake->currency->code }} @lang('stake') &middot; {{ $stake->status->label() }}</p>
                    </div>
                    <span class="we-pill we-pill--{{ $stake->status->color() }}">{{ $stake->status->label() }}</span>
                </div>
                <div class="we-card__body">
                    <dl class="we-dl">
                        <div>
                            <dt>@lang('User')</dt>
                            <dd>{{ $stake->user->name }}<span>{{ $stake->user->email }}</span></dd>
                        </div>
                        <div>
                            <dt>@lang('Wallet')</dt>
                            <dd>{{ $stake->wallet->uuid }}<span>{{ $stake->currency->code }}</span></dd>
                        </div>
                        <div>
                            <dt>@lang('Principal')</dt>
                            <dd>{{ number_format((float) $stake->principal_amount, (int) setting('site_decimal', 2)) }} {{ $stake->currency->code }}</dd>
                        </div>
                        <div>
                            <dt>@lang('Expected Profit')</dt>
                            <dd>{{ number_format((float) $stake->expected_profit, (int) setting('site_decimal', 2)) }} {{ $stake->currency->code }}</dd>
                        </div>
                        <div>
                            <dt>@lang('Paid Profit')</dt>
                            <dd>{{ number_format((float) $stake->paid_profit, (int) setting('site_decimal', 2)) }} {{ $stake->currency->code }}</dd>
                        </div>
                        <div>
                            <dt>@lang('Payouts')</dt>
                            <dd>{{ $stake->payouts_made }} / {{ $stake->total_payouts }}<span>{{ $stake->payout_frequency->label() }}</span></dd>
                        </div>
                        <div>
                            <dt>@lang('Started')</dt>
                            <dd>{{ $stake->starts_at?->format('d M Y, h:i A') ?? __('Pending') }}</dd>
                        </div>
                        <div>
                            <dt>@lang('Maturity')</dt>
                            <dd>{{ $stake->matures_at?->format('d M Y, h:i A') ?? __('Not scheduled') }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <div class="we-table-card mt-3">
                <div class="we-card__head">
                    <div>
                        <h2 class="we-card__title">@lang('Reward History')</h2>
                        <p class="we-card__subtitle">@lang('Paid reward entries linked to transactions.')</p>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="we-table">
                        <thead>
                            <tr>
                                <th>@lang('Payout')</th>
                                <th>@lang('Paid At')</th>
                                <th>@lang('Transaction')</th>
                                <th class="text-end">@lang('Amount')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($stake->rewards as $reward)
                                <tr>
                                    <td>#{{ $reward->payout_number }}</td>
                                    <td>{{ $reward->paid_at?->format('d M Y, h:i A') }}</td>
                                    <td>{{ $reward->transaction?->trx_id ?? __('N/A') }}</td>
                                    <td class="text-end fw-semibold">{{ number_format((float) $reward->amount, (int) setting('site_decimal', 2)) }} {{ $stake->currency->code }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4">
                                        <x-admin-not-found
                                            :title="__('No rewards paid yet')"
                                            :message="__('Reward payout records for this stake will appear here.')"
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
            @can('wallet-earn-manage')
                <div class="we-card">
                    <div class="we-card__head">
                        <div>
                            <h2 class="we-card__title">@lang('Admin Actions')</h2>
                            <p class="we-card__subtitle">@lang('Approve, reject, cancel, or complete this stake.')</p>
                        </div>
                    </div>
                    <div class="we-card__body">
                        <form class="d-grid gap-2" method="POST" action="{{ route('admin.wallet-earn.stakes.approve', $stake) }}">
                            @csrf
                            <textarea name="review_note" class="form-control" rows="3" placeholder="{{ __('Optional review note') }}">{{ old('review_note', $stake->review_note) }}</textarea>

                            @if($stake->status === \App\Enums\WalletEarnStatus::Pending)
                                <button class="btn btn-success">@lang('Approve Stake')</button>
                                <button class="btn btn-danger" formaction="{{ route('admin.wallet-earn.stakes.reject', $stake) }}">@lang('Reject & Refund')</button>
                            @elseif($stake->status === \App\Enums\WalletEarnStatus::Active)
                                <button class="btn btn-primary" formaction="{{ route('admin.wallet-earn.stakes.complete', $stake) }}">@lang('Complete Now')</button>
                                <button class="btn btn-outline-danger" formaction="{{ route('admin.wallet-earn.stakes.cancel', $stake) }}">@lang('Cancel & Refund')</button>
                            @else
                                <div class="we-notice">@lang('This stake is closed and no further action is available.')</div>
                            @endif
                        </form>
                    </div>
                </div>
            @endcan
        </div>
    </div>
@endsection
