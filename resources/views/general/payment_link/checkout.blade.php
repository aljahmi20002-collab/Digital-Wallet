@php use App\Enums\MethodType; @endphp
@extends('general.merchant.index')
@section('favicon', asset(setting('site_favicon')))
@section('title', __('Pay :title', ['title' => $paymentLink->title]))
@section('merchant_content')
	@php
		$isOpenAmount = $paymentLink->isOpenAmount();
		$currency     = $paymentLink->currencyCode();
		$displayName  = $paymentLink->displayName();
		$displayLogo  = $paymentLink->displayLogo();
		$merchant     = $paymentLink->merchant;
		$hasMerchant  = $paymentLink->hasMerchantShop();
	@endphp
	
	<div class="payment-link-checkout-wrap">
		<div class="card border-0 p-3 p-md-4 w-100 payment-link-card payment-checkout-card">
			{{-- Header --}}
			<div class="payment-checkout-header mb-3">
				<div class="payment-checkout-merchant">
					<div class="payment-checkout-logo-box">
						@if($displayLogo)
							<img src="{{ asset($displayLogo) }}"
							     alt="{{ $displayName }}" loading="lazy">
						@else
							<img src="{{ asset(setting('logo')) }}"
							     alt="{{ setting('site_title') }}" loading="lazy">
						@endif
					</div>
					<div class="payment-checkout-merchant-copy">
						<span class="payment-checkout-eyebrow">{{ __('Pay to') }}</span>
						<h6 class="payment-checkout-merchant-name">{{ $displayName }}</h6>
						@if($hasMerchant && $merchant?->site_url)
							<div class="payment-checkout-site">
								<i class="fas fa-globe me-1"></i>
								<span>{{ $merchant->site_url }}</span>
							</div>
						@endif
					</div>
				</div>
				<div class="payment-checkout-currency">
					<span>{{ __('Currency') }}</span>
					<strong>{{ $currency }}</strong>
				</div>
			</div>
			
			<div class="payment-security-strip mb-3" aria-label="{{ __('Checkout security') }}">
				<div class="payment-security-strip__seal">
					<svg viewBox="0 0 24 24" aria-hidden="true">
						<path d="M12 2l8 4v6c0 5-3.5 8.5-8 10-4.5-1.5-8-5-8-10V6l8-4z" fill="currentColor" opacity=".24"/>
						<path d="M12 2l8 4v6c0 5-3.5 8.5-8 10-4.5-1.5-8-5-8-10V6l8-4z" fill="none" stroke="currentColor" stroke-width="1.6"/>
						<path d="M8.6 12.4l2.3 2.3 4.8-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>
				</div>
				<div class="payment-security-strip__content">
					<strong>{{ __('Verified Secure Checkout') }}</strong>
					<span>{{ __('Encrypted session, protected payment flow, and verified recipient details.') }}</span>
				</div>
				<div class="payment-security-strip__badges">
                    <span>
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M7 11V8a5 5 0 0110 0v3" fill="none" stroke="currentColor" stroke-width="2"/>
                            <rect x="5" y="11" width="14" height="9" rx="2" fill="none" stroke="currentColor" stroke-width="2"/>
                        </svg>
                        {{ __('SSL Secured') }}
                    </span>
					<span>
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M5 12l4 4L19 6" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        {{ __('Verified') }}
                    </span>
				</div>
			</div>
			
			<div class="payment-checkout-summary mb-3">
				<div>
					<h5 class="payment-checkout-title">{{ $paymentLink->title }}</h5>
					@if($paymentLink->description)
						<p class="payment-checkout-description">{{ $paymentLink->description }}</p>
					@endif
                </div>
                @if(! $isOpenAmount)
                    <div class="payment-checkout-amount">
                        <div class="payment-checkout-amount__icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24">
                                <path d="M12 3l7 4v5c0 4.6-3 7.6-7 9-4-1.4-7-4.4-7-9V7l7-4z" fill="currentColor" opacity=".16"/>
                                <path d="M8.4 12.2l2.2 2.2 5-5.1" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <div class="payment-checkout-amount__copy">
                            <span>{{ __('Amount Due') }}</span>
                            <strong>
                                <b>{{ number_format((float) $paymentLink->amount, 2) }}</b>
                                <em>{{ $currency }}</em>
                            </strong>
                        </div>
                    </div>
                @endif
            </div>
			
			@if($errors->any())
				<div class="alert alert-danger small">
					<ul class="m-0 ps-3">
						@foreach($errors->all() as $error)
							<li>{{ $error }}</li>
						@endforeach
					</ul>
				</div>
			@endif
			
			<form action="{{ route('payment-link.pay', $paymentLink->token) }}"
			      method="post"
			      novalidate
			      id="paymentLinkForm"
			      data-processing-label="{{ __('Processing...') }}">
				@csrf
				
				@if($isOpenAmount)
					<div class="mb-3">
						<label for="amount" class="form-label fw-semibold">
							{{ __('Amount to Pay') }} ({{ $currency }})
						</label>
						<div class="input-group shadow-sm rounded">
                            <span class="input-group-text bg-light">
                                <i class="fas fa-coins text-secondary"></i>
                            </span>
							<input type="number"
							       step="0.01"
							       min="{{ $paymentLink->min_amount ?? '0.01' }}"
							       @if($paymentLink->max_amount) max="{{ $paymentLink->max_amount }}" @endif
							       class="form-control border-0"
							       id="amount"
							       name="amount"
							       value="{{ old('amount') }}"
							       placeholder="{{ __('Enter amount') }}"
							       required>
						</div>
						@if($paymentLink->min_amount || $paymentLink->max_amount)
							<small class="text-muted">
								@if($paymentLink->min_amount && $paymentLink->max_amount)
									{{ __('Range: :min - :max :currency', ['min' => number_format((float) $paymentLink->min_amount, 2), 'max' => number_format((float) $paymentLink->max_amount, 2), 'currency' => $currency]) }}
								@elseif($paymentLink->min_amount)
									{{ __('Minimum: :min :currency', ['min' => number_format((float) $paymentLink->min_amount, 2), 'currency' => $currency]) }}
								@else
									{{ __('Maximum: :max :currency', ['max' => number_format((float) $paymentLink->max_amount, 2), 'currency' => $currency]) }}
								@endif
							</small>
						@endif
					</div>
				@else
					<input type="hidden" name="amount" value="{{ $paymentLink->amount }}">
				@endif
				
				<input type="hidden"
				       name="selected_method"
				       id="paymentLinkSelectedMethod"
				       value="{{ old('selected_method', MethodType::SYSTEM->value) }}">
				
				<div class="payment-checkout-fields mb-3">
					<div>
						<label for="customer_name" class="form-label fw-semibold">{{ __('Your Name (optional)') }}</label>
						<input type="text"
						       class="form-control"
						       id="customer_name"
						       name="customer_name"
						       value="{{ old('customer_name') }}"
						       maxlength="120">
					</div>
					<div>
						<label for="customer_email" class="form-label fw-semibold">{{ __('Email (optional)') }}</label>
						<input type="email"
						       class="form-control"
						       id="customer_email"
						       name="customer_email"
						       value="{{ old('customer_email') }}"
						       maxlength="160">
					</div>
				</div>
				
				<div class="border-top pt-3">
					<h6 class="payment-checkout-section-title">{{ __('Choose Payment Method') }}</h6>
					
					<div class="payment-method-grid mb-3" id="paymentLinkMethodCards">
						<button type="button"
						        class="payment-logo-card active"
						        aria-pressed="true"
						        data-payment-link-method="{{ MethodType::SYSTEM->value }}">
							<span class="payment-method-check" aria-hidden="true"></span>
							<img src="{{ asset(setting('logo')) }}"
							     class="payment-logo"
							     alt="{{ setting('site_title') }} Logo" loading="lazy">
							<p class="payment-name">{{ __('Wallet PIN') }}</p>
						</button>
						
						@foreach($paymentMethods as $method)
							<button type="button"
							        class="payment-logo-card"
							        aria-pressed="false"
							        data-payment-link-method="{{ $method->method_code }}">
								<span class="payment-method-check" aria-hidden="true"></span>
								<img src="{{ asset($method->logo_alt) }}"
								     class="payment-logo"
								     alt="{{ $method->name }} Logo" loading="lazy">
								<p class="payment-name">{{ $method->name }}</p>
							</button>
						@endforeach
					</div>
				</div>
				
				<div class="payment-wallet-panel" data-wallet-payment-fields>
					<h6 class="payment-checkout-section-title">{{ __('Pay With Wallet') }}</h6>
					
					@auth
						<div class="payment-wallet-account mb-3">
							<div class="payment-wallet-account__copy">
								<strong class="payment-wallet-account__name">{{ auth()->user()->name }}</strong>
								<div class="small text-muted">{{ __('Pay with your :site_title wallet.', ['site_title' => setting('site_title')]) }}</div>
							</div>
							<span class="badge bg-success">{{ __('Logged in') }}</span>
						</div>
						<input type="hidden" name="use_account" value="1" data-wallet-payment-input>
					@else
						<div class="mb-3">
							<label for="walletID" class="form-label fw-semibold">
								{{ __('Your :currency Wallet ID', ['currency' => $currency]) }}
							</label>
							<div class="input-group shadow-sm rounded">
								<span class="input-group-text bg-light"><i class="fas fa-wallet text-secondary"></i></span>
								<input id="walletID"
								       name="wallet_id"
								       type="text"
								       class="form-control border-0"
								       placeholder="{{ __('e.g. DK-USD-9F2A-E710') }}"
								       value="{{ old('wallet_id') }}"
								       data-wallet-payment-input
								       required>
							</div>
						</div>
					@endauth
					
					<div class="mb-3">
						<label for="walletPin" class="form-label fw-semibold">
							{{ __('Wallet PIN') }}
						</label>
						<div class="input-group shadow-sm rounded">
							<span class="input-group-text bg-light"><i class="fas fa-key text-secondary"></i></span>
							<input id="walletPin"
							       name="pin"
							       type="password"
							       inputmode="numeric"
							       maxlength="6"
							       pattern="[0-9]{6}"
							       autocomplete="off"
							       class="form-control border-0 wallet-pin-input"
							       placeholder="{{ __('6-digit PIN') }}"
							       data-wallet-payment-input
							       required>
						</div>
						<div class="payment-pin-help">
							<a href="{{ route('user.settings.wallet-pin') }}" class="payment-pin-help__link">
								<svg viewBox="0 0 24 24" aria-hidden="true">
									<path d="M15 7a4 4 0 10-3.4 3.95L4 18.55V21h2.45l1.1-1.1H10v-2.45l1.05-1.05H13.5l2.55-2.55A4 4 0 0015 7z" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
									<path d="M16.5 7.5h.01" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"/>
								</svg>
								<span>{{ __('Forgot PIN') }}</span>
							</a>
						</div>
					</div>
				</div>
				
				<div class="d-grid mt-3">
					<button type="submit" class="btn btn-primary btn-lg fw-bold rounded-pill" id="payNowBtn">
						<i class="fas fa-arrow-right me-2"></i>
						{{ __('Pay Now') }}
					</button>
				</div>
			</form>
			
			<div class="payment-checkout-footer mt-3"
			     id="paymentSecurityFooter"
			     data-wallet-label="{{ __('Secure wallet payment with 256-bit SSL encryption') }}"
			     data-gateway-label="{{ __('Secure gateway checkout with 256-bit SSL encryption') }}">
				<i class="fas fa-lock me-1"></i>
				<span>{{ __('Secure wallet payment with 256-bit SSL encryption') }}</span>
			</div>
		</div>
	</div>
@endsection
{{-- payment-links.js is loaded by the public layout (general/merchant/index.blade.php) --}}
