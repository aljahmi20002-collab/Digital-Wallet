@extends('backend.auth.index')
@section('title', __('Reset Password'))
@section('auth-content')
    <div class="admin-auth-copy">
        <span class="admin-auth-eyebrow">{{ __('Reset Credentials') }}</span>
        <h2 class="admin-auth-title">{{ __('Create a new password') }}</h2>
        <p class="admin-auth-subtitle">{{ __('Choose a strong admin password to restore secure access to your control panel account.') }}</p>
    </div>

    <form action="{{ route('admin.reset.password.submit') }}" method="post" class="admin-auth-form">
        @csrf

        <input type="hidden" name="token" value="{{ request('token') }}">
        <input type="hidden" name="email" value="{{ request('email') }}">

        <div class="admin-auth-field mb-3">
            <label for="password-field" class="form-label admin-auth-label">{{ __('New Password') }}</label>
            <div class="input-group admin-auth-input-group">
                <span class="input-group-text admin-auth-input-icon">
                    <i class="fa-sharp fa-solid fa-lock"></i>
                </span>
                <input class="form-control admin-auth-input"
                       placeholder="{{ __('Enter your new password') }}"
                       id="password-field" type="password" name="password" required>
            </div>
            @error('password')
                <div class="admin-auth-error">{{ $message }}</div>
            @enderror
        </div>

        <div class="admin-auth-field mb-3">
            <label for="password-confirmation-field" class="form-label admin-auth-label">{{ __('Confirm Password') }}</label>
            <div class="input-group admin-auth-input-group">
                <span class="input-group-text admin-auth-input-icon">
                    <i class="fa-sharp fa-solid fa-lock"></i>
                </span>
                <input class="form-control admin-auth-input"
                       placeholder="{{ __('Confirm your new password') }}"
                       id="password-confirmation-field" type="password" name="password_confirmation" required>
            </div>
            @error('password_confirmation')
                <div class="admin-auth-error">{{ $message }}</div>
            @enderror
        </div>

        <button class="btn btn-primary w-100 admin-auth-submit" type="submit">
            <x-icon name="login" class="icon"/> {{ __('Reset Password') }}
        </button>

        <div class="admin-auth-meta mt-4">
            <span class="admin-auth-meta__icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
            </span>
            <span>{{ __('Use a unique password to keep administrator access protected.') }}</span>
        </div>
    </form>
@endsection
