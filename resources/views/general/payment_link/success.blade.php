@extends('general.merchant.index')
@section('favicon', asset(setting('site_favicon')))
@section('title', __('Payment Successful'))
@section('merchant_content')
    <div class="container d-flex justify-content-center py-5">
        <div class="card shadow-sm border-0 rounded-4 p-4 text-center w-100 payment-link-card">
            <div class="mb-3">
                <i class="fas fa-check-circle fa-4x text-success"></i>
            </div>
            <h4 class="fw-bold mb-2">{{ __('Payment Successful') }}</h4>
            <p class="text-muted mb-4">
                {{ __('Thank you for paying ":title". The recipient has been notified.', ['title' => $paymentLink->title]) }}
            </p>

            <a href="{{ route('home') }}" class="btn btn-outline-primary rounded-pill">
                <i class="fas fa-home me-1"></i> {{ __('Back to Home') }}
            </a>
        </div>
    </div>
@endsection
