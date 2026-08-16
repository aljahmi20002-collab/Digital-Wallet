@extends('backend.auth.index')
@section('title', __('Forget Password'))
@section('auth-content')
    <div class="admin-auth-copy">
        <span class="admin-auth-eyebrow">{{ __('Password Recovery') }}</span>
        <h2 class="admin-auth-title">{{ __('Forgot your password?') }}</h2>
        <p class="admin-auth-subtitle">{{ __("Enter the email address associated with your admin account and we'll send you a secure reset link.") }}</p>
    </div>

    <form action="{{ route('admin.forget.password.submit') }}" method="post" class="admin-auth-form">
        @csrf
        <div class="admin-auth-field mb-3">
            <label for="email" class="form-label admin-auth-label">{{ __('Email Address') }}</label>
            <div class="input-group admin-auth-input-group">
                 <span class="input-group-text admin-auth-input-icon">
                      <i class="fa-solid fa-envelope"></i>
                </span>
                <input class="form-control admin-auth-input" id="email" type="email" name="email" value="{{ old('email') }}" placeholder="{{ __('Enter your admin email') }}" required>
            </div>
            @error('email')
                <div class="admin-auth-error">{{ $message }}</div>
            @enderror
        </div>

        <div class="admin-auth-helper mb-3">
            <a href="{{ route('admin.login-view') }}" class="admin-auth-link">{{ __('Return to login') }}</a>
        </div>

        <button class="btn btn-primary w-100 admin-auth-submit" type="submit"><x-icon name="login" class="icon"/> {{ __('Send Reset Link') }}</button>

        <div class="admin-auth-meta mt-4">
            <span class="admin-auth-meta__icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
            </span>
            <span>{{ __('Reset emails are only sent to valid admin accounts.') }}</span>
        </div>
    </form>
@endsection
