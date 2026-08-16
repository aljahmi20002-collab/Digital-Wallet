<head>
	{{-- DK-MOBILE-FOUC: set [data-theme] before paint --}}
	@include('frontend.layouts.partials._pwa_theme')
	
	{{-- Basic Meta Tags --}}
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="csrf-token" content="{{ csrf_token() }}">
	
	{{-- Page Title --}}
	<title>{{ setting('site_title') }} | @yield('title', 'Dashboard')</title>

	@if(config('app.demo'))
		{{-- Demo Mode Disclosure (for automated scanners & reviewers) --}}
		<meta name="x-demo-mode" content="true">
		<meta name="x-demo-disclaimer" content="This is a software product demo. No real financial services, deposits, or cryptocurrency investments are offered. All data shown is fictitious and for evaluation purposes only.">
		<meta name="x-demo-disclosure" content="{{ url('/demo-disclosure') }}">
		<meta name="x-demo-vendor" content="{{ config('app.demo_vendor_name') }}">
		<link rel="alternate" type="text/html" title="Software Demo Disclosure" href="{{ url('/demo-disclosure') }}">
	@endif
	
	{{-- PWA install icons stay in the manifest. --}}
	@include('frontend.layouts.partials._pwa_meta')
	{{-- Browser tab icon must be declared after PWA meta so site_favicon wins. --}}
	@include('frontend.layouts.partials._favicon')
	
	{{-- Core CSS --}}
	<link rel="stylesheet" href="{{ asset('general/css/bootstrap.min.css') }}">
	<link rel="stylesheet" href="{{ asset('general/css/fontawesome.min.css') }}">
	<link rel="stylesheet" href="{{ asset('general/css/simple-notify.min.css') }}">
	@php($commonCssVersion = config('app.version').'-'.filemtime(public_path('general/css/common.css')))
	<link rel="stylesheet" href="{{ asset('general/css/common.css?v='.$commonCssVersion) }}">
	
	{{-- Dashboard Specific CSS --}}
	<link rel="stylesheet" href="{{ asset('general/css/daterangepicker.css') }}">
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="{{ asset('frontend/css/_variables.css?v=' . config('app.version')) }}">
	@php($dashboardStyleCssVersion = config('app.version').'-'.filemtime(public_path('frontend/css/dashboard-style.css')))
	<link rel="stylesheet" href="{{ asset('frontend/css/dashboard-style.css?v='.$dashboardStyleCssVersion) }}">
	@php($dashboardResponsiveCssVersion = config('app.version').'-'.filemtime(public_path('frontend/css/dashboard-responsive.css')))
	<link rel="stylesheet" href="{{ asset('frontend/css/dashboard-responsive.css?v='.$dashboardResponsiveCssVersion) }}">
	@php($premiumHeaderCssVersion = config('app.version').'-'.filemtime(public_path('frontend/css/premium-header.css')))
	<link rel="stylesheet" href="{{ asset('frontend/css/premium-header.css?v='.$premiumHeaderCssVersion) }}">
	@php($paymentLinksCssVersion = config('app.version').'-'.filemtime(public_path('frontend/css/payment-links.css')))
	<link rel="stylesheet" href="{{ asset('frontend/css/payment-links.css?v='.$paymentLinksCssVersion) }}">
	@php($mobileAppCssVersion = config('app.version').'-'.filemtime(public_path('frontend/css/dashboard-mobile-app.css')))
	<link rel="stylesheet" href="{{ asset('frontend/css/dashboard-mobile-app.css?v='.$mobileAppCssVersion) }}">
	<x-role-branding-theme />
	
	
	{{-- Custom CSS --}}
	@include('frontend.layouts.partials.custom.code-css')
	
	
	{{-- Page Specific Extra Styles --}}
	@yield('styles')
	@stack('styles')
</head>
