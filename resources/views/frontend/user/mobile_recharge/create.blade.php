@extends('frontend.layouts.user.index')
@section('title', __('Mobile Recharge'))

@section('content')
    @include('frontend.user.partials._feature_summary_statistics', ['trxType' => \App\Enums\TrxType::MOBILE_RECHARGE])

    <div class="row">
        <div class="col-lg-12 col-xl-7">
            <div class="single-form-card">
                <x-user-feature-header
                    :title="__('Mobile Recharge')"
                    :subtitle="__('Recharge a mobile number directly from your wallet balance.')"
                    icon="fas fa-mobile-screen-button"
                >
                    <a class="btn btn-light-success btn-sm" href="{{ route('user.transaction.index', ['type' => \App\Enums\TrxType::MOBILE_RECHARGE]) }}">
                        <i class="fas fa-list"></i> {{ __('History') }}
                    </a>
                </x-user-feature-header>

                <div class="card-main">
                    @if($wallets->isEmpty())
                        <x-user-not-found
                            :title="__('No wallet available')"
                            :message="__('Create or activate a wallet before using mobile recharge.')"
                            icon="fa-wallet"
                            :action-url="route('user.wallet.index')"
                            :action-label="__('Manage Wallets')"
                            action-icon="fa-wallet"
                        />
                    @else
                        @php
                            $rechargeFeeFixed = (float) setting('mobile_recharge_fee_fixed', config('mobile_services.recharge.fee_fixed', 0));
                            $rechargeFeePercent = (float) setting('mobile_recharge_fee_percent', config('mobile_services.recharge.fee_percent', 0));
                            $rechargeProvider = $activeProvider ?? null;
                            if ($rechargeProvider) {
                                $rechargeFeeFixed = (float) ($rechargeProvider->fee_fixed ?? $rechargeFeeFixed);
                                $rechargeFeePercent = (float) ($rechargeProvider->fee_percent ?? $rechargeFeePercent);
                            }
                        @endphp
                        <form action="{{ route('user.mobile-recharge.store') }}" method="POST"
                              data-mobile-recharge-form
                              data-fee-fixed="{{ $rechargeFeeFixed }}"
                              data-fee-percent="{{ $rechargeFeePercent }}"
                              data-default-currency="{{ siteCurrency() }}"
                              onsubmit="disableSubmitButton(this, '{{ __('Processing...') }}')">
                            @csrf
                            @if($rechargeProvider)
                                <span class="mobile-recharge-provider-pill mb-3">
                                    <i class="fa-solid fa-plug"></i>
                                    {{ __('Provider: :name', ['name' => $rechargeProvider->name]) }}
                                </span>
                            @endif

                            <div class="single-select-inner style-border">
                                <label>{{ __('Wallet') }}</label>
                                <select class="form-select mobile-recharge-wallet" name="wallet_id" required>
                                    <option value="">{{ __('Select Wallet') }}</option>
                                    @foreach($wallets as $wallet)
                                        <option
                                            value="{{ $wallet->id }}"
                                            data-balance="{{ $wallet->balance }}"
                                            data-currency="{{ $wallet->currency->code }}"
                                            @selected(old('wallet_id') == $wallet->id)
                                        >
                                            {{ $wallet->currency->code }} - {{ number_format($wallet->balance, 2) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="single-input-inner style-border mt-3">
                                <label class="form-label">{{ __('Mobile Number') }}</label>
                                <input type="text" class="form-control" name="phone_number" value="{{ old('phone_number', auth()->user()->phone) }}" placeholder="{{ __('Enter mobile number') }}" required>
                            </div>

                            <div class="single-input-inner style-border mt-3">
                                <label class="form-label">{{ __('Operator (Optional)') }}</label>
                                <input type="text" class="form-control" name="operator" value="{{ old('operator') }}" placeholder="{{ __('Example: Grameenphone, Robi, Airtel') }}">
                            </div>

                            <div class="single-input-inner style-border mb-0 mt-3">
                                <label class="form-label">{{ __('Amount') }}</label>
                                <div class="input-group">
                                    <input type="text" class="form-control mobile-recharge-amount" name="amount" value="{{ old('amount') }}" placeholder="{{ __('Enter Amount') }}" oninput="this.value = validateDouble(this.value)" required>
                                    <span class="input-group-text mobile-recharge-currency">{{ siteCurrency() }}</span>
                                </div>
                                <span class="small color-base fw-500">
                                    {{ __('Min: :min | Max: :max', ['min' => setting('mobile_recharge_min_amount', config('mobile_services.recharge.min_amount')), 'max' => setting('mobile_recharge_max_amount', config('mobile_services.recharge.max_amount'))]) }}
                                </span>
                            </div>

                            <button type="submit" class="btn btn-base w-100 mt-4 mobile-recharge-submit">
                                <x-icon name="check" height="20" />
                                {{ __('Recharge Now') }}
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-12 col-xl-5">
            <div class="single-form-card">
                <x-user-feature-header
                    :title="__('Summary')"
                    :subtitle="__('Check wallet balance, fee, and total debit before confirming.')"
                    icon="fas fa-chart-pie"
                    compact
                />
                <div class="card-main">
                    <ul class="summery-list list-unstyled">
                        <li class="d-flex justify-content-between">
                            <span>{{ __('Wallet Balance') }}</span>
                            <span class="mobile-recharge-summary-balance">-</span>
                        </li>
                        <li class="d-flex justify-content-between">
                            <span>{{ __('Amount') }}</span>
                            <span class="mobile-recharge-summary-amount">-</span>
                        </li>
                        <li class="d-flex justify-content-between">
                            <span>{{ __('Charge') }}</span>
                            <span class="mobile-recharge-summary-fee">-</span>
                        </li>
                        <li class="d-flex justify-content-between">
                            <strong>{{ __('Total Debit') }}</strong>
                            <strong class="mobile-recharge-summary-total">-</strong>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="single-form-card mt-3">
                <x-user-feature-header
                    :title="__('Recent Recharges')"
                    :subtitle="__('Your latest mobile top-up requests.')"
                    icon="fas fa-clock"
                    compact
                />
                <div class="card-main">
                    @if($recentRecharges->isEmpty())
                        <x-user-not-found
                            :title="__('No recharge yet')"
                            :message="__('Your mobile recharge history will appear here after the first top-up.')"
                            icon="fa-mobile-screen-button"
                        />
                    @else
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                    <tr>
                                        <th>{{ __('Phone') }}</th>
                                        <th>{{ __('Amount') }}</th>
                                        <th>{{ __('Status') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentRecharges as $recharge)
                                        <tr>
                                            <td>
                                                <div class="fw-semibold">{{ $recharge->phone_number }}</div>
                                                <div class="small text-muted">{{ $recharge->operator ?: __('Any operator') }}</div>
                                            </td>
                                            <td>{{ number_format($recharge->amount, 2) }} {{ $recharge->currency }}</td>
                                            <td>
                                                <span class="badge bg-{{ $recharge->status->color() }}">
                                                    {{ $recharge->status->label() }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('frontend/css/mobile-recharge.css?v=' . config('app.version')) }}">
@endpush

@push('scripts')
    <script src="{{ asset('frontend/js/mobile-recharge.js?v=' . config('app.version')) }}"></script>
@endpush
