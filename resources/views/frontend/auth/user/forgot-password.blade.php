@extends('frontend.layouts.auth')
@section('title', __('User Forgot Password'))
@push('styles')
    <link rel="stylesheet" href="{{ asset('frontend/css/agent.css?v=' . config('app.version') . '-' . filemtime(public_path('frontend/css/agent.css'))) }}">
@endpush
@section('auth-content')
    <div class="auth-shell">
        <div class="auth-card has-accent is-user">

            <div class="auth-head">
                <img src="{{ asset(setting('logo')) }}" alt="Logo" class="auth-logo" loading="lazy">
                <h4 class="auth-title">{{ __('User Password Reset') }}</h4>
                <p class="auth-subtitle">@lang("Enter your user account email — we'll send a secure reset link.")</p>
                <span class="auth-role-badge is-user">
                    <i class="fa-duotone fa-user"></i> {{ __('User Account') }}
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

            <form action="{{ route('user.password.email') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="email" class="form-label fw-semibold">@lang('Email Address')</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                        <input type="email" id="email" name="email" class="form-control"
                               placeholder="@lang('Enter your user email')" required autocomplete="email">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-submit-auth">
                    <i class="fa-light fa-paper-plane me-1"></i> @lang('Send Reset Link')
                </button>
            </form>

            <p class="text-center mt-4 mb-2">
                @lang("Remembered it?")
                <a href="{{ route('user.login') }}" class="text-decoration-none text-primary fw-semibold">
                    @lang('Return to User Login')
                </a>
            </p>

            @include('frontend.auth.partials._portal_switch', [
                'current' => 'user',
                'page'    => 'password.request',
                'heading' => __('Reset password for another portal'),
            ])
        </div>
    </div>
@endsection
