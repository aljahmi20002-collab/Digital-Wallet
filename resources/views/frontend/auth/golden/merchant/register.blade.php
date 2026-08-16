@extends('frontend.layouts.golden.auth')
@section('title', __('Create Merchant Account'))

@section('auth-content')
	@php
		$myCurrentLocation = getLocation();
		$allCountries      = getCountries();
		$selectedCountry   = old('country', strtoupper((string) ($myCurrentLocation['country_code'] ?? '')));
		$dialCode          = $myCurrentLocation['dial_code'] ?? '';
	@endphp

	<section class="shell form-register">
		<div class="form-panel">
			@include('frontend.layouts.golden.partials._auth_form_top')

			<div class="form-body">
				@if($errors->any())
					<div class="alert alert--validation is-visible" role="alert">
						<span class="alert__icon"><i class="fa-solid fa-exclamation"></i></span>
						<span class="alert__msg">
							<ul class="alert__list">
								@foreach($errors->all() as $error)
									<li>{{ $error }}</li>
								@endforeach
							</ul>
						</span>
						<button type="button" class="alert__close" data-alert-close aria-label="{{ __('Dismiss') }}"><i class="fa-solid fa-xmark"></i></button>
					</div>
				@endif

				<div class="eyebrow form-eyebrow">{{ __('Open a Merchant Account') }}</div>
				<h1 class="form-title">
					{!! __('Build a :em storefront.', ['em' => '<em class="italic-gold">'.__('considered').'</em>']) !!}
				</h1>
				<p class="form-desc">{{ __('Accept payments, generate QR codes, and access the API — at reduced fees once approved.') }}</p>

				<form action="{{ route('merchant.register') }}" method="POST" novalidate>
					@csrf

					<div class="form-section">{{ __('Personal Details') }}</div>
					<div class="grid-2">
						<div class="field">
							<label for="gdk-first">{{ __('First Name') }}</label>
							<div class="field__wrap">
								<input id="gdk-first" name="first_name" type="text" placeholder="Adrien" value="{{ old('first_name') }}" required>
							</div>
						</div>
						<div class="field">
							<label for="gdk-last">{{ __('Last Name') }}</label>
							<div class="field__wrap">
								<input id="gdk-last" name="last_name" type="text" placeholder="Whitlock" value="{{ old('last_name') }}" required>
							</div>
						</div>
						<div class="field">
							<label for="gdk-user">{{ __('Username') }}</label>
							<div class="field__wrap has-lead">
								<i class="lead fa-solid fa-at"></i>
								<input id="gdk-user" name="username" type="text" placeholder="a.whitlock" value="{{ old('username') }}" required>
							</div>
						</div>
						<div class="field">
							<label for="gdk-email">{{ __('Email Address') }}</label>
							<div class="field__wrap has-lead">
								<i class="lead fa-regular fa-envelope"></i>
								<input id="gdk-email" name="email" type="email" placeholder="adrien@whitlock.holdings" value="{{ old('email') }}" required>
							</div>
						</div>
					</div>

					<div class="form-section">{{ __('Residence') }}</div>
					<div class="grid-2">
						<div class="field">
							<label for="gdk-country">{{ __('Country') }}</label>
							<div class="select-wrap">
								<span class="select-wrap__flag" aria-hidden="true"></span>
								<select id="gdk-country" name="country" required>
									<option value="" disabled @selected(! $selectedCountry)>{{ __('Select Country') }}</option>
									@foreach($allCountries as $country)
										@php $code = strtoupper((string) $country['code']); @endphp
										<option value="{{ $code }}"
										        data-dial-code="{{ $country['dial_code'] ?? '' }}"
										        @selected($selectedCountry === $code)>
											{{ getCountryDisplayLabel($code) ?? title((string) $country['name']) }}
										</option>
									@endforeach
								</select>
								<i class="select-wrap__caret fa-solid fa-chevron-down"></i>
							</div>
						</div>
						<div class="field">
							<label for="gdk-phone">{{ __('Phone Number') }}</label>
							<div class="dial">
								<span class="dial__code">{{ $dialCode }} <i class="fa-solid fa-chevron-down"></i></span>
								<input id="gdk-phone" name="phone" type="tel" placeholder="{{ __('Phone') }}" value="{{ old('phone') }}">
							</div>
						</div>
					</div>

					<div class="form-section">{{ __('Business Information') }}</div>
					<div class="field">
						<label for="gdk-biz-name">{{ __('Business Name') }}</label>
						<div class="field__wrap has-lead">
							<i class="lead fa-solid fa-store"></i>
							<input id="gdk-biz-name" name="business_name" type="text" placeholder="{{ __('Your business name') }}" value="{{ old('business_name') }}" required>
						</div>
					</div>
					<div class="field">
						<label for="gdk-biz-address">{{ __('Business Address') }}</label>
						<div class="field__wrap has-lead">
							<i class="lead fa-solid fa-location-dot"></i>
							<input id="gdk-biz-address" name="business_address" type="text" placeholder="{{ __('Business address') }}" value="{{ old('business_address') }}" required>
						</div>
					</div>

					<div class="form-section">{{ __('Security') }}</div>
					<div class="grid-2">
						<div class="field">
							<label for="gdk-pass">{{ __('Password') }}</label>
							<div class="field__wrap has-lead">
								<i class="lead fa-solid fa-lock"></i>
								<input id="gdk-pass" name="password" type="password" placeholder="{{ __('At least 12 characters') }}" data-strength required autocomplete="new-password">
								<button type="button" class="field__btn" data-eye="gdk-pass" aria-label="{{ __('Show password') }}">
									<i class="fa-regular fa-eye"></i>
								</button>
							</div>
							<div class="meter" data-meter-for="gdk-pass">
								<span class="meter__pip"></span><span class="meter__pip"></span><span class="meter__pip"></span><span class="meter__pip"></span>
							</div>
							<div class="meter__lbl" data-meter-lbl="gdk-pass">{{ __('Strength · Awaiting input') }}</div>
						</div>
						<div class="field">
							<label for="gdk-pass-confirm">{{ __('Confirm Password') }}</label>
							<div class="field__wrap has-lead">
								<i class="lead fa-solid fa-lock"></i>
								<input id="gdk-pass-confirm" name="password_confirmation" type="password" placeholder="{{ __('Re-enter your passphrase') }}" data-confirm="gdk-pass" required autocomplete="new-password">
								<span class="field__suffix"><i class="fa-solid fa-check" data-match></i></span>
							</div>
							<div class="meter" data-meter-for="gdk-pass-confirm">
								<span class="meter__pip"></span><span class="meter__pip"></span><span class="meter__pip"></span><span class="meter__pip"></span>
							</div>
							<div class="meter__lbl" data-meter-lbl="gdk-pass-confirm">{{ __('Match · Awaiting input') }}</div>
						</div>
					</div>

					<div class="row u-mt-14">
						<label class="check">
							<input type="checkbox" name="terms" required>
							<span class="check__box"></span>
							<span class="check__lbl">
								{!! __('I accept the :terms and :privacy.', [
									'terms'   => '<a href="#" class="gold-link">'.__('Terms').'</a>',
									'privacy' => '<a href="#" class="gold-link">'.__('Privacy Policy').'</a>',
								]) !!}
							</span>
						</label>
					</div>

					<button type="submit" class="btn btn--filled btn--full u-mt-8">
						{{ __('Open Merchant Account') }} <i class="fa-solid fa-arrow-right"></i>
					</button>

					<div class="form-footer">
						{{ __('Already have a merchant account?') }}
						<a href="{{ route('merchant.login') }}" class="gold-link">{{ __('Sign In') }}</a>
					</div>

					@include('frontend.layouts.golden.partials._auth_portal_switch', ['current' => 'merchant', 'page' => 'register'])
				</form>
			</div>
		</div>

		@include('frontend.layouts.golden.partials._auth_brand_panel')
	</section>
@endsection
