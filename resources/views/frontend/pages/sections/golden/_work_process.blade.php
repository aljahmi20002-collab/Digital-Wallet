@php
	$locale = $locale ?? app()->getLocale();

	// Field-name compatibility (Classic → Golden fallbacks).
	$eyebrow = $data['eyebrow'][$locale] ?? $data['subheading'][$locale] ?? '';
	$heading = $data['heading'][$locale] ?? '';

	$renderedHeading = preg_replace_callback('/__(.+?)__/u', function ($m) {
		return '<em class="gdk-italic gdk-gold-text">'.e($m[1]).'</em>';
	}, e($heading));
@endphp

<section class="gdk-section gdk-process">
	<div class="gdk-container">
		<div class="gdk-process__head gdk-reveal">
			@if($eyebrow)
				<div class="gdk-eyebrow">{{ $eyebrow }}</div>
			@endif
			@if($heading)
				<h2 class="gdk-h-section gdk-section-head__title">{!! $renderedHeading !!}</h2>
			@endif
		</div>

		<div class="gdk-process__row">
			<div class="gdk-process__dash" aria-hidden="true"></div>

			@foreach($repeatedContents as $repeatedContent)
				@php
					$rc    = $repeatedContent->content_data ?? [];
					$index = $loop->index;
					$num   = str_pad((string)($index + 1), 2, '0', STR_PAD_LEFT);
					$down  = ($index + 1) % 2 === 0;
				@endphp
				<div class="gdk-step {{ $down ? 'gdk-step--down' : '' }} gdk-reveal">
					<div class="gdk-step__num">{{ $rc['step_number'] ?? $num }}</div>
					<div class="gdk-step__icon">
						<i class="{{ $rc['step_icon_class'] ?? 'fa-solid fa-envelope-open-text' }}"></i>
					</div>
					<h4 class="gdk-step__title">{{ $rc['step_title'][$locale] ?? '' }}</h4>
					<p class="gdk-step__txt">{!! nl2br(e($rc['step_description'][$locale] ?? '')) !!}</p>
				</div>
			@endforeach
		</div>
	</div>
</section>
