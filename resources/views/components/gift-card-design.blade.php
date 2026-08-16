@props([
    'preset' => 'premium',
    'amount' => 100,
    'currency' => 'USD',
    'currencySymbol' => '$',
    'recipient' => 'Recipient',
    'sender' => 'You',
    'code' => null,
    'showCode' => false,
    'width' => 360,
])
@php
    $presets = [
        'birthday'    => ['bg' => 'linear-gradient(135deg, #FB7185 0%, #F472B6 45%, #C026D3 100%)', 'ink' => '#FFFFFF', 'chip' => 'rgba(255,255,255,.22)', 'motif' => 'confetti', 'ribbon' => 'Happy Birthday'],
        'holiday'     => ['bg' => 'linear-gradient(135deg, #064E3B 0%, #065F46 40%, #0F766E 100%)', 'ink' => '#FFFFFF', 'chip' => 'rgba(255,255,255,.18)', 'motif' => 'snow',     'ribbon' => "Season's Greetings"],
        'thankyou'    => ['bg' => 'linear-gradient(135deg, #FDE68A 0%, #FBBF24 55%, #D97706 100%)', 'ink' => '#3F2A05', 'chip' => 'rgba(63,42,5,.10)',     'motif' => 'rays',     'ribbon' => 'With Gratitude'],
        'anniversary' => ['bg' => 'linear-gradient(135deg, #4C1D95 0%, #7E22CE 45%, #BE185D 100%)', 'ink' => '#FFFFFF', 'chip' => 'rgba(255,255,255,.20)', 'motif' => 'hearts',   'ribbon' => 'Happy Anniversary'],
        'congrats'    => ['bg' => 'linear-gradient(135deg, #1E3A8A 0%, #3B82F6 45%, #06B6D4 100%)', 'ink' => '#FFFFFF', 'chip' => 'rgba(255,255,255,.20)', 'motif' => 'sparkles', 'ribbon' => 'Congratulations'],
        'premium'     => ['ribbon' => 'A Gift For You', 'bg' => 'linear-gradient(135deg, #0B1330 0%, #14245F 45%, #1E3A8A 100%)', 'ink' => '#FFFFFF', 'chip' => 'rgba(255,255,255,.14)', 'motif' => 'mesh'],
    ];
    $key  = array_key_exists($preset, $presets) ? $preset : 'premium';
    $t    = $presets[$key];
    $w    = (int) $width;
    $h    = (int) round($w / 1.586);
    $isLightInk = strtoupper($t['ink']) === '#FFFFFF';
    $radius = $w >= 320 ? 18 : 12;
    // Subtle inner highlight only — outer drop shadows were stacking up
    // visually when the card sits inside its own list/grid/preview cards.
    // Clean minimal look reads "premium" without the noise.
    $shadow = 'inset 0 0 0 1px rgba(255,255,255,.10)';
    $formattedAmount = $currencySymbol . number_format((float) $amount, 2);
