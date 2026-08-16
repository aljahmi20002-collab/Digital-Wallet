@extends('frontend.layouts.user.index')

@section('title', __('Wallet Earn Details'))

@push('styles')
    <link rel="stylesheet" href="{{ asset('frontend/css/wallet-earn.css?v=' . config('app.version')) }}">
@endpush

@section('content')
    <div class="user-dashboard wallet-earn-page">
        <div class="row">
            <div class="col-12">
                <div class="card single-form-card">
                    <x-user-feature-header
                        :title="__('Stake Details')"
                        :subtitle="__('Review payout progress, maturity, and reward history.')"
                        icon="fas fa-lock"
                    >
                        <a href="{{ route('user.wallet-earn.stakes') }}" class="btn btn-light-primary btn-sm">
                            <i class="fas fa-arrow-left"></i> {{ __('Back') }}
                        </a>
                    </x-user-feature-header>

                    <div class="card-body">
                        <div class="we-detail-hero">
                            <div>
                                <span class="we-chip">{{ $stake->currency->code }}</span>
                                <h3>{{ $stake->plan_name }}</h3>
                                <p>{{ __('Started: :date', ['date' => $stake->starts_at?->format('d M Y, h:i A') ?? __('Pending approval')]) }}</p>
                            </div>
                            <span class="we-status we-status--{{ $stake->status->color() }}">{{ $stake->status->label() }}</span>
                        </div>

                        <div class="we-detail-grid mt-3">
                            <div>
                                <span>@lang('Principal')</span>
                                <strong>{{ number_format((float) $stake->principal_amount, (int) setting('site_decimal', 2)) }} {{ $stake->currency->code }}</strong>
                            </div>
                            <div>
                                <span>@lang('Expected Profit')</span>
                                <strong>{{ number_format((float) $stake->expected_profit, (int) setting('site_decimal', 2)) }} {{ $stake->currency->code }}</strong>
                            </div>
                            <div>
                                <span>@lang('Paid Profit')</span>
                                <strong>{{ number_format((float) $stake->paid_profit, (int) setting('site_decimal', 2)) }} {{ $stake->currency->code }}</strong>
                            </div>
                            <div>
                                <span>@lang('Next Payout')</span>
                                <strong>{{ $stake->next_payout_at?->format('d M Y, h:i A') ?? __('Not scheduled') }}</strong>
                            </div>
                            <div>
                                <span>@lang('Maturity')</span>
                                <strong>{{ $stake->matures_at?->format('d M Y, h:i A') ?? __('Pending') }}</strong>
                            </div>
                            <div>
                                <span>@lang('Payout Progress')</span>
                                <strong>{{ $stake->payouts_made }} / {{ $stake->total_payouts }}</strong>
                            </div>
                        </div>

                        <section class="we-user-section mt-4">
                            <div class="we-user-section__head">
                                <div>
                                    <h3>@lang('Reward History')</h3>
                                    <p>@lang('Every paid reward is recorded with its transaction reference.')</p>
                                </div>
                            </div>

                            <div class="we-stake-list">
                                @forelse($stake->rewards as $reward)
                                    <div class="we-stake-item">
                                        <span class="we-stake-item__icon"><x-icon name="reward" height="20" width="20"/></span>
                                        <span class="we-stake-item__body">
                                            <span class="we-stake-item__title">{{ __('Payout #:number', ['number' => $reward->payout_number]) }}</span>
                                            <span class="we-stake-item__meta">{{ $reward->transaction?->trx_id ?? __('Transaction pending') }}</span>
                                        </span>
                                        <span class="we-stake-item__amount text-success">
                                            +{{ number_format((float) $reward->amount, (int) setting('site_decimal', 2)) }} {{ $stake->currency->code }}
                                        </span>
                                    </div>
                                @empty
                                    <x-user-not-found
                                        class="mt-3"
                                        :title="__('No rewards paid yet')"
                                        :message="__('Reward entries will appear here once a payout is processed.')"
                                        icon="fa-clock"
                                    />
                                @endforelse
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
