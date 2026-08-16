@php
	$visual = $visual ?? 'vault';
@endphp

<aside class="brand-panel">
	{{-- Art-deco fan ornaments --}}
	<div class="brand__fan brand__fan--tr" aria-hidden="true">
		<svg viewBox="0 0 200 200" fill="none" stroke="#D4AF37" stroke-width="1">
			<g opacity=".6">
				<path d="M0 0 L200 60"/><path d="M0 0 L200 90"/><path d="M0 0 L200 120"/>
				<path d="M0 0 L200 150"/><path d="M0 0 L200 180"/>
				<path d="M0 0 L60 200"/><path d="M0 0 L90 200"/><path d="M0 0 L120 200"/>
				<path d="M0 0 L150 200"/><path d="M0 0 L180 200"/>
			</g>
			<circle cx="0" cy="0" r="50" stroke-opacity=".4"/>
			<circle cx="0" cy="0" r="85" stroke-opacity=".25"/>
		</svg>
	</div>
	<div class="brand__fan brand__fan--bl" aria-hidden="true">
		<svg viewBox="0 0 200 200" fill="none" stroke="#D4AF37" stroke-width="1">
			<g opacity=".6">
				<path d="M0 0 L200 60"/><path d="M0 0 L200 90"/><path d="M0 0 L200 120"/>
				<path d="M0 0 L60 200"/><path d="M0 0 L90 200"/><path d="M0 0 L120 200"/>
			</g>
			<circle cx="0" cy="0" r="50" stroke-opacity=".4"/>
		</svg>
	</div>

	{{-- Floating gold particles (populated by JS) --}}
	<div class="brand__particles" data-particles></div>

	<div class="brand__top">
		<div class="eyebrow">{{ __('Private Digital Wealth') }}</div>
		<h2 class="brand__title">
			{!! __('Where capital meets :em.', [
				'em' => '<em class="italic-gold">'.__('composure').'</em>',
			]) !!}
		</h2>
		<p class="brand__desc">{{ __('A discreet wallet for the modern connoisseur — engineered with the patience of a century-old house.') }}</p>
	</div>

	<div class="brand__mid">
		@if($visual === 'envelope')
			{{-- Envelope mockup for verify-email --}}
			<div class="envelope">
				<div class="envelope__frame">
					<div class="envelope__deco"></div>
					<div class="envelope__body">
						<div class="envelope__flap"></div>
					</div>
					<div class="envelope__rays"></div>
					<div class="envelope__seal"><span>DK</span></div>
				</div>
			</div>
		@else
			{{-- Default vault card --}}
			<div class="vault">
				<div class="vault__tag vault__tag--tl">
					<i class="fa-solid fa-shield-halved"></i>
					<div>
						<small>{{ __('Secured Balance') }}</small>
						<strong>$ 248,310.06</strong>
					</div>
				</div>
				<div class="vault__card">
					<div class="vault__rim"></div>
					<div class="vault__shine"></div>
					<div class="vault__head">
						<div class="vault__brand">DIGIKASH<small>{{ __('PRIVATE · BLACK') }}</small></div>
						<div class="vault__monogram">DK</div>
					</div>
					<div class="vault__chip"><span></span></div>
					<div class="vault__number">
						<span>4519</span><span>••••</span><span>••••</span><span>2208</span>
					</div>
					<div class="vault__foot">
						<div class="vault__name"><small>{{ __('Cardholder') }}</small>A. WHITLOCK</div>
						<div class="vault__expires-row">
							<small class="vault__expires-lbl">{{ __('Expires') }}</small>
							<span class="vault__net">08 / 31</span>
						</div>
					</div>
				</div>
				<div class="vault__tag vault__tag--br">
					<i class="fa-solid fa-chart-line"></i>
					<div>
						<small>{{ __('Annual Yield') }}</small>
						<strong>12.50%</strong>
					</div>
				</div>
			</div>
		@endif
	</div>

	<div class="brand__trust">
		<span class="trust__item"><i class="fa-solid fa-shield-halved"></i>{{ __('Licensed') }}</span>
		<span class="trust__dot"></span>
		<span class="trust__item"><i class="fa-solid fa-lock"></i>{{ __('PCI DSS') }}</span>
		<span class="trust__dot"></span>
		<span class="trust__item"><i class="fa-solid fa-certificate"></i>{{ __('ISO 27001') }}</span>
		<span class="trust__dot"></span>
		<span class="trust__item"><i class="fa-solid fa-key"></i>{{ __('256-bit') }}</span>
	</div>
</aside>
