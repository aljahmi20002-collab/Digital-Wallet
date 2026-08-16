@extends('frontend.layouts.auth')
@section('title', __('Agent Reset Password'))
@push('styles')
    <link rel="stylesheet" href="{{ asset('frontend/css/agent.css?v=' . config('app.version') . '-' . filemtime(public_path('frontend/css/agent.css'))) }}">
@endpush
@section('auth-content')
    <div class="auth-shell agent-context">
        <div class="auth-card has-accent is-agent">

            <div class="auth-head">
                <img src="{{ asset(setting('logo')) }}" alt="Logo" class="auth-logo" loading="lazy">
                <h4 class="auth-title is-agent">{{ __('Set New Agent Password') }}</h4>
                <p class="auth-subtitle">@lang('Enter and confirm a new password for your agent account.')</p>
                <span class="auth-role-badge is-agent">
                    <i class="fa-duotone fa-user-tie"></i> {{ __('Agent Account') }}
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

            <form action="{{ route('agent.password.store') }}" method="POST">
                @csrf
                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                <div class="mb-3">
                    <label for="email" class="form-label fw-semibold">@lang('Email Address')</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                        <input type="email" id="email" name="email" class="form-control"
                               placeholder="@lang('Enter your agent email')"
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

                <button type="submit" class="btn btn-agent btn-submit-auth">
                    <i class="fa-light fa-key me-1"></i> @lang('Update Password')
                </button>
            </form>

            <p class="text-center mt-4 mb-2">
                @lang("Remembered it?")
                <a href="{{ route('agent.login') }}" class="text-decoration-none text-agent fw-semibold">
                    @lang('Return to Agent Login')
                </a>
            </p>

            @include('frontend.auth.partials._portal_switch', [
                'current' => 'agent',
                'page'    => 'login',
                'heading' => __('Switch portal'),
            ])
        </div>
    </div>
@endsection
