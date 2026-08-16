@php
	$locale = $locale ?? app()->getLocale();

	// Field-name compatibility: pages built with the Classic theme can still
	// render through Golden views. Read the Golden field first, then fall
	// back to the Classic equivalent so existing data is honoured.
	$eyebrow      = $data['eyebrow'][$locale]               ?? $data['subheading'][$locale] ?? __('Private Digital Wealth');
	$heading      = $data['heading'][$locale]               ?? __('A discreet vault for the modern :italic of capital.', ['italic' => '__connoisseur__']);
	$description  = $data['description'][$locale]           ?? '';
	$primaryText  = $data['primary_button_text'][$locale]   ?? $data['button_text'][$locale] ?? __('Open Private Wallet');
	$primaryUrl   = $data['primary_button_url']             ?? $data['button_url']           ?? '#';
	$secondaryText = $data['secondary_button_text'][$locale] ?? '';
	$secondaryUrl  = $data['secondary_button_url']           ?? '#';

	// Heading: render with optional italic-gold word marked by __word__ wrappers
	$renderedHeading = preg_replace_callback('/__(.+?)__/u', function ($m) {
		return '<em class="gdk-italic gdk-gold-text">'.e($m[1]).'</em>';
	}, e($heading));
	$renderedHeading = nl2br($renderedHeading);

	$trustBadges = $data['trust_badges'] ?? [
		['icon' => 'fa-solid fa-shield-halved', 'label' => 'Licensed'],
		['icon' => 'fa-solid fa-lock',          'label' => 'PCI DSS'],
		['icon' => 'fa-solid fa-certificate',   'label' => 'ISO 27001'],
		['icon' => 'fa-solid fa-key',           'label' => '256-bit Encrypted'],
	];

	$vaultBrand    = $data['vault_brand']    ?? 'DIGIKASH';
	$vaultTier     = $data['vault_tier']     ?? 'PRIVATE · BLACK';
	$vaultMonogram = $data['vault_monogram'] ?? 'DK';
	$vaultNumber   = $data['vault_number']   ?? '4519 •••• •••• 2208';
	$vaultHolder   = $data['vault_holder']   ?? 'A. WHITLOCK';
	$vaultExpires  = $data['vault_expires']  ?? '08 / 31';
	$vaultBalance  = $data['vault_balance']  ?? '$ 248,310.06';
	$vaultYield    = $data['vault_yield']    ?? '12.50%';
@endphp

<section class="gdk-hero" id="home">

	{{-- Art-deco fans --}}
	<div class="gdk-fan gdk-fan--tl" aria-hidden="true">
		<svg viewBox="0 0 200 200" fill="none" stroke="#D4AF37" stroke-width="1">
			<g opacity=".6">
				<path d="M0 0 L200 60"/><path d="M0 0 L200 90"/><path d="M0 0 L200 120"/>
				<path d="M0 0 L200 150"/><path d="M0 0 L200 180"/>
				<path d="M0 0 L60 200"/><path d="M0 0 L90 200"/><path d="M0 0 L120 200"/>
				<path d="M0 0 L150 200"/><path d="M0 0 L180 200"/>
			</g>
			<circle cx="0" cy="0" r="40" stroke-opacity=".4"/>
			<circle cx="0" cy="0" r="70" stroke-opacity=".25"/>
		</svg>
	</div>
	<div class="gdk-fan gdk-fan--br" aria-hidden="true">
		<svg viewBox="0 0 200 200" fill="none" stroke="#D4AF37" stroke-width="1">
			<g opacity=".6">
				<path d="M0 0 L200 60"/><path d="M0 0 L200 90"/><path d="M0 0 L200 120"/>
				<path d="M0 0 L200 150"/><path d="M0 0 L200 180"/>
				<path d="M0 0 L60 200"/><path d="M0 0 L90 200"/><path d="M0 0 L120 200"/>
			</g>
			<circle cx="0" cy="0" r="40" stroke-opacity=".4"/>
		</svg>
	</div>

	<div class="gdk-container">
		<div class="gdk-hero__grid">
			<div class="gdk-reveal">
				@if($eyebrow)
					<div class="gdk-hero__eyebrow gdk-eyebrow">{{ $eyebrow }}</div>
				@endif

				<h1 class="gdk-hero__title gdk-h-display">
					{!! $renderedHeading !!}
				</h1>

				@if($description)
					<p class="gdk-hero__desc">{!! nl2br(e($description)) !!}</p>
				@endif

				<div class="gdk-hero__ctas">
					@if($primaryText)
						<a href="{{ $primaryUrl }}" class="gdk-btn gdk-btn--filled">
							{{ $primaryText }} <i class="fa-solid fa-arrow-right"></i>
						</a>
					@endif
					@if($secondaryText)
						<a href="{{ $secondaryUrl }}" class="gdk-btn gdk-btn--ghost">{{ $secondaryText }}</a>
					@endif
				</div>

				@if(!empty($trustBadges))
					<div class="gdk-trust">
						@foreach($trustBadges as $idx => $badge)
							@if($idx > 0)
								<span class="gdk-trust__dot"></span>
							@endif
							<span class="gdk-trust__item">
								<i class="{{ $badge['icon'] ?? 'fa-solid fa-shield-halved' }}"></i>{{ $badge['label'] ?? '' }}
							</span>
						@endforeach
					</div>
				@endif
			</div>

			<div class="gdk-vault gdk-reveal">
				<div class="gdk-vault__tag gdk-vault__tag--tl">
					<i class="fa-solid fa-shield-halved"></i>
					<div>
						<small class="gdk-vault__micro">{{ __('Secured Balance') }}</small>
						<strong>{{ $vaultBalance }}</strong>
					</div>
				</div>

				<div class="gdk-vault__card">
					<div class="gdk-vault__rim"></div>
					<div class="gdk-vault__shine"></div>
					<div class="gdk-vault__head">
						<div class="gdk-vault__brand">{{ $vaultBrand }}<small>{{ $vaultTier }}</small></div>
						<div class="gdk-vault__monogram">{{ $vaultMonogram }}</div>
					</div>
					<div class="gdk-vault__chip"><span></span></div>
					<div class="gdk-vault__number">
						@foreach(explode(' ', $vaultNumber) as $segment)
							<span>{{ $segment }}</span>
						@endforeach
					</div>
					<div class="gdk-vault__foot">
						<div class="gdk-vault__name"><small>{{ __('Cardholder') }}</small>{{ $vaultHolder }}</div>
						<div>
							<small class="gdk-vault__micro gdk-vault__micro--right">{{ __('Expires') }}</small>
							<span class="gdk-vault__net">{{ $vaultExpires }}</span>
						</div>
					</div>
				</div>

				<div class="gdk-vault__tag gdk-vault__tag--br">
					<i class="fa-solid fa-chart-line"></i>
					<div>
						<small class="gdk-vault__micro">{{ __('Annual Yield') }}</small>
						<strong>{{ $vaultYield }}</strong>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>
