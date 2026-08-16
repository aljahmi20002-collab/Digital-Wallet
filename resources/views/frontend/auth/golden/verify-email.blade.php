@extends('frontend.layouts.golden.auth')
@section('title', __('Verify Email'))

@section('auth-content')
	@php
		$user = auth()->user();
		$emailMasked = $user?->email
			? preg_replace_callback('/^(.).*(.)@/u', fn ($m) => $m[1].'***'.$m[2].'@', (string) $user->email)
			: '';
	@endphp

	<section class="shell">
		<div class="form-panel">
			@include('frontend.layouts.golden.partials._auth_form_top')

			<div class="form-body">
				@if(session('status') === 'verification-link-sent')
					<div class="flash-status">
						<span class="flash-status__icon"><i class="fa-solid fa-check"></i></span>
						<span>{{ __('A new verification link has been sent to the email address you provided during registration.') }}</span>
					</div>
				@endif

				<div class="eyebrow form-eyebrow">{{ __('Almost In') }}</div>
				<h1 class="form-title">
					{!! __('Confirm your :em to continue.', ['em' => '<em class="italic-gold">'.__('address').'</em>']) !!}
				</h1>

				@if($emailMasked)
					<div class="email-tile">
						<span class="email-tile__ico"><i class="fa-regular fa-envelope"></i></span>
						<span class="email-tile__addr">{{ $emailMasked }}</span>
						<form method="POST" action="{{ route('user.logout') }}" class="email-tile__edit-form">
							@csrf
							<button type="submit" class="email-tile__edit" aria-label="{{ __('Change email') }}" title="{{ __('Change email') }}">
								<i class="fa-regular fa-pen-to-square"></i>
							</button>
						</form>
					</div>
				@endif

				<p class="form-desc">{{ __('We sent a sealed link. Open it within fifteen minutes.') }}</p>

				<div class="cta-row">
					@if($user?->email)
						<a href="mailto:{{ $user->email }}" class="btn btn--filled">
							<i class="fa-regular fa-envelope-open"></i>{{ __('Open Email App') }}
						</a>
					@endif

					<form method="POST" action="{{ route('verification.send') }}">
						@csrf
						<button type="submit" class="btn btn--ghost" data-resend>
							{{ __('Resend Link') }}
						</button>
					</form>
				</div>

				<div class="form-footer">
					{{ __('Wrong address?') }}
					<form method="POST" action="{{ route('user.logout') }}" class="form-footer__inline">
						@csrf
						<button type="submit" class="gold-link">{{ __('Sign out and try again') }}</button>
					</form>
				</div>
			</div>
		</div>

		@include('frontend.layouts.golden.partials._auth_brand_panel', ['visual' => 'envelope'])
	</section>
@endsection
