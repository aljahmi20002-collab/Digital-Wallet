@php
	$locale = $locale ?? app()->getLocale();

	// Field-name compatibility (Classic → Golden fallbacks).
	$eyebrow     = $data['eyebrow'][$locale]     ?? $data['subheading'][$locale]  ?? '';
	$heading     = $data['heading'][$locale]     ?? $data['offer_title'][$locale] ?? '';
	$description = $data['description'][$locale] ?? '';
	$buttonText  = $data['button_text'][$locale] ?? '';
	$buttonUrl   = $data['button_url']           ?? '#';

	$renderedHeading = preg_replace_callback('/__(.+?)__/u', function ($m) {
		return '<em class="gdk-italic gdk-gold-text">'.e($m[1]).'</em>';
	}, e($heading));
@endphp

<section class="gdk-section gdk-offer">
	<div class="gdk-offer__deco" aria-hidden="true"></div>
	<div class="gdk-container">
		<div class="gdk-offer__grid">
			<div class="gdk-reveal">
				@if($eyebrow)
					<div class="gdk-eyebrow">{{ $eyebrow }}</div>
				@endif
				@if($heading)
					<h2 class="gdk-offer__title gdk-h-section">{!! $renderedHeading !!}</h2>
				@endif
				@if($description)
					<p class="gdk-lead">{!! nl2br(e($description)) !!}</p>
				@endif
				@if($buttonText)
					<a href="{{ $buttonUrl }}" class="gdk-btn gdk-btn--filled gdk-offer__cta">
						{{ $buttonText }} <i class="fa-solid fa-arrow-right"></i>
					</a>
				@endif
			</div>

			<div class="gdk-offer__counters gdk-reveal">
				@foreach($repeatedContents as $repeatedContent)
					@php
						$rc      = $repeatedContent->content_data ?? [];
						$num     = $rc['counter_number'] ?? '0';
						$prefix  = $rc['counter_prefix'] ?? '';
						$suffix  = $rc['counter_suffix'] ?? '';
						$dec     = (int)(($rc['counter_decimals'] ?? 0));
					@endphp
					<div>
						<div class="gdk-counter__num">
							{!! $prefix !!}<span data-count="{{ $num }}" data-suffix="{{ $suffix }}" data-dec="{{ $dec }}">0</span>
						</div>
						<div class="gdk-counter__div"></div>
						<div class="gdk-counter__lbl">{{ $rc['counter_title'][$locale] ?? '' }}</div>
					</div>
				@endforeach
			</div>
		</div>
	</div>
</section>
