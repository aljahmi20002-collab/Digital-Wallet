@extends('frontend.layouts.auth')
@section('title', __('Merchant Forgot Password'))
@push('styles')
    <link rel="stylesheet" href="{{ asset('frontend/css/agent.css?v=' . config('app.version') . '-' . filemtime(public_path('frontend/css/agent.css'))) }}">
@endpush
@section('auth-content')
    <div class="auth-shell">
        <div class="auth-card has-accent is-merchant">

            <div class="auth-head">
                <img src="{{ asset(setting('logo')) }}" alt="Logo" class="auth-logo" loading="lazy">
                <h4 class="auth-title">{{ __('Merchant Password Reset') }}</h4>
                <p class="auth-subtitle">@lang("Enter your merchant account email — we'll send a secure reset link.")</p>
                <span class="auth-role-badge is-merchant">
                    <i class="fa-duotone fa-store"></i> {{ __('Merchant Account') }}
                </span>
            </div>

            @if ($errors->any())
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    @foreach ($errors->all() as $error)
                        <strong>{{ $error }}</strong><br>
                    @endforeach
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <form action="{{ route('merchant.password.email') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="email" class="form-label fw-semibold">@lang('Email Address')</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                        <input type="email" id="email" name="email" class="form-control"
                               placeholder="@lang('Enter your merchant email')" required autocomplete="email">
                    </div>
                </div>

                <button type="submit" class="btn btn-success btn-submit-auth">
                    <i class="fa-light fa-paper-plane me-1"></i> @lang('Send Reset Link')
                </button>
            </form>

            <p class="text-center mt-4 mb-2">
                @lang("Remembered it?")
                <a href="{{ route('merchant.login') }}" class="text-decoration-none text-success fw-semibold">
                    @lang('Return to Merchant Login')
                </a>
            </p>

            @include('frontend.auth.partials._portal_switch', [
                'current' => 'merchant',
                'page'    => 'password.request',
                'heading' => __('Reset password for another portal'),
            ])
        </div>
    </div>
@endsection
