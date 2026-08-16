@extends('frontend.layouts.user.index')
@section('title', __('QR Cash-Out'))
@push('styles')
	<link rel="stylesheet" href="{{ asset('frontend/css/agent.css?v=' . config('app.version') . '-' . filemtime(public_path('frontend/css/agent.css'))) }}">
@endpush
@section('content')
	@php
		$currencyCodes = $agent->supportedCurrencies->pluck('code')->implode(', ') ?: $agent->currency?->code;
		$supportedCurrencyLabels = $agent->supportedCurrencies->isNotEmpty()
			? $agent->supportedCurrencies->map(fn ($currency) => trim($currency->code.' - '.$currency->name))->values()
			: collect([$agent->currency?->code])->filter()->values();
	@endphp
	
	<div class="row">
		<div class="col-12">
			<div class="card single-form-card agent-service-card">
				<x-user-feature-header
					:title="__('Counter Cash-Out')"
					:subtitle="__('Debit your wallet securely at this approved counter and collect cash.')"
					icon="fas fa-money-bill-wave"
				>
					@can('agent')
						<div class="agent-qr-header-actions">
							<a class="btn btn-agent btn-sm" href="{{ route('user.agent.index', ['tab' => 'counter-cashout']) }}">
								<i class="fas fa-qrcode"></i> {{ __('View QR Code') }}
							</a>
							<a class="btn btn-light-agent btn-sm" href="{{ route('user.agent.index') }}">
								<i class="fas fa-briefcase"></i> {{ __('Agent Services') }}
							</a>
						</div>
					@endcan
				</x-user-feature-header>
				
				<div class="card-body">
					<div class="agent-qr-checkout agent-cashout-checkout">
						<section class="agent-qr-checkout__merchant agent-cashout-counter-panel">
							<div class="agent-cashout-counter-panel__badge">
								<i class="fa-solid fa-shield-check"></i>
								<span>{{ __('Approved Counter') }}</span>
							</div>
							
							<div class="agent-cashout-counter-panel__identity">
								<img src="{{ asset($agent->logo) }}" alt="{{ $agent->agent_name }}" loading="lazy">
								<div>
									<span>{{ __('Counter Name') }}</span>
									<h2>{{ $agent->agent_name }}</h2>
									<p>{{ __('Code') }} {{ $agent->agent_code }}</p>
								</div>
							</div>
							
							<div class="agent-cashout-wallet-strip">
								<span>{{ __('Supported Wallets') }}</span>
								<div>
									@foreach($supportedCurrencyLabels as $currencyLabel)
										<strong>{{ $currencyLabel }}</strong>
									@endforeach
								</div>
							</div>
							
							<div class="agent-cashout-checklist">
								<div class="agent-cashout-checklist__head">
									<i class="fa-solid fa-clipboard-check"></i>
									<strong>{{ __('Before You Confirm') }}</strong>
								</div>
								<ul>
									<li>
										<i class="fa-solid fa-circle-check"></i>
										<span>{{ __('Match the counter name before entering your PIN.') }}</span>
									</li>
									<li>
										<i class="fa-solid fa-circle-check"></i>
										<span>{{ __('Enter the exact cash amount you want to collect.') }}</span>
									</li>
									<li>
										<i class="fa-solid fa-circle-check"></i>
										<span>{{ __('Show the generated reference to the counter agent.') }}</span>
									</li>
								</ul>
							</div>
						</section>
						
						<section class="agent-qr-checkout__form agent-cashout-form-panel">
							@if($wallets->isEmpty())
								<x-user-not-found
									:title="__('No matching wallet')"
									:message="__('You need an active wallet in one of this agent counter currencies before cash-out.')"
									icon="fa-wallet"
									:action-url="route('user.wallet.index')"
									:action-label="__('Manage Wallets')"
									action-icon="fa-wallet"
								/>
							@else
								<div class="agent-cashout-form-panel__head">
                                    <span>
                                        <i class="fa-solid fa-wallet"></i>
                                    </span>
									<div>
										<h3>{{ __('Cash-Out Details') }}</h3>
										<p>{{ __('Choose your wallet, confirm the amount, then collect cash after the counter verifies your reference.') }}</p>
									</div>
								</div>
								
								<form action="{{ route('user.agent.qr.cash-out.store', $agent->qr_token) }}" method="POST" class="agent-qr-form agent-cashout-form">
									@csrf
									
									<div class="row g-3 agent-cashout-fields">
										<div class="col-12">
											<label class="form-label" for="qr_wallet_id">{{ __('Pay From Wallet') }}</label>
											<select class="form-select @error('wallet_id') is-invalid @enderror" id="qr_wallet_id" name="wallet_id" required>
												<option value="" disabled selected>{{ __('Select a supported wallet') }}</option>
												@foreach($wallets as $wallet)
													<option value="{{ $wallet->id }}" @selected(old('wallet_id') == $wallet->id)>
														{{ $wallet->currency?->code }} {{ __('Wallet') }}
														- {{ __('Balance') }} {{ getSymbol($wallet->currency?->code) }}{{ number_format((float) $wallet->balance, (int) setting('site_decimal', 2)) }}
													</option>
												@endforeach
											</select>
											@error('wallet_id')
											<div class="invalid-feedback">{{ $message }}</div>
											@enderror
										</div>
										
										<div class="col-12 col-md-6">
											<label class="form-label" for="qr_amount">{{ __('Cash-Out Amount') }}</label>
											<input
												type="text"
												class="form-control @error('amount') is-invalid @enderror"
												id="qr_amount"
												name="amount"
												value="{{ old('amount') }}"
												inputmode="decimal"
												oninput="this.value = validateDouble(this.value)"
												placeholder="{{ __('Enter amount') }}"
												required
											>
											@error('amount')
											<div class="invalid-feedback">{{ $message }}</div>
											@enderror
										</div>
										
										<div class="col-12 col-md-6">
											<label class="form-label" for="qr_wallet_pin">{{ __('Wallet PIN') }}</label>
											<input
												type="password"
												class="form-control @error('wallet_pin') is-invalid @enderror"
												id="qr_wallet_pin"
												name="wallet_pin"
												inputmode="numeric"
												maxlength="6"
												autocomplete="off"
												required
											>
											@error('wallet_pin')
											<div class="invalid-feedback">{{ $message }}</div>
											@enderror
										</div>
										
										<div class="col-12">
											<label class="form-label" for="qr_note">{{ __('Counter Note') }} <span>{{ __('Optional') }}</span></label>
											<input
												type="text"
												class="form-control @error('note') is-invalid @enderror"
												id="qr_note"
												name="note"
												value="{{ old('note') }}"
												maxlength="255"
												placeholder="{{ __('Optional counter note') }}"
											>
											@error('note')
											<div class="invalid-feedback">{{ $message }}</div>
											@enderror
										</div>
									</div>
									
									<div class="agent-qr-confirm agent-cashout-submit">
										<div class="agent-cashout-submit__copy">
											<i class="fa-solid fa-lock"></i>
											<div>
												<strong>{{ __('Ready to Collect Cash?') }}</strong>
												<span>{{ __('Your wallet will be debited after PIN verification.') }}</span>
											</div>
										</div>
										<button type="submit" class="btn btn-agent">
											<i class="fa-solid fa-shield-check me-1"></i>{{ __('Confirm Wallet Debit') }}
										</button>
									</div>
								</form>
							@endif
						</section>
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection
