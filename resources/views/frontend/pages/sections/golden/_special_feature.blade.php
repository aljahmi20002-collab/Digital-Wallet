@php
	$locale = $locale ?? app()->getLocale();

	// Field-name compatibility (Classic → Golden fallbacks).
	$eyebrow       = $data['eyebrow'][$locale]        ?? $data['subheading'][$locale] ?? '';
	$heading       = $data['heading'][$locale]        ?? '';
	$description   = $data['description'][$locale]    ?? '';
	$phoneGreeting = $data['phone_greeting'][$locale] ?? __('Good evening');
	$phoneName     = $data['phone_name']              ?? 'Mr. Whitlock';
	$phoneBalance  = $data['phone_balance']           ?? '$ 248,310.06';
	$phoneItems    = $data['phone_portfolio'] ?? [
		['label' => 'Yield · Sovereign', 'sub' => '+ 12.50% APY', 'value' => '$94,200'],
		['label' => 'Gold Allocation',   'sub' => '+ 2.1% MTD',   'value' => '$54,180'],
		['label' => 'Cash · USD',        'sub' => 'Liquid',       'value' => '$28,400'],
	];

	$renderedHeading = preg_replace_callback('/__(.+?)__/u', function ($m) {
		return '<em class="gdk-italic gdk-gold-text">'.e($m[1]).'</em>';
	}, e($heading));

	$leftItems  = $repeatedContents->slice(0, 3);
	$rightItems = $repeatedContents->slice(3, 3);

	// Resolve a triptych blurb's icon, title and text from either Golden or
	// Classic field names. Classic stores `feature_icon` as an image path; if
	// only that is present we emit it as <img>, otherwise we emit the FA class.
	$gdkResolveBlurb = function (array $rc, string $locale): array {
		$title = $rc['spfeat_title'][$locale] ?? $rc['feature_title'][$locale] ?? '';
		$text  = $rc['spfeat_text'][$locale]  ?? $rc['feature_text'][$locale]  ?? '';
		$icon  = $rc['spfeat_icon_class'] ?? null;
		$image = null;
		if (! $icon && ! empty($rc['feature_icon'])) {
			$candidate = $rc['feature_icon'];
			if (is_string($candidate) && str_contains($candidate, '/')) {
				$image = $candidate;
			} else {
				$icon = $candidate;
			}
		}

		return [
			'title' => $title,
			'text'  => $text,
			'icon'  => $icon ?? 'fa-solid fa-fingerprint',
			'image' => $image,
		];
	};
@endphp

<section class="gdk-section gdk-special">
	<div class="gdk-container">
		<div class="gdk-special__head gdk-reveal">
			@if($eyebrow)
				<div class="gdk-eyebrow">{{ $eyebrow }}</div>
			@endif
			@if($heading)
				<h2 class="gdk-h-section gdk-section-head__title">{!! $renderedHeading !!}</h2>
			@endif
			@if($description)
				<p class="gdk-lead gdk-lead--centered">{!! nl2br(e($description)) !!}</p>
			@endif
		</div>

		<div class="gdk-special__grid">
			{{-- Left column --}}
			<div class="gdk-special__col gdk-reveal">
				@foreach($leftItems as $repeatedContent)
					@php $b = $gdkResolveBlurb($repeatedContent->content_data ?? [], $locale); @endphp
					@if($b['title'] || $b['text'])
						<div class="gdk-spfeat gdk-spfeat--right">
							<span class="gdk-iconring">
								@if($b['image'])
									<img src="{{ asset($b['image']) }}" alt="" loading="lazy" width="22" height="22">
								@else
									<i class="{{ $b['icon'] }}"></i>
								@endif
							</span>
							<div>
								<h4 class="gdk-spfeat__title">{{ $b['title'] }}</h4>
								<p class="gdk-spfeat__txt">{!! nl2br(e($b['text'])) !!}</p>
							</div>
						</div>
					@endif
				@endforeach
			</div>

			{{-- Centre: phone mockup --}}
			<div class="gdk-phone gdk-reveal" aria-hidden="true">
				<div class="gdk-phone__notch"></div>
				<div class="gdk-phone__screen">
					<div class="gdk-phone__head"><span>9:41</span><span>●●●●</span></div>
					<div class="gdk-phone__hello">{{ $phoneGreeting }}</div>
					<div class="gdk-phone__name">{{ $phoneName }}</div>
					<div class="gdk-phone__card">
						<div class="gdk-phone__bal">{{ __('Total Holdings') }}</div>
						<div class="gdk-phone__num">{{ $phoneBalance }}</div>
						<div class="gdk-phone__actions">
							<div class="gdk-phone__act"><i class="fa-solid fa-arrow-up"></i>{{ __('Send') }}</div>
							<div class="gdk-phone__act"><i class="fa-solid fa-arrow-down"></i>{{ __('Hold') }}</div>
							<div class="gdk-phone__act"><i class="fa-solid fa-coins"></i>{{ __('Earn') }}</div>
							<div class="gdk-phone__act"><i class="fa-solid fa-ellipsis"></i>{{ __('More') }}</div>
						</div>
					</div>
					<div class="gdk-phone__list">
						@foreach($phoneItems as $row)
							<div class="gdk-phone__row">
								<div>
									<div class="lbl">{{ $row['label'] }}</div>
									<div class="sub">{{ $row['sub'] }}</div>
								</div>
								<div class="val">{{ $row['value'] }}</div>
							</div>
						@endforeach
					</div>
				</div>
			</div>

			{{-- Right column --}}
			<div class="gdk-special__col gdk-reveal">
				@foreach($rightItems as $repeatedContent)
					@php $b = $gdkResolveBlurb($repeatedContent->content_data ?? [], $locale); @endphp
					@if($b['title'] || $b['text'])
						<div class="gdk-spfeat">
							<span class="gdk-iconring">
								@if($b['image'])
									<img src="{{ asset($b['image']) }}" alt="" loading="lazy" width="22" height="22">
								@else
									<i class="{{ $b['icon'] }}"></i>
								@endif
							</span>
							<div>
								<h4 class="gdk-spfeat__title">{{ $b['title'] }}</h4>
								<p class="gdk-spfeat__txt">{!! nl2br(e($b['text'])) !!}</p>
							</div>
						</div>
					@endif
				@endforeach
			</div>
		</div>
	</div>
</section>
