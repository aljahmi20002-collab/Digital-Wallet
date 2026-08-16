@php
	$locale = $locale ?? app()->getLocale();

	// Field-name compatibility (Classic → Golden fallbacks).
	$eyebrow     = $data['eyebrow'][$locale]     ?? $data['small_title'][$locale] ?? '';
	$heading     = $data['heading'][$locale]     ?? __('Receive Our __Quarterly__ Dispatch.');
	$description = $data['description'][$locale] ?? __('Four times a year, a private letter on markets, custody, and the craft of preserving wealth.');
	$buttonText  = $data['button_text'][$locale] ?? __('Subscribe');

	$renderedHeading = preg_replace_callback('/__(.+?)__/u', function ($m) {
		return '<em class="gdk-italic gdk-gold-text">'.e($m[1]).'</em>';
	}, e($heading));
@endphp

<section class="gdk-newsletter">
	<div class="gdk-container">
		<div class="gdk-newsletter__panel gdk-reveal">
			<div class="gdk-corner gdk-corner--tl"></div>
			<div class="gdk-corner gdk-corner--tr"></div>
			<div class="gdk-corner gdk-corner--bl"></div>
			<div class="gdk-corner gdk-corner--br"></div>
			@if($eyebrow)
				<div class="gdk-eyebrow">{{ $eyebrow }}</div>
			@endif
			<h2 class="gdk-h-section">{!! $renderedHeading !!}</h2>
			@if($description)
				<p>{!! nl2br(e($description)) !!}</p>
			@endif
			<form class="gdk-subscribe" action="{{ route('subscribe.submit') }}" method="POST">
				@csrf
				<input type="email" name="email" placeholder="{{ __('you@example.com') }}" required>
				<button type="submit">{{ $buttonText }} <i class="fa-solid fa-arrow-right"></i></button>
			</form>
		</div>
	</div>
</section>
