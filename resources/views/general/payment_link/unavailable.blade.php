@extends('general.merchant.index')
@section('favicon', asset(setting('site_favicon')))
@section('title', __('Payment Link Not Available'))
@section('merchant_content')
    <div class="payment-link-unavailable-wrap">
        <section class="payment-link-unavailable" aria-labelledby="payment-link-unavailable-title">
            <div class="payment-link-unavailable__visual" aria-hidden="true">
                <span class="payment-link-unavailable__ring"></span>
                <span class="payment-link-unavailable__icon">
                    <i class="fas fa-link"></i>
                </span>
                <span class="payment-link-unavailable__slash"></span>
            </div>

            <div class="payment-link-unavailable__content">
                <span class="payment-link-unavailable__eyebrow">
                    <i class="fas fa-circle-info"></i>
                    {{ __('Checkout unavailable') }}
                </span>
                <h1 id="payment-link-unavailable-title" class="payment-link-unavailable__title">
                    {{ __('Payment Link Not Available') }}
                </h1>
                <p class="payment-link-unavailable__text">
                    {{ $message ?: __('This payment link may have expired, been disabled, or reached its payment limit.') }}
                </p>
            </div>

            <div class="payment-link-unavailable__panel">
                <div class="payment-link-unavailable__panel-item">
                    <span class="payment-link-unavailable__panel-icon">
                        <i class="fas fa-clock"></i>
                    </span>
                    <span>{{ __('The link may have expired.') }}</span>
                </div>
                <div class="payment-link-unavailable__panel-item">
                    <span class="payment-link-unavailable__panel-icon">
                        <i class="fas fa-lock"></i>
                    </span>
                    <span>{{ __('The receiver may have paused this checkout.') }}</span>
                </div>
                <div class="payment-link-unavailable__panel-item">
                    <span class="payment-link-unavailable__panel-icon">
                        <i class="fas fa-check-circle"></i>
                    </span>
                    <span>{{ __('The payment limit may already be reached.') }}</span>
                </div>
            </div>

            <div class="payment-link-unavailable__actions">
                <a href="{{ route('home') }}" class="btn payment-link-unavailable__primary">
                    <i class="fas fa-home"></i>
                    {{ __('Back to Home') }}
                </a>
            </div>
        </section>
    </div>
@endsection
