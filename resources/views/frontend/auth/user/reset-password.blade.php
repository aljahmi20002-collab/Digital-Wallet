@extends('frontend.layouts.auth')
@section('title', __('User Reset Password'))
@push('styles')
    <link rel="stylesheet" href="{{ asset('frontend/css/agent.css?v=' . config('app.version') . '-' . filemtime(public_path('frontend/css/agent.css'))) }}">
@endpush
@section('auth-content')
    <div class="auth-shell">
        <div class="auth-card has-accent is-user">

            <div class="auth-head">
                <img src="{{ asset(setting('logo')) }}" alt="Logo" class="auth-logo" loading="lazy">
                <h4 class="auth-title">{{ __('Set New User Password') }}</h4>
                <p class="auth-subtitle">@lang("Enter and confirm a new password for your user account.")</p>
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

            <form action="{{ route('user.password.store') }}" method="POST">
                @csrf
                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                <div class="mb-3">
                    <label for="email" class="form-label fw-semibold">@lang('Email Address')</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                        <input type="email" id="email" name="email" class="form-control"
                               placeholder="@lang('Enter your user email')"
                               value="{{ old('email', $request->email) }}" required autocomplete="email">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label fw-semibold">@lang('New Password')</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" id="password" name="password" class="form-control"
                               placeholder="@lang('Enter new password')" required autocomplete="new-password">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="password_confirmation" class="form-label fw-semibold">@lang('Confirm Password')</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-key"></i></span>
                        <input type="password" id="password_confirmation" name="password_confirmation" class="form-control"
                               placeholder="@lang('Confirm your password')" required autocomplete="new-password">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-submit-auth">
                    <i class="fa-light fa-key me-1"></i> @lang('Update Password')
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
                'page'    => 'login',
                'heading' => __('Switch portal'),
            ])
        </div>
    </div>
@endsection
