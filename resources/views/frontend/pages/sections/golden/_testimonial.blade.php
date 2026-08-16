@php
	$locale = $locale ?? app()->getLocale();

	// Field-name compatibility (Classic → Golden fallbacks).
	$eyebrow = $data['eyebrow'][$locale] ?? $data['subheading'][$locale] ?? '';
	$heading = $data['heading'][$locale] ?? '';

	$renderedHeading = preg_replace_callback('/__(.+?)__/u', function ($m) {
		return '<em class="gdk-italic gdk-gold-text">'.e($m[1]).'</em>';
	}, e($heading));

	$items = $repeatedContents->map(function ($rc) use ($locale) {
		$c = $rc->content_data ?? [];
		return [
			'body'  => $c['comment_text'][$locale] ?? '',
			'name'  => $c['client_name'][$locale]  ?? '',
			'role'  => $c['client_position'][$locale] ?? '',
			'photo' => !empty($c['client_image']) ? asset($c['client_image']) : '',
			'rating'=> (int) ($c['rating'] ?? 5),
		];
	})->values();

	$first = $items->first() ?? ['body' => '', 'name' => '', 'role' => '', 'photo' => '', 'rating' => 5];
@endphp

<section class="gdk-section gdk-testi">
	<div class="gdk-container">
		<div class="gdk-testi__head gdk-reveal">
			@if($eyebrow)
				<div class="gdk-eyebrow">{{ $eyebrow }}</div>
			@endif
			@if($heading)
				<h2 class="gdk-h-section gdk-section-head__title">{!! $renderedHeading !!}</h2>
			@endif
		</div>

		<div class="gdk-testi__stage gdk-reveal" data-gdk-testimonials="{{ $items->toJson() }}">
			<button class="gdk-arrow gdk-testi__arrow gdk-testi__arrow--prev" id="testiPrev" aria-label="{{ __('Previous') }}"><i class="fa-solid fa-arrow-left"></i></button>
			<div class="gdk-quote" id="testiCard">
				<div class="gdk-quote__glyph">&ldquo;</div>
				<div class="gdk-quote__stars" aria-label="{{ __('Five out of five') }}">
					@for($i = 1; $i <= 5; $i++)
						<i class="{{ ($first['rating'] ?? 5) >= $i ? 'fa-solid' : 'fa-regular' }} fa-star"></i>
					@endfor
				</div>
				<blockquote class="gdk-quote__body" id="testiBody">{{ $first['body'] }}</blockquote>
				<div class="gdk-quote__client">
					<div class="gdk-quote__photo">
						<span id="testiPhoto" style="--gdk-img: url('{{ $first['photo'] }}')"></span>
					</div>
					<div>
						<div class="gdk-quote__name" id="testiName">{{ $first['name'] }}</div>
						<div class="gdk-quote__role" id="testiRole">{{ $first['role'] }}</div>
					</div>
				</div>
			</div>
			<button class="gdk-arrow gdk-testi__arrow gdk-testi__arrow--next" id="testiNext" aria-label="{{ __('Next') }}"><i class="fa-solid fa-arrow-right"></i></button>
		</div>

		<div class="gdk-testi__dots" id="testiDots">
			@foreach($items as $idx => $_)
				<button class="gdk-testi__dot {{ $idx === 0 ? 'is-active' : '' }}" data-i="{{ $idx }}" aria-label="{{ __('Slide :n', ['n' => $idx + 1]) }}"></button>
			@endforeach
		</div>
	</div>
</section>
