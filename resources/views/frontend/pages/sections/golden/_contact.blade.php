@php
	$locale = $locale ?? app()->getLocale();

	// Field-name compatibility (Classic → Golden fallbacks).
	$eyebrow       = $data['eyebrow'][$locale]        ?? $data['subheading'][$locale] ?? '';
	$heading       = $data['heading'][$locale]        ?? '';
	$description   = $data['description'][$locale]    ?? '';
	$visualImage   = $data['visual_image']            ?? $data['contact_image']       ?? null;
	$visualCaption = $data['visual_caption'][$locale] ?? __('24 / 7 Concierge');

	$renderedHeading = preg_replace_callback('/__(.+?)__/u', function ($m) {
		return '<em class="gdk-italic gdk-gold-text">'.e($m[1]).'</em>';
	}, e($heading));
@endphp

<section class="gdk-section gdk-contact" id="contact">
	<div class="gdk-container">
		<div class="gdk-contact__grid">
			<div class="gdk-reveal">
				@if($eyebrow)
					<div class="gdk-eyebrow">{{ $eyebrow }}</div>
				@endif
				@if($heading)
					<h2 class="gdk-h-section gdk-section-head__title">{!! $renderedHeading !!}</h2>
				@endif
				@if($description)
					<p class="gdk-lead gdk-lead--spaced">{!! nl2br(e($description)) !!}</p>
				@endif

				<form class="gdk-form" action="{{ route('contact.submit') }}" method="POST">
					@csrf
					<div class="gdk-field">
						<label for="gdk-name">{{ __('Name') }}</label>
						<input id="gdk-name" name="name" type="text" placeholder="{{ __('Your name') }}" required>
					</div>
					<div class="gdk-field">
						<label for="gdk-email">{{ __('Email') }}</label>
						<input id="gdk-email" name="email" type="email" placeholder="{{ __('you@example.com') }}" required>
					</div>
					<div class="gdk-field">
						<label for="gdk-phone">{{ __('Phone') }}</label>
						<input id="gdk-phone" name="phone" type="tel" placeholder="{{ __('Phone number') }}">
					</div>
					<div class="gdk-field">
						<label for="gdk-subject">{{ __('Subject') }}</label>
						<input id="gdk-subject" name="subject" type="text" placeholder="{{ __('Subject') }}">
					</div>
					<div class="gdk-field gdk-field--full">
						<label for="gdk-message">{{ __('Message') }}</label>
						<textarea id="gdk-message" name="message" placeholder="{{ __('A few words on what you\'d like to discuss…') }}"></textarea>
					</div>
					<div class="gdk-field--full">
						<button type="submit" class="gdk-btn gdk-btn--filled">{{ __('Send Message') }} <i class="fa-solid fa-arrow-right"></i></button>
					</div>
				</form>
			</div>

			<div class="gdk-reveal">
				<div class="gdk-concierge">
					@if($visualImage)
						<div class="gdk-concierge__img" style="--gdk-img: url('{{ asset($visualImage) }}')"></div>
					@else
						<div class="gdk-concierge__img gdk-img-fallback"></div>
					@endif
					<div class="gdk-corner gdk-corner--tl"></div>
					<div class="gdk-corner gdk-corner--tr"></div>
					<div class="gdk-corner gdk-corner--bl"></div>
					<div class="gdk-corner gdk-corner--br"></div>
					<div class="gdk-concierge__caption">
						<i class="fa-solid fa-headset"></i>{{ $visualCaption }}
					</div>
				</div>
			</div>
		</div>
	</div>
</section>
