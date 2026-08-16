@extends('backend.auth.index')
@section('title', __('Two-Factor Authentication'))
@section('auth-content')
	<div class="admin-auth-copy">
		<span class="admin-auth-eyebrow">{{ __('Two-Factor Verification') }}</span>
		<h2 class="admin-auth-title">{{ __('Verify your identity') }}</h2>
		<p class="admin-auth-subtitle">{{ __('Enter the 6-digit code from your Google Authenticator app to complete your secure administrator login.') }}</p>
	</div>
	
	<form action="{{ route('admin.two-factor-challenge') }}" method="post" class="admin-auth-form">
		@csrf
		<div class="admin-auth-field mb-3">
			<label for="verification_code" class="form-label admin-auth-label">{{ __('Authentication Code') }}</label>
			<div class="input-group admin-auth-input-group">
                <span class="input-group-text admin-auth-input-icon">
                    <i class="fas fa-shield"></i>
                </span>
				<input class="form-control admin-auth-input admin-auth-input--code" placeholder="••••••" id="verification_code"
				       type="password" name="verification_code"
				       title="{{ __('Enter the 6-digit code from your authenticator app') }}" required>
			</div>
			@error('verification_code')
			<div class="admin-auth-error">{{ $message }}</div>
			@enderror
		</div>
		
		<button class="btn btn-primary w-100 admin-auth-submit" type="submit">
			<i class="fas fa-check-circle"></i> {{ __('Verify & Proceed') }}
		</button>
		
		<div class="admin-auth-meta mt-4">
            <span class="admin-auth-meta__icon">
	              <x-icon icon="shield"/>
            </span>
			<span>{{ __('This additional verification keeps privileged access protected.') }}</span>
		</div>
	</form>
@endsection
