@if(isset($navigations) && $navigations->isNotEmpty())
	@foreach($navigations as $navigation)
		<a href="{{ $navigation->url }}" target="{{ $navigation->target }}">{{ $navigation->label }}</a>
	@endforeach
@else
	{{-- Sensible fallback if no navigations are configured yet --}}
	<a href="{{ route('home') }}" class="is-active">{{ __('Home') }}</a>
	<a href="{{ route('home') }}#about">{{ __('About') }}</a>
	<a href="{{ route('home') }}#services">{{ __('Services') }}</a>
	<a href="{{ route('home') }}#plans">{{ __('Plans') }}</a>
	<a href="{{ route('home') }}#earn">{{ __('Earn') }}</a>
	<a href="{{ route('home') }}#contact">{{ __('Contact') }}</a>
@endif
