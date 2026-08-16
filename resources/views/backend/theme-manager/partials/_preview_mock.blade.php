{{-- Inline SVG mockup used when no preview image is available on disk --}}
@php
	$isDark = $theme['is_dark'];
	$bg     = $isDark ? '#0A0A0F' : '#FFFFFF';
	$bg2    = $isDark ? '#16161E' : '#F4F6FB';
	$text   = $isDark ? '#F4EDD8' : '#1B2240';
	$mute   = $isDark ? '#3A3A45' : '#CDD3DF';
	$accent = $theme['accent_color'];
	$id     = 'tm-mock-'.$theme['value'];
@endphp

<svg viewBox="0 0 720 360" xmlns="http://www.w3.org/2000/svg" class="tm-card__preview-svg" preserveAspectRatio="xMidYMid slice" aria-hidden="true">
	<defs>
		<linearGradient id="{{ $id }}-acc" x1="0" y1="0" x2="1" y2="1">
			<stop offset="0%" stop-color="{{ $accent }}" stop-opacity="1"/>
			<stop offset="100%" stop-color="{{ $accent }}" stop-opacity=".6"/>
		</linearGradient>
		<linearGradient id="{{ $id }}-glow" x1="0" y1="0" x2="0" y2="1">
			<stop offset="0%" stop-color="{{ $accent }}" stop-opacity=".10"/>
			<stop offset="100%" stop-color="{{ $accent }}" stop-opacity="0"/>
		</linearGradient>
		<filter id="{{ $id }}-soft" x="-10%" y="-10%" width="120%" height="120%">
			<feGaussianBlur stdDeviation="0.4"/>
		</filter>
	</defs>

	{{-- Background --}}
	<rect width="720" height="360" fill="{{ $bg }}"/>
	<rect width="720" height="180" fill="url(#{{ $id }}-glow)"/>

	{{-- Top nav strip --}}
	<rect x="0" y="0" width="720" height="44" fill="{{ $bg2 }}"/>
	<circle cx="36" cy="22" r="5" fill="url(#{{ $id }}-acc)"/>
	<rect x="50" y="18" width="56" height="8" rx="2" fill="{{ $text }}" opacity=".7"/>
	<g opacity=".4">
		<rect x="180" y="20" width="32" height="4" rx="2" fill="{{ $text }}"/>
		<rect x="220" y="20" width="32" height="4" rx="2" fill="{{ $text }}"/>
		<rect x="260" y="20" width="32" height="4" rx="2" fill="{{ $text }}"/>
		<rect x="300" y="20" width="32" height="4" rx="2" fill="{{ $text }}"/>
	</g>
	<rect x="540" y="14" width="68" height="16" rx="3" fill="none" stroke="{{ $accent }}" stroke-width="1" stroke-opacity=".7"/>
	<rect x="616" y="14" width="76" height="16" rx="3" fill="url(#{{ $id }}-acc)"/>
	<line x1="0" y1="44" x2="720" y2="44" stroke="{{ $accent }}" stroke-opacity=".18"/>

	{{-- Left: hero content --}}
	<g transform="translate(36,82)" filter="url(#{{ $id }}-soft)">
		{{-- Eyebrow --}}
		<rect x="0" y="0" width="112" height="6" rx="3" fill="{{ $accent }}" opacity=".85"/>

		{{-- Headline (2 lines) --}}
		<rect x="0" y="20" width="370" height="14" rx="3" fill="{{ $text }}" opacity=".92"/>
		<rect x="0" y="42" width="290" height="14" rx="3" fill="{{ $text }}" opacity=".75"/>

		{{-- Description (3 lines) --}}
		<rect x="0" y="78" width="380" height="5" rx="2.5" fill="{{ $mute }}"/>
		<rect x="0" y="90" width="350" height="5" rx="2.5" fill="{{ $mute }}"/>
		<rect x="0" y="102" width="260" height="5" rx="2.5" fill="{{ $mute }}"/>

		{{-- CTAs --}}
		<rect x="0" y="130" width="118" height="32" rx="4" fill="url(#{{ $id }}-acc)"/>
		<rect x="128" y="130" width="118" height="32" rx="4" fill="none" stroke="{{ $accent }}" stroke-width="1.2" stroke-opacity=".8"/>
	</g>

	{{-- Right: floating card --}}
	<g transform="translate(420,80) rotate(-3)">
		<rect width="240" height="148" rx="10" fill="{{ $isDark ? '#1c1c26' : '#FFFFFF' }}" stroke="{{ $accent }}" stroke-width="1.2" stroke-opacity=".55"/>
		<rect x="1" y="1" width="238" height="146" rx="9" fill="none" stroke="{{ $accent }}" stroke-opacity=".15"/>
		{{-- chip --}}
		<rect x="18" y="58" width="40" height="28" rx="3" fill="url(#{{ $id }}-acc)"/>
		<line x1="22" y1="68" x2="54" y2="68" stroke="{{ $bg }}" stroke-width="0.8" opacity=".3"/>
		<line x1="22" y1="76" x2="54" y2="76" stroke="{{ $bg }}" stroke-width="0.8" opacity=".3"/>
		{{-- brand label --}}
		<rect x="18" y="20" width="56" height="6" rx="2" fill="{{ $accent }}" opacity=".85"/>
		<rect x="18" y="30" width="36" height="3" rx="1.5" fill="{{ $text }}" opacity=".45"/>
		{{-- card number --}}
		<g opacity=".75">
			<rect x="18" y="100" width="34" height="5" rx="2" fill="{{ $text }}"/>
			<rect x="56" y="100" width="34" height="5" rx="2" fill="{{ $text }}" opacity=".4"/>
			<rect x="94" y="100" width="34" height="5" rx="2" fill="{{ $text }}" opacity=".4"/>
			<rect x="132" y="100" width="34" height="5" rx="2" fill="{{ $text }}"/>
		</g>
		{{-- footer --}}
		<rect x="18" y="120" width="48" height="4" rx="1.5" fill="{{ $text }}" opacity=".5"/>
		<rect x="18" y="128" width="60" height="6" rx="2" fill="{{ $text }}" opacity=".75"/>
		<rect x="186" y="128" width="36" height="6" rx="2" fill="{{ $accent }}" opacity=".8"/>
		{{-- monogram --}}
		<text x="218" y="38" font-family="Georgia, serif" font-style="italic" font-size="22" font-weight="600" fill="url(#{{ $id }}-acc)" text-anchor="end">DK</text>
	</g>

	{{-- Bottom: trust badges --}}
	<g transform="translate(36,254)" opacity=".75">
		<circle cx="6" cy="10" r="3" fill="{{ $accent }}"/>
		<rect x="16" y="6" width="52" height="6" rx="2" fill="{{ $text }}" opacity=".55"/>

		<circle cx="92" cy="10" r="2" fill="{{ $accent }}" opacity=".4"/>

		<circle cx="106" cy="10" r="3" fill="{{ $accent }}"/>
		<rect x="116" y="6" width="52" height="6" rx="2" fill="{{ $text }}" opacity=".55"/>

		<circle cx="192" cy="10" r="2" fill="{{ $accent }}" opacity=".4"/>

		<circle cx="206" cy="10" r="3" fill="{{ $accent }}"/>
		<rect x="216" y="6" width="62" height="6" rx="2" fill="{{ $text }}" opacity=".55"/>

		<circle cx="302" cy="10" r="2" fill="{{ $accent }}" opacity=".4"/>

		<circle cx="316" cy="10" r="3" fill="{{ $accent }}"/>
		<rect x="326" y="6" width="74" height="6" rx="2" fill="{{ $text }}" opacity=".55"/>
	</g>

	{{-- Decorative corner lines for the dark theme --}}
	@if($isDark)
		<g opacity=".35" stroke="{{ $accent }}" stroke-width="0.8" fill="none">
			<path d="M0 0 L40 0 M0 0 L0 40"/>
			<path d="M720 0 L680 0 M720 0 L720 40"/>
			<path d="M0 360 L40 360 M0 360 L0 320"/>
			<path d="M720 360 L680 360 M720 360 L720 320"/>
		</g>
	@endif
</svg>
