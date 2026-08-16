<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('errors.partials.head')
<body class="error-body">

<main class="error-main">
	{{--Dynamic Error Content Will Be Displayed Here--}}
	@yield('error-content')
</main>

@include('errors.partials.script')
</body>
</html>
