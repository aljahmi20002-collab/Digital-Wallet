@extends('frontend.user.setting.index')
@section('title', __('Profile Settings'))

@section('user_setting_actions')
	<button type="submit" form="settings-profile-form" class="btn btn-primary btn-sm settings-heading-action">
		<i class="fas fa-check" aria-hidden="true"></i>
		<span>{{ __('Save Changes') }}</span>
	</button>
@endsection

@section('user_setting_content')
	@php
		use App\Enums\Gender;
		use App\Enums\UserRole;

		$isEmailVerified = ! is_null($user->email_verified_at);
		$isPhoneVerified = $user->hasVerifiedPhone();
	@endphp
	
	{{-- Email verification notice --}}
	@unless($isEmailVerified)
		<div class="settings-inline-alert settings-inline-alert--warning mb-3" role="alert">
            <span class="settings-inline-alert__icon" aria-hidden="true">
                <i class="fas fa-triangle-exclamation"></i>
            </span>
			<div class="settings-inline-alert__body">
				<strong class="settings-inline-alert__title">{{ __('Email not verified') }}</strong>
				<span class="settings-inline-alert__text">
                    {{ __('Verify your email address to secure your account and receive important alerts.') }}
                </span>
			</div>
			<a href="{{ route('user.settings.verify-email') }}" class="btn btn-sm btn-warning settings-inline-alert__action">
				<i class="fas fa-paper-plane me-1"></i> {{ __('Verify Now') }}
			</a>
		</div>
	@endunless
	
	<form id="settings-profile-form" action="{{ route('user.settings.profile.update') }}" method="POST" enctype="multipart/form-data">
		@csrf
		
		{{-- Personal information --}}
		<section class="settings-section">
			<header class="settings-section__header">
                <span class="settings-section__icon" aria-hidden="true">
                    <i class="fas fa-address-card"></i>
                </span>
				<div>
					<h6 class="settings-section__title">{{ __('Personal Information') }}</h6>
					<p class="settings-section__subtitle">
						{{ __('This information is displayed on your profile and identity documents.') }}
					</p>
				</div>
			</header>
			
			<div class="settings-section__body">
				<div class="mb-3">
					<label for="avatar" class="form-label">{{ __('Profile Image') }}</label>
					<x-img name="avatar" :old="$user->avatar" :ref="'avatar'" :name="'avatar'"/>
				</div>
				
				<div class="row g-3">
					<div class="col-md-6">
						<label for="first_name" class="form-label">{{ __('First Name') }}</label>
						<input type="text" id="first_name" name="first_name"
						       class="form-control" value="{{ old('first_name', $user->first_name) }}">
					</div>
					<div class="col-md-6">
						<label for="last_name" class="form-label">{{ __('Last Name') }}</label>
						<input type="text" id="last_name" name="last_name"
						       class="form-control" value="{{ old('last_name', $user->last_name) }}">
					</div>
					
					<div class="col-md-6">
						<label for="username" class="form-label">{{ __('Username') }}</label>
						<input type="text" id="username" name="username"
						       class="form-control" value="{{ old('username', $user->username) }}">
					</div>
					<div class="col-md-6">
						<label for="gender" class="form-label">{{ __('Gender') }}</label>
						<select class="form-select" id="gender" name="gender">
							@foreach(Gender::cases() as $gender)
								<option value="{{ $gender->value }}"
									{{ old('gender', $user->gender) === $gender ? 'selected' : '' }}>
									{{ $gender->label() }}
								</option>
							@endforeach
						</select>
					</div>
					
					<div class="col-md-6">
						<label for="birthday" class="form-label">{{ __('Birthday') }}</label>
						<input type="date" id="birthday" name="birthday"
						       class="form-control" value="{{ old('birthday', $user->birthday) }}">
					</div>
				</div>
			</div>
		</section>
		
		{{-- Contact details --}}
		<section class="settings-section">
			<header class="settings-section__header">
                <span class="settings-section__icon" aria-hidden="true">
                    <i class="fas fa-envelope-open-text"></i>
                </span>
				<div>
					<h6 class="settings-section__title">{{ __('Contact Details') }}</h6>
					<p class="settings-section__subtitle">
						{{ __('Used to notify you about account activity and verification updates.') }}
					</p>
				</div>
			</header>
			
			<div class="settings-section__body">
				<div class="row g-3">
					<div class="col-md-6">
						<label for="email" class="form-label d-flex align-items-center justify-content-between">
							<span>{{ __('Email') }}</span>
							@if($isEmailVerified)
								<span class="settings-badge settings-badge--success">
                                    <i class="fas fa-circle-check"></i> {{ __('Verified') }}
                                </span>
							@else
								<span class="settings-badge settings-badge--danger">
                                    <i class="fas fa-circle-exclamation"></i> {{ __('Not Verified') }}
                                </span>
							@endif
						</label>
						<input type="email" id="email" name="email"
						       class="form-control" value="{{ old('email', $user->email) }}">
					</div>
					<div class="col-md-6">
						<label for="phone" class="form-label d-flex align-items-center justify-content-between">
							<span>{{ __('Phone') }}</span>
							@if($isPhoneVerified)
								<span class="settings-badge settings-badge--success">
                                    <i class="fas fa-circle-check"></i> {{ __('Verified') }}
                                </span>
							@else
								<a href="{{ route('user.settings.phone.verify') }}" class="settings-badge settings-badge--info text-decoration-none">
									<i class="fas fa-paper-plane"></i> {{ __('Verify') }}
								</a>
							@endif
						</label>
						<input type="tel" id="phone" name="phone"
						       class="form-control" value="{{ old('phone', $user->phone) }}">
					</div>
				</div>
			</div>
		</section>
		
		{{-- Business information (merchants only) --}}
		@if($user->role === UserRole::MERCHANT)
			<section class="settings-section">
				<header class="settings-section__header">
                    <span class="settings-section__icon" aria-hidden="true">
                        <i class="fas fa-store"></i>
                    </span>
					<div>
						<h6 class="settings-section__title">{{ __('Business Information') }}</h6>
						<p class="settings-section__subtitle">
							{{ __('Shown to customers on payment pages and receipts.') }}
						</p>
					</div>
					<span class="settings-badge settings-badge--info">
                        <i class="fas fa-store"></i> {{ __('Merchant') }}
                    </span>
				</header>
				
				<div class="settings-section__body">
					<div class="row g-3">
						<div class="col-md-6">
							<label for="business_name" class="form-label">{{ __('Business Name') }}</label>
							<input type="text" id="business_name" name="business_name"
							       class="form-control" value="{{ old('business_name', $user->business_name) }}">
						</div>
						<div class="col-md-6">
							<label for="business_address" class="form-label">{{ __('Business Address') }}</label>
							<input type="text" id="business_address" name="business_address"
							       class="form-control" value="{{ old('business_address', $user->business_address) }}">
						</div>
					</div>
				</div>
			</section>
		@endif
		
		{{-- Preferences --}}
		@if(isset($currencies) && $currencies->count() > 0)
			<section class="settings-section">
				<header class="settings-section__header">
                    <span class="settings-section__icon" aria-hidden="true">
                        <i class="fas fa-sliders-h"></i>
                    </span>
					<div>
						<h6 class="settings-section__title">{{ __('Preferences') }}</h6>
						<p class="settings-section__subtitle">
							{{ __('Configure the default currency for your wallet and transactions.') }}
						</p>
					</div>
				</header>
				
				<div class="settings-section__body">
					<div class="row g-3">
						<div class="col-md-6">
							<label for="default_currency_id" class="form-label">{{ __('Default Currency') }}</label>
							<select class="form-select" id="default_currency_id" name="default_currency_id" required>
								<option value="">{{ __('Select Currency') }}</option>
								@foreach($currencies as $currency)
									<option value="{{ $currency->id }}"
										@selected(old('default_currency_id', $user->default_currency_id ?? optional($currencies->firstWhere('default', true))->id) == $currency->id)>
										{{ $currency->name }} ({{ $currency->code }})
									</option>
								@endforeach
							</select>
						</div>
					</div>
				</div>
			</section>
		@endif
		
		{{-- Address --}}
		<section class="settings-section">
			<header class="settings-section__header">
                <span class="settings-section__icon" aria-hidden="true">
                    <i class="fas fa-map-marker-alt"></i>
                </span>
				<div>
					<h6 class="settings-section__title">{{ __('Address') }}</h6>
					<p class="settings-section__subtitle">
						{{ __('Your country is locked based on registration; update state, city and street address anytime.') }}
					</p>
				</div>
			</header>
			
			<div class="settings-section__body">
				<div class="row g-3">
					<div class="col-md-6">
						<label for="country" class="form-label">{{ __('Country') }}</label>
						<input type="text" id="country" class="form-control"
						       value="{{ $user->country }}" disabled>
					</div>
					<div class="col-md-6">
						<label for="state" class="form-label">{{ __('State') }}</label>
						<input type="text" id="state" name="state" class="form-control"
						       value="{{ old('state', $user->state) }}" placeholder="{{ __('Enter state') }}">
					</div>
					
					<div class="col-md-6">
						<label for="city" class="form-label">{{ __('City') }}</label>
						<input type="text" id="city" name="city" class="form-control"
						       value="{{ old('city', $user->city) }}" placeholder="{{ __('Enter city') }}">
					</div>
					<div class="col-md-6">
						<label for="postal_code" class="form-label">{{ __('Postal Code') }}</label>
						<input type="text" id="postal_code" name="postal_code"
						       class="form-control" value="{{ old('postal_code', $user->postal_code) }}">
					</div>
					
					<div class="col-12">
						<label for="address" class="form-label">{{ __('Street Address') }}</label>
						<textarea class="form-control rounded" name="address" id="address"
						          rows="3" placeholder="{{ __('Enter your address') }}">{{ old('address', $user->address) }}</textarea>
					</div>
				</div>
			</div>
		</section>
	</form>
@endsection
