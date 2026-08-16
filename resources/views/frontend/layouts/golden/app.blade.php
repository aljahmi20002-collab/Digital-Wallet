<!doctype html>
<html lang="{{ app()->getLocale() }}" data-theme="golden">
@include('frontend.layouts.golden.partials._head')
<body class="gdk-body">

	{{-- Demo Mode Banner (renders only when APP_DEMO=true) --}}
	<x-demo-banner />

	{{-- Particles layer behind everything --}}
	<div class="gdk-particles" id="gdkParticles" aria-hidden="true"></div>

	@include('frontend.layouts.golden.partials._topstrip')
	@include('frontend.layouts.golden.partials._header')

	<main>
		@yield('content')
	</main>

	{{-- Cookies Include --}}
	@if(setting('cookie_status'))
		@include('frontend.layouts.partials._cookies')
	@endif

	@include('frontend.layouts.golden.partials._footer')
	@include('frontend.layouts.golden.partials._script')
</body>
</html>
