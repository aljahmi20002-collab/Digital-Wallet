@extends('backend.auth.index')
@section('title', __('Login'))
@section('auth-content')
	<div class="admin-auth-copy">
		<span class="admin-auth-eyebrow">{{ __('Administrator Sign In') }}</span>
		<h2 class="admin-auth-title">{{ __('Welcome back') }}</h2>
		<p class="admin-auth-subtitle">{{ __('Sign in to access platform controls, merchant operations, security tools, and system settings.') }}</p>
	</div>
	
	<form action="{{ route('admin.login') }}" method="post" class="admin-auth-form">
		@csrf
		<div class="admin-auth-field mb-3">
			<label for="email" class="form-label admin-auth-label">{{ __('E-mail address') }}</label>
			<div class="input-group admin-auth-input-group">
                <span class="input-group-text admin-auth-input-icon">
                    <i class="fa-solid fa-envelope"></i>
                </span>
				<input class="form-control admin-auth-input" id="email" type="email"
				       name="email"
				       value="{{ old('email') }}"
				       placeholder="{{ __('Enter your admin email') }}" required
				       autocomplete="username">
			</div>
			@error('email')
			<div class="admin-auth-error">{{ $message }}</div>
			@enderror
		</div>
		
		<div class="admin-auth-field mb-3">
			<label for="password-field" class="form-label admin-auth-label">{{ __('Password') }}</label>
			<div class="input-group admin-auth-input-group">
                <span class="input-group-text admin-auth-input-icon">
                    <i class="fa-sharp fa-solid fa-lock"></i>
                </span>
				<input class="form-control admin-auth-input"
				       placeholder="{{ __('Enter your password') }}"
				       id="password-field" type="password" name="password" required
				       autocomplete="current-password">
			</div>
			@error('password')
			<div class="admin-auth-error">{{ $message }}</div>
			@enderror
		</div>
		
		<div class="row mb-3 align-items-center g-2">
			<div class="col-sm-6">
				<div class="form-check">
					<input class="form-check-input admin-auth-check" type="checkbox" id="remember" name="remember">
					<label class="form-check-label admin-auth-check-label" for="remember">
						{{ __('Remember Me') }}
					</label>
				</div>
			</div>
			
			<div class="col-sm-6 text-sm-end">
				<a href="{{ route('admin.forget.password.now') }}"
				   class="admin-auth-link">{{ __('Forgot password?') }}</a>
			</div>
		</div>
		
		@if(config('services.recaptcha.status'))
			<div class="admin-auth-recaptcha g-recaptcha mt-4 mb-4" data-sitekey="{{ config('services.recaptcha.key') }}"></div>
		@endif
		
		<button class="btn btn-primary w-100 admin-auth-submit" type="submit">
			<x-icon name="login" class="icon"/>
			{{ __('Sign In to Admin Panel') }}
		</button>
		
		<div class="admin-auth-meta mt-3">
            <span class="admin-auth-meta__icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect
		                x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
            </span>
			<span>{{ __('Protected by session security and optional verification checks.') }}</span>
		</div>
	</form>
@endsection
@push('scripts')
	<script async src="https://www.google.com/recaptcha/api.js"></script>
@endpush
