@extends('frontend.layouts.golden.auth')
@section('title', __('Set New Agent Password'))

@section('auth-content')
	<section class="shell">
		<div class="form-panel">
			@include('frontend.layouts.golden.partials._auth_form_top', [
				'backLabel' => __('Back to Agent Sign In'),
				'backUrl'   => route('agent.login'),
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

				<div class="eyebrow form-eyebrow">{{ __('New Credentials') }}</div>
				<h1 class="form-title">
					{!! __('Set a new :em passphrase.', ['em' => '<em class="italic-gold">'.__('sovereign').'</em>']) !!}
				</h1>
				<p class="form-desc">{{ __('Strong passwords have 12+ characters and mix letters, numbers, and a symbol.') }}</p>

				<form action="{{ route('agent.password.store') }}" method="POST" novalidate>
					@csrf
					<input type="hidden" name="token" value="{{ $request->route('token') }}">

					<div class="field">
						<label for="gdk-email">{{ __('Email Address') }}</label>
						<div class="field__wrap has-lead">
							<i class="lead fa-solid fa-lock"></i>
							<input id="gdk-email" name="email" type="email" value="{{ old('email', $request->email) }}" readonly autocomplete="email">
						</div>
					</div>

					<div class="field">
						<label for="gdk-pass">{{ __('New Password') }}</label>
						<div class="field__wrap has-lead">
							<i class="lead fa-solid fa-lock"></i>
							<input id="gdk-pass" name="password" type="password" placeholder="{{ __('At least 12 characters') }}" data-strength required autocomplete="new-password" autofocus>
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
						<div class="field__error" data-mismatch>
							<i class="fa-solid fa-triangle-exclamation"></i> {{ __('Passwords do not match.') }}
						</div>
					</div>

					<button type="submit" class="btn btn--filled btn--full">
						{{ __('Reset Password') }} <i class="fa-solid fa-arrow-right"></i>
					</button>

					<div class="form-footer">
						<a href="{{ route('agent.login') }}" class="gold-link">{{ __('Back to Sign In') }}</a>
					</div>
				</form>
			</div>
		</div>

		@include('frontend.layouts.golden.partials._auth_brand_panel')
	</section>
@endsection
