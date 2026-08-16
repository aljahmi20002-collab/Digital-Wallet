@php
	$locale = $locale ?? app()->getLocale();

	// Field-name compatibility (Classic → Golden fallbacks).
	$eyebrow = $data['eyebrow'][$locale] ?? $data['subheading'][$locale] ?? '';
	$heading = $data['heading'][$locale] ?? '';

	$renderedHeading = preg_replace_callback('/__(.+?)__/u', function ($m) {
		return '<em class="gdk-italic gdk-gold-text">'.e($m[1]).'</em>';
	}, e($heading));
@endphp

<section class="gdk-section gdk-section--elev" id="team">
	<div class="gdk-container">
		<div class="gdk-team__head gdk-reveal">
			@if($eyebrow)
				<div class="gdk-eyebrow">{{ $eyebrow }}</div>
			@endif
			@if($heading)
				<h2 class="gdk-h-section gdk-section-head__title">{!! $renderedHeading !!}</h2>
			@endif
		</div>

		<div class="gdk-team__grid">
			@foreach($repeatedContents as $repeatedContent)
				@php
					$rc       = $repeatedContent->content_data ?? [];
					$photo    = !empty($rc['team_image']) ? asset($rc['team_image']) : '';
					$linkedin = $rc['linkedin_url'] ?? null;
					$twitter  = $rc['twitter_url']  ?? null;
					$facebook = $rc['facebook_url'] ?? null;
					$email    = $rc['email']        ?? null;
				@endphp
				<article class="gdk-member gdk-reveal">
					<div class="gdk-member__img" style="--gdk-img: url('{{ $photo }}')">
						<div class="gdk-member__corners">
							<div class="gdk-corner gdk-corner--tl"></div><div class="gdk-corner gdk-corner--tr"></div>
							<div class="gdk-corner gdk-corner--bl"></div><div class="gdk-corner gdk-corner--br"></div>
						</div>
					</div>
					<div class="gdk-member__body">
						<h4 class="gdk-member__name">{{ $rc['name'][$locale] ?? '' }}</h4>
						<div class="gdk-member__role">{{ $rc['designation'][$locale] ?? '' }}</div>
						<div class="gdk-member__socials">
							<span class="gdk-member__icons">
								@if($linkedin)<a href="{{ $linkedin }}"><i class="fa-brands fa-linkedin-in"></i></a>@endif
								@if($twitter)<a href="{{ $twitter }}"><i class="fa-brands fa-x-twitter"></i></a>@endif
								@if($facebook)<a href="{{ $facebook }}"><i class="fa-brands fa-facebook-f"></i></a>@endif
								@if($email)<a href="mailto:{{ $email }}"><i class="fa-regular fa-envelope"></i></a>@endif
							</span>
							<span class="gdk-member__plus"><i class="fa-solid fa-plus"></i></span>
						</div>
					</div>
				</article>
			@endforeach
		</div>
	</div>
</section>
