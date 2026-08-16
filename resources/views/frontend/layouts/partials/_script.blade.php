{{-- =========================
| All JavaScript Files Link
|========================= --}}

{{-- Core Libraries --}}
<script src="{{ asset('frontend/js/jquery-3.7.1.min.js') }}"></script>
<script src="{{ asset('general/js/bootstrap.bundle.min.js') }}"></script>

{{-- Plugins --}}
<script src="{{ asset('frontend/js/viewport.jquery.js') }}"></script>
<script src="{{ asset('frontend/js/jquery.nice-select.min.js') }}"></script>
<script src="{{ asset('frontend/js/jquery.waypoints.js') }}"></script>
<script src="{{ asset('frontend/js/jquery.counterup.min.js') }}"></script>
<script src="{{ asset('frontend/js/swiper-bundle.min.js') }}"></script>
<script src="{{ asset('frontend/js/jquery.magnific-popup.min.js') }}"></script>
<script src="{{ asset('frontend/js/wow.min.js') }}"></script>

{{-- Notifications & Helpers --}}
<script src="{{ asset('general/js/simple-notify.min.js') }}"></script>
@php($helpersJsVersion = config('app.version').'-'.filemtime(public_path('general/js/helpers.js')))
<script src="{{ asset('general/js/helpers.js?v='.$helpersJsVersion) }}"></script>

{{-- Theme Main Script --}}
<script src="{{ asset('frontend/js/main.js') }}"></script>

{{-- PWA registration. Registers the service worker on every public page so the
     browser meets PWA install criteria from anywhere on the site. The install
     prompt UI is rendered only on /user/* dashboard pages. --}}
@include('frontend.layouts.partials._pwa_script')

{{-- Global Notification Events --}}
@include('general._notify_evs')

{{-- Page Specific Scripts --}}
@yield('scripts')
@stack('scripts')
