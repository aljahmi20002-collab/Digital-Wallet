@php
	$gdkSiteName = setting('site_title', 'DigiKash');
@endphp

<header class="gdk-header">
	<div class="gdk-container">
		<div class="gdk-header__inner">
			<a href="{{ route('home') }}" class="gdk-logo" aria-label="{{ $gdkSiteName }}">
				<span class="gdk-logo__mark" aria-hidden="true"></span>
				<span class="gdk-logo__type">{!! preg_replace('/^(.{4,5})/u', '$1<em>', e($gdkSiteName)) !!}</em></span>
			</a>

			<nav class="gdk-nav" aria-label="{{ __('Primary') }}">
				@include('frontend.layouts.golden.partials._menu_list')
			</nav>

			<div class="gdk-header__cta">
				@auth
					<a href="{{ route('user.dashboard') }}" class="gdk-btn gdk-btn--filled gdk-btn--sm">
						{{ __('Dashboard') }} <i class="fa-solid fa-arrow-right"></i>
					</a>
				@else
					<a href="{{ route('user.login') }}" class="gdk-btn gdk-btn--ghost gdk-btn--sm">{{ __('Sign In') }}</a>
					<a href="{{ route('user.register') }}" class="gdk-btn gdk-btn--filled gdk-btn--sm">
						{{ __('Open Account') }} <i class="fa-solid fa-arrow-right"></i>
					</a>
				@endauth
				<button class="gdk-burger" aria-label="{{ __('Menu') }}" type="button">
					<i class="fa-solid fa-bars"></i>
				</button>
			</div>
		</div>
	</div>
</header>