@endphp
<div class="gc-card"
     style="width:{{ $w }}px; height:{{ $h }}px; position:relative; background:{{ $t['bg'] }}; color:{{ $t['ink'] }}; border-radius:{{ $radius }}px; overflow:hidden; box-shadow:{{ $shadow }}; font-family:'Plus Jakarta Sans', system-ui, sans-serif; flex:none;">

    {{-- Motif SVG overlays --}}
    @switch($t['motif'])
        @case('confetti')
            <svg style="position:absolute;inset:0;width:100%;height:100%;pointer-events:none" viewBox="0 0 400 252" preserveAspectRatio="none">
                @for($i = 0; $i < 28; $i++)
                    @php
                        $x = ($i * 47) % 400; $y = ($i * 31) % 252;
                        $r = ($i % 3) * 4 + 6; $rot = ($i * 23) % 360;
                        $colors = ['#FDE68A','#FCA5A5','#A7F3D0','#BFDBFE','#FBCFE8'];
                    @endphp
                    <rect x="{{ $x }}" y="{{ $y }}" width="{{ $r }}" height="{{ $r * 0.5 }}" fill="{{ $colors[$i % 5] }}" opacity=".55" transform="rotate({{ $rot }} {{ $x }} {{ $y }})" rx="1"/>
                @endfor
            </svg>
            @break
        @case('snow')
            <svg style="position:absolute;inset:0;width:100%;height:100%;pointer-events:none" viewBox="0 0 400 252" preserveAspectRatio="none">
                @for($i = 0; $i < 40; $i++)
                    @php $x = ($i * 41) % 400; $y = ($i * 19) % 252; $op = 0.25 + ($i % 3) * 0.15; @endphp
                    <circle cx="{{ $x }}" cy="{{ $y }}" r="{{ ($i % 3) + 1.2 }}" fill="#fff" opacity="{{ $op }}"/>
                @endfor
            </svg>
            @break
        @case('rays')
            <svg style="position:absolute;inset:0;width:100%;height:100%;pointer-events:none" viewBox="0 0 400 252" preserveAspectRatio="none">
                <defs>
                    <radialGradient id="gc-ray-{{ md5($key) }}" cx="20%" cy="0%" r="80%">
                        <stop offset="0%" stop-color="#fff" stop-opacity=".55"/>
                        <stop offset="100%" stop-color="#fff" stop-opacity="0"/>
                    </radialGradient>
                </defs>
                <rect width="400" height="252" fill="url(#gc-ray-{{ md5($key) }})"/>
                @for($i = 0; $i < 8; $i++)
                    <line x1="80" y1="0" x2="{{ $i * 60 }}" y2="252" stroke="#fff" stroke-opacity="0.06" stroke-width="1"/>
                @endfor
            </svg>
            @break
        @case('hearts')
            <svg style="position:absolute;inset:0;width:100%;height:100%;pointer-events:none" viewBox="0 0 400 252" preserveAspectRatio="none">
                @for($i = 0; $i < 14; $i++)
                    @php
                        $x = ($i * 53) % 400; $y = ($i * 37) % 252;
                        $s = 8 + ($i % 3) * 4;
                        $cx = $x; $cy = $y + $s * 0.3;
                    @endphp
                    <circle cx="{{ $cx }}" cy="{{ $cy }}" r="{{ $s * 0.4 }}" fill="#fff" opacity="0.10"/>
                @endfor
            </svg>
            @break
        @case('sparkles')
            <svg style="position:absolute;inset:0;width:100%;height:100%;pointer-events:none" viewBox="0 0 400 252" preserveAspectRatio="none">
                @for($i = 0; $i < 18; $i++)
                    @php $x = ($i * 41) % 400; $y = ($i * 23) % 252; $s = 4 + ($i % 3) * 3; @endphp
                    <g opacity="0.4" transform="translate({{ $x }} {{ $y }})">
                        <path d="M0 -{{ $s }} L{{ $s * 0.3 }} -{{ $s * 0.3 }} L{{ $s }} 0 L{{ $s * 0.3 }} {{ $s * 0.3 }} L0 {{ $s }} L-{{ $s * 0.3 }} {{ $s * 0.3 }} L-{{ $s }} 0 L-{{ $s * 0.3 }} -{{ $s * 0.3 }} Z" fill="#FDE68A"/>
                    </g>
                @endfor
            </svg>
            @break
        @case('mesh')
            <svg style="position:absolute;inset:0;width:100%;height:100%;pointer-events:none" viewBox="0 0 400 252" preserveAspectRatio="none">
                <defs>
                    <radialGradient id="gc-m1-{{ md5($key) }}" cx="85%" cy="20%" r="50%">
                        <stop offset="0%" stop-color="#60A5FA" stop-opacity=".55"/>
                        <stop offset="100%" stop-color="#60A5FA" stop-opacity="0"/>
                    </radialGradient>
                    <radialGradient id="gc-m2-{{ md5($key) }}" cx="10%" cy="90%" r="55%">
                        <stop offset="0%" stop-color="#FBBF24" stop-opacity=".30"/>
                        <stop offset="100%" stop-color="#FBBF24" stop-opacity="0"/>
                    </radialGradient>
                    <pattern id="gc-dots-{{ md5($key) }}" width="14" height="14" patternUnits="userSpaceOnUse">
                        <circle cx="1" cy="1" r="1" fill="#fff" opacity="0.08"/>
                    </pattern>
                </defs>
                <rect width="400" height="252" fill="url(#gc-m1-{{ md5($key) }})"/>
                <rect width="400" height="252" fill="url(#gc-m2-{{ md5($key) }})"/>
                <rect width="400" height="252" fill="url(#gc-dots-{{ md5($key) }})"/>
            </svg>
            @break
    @endswitch

    {{-- Brand mark --}}
    <div style="position:absolute; top:{{ $w * 0.045 }}px; left:{{ $w * 0.045 }}px; display:flex; align-items:center; gap:{{ $w * 0.018 }}px; font-weight:800; letter-spacing:-.02em; font-size:{{ $w * 0.044 }}px; line-height:1; color:{{ $t['ink'] }};">
        <div style="width:{{ $w * 0.062 }}px; height:{{ $w * 0.062 }}px; border-radius:{{ $w * 0.014 }}px; background:rgba(255,255,255,.22); border:1px solid rgba(255,255,255,.35); display:grid; place-items:center;">
            <svg viewBox="0 0 24 24" width="{{ $w * 0.04 }}" height="{{ $w * 0.04 }}" fill="none">
                <path d="M4 7h12a4 4 0 0 1 0 8H4V7z" fill="{{ $t['ink'] }}"/>
            </svg>
        </div>
        DigiKash
    </div>

    {{-- Ribbon --}}
    <div style="position:absolute; top:{{ $w * 0.05 }}px; right:{{ $w * 0.05 }}px; background:{{ $t['chip'] }}; border:1px solid {{ $isLightInk ? 'rgba(255,255,255,.30)' : 'rgba(63,42,5,.18)' }}; padding:{{ $w * 0.014 }}px {{ $w * 0.028 }}px; border-radius:999px; font-size:{{ $w * 0.028 }}px; font-weight:700; letter-spacing:.08em; text-transform:uppercase;">
        {{ $t['ribbon'] }}
    </div>

    @php
        /*
         * When the code stripe is visible (recipient view), the amount
         * block needs to sit higher otherwise the stripe overlaps the
         * digits. 28% top leaves a clean ~25px gap between the amount
         * descender and the stripe top edge at every card width.
         */
        $amountTopPct = $showCode && $code ? 26 : 38;
    @endphp
    {{-- Amount --}}
    <div style="position:absolute; left:{{ $w * 0.05 }}px; right:{{ $w * 0.05 }}px; top:{{ $amountTopPct }}%; text-align:left;">
        <div style="font-size:{{ $w * 0.028 }}px; letter-spacing:.16em; text-transform:uppercase; font-weight:700; opacity:.78; margin-bottom:{{ $w * 0.01 }}px;">
            {{ __('Gift Card Value') }}
        </div>
        <div class="gc-money" style="font-size:{{ $w * 0.16 }}px; font-weight:800; letter-spacing:-.03em; line-height:.95; {{ $isLightInk ? 'text-shadow:0 2px 12px rgba(0,0,0,.18);' : '' }}">
            {{ $formattedAmount }}
        </div>
    </div>

    {{-- Footer To/From --}}
    <div style="position:absolute; left:{{ $w * 0.05 }}px; right:{{ $w * 0.05 }}px; bottom:{{ $w * 0.05 }}px; display:flex; justify-content:space-between; align-items:flex-end; gap:{{ $w * 0.04 }}px;">
        <div style="min-width:0; flex:1;">
            <div style="font-size:{{ $w * 0.028 }}px; letter-spacing:.12em; text-transform:uppercase; font-weight:700; opacity:.72;">{{ __('To') }}</div>
            <div style="font-size:{{ $w * 0.045 }}px; font-weight:700; margin-top:{{ $w * 0.005 }}px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $recipient ?: '—' }}</div>
        </div>
        <div style="min-width:0; flex:1; text-align:right;">
            <div style="font-size:{{ $w * 0.028 }}px; letter-spacing:.12em; text-transform:uppercase; font-weight:700; opacity:.72;">{{ __('From') }}</div>
            <div style="font-size:{{ $w * 0.045 }}px; font-weight:700; margin-top:{{ $w * 0.005 }}px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $sender ?: '—' }}</div>
        </div>
    </div>

    {{-- Code stripe (recipient view only) --}}
    @if($showCode && $code)
        <div style="position:absolute; left:{{ $w * 0.05 }}px; right:{{ $w * 0.05 }}px; bottom:{{ $w * 0.20 }}px; background:{{ $isLightInk ? 'rgba(0,0,0,.22)' : 'rgba(255,255,255,.55)' }}; border:1px dashed {{ $isLightInk ? 'rgba(255,255,255,.35)' : 'rgba(63,42,5,.25)' }}; padding:{{ $w * 0.018 }}px {{ $w * 0.032 }}px; border-radius:8px; font-family:ui-monospace, 'SF Mono', Menlo, monospace; font-size:{{ $w * 0.036 }}px; font-weight:700; letter-spacing:.18em; text-align:center;">
            {{ $code }}
        </div>
    @endif
</div>
