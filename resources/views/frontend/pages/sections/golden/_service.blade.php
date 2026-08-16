@php
	$locale = $locale ?? app()->getLocale();

	// Field-name compatibility (Classic → Golden fallbacks).
	$eyebrow = $data['eyebrow'][$locale] ?? $data['subheading'][$locale] ?? '';
	$heading = $data['heading'][$locale] ?? '';

	$renderedHeading = preg_replace_callback('/__(.+?)__/u', function ($m) {
		return '<em class="gdk-italic gdk-gold-text">'.e($m[1]).'</em>';
	}, e($heading));
@endphp

<section class="gdk-section gdk-services" id="services">
	<div class="gdk-container">
		<div class="gdk-services__head">
			<div>
				@if($eyebrow)
					<div class="gdk-eyebrow">{{ $eyebrow }}</div>
				@endif
				@if($heading)
					<h2 class="gdk-h-section">{!! $renderedHeading !!}</h2>
				@endif
			</div>
			<div class="gdk-arrows">
				<button class="gdk-arrow" id="svcPrev" aria-label="{{ __('Previous') }}"><i class="fa-solid fa-arrow-left"></i></button>
				<button class="gdk-arrow" id="svcNext" aria-label="{{ __('Next') }}"><i class="fa-solid fa-arrow-right"></i></button>
			</div>
		</div>

		<div class="gdk-services__viewport">
			<div class="gdk-services__track" id="svcTrack">
				@foreach($repeatedContents as $repeatedContent)
					@php
						$rc = $repeatedContent->content_data ?? [];

						$svcTitle = $rc['service_title'][$locale] ?? '';
						$svcText  = $rc['service_text'][$locale]  ?? '';

						// Always render a gold FontAwesome icon in Golden — classic data
						// stores `service_image` as a raster PNG which clashes with the
						// luxury aesthetic at the icon's small render size. We pick a
						// curated default if no FA class is provided.
						$svcIcon = $rc['service_icon_class'] ?? 'fa-solid fa-gem';
					@endphp
					@if($svcTitle || $svcText)
						<article class="gdk-svc gdk-reveal">
							<div class="gdk-corner gdk-corner--tl"></div><div class="gdk-corner gdk-corner--tr"></div>
							<div class="gdk-corner gdk-corner--bl"></div><div class="gdk-corner gdk-corner--br"></div>
							<div class="gdk-svc__icon">
								<i class="{{ $svcIcon }}" aria-hidden="true"></i>
							</div>
							<h3 class="gdk-svc__title">{{ $svcTitle }}</h3>
							<p class="gdk-svc__desc">{!! nl2br(e($svcText)) !!}</p>
							<a href="{{ $rc['service_link'] ?? '#' }}" class="gdk-link">{{ __('Learn More') }} <i class="fa-solid fa-arrow-right"></i></a>
						</article>
					@endif
				@endforeach
			</div>
		</div>
	</div>
</section>
