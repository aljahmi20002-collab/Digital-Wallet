<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" data-theme="golden">
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="csrf-token" content="{{ csrf_token() }}">
	<title>@yield('title', __('Sign In')) — {{ setting('site_title') }}</title>

	@if(config('app.demo'))
		<meta name="x-demo-mode" content="true">
	@endif

	@include('frontend.layouts.partials._favicon')

	{{-- Notifications --}}
	<link rel="stylesheet" href="{{ asset('general/css/simple-notify.min.css') }}">

	{{-- Fonts --}}
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500;1,600&family=Cinzel:wght@400;500;600&family=Manrope:wght@300;400;500;600;700&display=swap" rel="stylesheet">

	{{-- FontAwesome 6 --}}
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

	{{-- Golden auth bundle --}}
	<link rel="stylesheet" href="{{ asset('frontend/css/golden/auth.css?v='.config('app.version')) }}">

	@stack('styles')
</head>
<body class="gdk-auth-body">

	{{-- Demo Mode Banner (renders only when APP_DEMO=true) --}}
	<x-demo-banner />

	{{-- SVG defs (gradient used by the OTP countdown ring) --}}
	<svg width="0" height="0" class="gdk-svg-defs" aria-hidden="true">
		<defs>
			<linearGradient id="ringGrad" x1="0" y1="0" x2="1" y2="1">
				<stop offset="0%" stop-color="#F5E6A8"/>
				<stop offset="60%" stop-color="#D4AF37"/>
				<stop offset="100%" stop-color="#B8860B"/>
			</linearGradient>
		</defs>
	</svg>

	@yield('auth-content')

	{{-- jQuery (loaded for parity with other auth flows even though
	     the golden bundle itself is vanilla) --}}
	<script src="{{ asset('frontend/js/jquery-3.7.1.min.js') }}"></script>
	<script src="{{ asset('general/js/simple-notify.min.js') }}"></script>

	{{-- Global notify --}}
	@include('general._notify_evs')

	{{-- Golden auth interactions --}}
	<script src="{{ asset('frontend/js/golden/auth.js?v='.config('app.version')) }}"></script>

	@stack('scripts')
	@yield('scripts')
</body>
</html>
