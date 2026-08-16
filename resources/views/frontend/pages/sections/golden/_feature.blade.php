@php
	$locale = $locale ?? app()->getLocale();

	// Field-name compatibility (Classic → Golden fallbacks).
	$eyebrow = $data['eyebrow'][$locale] ?? $data['subheading'][$locale] ?? '';
	$heading = $data['heading'][$locale] ?? '';

	$renderedHeading = preg_replace_callback('/__(.+?)__/u', function ($m) {
		return '<em class="gdk-italic gdk-gold-text">'.e($m[1]).'</em>';
	}, e($heading));
@endphp

<section class="gdk-section">
	<div class="gdk-container">
		<div class="gdk-features__head gdk-reveal">
			@if($eyebrow)
				<div class="gdk-eyebrow">{{ $eyebrow }}</div>
			@endif
			@if($heading)
				<h2 class="gdk-h-section">{!! $renderedHeading !!}</h2>
			@endif
		</div>

		<div class="row g-4">
			@foreach($repeatedContents as $repeatedContent)
				@php
					$rc = $repeatedContent->content_data ?? [];
					$num = str_pad((string)($loop->index + 1), 2, '0', STR_PAD_LEFT);
				@endphp
				<div class="col-md-6 col-lg-4">
					<div class="gdk-feature gdk-reveal">
						<div class="gdk-corner gdk-corner--tl"></div><div class="gdk-corner gdk-corner--tr"></div>
						<div class="gdk-corner gdk-corner--bl"></div><div class="gdk-corner gdk-corner--br"></div>
						<div class="gdk-feature__num">{{ $rc['feature_number'] ?? $num }}</div>
						<div class="gdk-feature__icon">
							<i class="{{ $rc['feature_icon_class'] ?? 'fa-solid fa-gem' }}"></i>
						</div>
						<h3 class="gdk-feature__title">{{ $rc['feature_title'][$locale] ?? '' }}</h3>
						<p class="gdk-feature__txt">{!! nl2br(e($rc['feature_text'][$locale] ?? '')) !!}</p>
					</div>
				</div>
			@endforeach
		</div>
	</div>
</section>
