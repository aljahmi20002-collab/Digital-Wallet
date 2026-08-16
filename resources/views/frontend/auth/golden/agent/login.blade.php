@extends('frontend.layouts.golden.auth')
@section('title', __('Agent Login'))

@section('auth-content')
	<section class="shell">
		<div class="form-panel">
			@include('frontend.layouts.golden.partials._auth_form_top')

			<div class="form-body">
				@if(session('status'))
					<div class="flash-status">
						<span class="flash-status__icon"><i class="fa-solid fa-check"></i></span>
						<span>{{ session('status') }}</span>
					</div>
				@endif

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

				<div class="eyebrow form-eyebrow">{{ __('Agent Portal') }}</div>
				<h1 class="form-title">
					{!! __('Open your :em ledger.', ['em' => '<em class="italic-gold">'.__('commission').'</em>']) !!}
				</h1>
				<p class="form-desc">{{ __('Track referrals, settlements, and your dedicated agent profile — once approved.') }}</p>

				<form action="{{ route('agent.login') }}" method="POST" novalidate>
					@csrf

					<div class="field">
						<label for="gdk-login">{{ __('Email or Username') }}</label>
						<div class="field__wrap has-lead">
							<i class="lead fa-regular fa-envelope"></i>
							<input id="gdk-login" name="login" type="text" placeholder="{{ __('Your agent email') }}" value="{{ old('login') }}" required autofocus>
						</div>
					</div>

					<div class="field">
						<label for="gdk-password">{{ __('Password') }}</label>
						<div class="field__wrap has-lead">
							<i class="lead fa-solid fa-lock"></i>
							<input id="gdk-password" name="password" type="password" placeholder="••••••••••" required autocomplete="current-password">
							<button type="button" class="field__btn" data-eye="gdk-password" aria-label="{{ __('Show password') }}">
								<i class="fa-regular fa-eye"></i>
							</button>
						</div>
					</div>

					<div class="row">
						<label class="check">
							<input type="checkbox" name="remember">
							<span class="check__box"></span>
							<span class="check__lbl">{{ __('Remember this device') }}</span>
						</label>
						<a href="{{ route('agent.password.request') }}" class="gold-link gold-link--caps">{{ __('Forgot password?') }}</a>
					</div>

					@if(config('services.recaptcha.status'))
						<div class="field">
							<div class="g-recaptcha" data-sitekey="{{ config('services.recaptcha.key') }}"></div>
						</div>
					@endif

					<button type="submit" class="btn btn--filled btn--full">
						{{ __('Sign In') }} <i class="fa-solid fa-arrow-right"></i>
					</button>

					<div class="or-div">{{ __('Or') }}</div>

					<div class="muted-note--centered">
						{{ __("Don't have an agent account?") }}
						<a href="{{ route('agent.register') }}" class="gold-link">{{ __('Apply as an Agent') }}</a>
					</div>

					@include('frontend.layouts.golden.partials._auth_portal_switch', ['current' => 'agent', 'page' => 'login'])
				</form>
			</div>
		</div>

		@include('frontend.layouts.golden.partials._auth_brand_panel')
	</section>
@endsection

@push('scripts')
	@if(config('services.recaptcha.status'))
		<script async src="https://www.google.com/recaptcha/api.js"></script>
	@endif
@endpush
