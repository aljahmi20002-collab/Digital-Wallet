<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="csrf-token" content="{{ csrf_token() }}">
	<meta name="author" content="{{ setting('site_title') }}">

	<title>{{ $meta['meta']['title'] ?? setting('site_title') }}</title>

	<meta name="description" content="{{ $meta['meta']['description'] ?? '' }}">
	<meta name="keywords" content="{{ $meta['meta']['keywords'] ?? '' }}">
	<link rel="canonical" href="{{ $meta['meta']['canonical_url'] ?? url()->current() }}">
	<meta name="robots" content="{{ $meta['meta']['robots'] ?? 'index,follow' }}">

	@if(config('app.demo'))
		<meta name="x-demo-mode" content="true">
		<meta name="x-demo-disclaimer" content="This is a software product demo. No real financial services, deposits, or cryptocurrency investments are offered. All data shown is fictitious and for evaluation purposes only.">
		<meta name="x-demo-disclosure" content="{{ url('/demo-disclosure') }}">
		<meta name="x-demo-vendor" content="{{ config('app.demo_vendor_name') }}">
		<link rel="alternate" type="text/html" title="Software Demo Disclosure" href="{{ url('/demo-disclosure') }}">
	@endif

	{{-- Open Graph --}}
	<meta property="og:site_name" content="{{ $meta['meta']['og']['site_name'] ?? setting('site_title') }}">
	<meta property="og:title" content="{{ $meta['meta']['og']['title'] ?? setting('site_title') }}">
	<meta property="og:description" content="{{ $meta['meta']['og']['description'] ?? '' }}">
	<meta property="og:url" content="{{ $meta['meta']['og']['url'] ?? url()->current() }}">
	<meta property="og:type" content="{{ $meta['meta']['og']['type'] ?? 'website' }}">
	<meta property="og:image" content="{{ $meta['meta']['og']['image'] ?? '' }}">
	<meta property="og:locale" content="{{ $meta['meta']['og']['locale'] ?? 'en_US' }}">

	<meta name="twitter:card" content="{{ $meta['meta']['twitter']['card'] ?? 'summary_large_image' }}">
	<meta name="twitter:title" content="{{ $meta['meta']['twitter']['title'] ?? setting('site_title') }}">
	<meta name="twitter:description" content="{{ $meta['meta']['twitter']['description'] ?? '' }}">
	<meta name="twitter:image" content="{{ $meta['meta']['twitter']['image'] ?? '' }}">

	{{-- PWA & favicon --}}
	@include('frontend.layouts.partials._pwa_meta')
	@include('frontend.layouts.partials._favicon')

	{{-- Bootstrap 5 grid only (matches the original design's spec) --}}
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap-grid.min.css">

	{{-- Notifications --}}
	<link rel="stylesheet" href="{{ asset('general/css/simple-notify.min.css') }}">

	{{-- Fonts --}}
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500;1,600&family=Cinzel:wght@400;500;600&family=Manrope:wght@300;400;500;600;700&display=swap" rel="stylesheet">

	{{-- FontAwesome 6 --}}
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

	{{-- Golden Theme bundle --}}
	<link rel="stylesheet" href="{{ asset('frontend/css/golden/theme.css?v='.config('app.version')) }}">

	{{-- Custom CSS --}}
	@include('frontend.layouts.partials.custom.code-css')

	{{-- Extra styles --}}
	@yield('styles')
	@stack('styles')
</head>
