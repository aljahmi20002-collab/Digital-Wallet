@extends('general.merchant.index')
@section('favicon', asset(setting('site_favicon')))
@section('title', __('Payment Wallet'))
@section('merchant_content')
	<div class="container d-flex justify-content-center py-5">
		<div class="card shadow-sm border-0 rounded-4 p-4">
			{{-- Test Payment Information (Sandbox Only) --}}
			@if($data['is_sandbox'] ?? false)
				<div class="alert alert-info border-0 test-payment-info mb-4 p-3 p-md-4">
					<div class="row g-3">
						<div class="col-12">
							<h6 class="text-primary d-flex align-items-center mb-2">
								<i class="fas fa-info-circle me-2"></i>
								@lang('Test Payment Info')
								<span class="badge bg-warning ms-2">
                                    @lang('TEST MODE')
                                </span>
							</h6>
							<p class="small text-muted mb-0">
								@lang('Use these credentials for testing')
							</p>
						</div>
						
						<div class="col-12 col-md-6">
							<div class="card shadow-sm border-0 h-100">
								<div class="card-body p-3">
									<strong class="text-primary small">@lang('Demo Wallet:')</strong>
									<div class="mt-2">
										<code class="d-block bg-light px-2 py-1 rounded mb-1 w-100">@lang('ID:'): 123456789</code>
										<code class="d-block bg-light px-2 py-1 rounded w-100">@lang('PIN:'): 123456</code>
									</div>
								</div>
							</div>
						</div>
						
						<div class="col-12 col-md-6">
							<div class="card shadow-sm border-0 h-100">
								<div class="card-body p-3">
									<strong class="text-success small">@lang('Demo Voucher:')</strong>
									<div class="mt-2">
										<code class="d-block bg-light px-2 py-1 rounded w-100">TESTVOUCHER</code>
									</div>
									<small class="text-muted d-block mt-2">@lang('Instant redemption')</small>
								</div>
							</div>
						</div>
					</div>
				</div>
			@endif
			
			{{-- Total Pay Section (Top Position) --}}
			<div class="bg-light p-3 rounded d-flex align-items-center justify-content-between border-dashed mb-4">
				<img src="{{ asset(setting('logo')) }}" alt="{{ setting('site_title') }}" class="img-fluid icon text-primary me-3 fs-3" loading="lazy">
				<div class="text-end">
					<small class="text-muted d-block">@lang('Total Payable Amount')</small>
					<h4 class="fw-bold text-primary m-0">
						{{ $data['payment_amount'] }}
						@if($data['is_sandbox'] ?? false)
							<small class="badge bg-secondary ms-2">@lang('TEST')</small>
						@endif
					</h4>
				</div>
			</div>
			
			{{-- Payment Options Navigation --}}
			<ul class="nav nav-pills nav-justified mb-4" id="paymentTab" role="tablist">
				<li class="nav-item" role="presentation">
					<button class="nav-link active" id="wallet-tab" data-bs-toggle="pill" data-bs-target="#wallet-pay" type="button" role="tab" aria-controls="wallet-pay" aria-selected="true">
						<i class="fas fa-wallet me-1"></i> @lang('Wallet Payment')
					</button>
				</li>
				<li class="nav-item" role="presentation">
					<button class="nav-link" id="voucher-tab" data-bs-toggle="pill" data-bs-target="#voucher-pay" type="button" role="tab" aria-controls="voucher-pay" aria-selected="false">
						<i class="fas fa-ticket-alt me-1"></i> @lang('Voucher Payment')
					</button>
				</li>
			</ul>
			
			<div class="tab-content" id="paymentTabContent">
				{{-- Wallet Payment Tab --}}
				<div class="tab-pane fade show active" id="wallet-pay" role="tabpanel" aria-labelledby="wallet-tab">
					<div class="text-center mb-3">
						<small class="text-muted d-block">
							@lang('Pay using your :site_title :wallet_name Wallet ID or log in for a faster checkout.', ['site_title' => setting('site_title'), 'wallet_name' => $data['currency']])
						</small>
						@auth
							<form action="{{ route('payment.with.account') }}" method="post" class="d-inline-flex align-items-center gap-2 flex-wrap justify-content-center" id="loginPayForm">
								@csrf
								<input type="hidden" name="trx_id" value="{{ $trxId }}">
								<input type="password"
								       name="pin"
								       inputmode="numeric"
								       maxlength="6"
								       pattern="[0-9]{6}"
								       autocomplete="off"
								       class="form-control form-control-sm wallet-pin-input"
								       placeholder="@lang('Wallet PIN')"
								       style="max-width: 140px;"
								       required>
								<button type="submit" class="text-primary border-0 bg-transparent fw-bold text-decoration-none" id="loginPayBtn">
									<i class="fa-light fa-fingerprint"></i>
									@lang('Login to Pay')
								</button>
							</form>
						@else
							@php $token = Payment::generateToken($trxId); @endphp
							<a href="{{ route('payment.with.account', ['token' => $token]) }}" class="text-primary fw-bold text-decoration-none" id="loginPayLink">
								<i class="fa-light fa-fingerprint"></i>
								@lang('Login to Pay')
							</a>
						@endauth
					</div>
					
					<form id="paymentForm" action="{{ route('payment.complete') }}" method="post" novalidate>
						@csrf
						<input type="hidden" name="trx_id" value="{{ $trxId }}">
						<div class="mb-3">
							<label for="walletID" class="form-label fw-semibold">
								@lang('Enter Your :wallet_name Wallet ID',['wallet_name' => $data['currency']])
							</label>
							<div class="input-group shadow-sm rounded">
                                <span class="input-group-text bg-light">
                                    <i class="fas fa-wallet text-secondary"></i>
                                </span>
								<input id="walletID" name="wallet_id" type="text" class="form-control border-0" placeholder="@lang('Wallet ID: DK-USD-9F2A-E710')"
								       required>
								@if($data['is_sandbox'] ?? false)
									<button type="button" class="btn btn-outline-secondary" id="fillDemoWallet" title="@lang('Fill demo wallet')">
										<i class="fas fa-magic"></i>
									</button>
								@endif
							</div>
						</div>
						<div class="mb-3">
							<label for="walletPin" class="form-label fw-semibold">
								@lang('Enter Wallet PIN')
							</label>
							<div class="input-group shadow-sm rounded">
                                <span class="input-group-text bg-light">
                                    <i class="fas fa-key text-secondary"></i>
                                </span>
								<input id="walletPin"
								       name="pin"
								       type="password"
								       inputmode="numeric"
								       maxlength="6"
								       pattern="[0-9]{6}"
								       autocomplete="off"
								       class="form-control border-0 wallet-pin-input"
								       placeholder="@lang('6-digit PIN')"
								       required>
								<button type="button" class="btn btn-light border-0" id="togglePassword" aria-label="@lang('Show/Hide PIN')">
									<i class="fas fa-eye"></i>
								</button>
							</div>
							<div class="mt-2 small text-muted text-end">
								<a href="{{ route('user.settings.wallet-pin') }}" class="text-decoration-none">
									@lang('Forgot PIN')
								</a>
							</div>
						</div>
						<div class="d-grid mt-4">
							<button id="payButton" type="submit" class="btn btn-dark fw-bold rounded-pill" disabled>
								<span class="spinner-border spinner-border-sm d-none" id="paySpinner"></span>
								<i class="fas fa-arrow-right me-2"></i>
								@lang('Proceed to Payment')
							</button>
						</div>
					</form>
				</div>
				
				{{-- Voucher Payment Tab --}}
				<div class="tab-pane fade" id="voucher-pay" role="tabpanel" aria-labelledby="voucher-tab">
					<div class="mb-3 text-center">
						<small class="text-muted">
							@lang('Pay instantly using a voucher code.')
						</small>
					</div>
					<form id="voucherForm" action="{{ route('payment.complete') }}" method="post" novalidate>
						@csrf
						<input type="hidden" name="trx_id" value="{{ $trxId }}">
						<div class="mb-3">
							<label for="voucherCode" class="form-label fw-semibold">
								@lang('Voucher Code')
							</label>
							<div class="input-group shadow-sm rounded">
                                <span class="input-group-text bg-light">
                                    <i class="fas fa-ticket-alt text-secondary"></i>
                                </span>
								<input id="voucherCode" name="voucher_code" type="text" class="form-control border-0" placeholder="@lang('Enter your voucher code')" required maxlength="32">
								@if($data['is_sandbox'] ?? false)
									<button type="button" class="btn btn-outline-secondary" id="fillDemoVoucher" title="@lang('Fill demo voucher')">
										<i class="fas fa-magic"></i>
									</button>
								@endif
							</div>
						</div>
						<div class="d-grid mt-4">
							<button id="voucherPayButton" type="submit" class="btn btn-success fw-bold rounded-pill">
								<i class="fas fa-arrow-right me-2"></i>
								@lang('Redeem & Pay')
							</button>
						</div>
					</form>
				</div>
			</div>
			
			{{-- Security Footer --}}
			<div class="text-center mt-4 small text-muted">
				<i class="fas fa-lock me-1"></i>
				@lang('Secure Wallet Payment with 256-bit SSL encryption')
			</div>
		</div>
	</div>

