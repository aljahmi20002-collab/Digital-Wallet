@php use App\Enums\MethodType; @endphp
@extends('general.merchant.index')
@section('favicon', asset($data['business_logo']))
@section('title', __('Payment Checkout'))
@section('merchant_content')
    <div class="payment-link-checkout-wrap">
        <div class="card border-0 p-3 p-md-4 w-100 payment-link-card payment-checkout-card">
            <div class="payment-checkout-header mb-3">
                <div class="payment-checkout-merchant">
                    <div class="payment-checkout-logo-box">
                        <img src="{{ asset($data['business_logo'] ?? setting('logo')) }}"
                             alt="{{ $data['business_name'] ?? setting('site_title') }}" loading="lazy">
                    </div>
                    <div class="payment-checkout-merchant-copy">
                        <span class="payment-checkout-eyebrow">@lang('Pay to')</span>
                        <h6 class="payment-checkout-merchant-name">
                            {{ $data['business_name'] }}
                            @if($data['is_sandbox'] ?? false)
                                <span class="badge bg-warning ms-2">@lang('TEST MODE')</span>
                            @endif
                        </h6>
                        @if(! empty($data['description']))
                            <div class="payment-checkout-site">
                                <span>{{ $data['description'] }}</span>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="payment-checkout-amount">
                    <div class="payment-checkout-amount__icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <path d="M12 3l7 4v5c0 4.6-3 7.6-7 9-4-1.4-7-4.4-7-9V7l7-4z" fill="currentColor" opacity=".16"/>
                            <path d="M8.4 12.2l2.2 2.2 5-5.1" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <div class="payment-checkout-amount__copy">
                        <span>@lang('Amount Due')</span>
                        <strong>{{ $data['payment_amount'] }}</strong>
                    </div>
                </div>
            </div>

            <div class="payment-security-strip mb-3" aria-label="@lang('Checkout security')">
                <div class="payment-security-strip__seal">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M12 2l8 4v6c0 5-3.5 8.5-8 10-4.5-1.5-8-5-8-10V6l8-4z" fill="currentColor" opacity=".24"/>
                        <path d="M12 2l8 4v6c0 5-3.5 8.5-8 10-4.5-1.5-8-5-8-10V6l8-4z" fill="none" stroke="currentColor" stroke-width="1.6"/>
                        <path d="M8.6 12.4l2.3 2.3 4.8-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <div class="payment-security-strip__content">
                    <strong>@lang('Verified Secure Checkout')</strong>
                    <span>@lang('Encrypted session, protected payment flow, and verified merchant details.')</span>
                </div>
                <div class="payment-security-strip__badges">
                    <span>
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M7 11V8a5 5 0 0110 0v3" fill="none" stroke="currentColor" stroke-width="2"/>
                            <rect x="5" y="11" width="14" height="9" rx="2" fill="none" stroke="currentColor" stroke-width="2"/>
                        </svg>
                        @lang('SSL Secured')
                    </span>
                    <span>
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M5 12l4 4L19 6" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        @lang('Verified')
                    </span>
                </div>
            </div>

            @if($data['is_sandbox'] ?? false)
                <div class="alert alert-info border-0 test-payment-info mb-3 p-3">
                    <div class="d-flex align-items-start gap-3">
                        <span class="text-primary fs-5" aria-hidden="true">
                            <i class="fas fa-info-circle"></i>
                        </span>
                        <div class="w-100">
                            <h6 class="text-primary mb-1">@lang('Test Payment Info')</h6>
                            <p class="small text-muted mb-3">@lang('Use these credentials for testing')</p>
                            <div class="row g-2">
                                <div class="col-md-4">
                                    <div class="bg-white rounded p-2 border h-100">
                                        <strong class="text-primary small">@lang('Demo Wallet')</strong>
                                        <code class="d-block bg-light px-2 py-1 rounded mt-1">@lang('ID:') 123456789</code>
                                        <code class="d-block bg-light px-2 py-1 rounded mt-1">@lang('PIN:') 123456</code>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="bg-white rounded p-2 border h-100">
                                        <strong class="text-success small">@lang('Demo Voucher')</strong>
                                        <code class="d-block bg-light px-2 py-1 rounded mt-1">TESTVOUCHER</code>
                                        <small class="text-muted d-block mt-1">@lang('Instant redemption')</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="bg-white rounded p-2 border h-100">
                                        <strong class="text-info small">@lang('Gateway')</strong>
                                        <code class="d-block bg-light px-2 py-1 rounded mt-1">@lang('Auto Success')</code>
                                        <small class="text-muted d-block mt-1">@lang('Instant completion')</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <section aria-label="@lang('Payment Methods')">
                <h6 class="payment-checkout-section-title">
                    @lang('Choose Payment Method')
                </h6>

                <form id="paymentForm" action="{{ route('payment.process') }}" method="post">
                    @csrf
                    <input type="hidden" name="selected_method" id="selectedMethod">
                    <input type="hidden" name="trx_id" value="{{ $trxId }}">
                    <div class="payment-method-grid">
                        <button type="button"
                                class="payment-logo-card"
                                aria-pressed="false"
                                data-method="{{ MethodType::SYSTEM->value }}">
                            <span class="payment-method-check" aria-hidden="true"></span>
                            <img src="{{ asset(setting('logo')) }}" class="payment-logo"
                                 alt="{{ setting('site_title') }} Logo" loading="lazy">
                            <p class="payment-name">{{ __('Wallet PIN') }}</p>
                        </button>

                        @foreach($paymentMethods as $method)
                            <button type="button"
                                    class="payment-logo-card"
                                    aria-pressed="false"
                                    data-method="{{ $method->method_code }}">
                                <span class="payment-method-check" aria-hidden="true"></span>
                                <img src="{{ asset($method->logo_alt) }}" class="payment-logo"
                                     alt="{{ $method->name }} Logo" loading="lazy">
                                <p class="payment-name">{{ $method->name }}</p>
                            </button>
                        @endforeach
                    </div>

                    <div class="d-grid mt-3">
                        <button id="payButton" type="submit" class="btn btn-primary btn-lg fw-bold rounded-pill" disabled>
                            <i class="fas fa-arrow-right me-2"></i>
                            @lang('Continue to Pay :amount', ['amount' => $data['payment_amount']])
                        </button>
                    </div>
                </form>
            </section>

            <div class="payment-checkout-footer mt-3">
                <i class="fas fa-lock me-1"></i>
                <span>@lang('Secure checkout with 256-bit SSL encryption')</span>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        "use strict";

        document.addEventListener('DOMContentLoaded', function () {
            const isSandbox = {{ ($data['is_sandbox'] ?? false) ? 'true' : 'false' }};

            if (isSandbox) {
                console.log('%cSANDBOX MODE ACTIVE', 'color: #007bff; font-weight: bold; font-size: 14px;');
                console.log('Transaction ID:', '{{ $data['sandbox_transaction_id'] ?? $trxId }}');
                console.log('Environment:', '{{ $data['environment'] ?? 'sandbox' }}');
            }

            const paymentCards = document.querySelectorAll('.payment-logo-card');
            const payButton = document.getElementById('payButton');
            const selectedMethodInput = document.getElementById('selectedMethod');
            const paymentForm = document.getElementById('paymentForm');

            paymentCards.forEach(card => {
                card.addEventListener('click', function () {
                    paymentCards.forEach(item => {
                        item.classList.remove('active');
                        item.setAttribute('aria-pressed', 'false');
                    });

                    this.classList.add('active');
                    this.setAttribute('aria-pressed', 'true');

                    const selectedMethod = this.dataset.method;
                    selectedMethodInput.value = selectedMethod;
                    payButton.disabled = false;

                    if (isSandbox && selectedMethod !== '{{ MethodType::SYSTEM->value }}') {
                        console.log('Auto-completing sandbox gateway payment:', selectedMethod);
                        payButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>@lang("Auto Processing...")';
                        payButton.disabled = true;

                        setTimeout(() => {
                            paymentForm.submit();
                        }, 1000);
                    }
                });

                card.addEventListener('keydown', function (event) {
                    if (event.key === 'Enter' || event.key === ' ') {
                        event.preventDefault();
                        this.click();
                    }
                });
            });

            paymentForm.addEventListener('submit', function () {
                if (! payButton.innerHTML.includes('Auto Processing')) {
                    payButton.disabled = true;
                    payButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>@lang("Processing...")';
                }
            });
        });
    </script>
@endpush
