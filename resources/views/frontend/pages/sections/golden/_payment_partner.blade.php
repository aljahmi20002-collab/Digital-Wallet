@php
	use App\Models\PaymentGateway;

	$locale  = $locale ?? app()->getLocale();
	$heading = $data['section_heading'][$locale] ?? __('Trusted by Global Payment Networks');

	// Pre-filter to gateways that have a logo — keeps the marquee loop tidy
	// and stops divider issues when some rows are skipped mid-loop.
	$gatewaysWithLogos = PaymentGateway::allCached()->filter(fn ($gw) => ! empty($gw->logo))->values();
@endphp

<section class="gdk-partners">
	<div class="gdk-container">
		<div class="gdk-partners__head">
			<div class="gdk-eyebrow">{{ $heading }}</div>
		</div>
	</div>

	@if($gatewaysWithLogos->isNotEmpty())
		<div class="gdk-marquee">
			<div class="gdk-marquee__track" id="marquee">
				{{-- Render twice for a seamless CSS-driven horizontal loop --}}
				@for($pass = 0; $pass < 2; $pass++)
					@foreach($gatewaysWithLogos as $gw)
						<div class="gdk-marquee__item">
							<img src="{{ asset($gw->logo) }}" alt="{{ $gw->name }}" class="gdk-marquee__logo" loading="lazy">
						</div>
						@unless($loop->last)
							<div class="gdk-marquee__sep"></div>
						@endunless
					@endforeach
					{{-- Separator between the two passes so logos don't touch when looped --}}
					@if($pass === 0)
						<div class="gdk-marquee__sep"></div>
					@endif
				@endfor
			</div>
		</div>
	@endif
</section>