@endsection
@push('scripts')
	<script>
        "use strict";

        document.addEventListener('DOMContentLoaded', function () {
            const isSandbox = {{ ($data['is_sandbox'] ?? false) ? 'true' : 'false' }};

            // Add sandbox logging
            if (isSandbox) {
                console.log('%c🧪 SANDBOX WALLET PAYMENT ACTIVE', 'color: #007bff; font-weight: bold; font-size: 14px;');
                console.log('Transaction ID:', '{{ $data['sandbox_transaction_id'] ?? $trxId }}');
                console.log('Environment:', '{{ $data['environment'] ?? 'sandbox' }}');
                console.log('Currency:', '{{ $data['currency'] }}');
            }

            // Demo data fill buttons (sandbox only)
            if (isSandbox) {
                // Fill demo wallet credentials
                const fillDemoWalletBtn = document.getElementById('fillDemoWallet');
                if (fillDemoWalletBtn) {
                    fillDemoWalletBtn.addEventListener('click', function () {
                        const walletInput = document.getElementById('walletID');
                        const pinInput = document.getElementById('walletPin');
                        const payButton = document.getElementById('payButton');

                        if (walletInput && pinInput && payButton) {
                            walletInput.value = '123456789';
                            pinInput.value = '123456';
                            payButton.disabled = false;

                            // Add visual feedback
                            this.innerHTML = '<i class="fas fa-check text-success"></i>';
                            setTimeout(() => {
                                this.innerHTML = '<i class="fas fa-magic"></i>';
                            }, 2000);

                            console.log('🧪 Demo wallet credentials filled');
                        }
                    });
                }

                // Fill demo voucher
                const fillDemoVoucherBtn = document.getElementById('fillDemoVoucher');
                if (fillDemoVoucherBtn) {
                    fillDemoVoucherBtn.addEventListener('click', function () {
                        const voucherInput = document.getElementById('voucherCode');

                        if (voucherInput) {
                            voucherInput.value = 'TESTVOUCHER';

                            // Add visual feedback
                            this.innerHTML = '<i class="fas fa-check text-success"></i>';
                            setTimeout(() => {
                                this.innerHTML = '<i class="fas fa-magic"></i>';
                            }, 2000);

                            console.log('🧪 Demo voucher filled');
                        }
                    });
                }

                // Auto-payment for login (sandbox only)
                const loginPayForm = document.getElementById('loginPayForm');
                const loginPayLink = document.getElementById('loginPayLink');
                const loginPayBtn = document.getElementById('loginPayBtn');

                if (loginPayForm) {
                    loginPayForm.addEventListener('submit', function (e) {
                        console.log('🧪 Auto-completing sandbox login payment');
                        if (loginPayBtn) {
                            loginPayBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>@lang("Auto Processing...")';
                            loginPayBtn.disabled = true;
                        }
                    });
                }

                if (loginPayLink) {
                    loginPayLink.addEventListener('click', function (e) {
                        console.log('🧪 Auto-completing sandbox login payment via link');
                        this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>@lang("Auto Processing...")';
                    });
                }
            }

            // Force numeric-only on every PIN input
            document.querySelectorAll('.wallet-pin-input').forEach(function (input) {
                input.addEventListener('input', function () {
                    this.value = this.value.replace(/\D+/g, '').slice(0, 6);
                });
            });

            // Wallet PIN visibility toggle
            document.getElementById('togglePassword').addEventListener('click', function () {
                const pin = document.getElementById('walletPin');
                pin.type = pin.type === 'password' ? 'text' : 'password';
                this.querySelector('i').classList.toggle('fa-eye');
                this.querySelector('i').classList.toggle('fa-eye-slash');
            });

            // Enable pay button when wallet ID and a 6-digit PIN are present
            document.getElementById('paymentForm').addEventListener('input', function () {
                const wallet = document.getElementById('walletID').value.trim();
                const pin = document.getElementById('walletPin').value.trim();
                document.getElementById('payButton').disabled = !(wallet && pin.length === 6);
            });

            // Form submission handling
            document.getElementById('paymentForm').addEventListener('submit', function (e) {
                const btn = document.getElementById('payButton');
                btn.disabled = true;
                document.getElementById('paySpinner').classList.remove('d-none');

                if (isSandbox) {
                    console.log('🧪 Submitting sandbox wallet payment');
                    console.log('Wallet ID:', document.getElementById('walletID').value);
                }
            });

            // Voucher form submission logging
            document.getElementById('voucherForm').addEventListener('submit', function (e) {
                if (isSandbox) {
                    console.log('🧪 Submitting sandbox voucher payment');
                    console.log('Voucher Code:', document.getElementById('voucherCode').value);
                }
            });

            // Enhanced form validation for sandbox
            if (isSandbox) {
                const walletInput = document.getElementById('walletID');
                const pinInput = document.getElementById('walletPin');

                walletInput.addEventListener('input', function () {
                    if (this.value.length > 0) {
                        console.log('🧪 Wallet ID input:', this.value);
                    }
                });

                pinInput.addEventListener('input', function () {
                    if (this.value.length > 0) {
                        console.log('🧪 PIN input detected');
                    }
                });
            }
        });
	</script>
@endpush
