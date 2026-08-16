@extends('backend.auth.index')
@section('title', __('Account Locked'))
@section('auth-content')
    <div class="admin-auth-copy">
        <span class="admin-auth-eyebrow">{{ __('Session Locked') }}</span>
        <h2 class="admin-auth-title">{{ __('Unlock administrator session') }}</h2>
        <p class="admin-auth-subtitle">{{ __('Enter your password to restore access to the admin control surface.') }}</p>
    </div>

    <form action="{{ route('admin.lock-screen.unlock') }}" method="post" class="admin-auth-form">
        @csrf
        <div class="admin-auth-field mb-3">
            <label for="password-field" class="form-label admin-auth-label">{{ __('Password') }}</label>
            <div class="input-group admin-auth-input-group">
                <span class="input-group-text admin-auth-input-icon">
                    <x-icon name="cil-lock-locked" class="icon"/>
                </span>
                <input class="form-control admin-auth-input" placeholder="{{ __('Enter your password') }}" id="password-field" type="password" name="password" required>
            </div>
            @error('password')
                <div class="admin-auth-error">{{ $message }}</div>
            @enderror
        </div>

        <button class="btn btn-primary w-100 admin-auth-submit" type="submit"><x-icon name="unlock" class="icon"/> {{ __('Unlock Session') }}</button>

        <div class="admin-auth-meta mt-4">
            <span class="admin-auth-meta__icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
            </span>
            <span>{{ __('Your session stays protected until the correct password is entered.') }}</span>
        </div>
    </form>
@endsection
