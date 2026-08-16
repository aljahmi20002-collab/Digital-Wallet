@extends('frontend.layouts.golden.auth')
@section('title', __('User Password Reset'))

@section('auth-content')
	<section class="shell">
		<div class="form-panel">
			@include('frontend.layouts.golden.partials._auth_form_top', [
				'backLabel' => __('Back to Sign In'),
				'backUrl'   => route('user.login'),
			])

			@if(session('status'))
				{{-- Success state — link dispatched --}}
				<div class="form-body success is-visible">
					<div class="success__ring"><i class="fa-solid fa-check"></i></div>
					<h2>{{ __('Check your inbox.') }}</h2>
					<p>{{ session('status') }}</p>
					<a href="{{ route('user.login') }}" class="gold-link gold-link--caps-lg">{{ __('Return to Sign In') }}</a>
				</div>
			@else
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

					<div class="eyebrow form-eyebrow">{{ __('Account Recovery') }}</div>
					<h1 class="form-title">
						{!! __("We'll send a :em reset link.", ['em' => '<em class="italic-gold">'.__('discreet').'</em>']) !!}
					</h1>
					<p class="form-desc">{{ __('Check your inbox within sixty seconds. If nothing arrives, our concierge is one click away.') }}</p>

					<form action="{{ route('user.password.email') }}" method="POST" novalidate>
						@csrf
						<div class="field {{ $errors->has('email') ? 'has-error' : '' }}">
							<label for="gdk-email">{{ __('Email Address') }}</label>
							<div class="field__wrap has-lead">
								<i class="lead fa-regular fa-envelope"></i>
								<input id="gdk-email" name="email" type="email" placeholder="{{ __('Enter your user email') }}" value="{{ old('email') }}" required autocomplete="email" autofocus>
							</div>
							@error('email')
								<div class="field__error"><i class="fa-solid fa-triangle-exclamation"></i> {{ $message }}</div>
							@enderror
						</div>

						<button type="submit" class="btn btn--filled btn--full">
							{{ __('Send Reset Link') }} <i class="fa-solid fa-arrow-right"></i>
						</button>

						<p class="muted-note--centered u-mt-24">
							{{ __('Concierge available 24/7 — ') }}
							@if($email = setting('support_email'))
								<a href="mailto:{{ $email }}" class="gold-link">{{ __('email :addr', ['addr' => $email]) }}</a>
							@endif
						</p>

						<div class="form-footer">
							{{ __('Remembered it?') }}
							<a href="{{ route('user.login') }}" class="gold-link">{{ __('Return to Sign In') }}</a>
						</div>

						@include('frontend.layouts.golden.partials._auth_portal_switch', ['current' => 'user', 'page' => 'forgot'])
					</form>
				</div>
			@endif
		</div>

		@include('frontend.layouts.golden.partials._auth_brand_panel')
	</section>
@endsection
