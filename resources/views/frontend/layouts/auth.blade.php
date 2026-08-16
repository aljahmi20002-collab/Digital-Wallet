<!DOCTYPE html>
<html lang="en" class="frontend-auth-html">
    @include('frontend.layouts.user.partials._head')
<body class="frontend-auth-layout">

{{-- Demo Mode Banner (renders only when APP_DEMO=true) --}}
<x-demo-banner />

{{--<< Dynamic Content Show Here >>--}}
<div class="auth-content bg-gray">
    @yield('auth-content')
</div>




{{-- Notifications & Helpers --}}
<script src="{{ asset('frontend/js/jquery-3.7.1.min.js') }}"></script>
<script src="{{ asset('general/js/simple-notify.min.js') }}"></script>
@php($helpersJsVersion = config('app.version').'-'.filemtime(public_path('general/js/helpers.js')))
<script src="{{ asset('general/js/helpers.js?v='.$helpersJsVersion) }}"></script>

{{-- Auth Script --}}
<script src="{{ asset('frontend/js/auth.js')}}"></script>

{{-- Global Notify Configuration --}}
@include('general._notify_evs')

{{-- PWA registration. SW must be registered before the user logs in so the
     browser already meets install criteria when they reach the dashboard. --}}
@include('frontend.layouts.partials._pwa_script')

{{-- Page Specific Scripts --}}
@yield('scripts')
@stack('scripts')
</body>
</html>
