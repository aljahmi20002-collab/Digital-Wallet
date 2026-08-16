@php
	$gdkSiteName     = setting('site_title', 'DigiKash');
	$gdkFooters      = $footers ?? collect();
	$gdkSocials      = $socials ?? collect();
	$gdkSupportEmail = setting('support_email');
	$gdkSupportPhone = setting('support_phone');
	$gdkCopyright    = setting('copyright_text');
	$gdkAboutText    = setting('footer_about_text', __('A private digital wallet for the modern connoisseur of capital. Discreet by design, exquisite by intention.'));
	$gdkLicense      = setting('footer_license_text');
@endphp

<footer class="gdk-footer">
	<div class="gdk-container">

		<div class="gdk-footer__grid">

			{{-- ========= Brand column ========= --}}
			<div class="gdk-footer__brand">
				<a href="{{ route('home') }}" class="gdk-logo" aria-label="{{ $gdkSiteName }}">
					<span class="gdk-logo__mark" aria-hidden="true"></span>
					<span class="gdk-logo__type">{!! preg_replace('/^(.{4,5})/u', '$1<em>', e($gdkSiteName)) !!}</em></span>
				</a>
				<p class="gdk-footer__tagline">{{ $gdkAboutText }}</p>
				@if($gdkSocials->isNotEmpty())
					<div class="gdk-socials gdk-footer__socials">
						@foreach($gdkSocials as $social)
							<a href="{{ $social->url }}" target="{{ $social->target }}" aria-label="{{ $social->name ?? '' }}">
								<i class="{{ $social->icon_class }}"></i>
							</a>
						@endforeach
					</div>
				@endif
				@if($gdkLicense)
					<p class="gdk-footer__license">{{ $gdkLicense }}</p>
				@endif
			</div>

			{{-- ========= DB-driven columns ========= --}}
			@foreach($gdkFooters as $footer)
				<div class="gdk-footer__col">
					<h6 class="gdk-footer__col-title">{{ $footer->title_text }}</h6>

					@if($footer->type == \App\Enums\FooterSectionType::TEXT)
						{{-- TEXT sections: title + paragraph blocks (e.g. "About" with
						     multiple "heading + body" pairs) --}}
						<div class="gdk-footer__textblocks">
							@foreach($footer->items as $item)
								<div class="gdk-footer__textblock">
									@if($item->label_text)
										<div class="gdk-footer__textblock-head">
											@if($item->icon)
												<i class="gdk-footer__textblock-icon {{ $item->icon }}" aria-hidden="true"></i>
											@endif
											<span>{{ $item->label_text }}</span>
										</div>
									@endif
									@if($item->content_text)
										<p class="gdk-footer__textblock-body">{{ $item->content_text }}</p>
									@endif
								</div>
							@endforeach
						</div>
					@else
						{{-- LINK sections: gold-dot bullet + label, slides on hover --}}
						<ul class="gdk-footer__links">
							@foreach($footer->items as $item)
								<li class="gdk-footer__link-row">
									<a href="{{ $item->resolved_url }}" target="{{ $item->target }}" class="gdk-footer__link">
										@if($item->icon)
											<i class="gdk-footer__link-icon {{ $item->icon }}" aria-hidden="true"></i>
										@else
											<span class="gdk-footer__link-dot" aria-hidden="true"></span>
										@endif
										<span>{{ $item->label_text }}</span>
									</a>
								</li>
							@endforeach
						</ul>
					@endif
				</div>
			@endforeach

			{{-- ========= Synthesised Contact column ========= --}}
			@if($gdkSupportEmail || $gdkSupportPhone)
				<div class="gdk-footer__col">
					<h6 class="gdk-footer__col-title">{{ __('Contact') }}</h6>
					<ul class="gdk-footer__links">
						@if($gdkSupportEmail)
							<li class="gdk-footer__link-row">
								<a href="mailto:{{ $gdkSupportEmail }}" class="gdk-footer__link">
									<i class="gdk-footer__link-icon fa-regular fa-envelope" aria-hidden="true"></i>
									<span>{{ $gdkSupportEmail }}</span>
								</a>
							</li>
						@endif
						@if($gdkSupportPhone)
							<li class="gdk-footer__link-row">
								<a href="tel:{{ $gdkSupportPhone }}" class="gdk-footer__link">
									<i class="gdk-footer__link-icon fa-solid fa-phone" aria-hidden="true"></i>
									<span>{{ $gdkSupportPhone }}</span>
								</a>
							</li>
						@endif
					</ul>
				</div>
			@endif
		</div>

		{{-- Hairline divider --}}
		<div class="gdk-footer__rule" aria-hidden="true"></div>

		<div class="gdk-footer__bottom">
			<div class="gdk-footer__copyright">
				{{ $gdkCopyright ?: '© '.date('Y').' '.$gdkSiteName.'. '.__('All rights reserved.') }}
			</div>
			<div class="gdk-footer__attribution">
				<x-demo-vendor-attribution variant="public" />
			</div>
		</div>
	</div>
</footer>
