@extends('frontend.layouts.golden.auth')
@section('title', __('Two-Factor Authentication'))

@section('auth-content')
	<section class="shell">
		<div class="form-panel">
			@include('frontend.layouts.golden.partials._auth_form_top', [
				'backLabel' => __('Back to Sign In'),
				'backUrl'   => route('user.login'),
			])

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

				<div class="eyebrow form-eyebrow">{{ __('Verify Identity') }}</div>
				<h1 class="form-title">
					{!! __('Enter your :em seal.', ['em' => '<em class="italic-gold">'.__('six-figure').'</em>']) !!}
				</h1>
				<p class="form-desc">{{ __('Open your authenticator app. Code refreshes every thirty seconds — a gold ring shows time remaining.') }}</p>

				<form action="{{ route('user.two-factor-authenticate') }}" method="POST" novalidate>
					@csrf

					<label class="otp__label">{{ __('Authentication Code') }}</label>

					<div class="otp-wrap">
						<div class="otp" data-otp-group>
							<input class="otp__cell" maxlength="1" inputmode="numeric" autocomplete="one-time-code" aria-label="{{ __('Digit :n', ['n' => 1]) }}" autofocus>
							<input class="otp__cell" maxlength="1" inputmode="numeric" aria-label="{{ __('Digit :n', ['n' => 2]) }}">
							<input class="otp__cell" maxlength="1" inputmode="numeric" aria-label="{{ __('Digit :n', ['n' => 3]) }}">
							<input class="otp__cell" maxlength="1" inputmode="numeric" aria-label="{{ __('Digit :n', ['n' => 4]) }}">
							<input class="otp__cell" maxlength="1" inputmode="numeric" aria-label="{{ __('Digit :n', ['n' => 5]) }}">
							<input class="otp__cell" maxlength="1" inputmode="numeric" aria-label="{{ __('Digit :n', ['n' => 6]) }}">
						</div>
						<div class="otp-ring" aria-hidden="true">
							<svg width="48" height="48" viewBox="0 0 48 48">
								<circle class="otp-ring__bg" cx="24" cy="24" r="20"/>
								<circle class="otp-ring__fg" cx="24" cy="24" r="20" stroke-dasharray="125.66" stroke-dashoffset="0" data-otp-ring/>
							</svg>
							<span class="otp-ring__label" data-otp-ring-label>30</span>
						</div>
					</div>

					{{-- Hidden field stores the joined OTP for the controller --}}
					<input type="hidden" name="verification_code" data-otp-hidden value="{{ old('verification_code') }}">

					<div class="backup-link-row">
						<a href="#" class="gold-link gold-link--caps">{{ __('Use a backup code instead') }}</a>
					</div>

					<button type="submit" class="btn btn--filled btn--full" data-otp-submit disabled>
						{{ __('Verify & Proceed') }} <i class="fa-solid fa-arrow-right"></i>
					</button>

					<p class="muted-note muted-note--centered u-mt-24">{{ __("Time-sync your device if codes keep failing.") }}</p>
				</form>
			</div>
		</div>

		@include('frontend.layouts.golden.partials._auth_brand_panel')
	</section>
@endsection
