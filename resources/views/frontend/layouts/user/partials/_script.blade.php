{{-- =========================
| All JavaScript Files Link
|========================= --}}

{{-- Core Libraries --}}
<script src="{{ asset('general/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('frontend/js/jquery-3.7.1.min.js') }}"></script>

{{-- Essential Plugins --}}
<script src="{{ asset('frontend/js/owl.carousel.min.js') }}"></script>
<script src="{{ asset('general/js/apexcharts.min.js') }}"></script>
<script src="{{ asset('general/js/moment.js') }}"></script>
<script src="{{ asset('general/js/daterangepicker.min.js') }}"></script>
<script src="{{ asset('general/js/datepicker.min.js') }}"></script>
<script src="{{ asset('general/js/clipboard.min.js') }}"></script>

{{-- Notifications & Helpers --}}
<script src="{{ asset('general/js/simple-notify.min.js') }}"></script>
@php($helpersJsVersion = config('app.version').'-'.filemtime(public_path('general/js/helpers.js')))
<script src="{{ asset('general/js/helpers.js?v='.$helpersJsVersion) }}"></script>

{{-- Theme Main Script --}}
<script src="{{ asset('frontend/js/dashboard-main.js') }}"></script>
@include('frontend.layouts.partials._pwa_script')

{{-- Real-Time Notifications --}}
@include('general.notification_config._tune_player')
@include('general.notification_config._pusher_config')

{{-- Global Date Picker & Notify Config --}}
@include('general._notify_evs')
@include('general._date_range_picker')

{{-- Page Specific Scripts --}}
@yield('scripts')
@stack('scripts')
{{-- DK Mobile App Shell controller --}}
@php($mobileAppJsVersion = config('app.version').'-'.filemtime(public_path('frontend/js/dashboard-mobile-app.js')))
<script src="{{ asset('frontend/js/dashboard-mobile-app.js?v='.$mobileAppJsVersion) }}" defer></script>
