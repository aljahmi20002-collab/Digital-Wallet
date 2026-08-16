<head>
    {{-- Basic Meta Tags --}}
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="author" content="{{ setting('site_title') }}">

	{{-- Dynamic Page Title --}}
	<title>{{ $meta['meta']['title'] ?? setting('site_title') }}</title>

    {{-- SEO Meta Tags --}}
	<meta name="description" content="{{ $meta['meta']['description'] ?? '' }}">
	<meta name="keywords" content="{{ $meta['meta']['keywords'] ?? '' }}">
	<meta name="author" content="{{ setting('site_title') }}">
	<meta name="csrf-token" content="{{ csrf_token() }}">

	{{-- Canonical URL --}}
	<link rel="canonical" href="{{ $meta['meta']['canonical_url'] ?? url()->current() }}">

	{{-- Robots Meta --}}
	<meta name="robots" content="{{ $meta['meta']['robots'] ?? 'index,follow' }}">

	@if(config('app.demo'))
		{{-- Demo Mode Disclosure (for automated scanners & reviewers) --}}
		<meta name="x-demo-mode" content="true">
		<meta name="x-demo-disclaimer" content="This is a software product demo. No real financial services, deposits, or cryptocurrency investments are offered. All data shown is fictitious and for evaluation purposes only.">
		<meta name="x-demo-disclosure" content="{{ url('/demo-disclosure') }}">
		<meta name="x-demo-vendor" content="{{ config('app.demo_vendor_name') }}">
		<link rel="alternate" type="text/html" title="Software Demo Disclosure" href="{{ url('/demo-disclosure') }}">
	@endif

	{{-- Open Graph Tags --}}
	<meta property="og:site_name" content="{{ $meta['meta']['og']['site_name'] ?? setting('site_title') }}">
	<meta property="og:title" content="{{ $meta['meta']['og']['title'] ?? setting('site_title') }}">
	<meta property="og:description" content="{{ $meta['meta']['og']['description'] ?? '' }}">
	<meta property="og:url" content="{{ $meta['meta']['og']['url'] ?? url()->current() }}">
	<meta property="og:type" content="{{ $meta['meta']['og']['type'] ?? 'website' }}">
	<meta property="og:image" content="{{ $meta['meta']['og']['image'] ?? '' }}">
	<meta property="og:locale" content="{{ $meta['meta']['og']['locale'] ?? 'en_US' }}">

	{{-- Twitter Card Meta --}}
	<meta name="twitter:card" content="{{ $meta['meta']['twitter']['card'] ?? 'summary_large_image' }}">
	@if(!empty($meta['meta']['twitter']['site']))
		<meta name="twitter:site" content="{{ $meta['meta']['twitter']['site'] }}">
	@endif
	<meta name="twitter:title" content="{{ $meta['meta']['twitter']['title'] ?? setting('site_title') }}">
	<meta name="twitter:description" content="{{ $meta['meta']['twitter']['description'] ?? '' }}">
	<meta name="twitter:image" content="{{ $meta['meta']['twitter']['image'] ?? ''
 }}">


    {{-- PWA meta (manifest + theme-color + apple-mobile-web-app-capable).
         Required on every page so the browser can install the site as a real PWA
         instead of a plain browser shortcut, regardless of where the user taps
         "Add to Home screen". The install prompt UI itself only renders on
         /user/* dashboard pages — see _mobile_app_footer.blade.php. --}}
    @include('frontend.layouts.partials._pwa_meta')

	{{-- Favicon. Keep this after PWA meta so the browser tab uses site_favicon. --}}
    @include('frontend.layouts.partials._favicon')

    {{-- Core CSS --}}
    <link rel="stylesheet" href="{{ asset('general/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('general/css/fontawesome.min.css') }}">
	<link rel="stylesheet" href="{{ asset('general/css/simple-notify.min.css') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">


	{{-- Frontend Plugins CSS --}}
    <link rel="stylesheet" href="{{ asset('frontend/css/animate.css') }}">
    <link rel="stylesheet" href="{{ asset('frontend/css/magnific-popup.css') }}">
    <link rel="stylesheet" href="{{ asset('frontend/css/swiper-bundle.min.css') }}">
    <link rel="stylesheet" href="{{ asset('frontend/css/nice-select.css') }}">
    <link rel="stylesheet" href="{{ asset('frontend/css/icomoon.css') }}">
    <link rel="stylesheet" href="{{ asset('frontend/css/_variables.css?v=' . config('app.version')) }}">
    <link rel="stylesheet" href="{{ asset('frontend/css/main.css') }}">
    <x-role-branding-theme />

	{{-- Custom CSS --}}
	@include('frontend.layouts.partials.custom.code-css')

    {{-- Extra Styles --}}
    @yield('styles')
    @stack('styles')
</head>
