<head>
    {{-- Meta Tags --}}
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex,nofollow">
    
    {{-- Page Title --}}
    <title>{{ setting('site_title') }} | @yield('title')</title>

    @if(config('app.demo'))
        {{-- Demo Mode Disclosure (for automated scanners & reviewers) --}}
        <meta name="x-demo-mode" content="true">
        <meta name="x-demo-disclaimer" content="This is a software product demo. No real financial services, deposits, or cryptocurrency investments are offered. All data shown is fictitious and for evaluation purposes only.">
        <meta name="x-demo-disclosure" content="{{ url('/demo-disclosure') }}">
        <meta name="x-demo-vendor" content="{{ config('app.demo_vendor_name') }}">
        <link rel="alternate" type="text/html" title="Software Demo Disclosure" href="{{ url('/demo-disclosure') }}">
    @endif
    
    {{-- Favicon --}}
    <link rel="shortcut icon" href="{{ asset(setting('site_favicon')) }}" type="image/x-icon"/>
    
    {{-- Core Vendor CSS --}}
    <link rel="stylesheet" href="{{ asset('general/css/google-fonts-inter-jetbrains.css') }}">
    <link rel="stylesheet" href="{{ asset('general/css/fontawesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('general/css/chartjs.css') }}">
    <link rel="stylesheet" href="{{ asset('general/css/simple-notify.min.css') }}">
    <link rel="stylesheet" href="{{ asset('general/css/daterangepicker.css') }}">
    
    {{-- Plugin CSS --}}
    <link rel="stylesheet" href="{{ asset('backend/css/summernote-lite.min.css') }}">
    <link rel="stylesheet" href="{{ asset('backend/css/tagify.css') }}">
    
    {{-- Application CSS --}}
    @php($commonCssVersion = config('app.version').'-'.filemtime(public_path('general/css/common.css')))
    <link rel="stylesheet" href="{{ asset('general/css/common.css?v='.$commonCssVersion) }}">
    <link rel="stylesheet" href="{{ asset('backend/css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('backend/css/_variables.css?v=' . config('app.version')) }}">
    @php($backendCustomCssVersion = config('app.version').'-'.filemtime(public_path('backend/css/custom.css')))
    <link rel="stylesheet" href="{{ asset('backend/css/custom.css?v='.$backendCustomCssVersion) }}"/>
    <link rel="stylesheet" href="{{ asset('backend/css/admin-table.css?v=' . config('app.version')) }}"/>
    <x-role-branding-theme />
    
    {{-- Page Specific Styles --}}
    @yield('styles')
    @stack('styles')
</head>
