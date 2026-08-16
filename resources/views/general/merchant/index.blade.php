<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>
    {{-- Favicon --}}
    <link rel="shortcut icon" href="@yield('favicon')" type="image/x-icon"/>
    {{-- Bootstrap 5 CSS --}}
    <link href="{{ asset('general/css/bootstrap.min.css') }}" rel="stylesheet">
    {{-- Font Awesome --}}
    <link rel="stylesheet" href="{{ asset('general/css/fontawesome.min.css') }}">
    {{-- Custom CSS --}}
    <link rel="stylesheet" href="{{ asset('frontend/css/_variables.css') }}">
    <link rel="stylesheet" href="{{ asset('general/css/payment-checkout.css') }}">
    <link rel="stylesheet" href="{{ asset('frontend/css/payment-links.css') }}">
    <link rel="stylesheet" href="{{ asset('general/css/simple-notify.min.css') }}"/>
</head>
<body class="payment-checkout-body">
<main class="container payment-checkout-main py-4 py-md-5">
    @yield('merchant_content')
</main>

{{-- Scripts --}}
<script src="{{ asset('general/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('general/js/jquery.min.js') }}"></script>
<script src="{{ asset('general/js/payment-checkout.js') }}"></script>
<script src="{{ asset('frontend/js/auth.js') }}"></script>
<script src="{{ asset('general/js/simple-notify.min.js') }}"></script>
@php($helpersJsVersion = config('app.version').'-'.filemtime(public_path('general/js/helpers.js')))
<script src="{{ asset('general/js/helpers.js?v='.$helpersJsVersion) }}"></script>
<script src="{{ asset('frontend/js/payment-links.js') }}"></script>
@include('general._notify_evs')
@stack('scripts')
</body>
</html>
