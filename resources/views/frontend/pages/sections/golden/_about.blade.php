@php
	$locale = $locale ?? app()->getLocale();

	// Field-name compatibility: pages built with the Classic theme can still
	// render through Golden views. Read the Golden field first, then fall
	// back to the Classic equivalent so existing data is honoured.
	$eyebrow     = $data['eyebrow'][$locale]     ?? $data['subheading'][$locale] ?? '';
	$heading     = $data['heading'][$locale]     ?? '';
	$description = $data['description'][$locale] ?? '';
	$portrait    = $data['portrait_image']       ?? $data['main_image']          ?? null;
	$buttonText  = $data['button_text'][$locale] ?? '';
	$buttonUrl   = $data['button_url']           ?? '#';
	$statValue   = $data['stat_value']           ?? '';
	$statLabel   = $data['stat_label'][$locale]  ?? '';
	$statIcon    = $data['stat_icon']            ?? 'fa-solid fa-users';

	$renderedHeading = preg_replace_callback('/__(.+?)__/u', function ($m) {
		return '<em class="gdk-italic gdk-gold-text">'.e($m[1]).'</em>';
	}, e($heading));
@endphp

<section class="gdk-section" id="about">
	<div class="gdk-container">
		<div class="gdk-about__grid">
			<div class="gdk-reveal gdk-about__column">
				<div class="gdk-frame">
					<div class="gdk-corner gdk-corner--tl"></div>
					<div class="gdk-corner gdk-corner--tr"></div>
					<div class="gdk-corner gdk-corner--bl"></div>
					<div class="gdk-corner gdk-corner--br"></div>
					@if($portrait)
						<div class="gdk-frame__img" style="--gdk-img: url('{{ asset($portrait) }}')"></div>
					@else
						<div class="gdk-frame__img gdk-img-fallback--soft"></div>
					@endif
				</div>
				@if($statValue)
					<div class="gdk-floatstat">
						<div class="gdk-floatstat__ring"><span><i class="{{ $statIcon }}"></i></span></div>
						<div>
							<div class="gdk-floatstat__num">{{ $statValue }}</div>
							<div class="gdk-floatstat__lbl">{{ $statLabel }}</div>
						</div>
					</div>
				@endif
			</div>

			<div class="gdk-reveal">
				@if($eyebrow)
					<div class="gdk-eyebrow">{{ $eyebrow }}</div>
				@endif

				@if($heading)
					<h2 class="gdk-about__title gdk-h-section">{!! $renderedHeading !!}</h2>
				@endif

				@if($description)
					<p class="gdk-about__desc">{!! nl2br(e($description)) !!}</p>
				@endif

				<div class="gdk-about__pillars">
					@foreach($repeatedContents as $repeatedContent)
						@php
							$rc = $repeatedContent->content_data ?? [];

							// Honour Golden field names first, then fall back to Classic.
							$pillarIcon  = $rc['pillar_icon_class']     ?? $rc['about_icon_class']    ?? 'fa-solid fa-shield-halved';
							$pillarTitle = $rc['pillar_title'][$locale] ?? $rc['about_title'][$locale] ?? '';
							$pillarText  = $rc['pillar_text'][$locale]  ?? $rc['about_text'][$locale]  ?? '';
						@endphp

						@if($pillarTitle || $pillarText)
							<div class="gdk-pillar">
								<span class="gdk-iconring"><i class="{{ $pillarIcon }}"></i></span>
								<div>
									<h4 class="gdk-pillar__title">{{ $pillarTitle }}</h4>
									<p class="gdk-pillar__txt">{!! nl2br(e($pillarText)) !!}</p>
								</div>
							</div>
						@endif
					@endforeach
				</div>

				@if($buttonText)
					<a href="{{ $buttonUrl }}" class="gdk-btn gdk-btn--filled gdk-btn--pill">
						{{ $buttonText }} <i class="fa-solid fa-arrow-right"></i>
					</a>
				@endif
			</div>
		</div>
	</div>
</section>
