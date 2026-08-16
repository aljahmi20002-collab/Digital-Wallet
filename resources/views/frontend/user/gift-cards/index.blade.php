@extends('frontend.layouts.user.index')
@section('title', __('Gift Cards'))
@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/frontend/css/gift-card.css') }}?v={{ filemtime(public_path('assets/frontend/css/gift-card.css')) }}">
@endpush
@section('content')
    @php
        /*
         * These two locals MUST live inside @section('content') —
         * not above it. With @extends at the top of the file,
         * Blade defers rendering of @section until the parent
         * layout calls @yield('content'). By that point the
         * outer @php scope has been unwound and any locals
         * defined there are gone, so referencing $totalValue or
         * $statusMap inside the section throws
         * "Undefined variable" at runtime.
         */
        $statusMap = [
            'pending'   => ['label' => __('Pending'),   'cls' => 'warning'],
            'scheduled' => ['label' => __('Scheduled'), 'cls' => 'info'],
            'delivered' => ['label' => __('Delivered'), 'cls' => 'success'],
            'redeemed'  => ['label' => __('Redeemed'),  'cls' => 'muted'],
            'expired'   => ['label' => __('Expired'),   'cls' => 'muted'],
            'cancelled' => ['label' => __('Cancelled'), 'cls' => 'danger'],
        ];
        $totalValue = $stats['total_value'] ?? 0;
    @endphp
    <div class="row">
        <div class="col-12">
            <div class="card single-form-card">
                <x-user-feature-header
                    :title="__('Gift Cards')"
                    :subtitle="__('Send a designed gift card from your wallet, or redeem one you have received.')"
                    icon="fas fa-gift"
                >
                    <button type="button"
                            class="btn btn-light-success btn-sm"
                            data-bs-toggle="modal"
                            data-bs-target="#redeemGiftCardModal">
                        <i class="fas fa-qrcode me-1"></i>{{ __('Redeem Code') }}
                    </button>
                    <a href="{{ route('user.gift-card.create') }}" class="btn btn-light-primary btn-sm">
                        <i class="fas fa-plus-circle me-1"></i>{{ __('Create Gift Card') }}
                    </a>
                </x-user-feature-header>

                <div class="card-main">
                    <div class="gc-page">

        {{-- Hero banner --}}
        <div class="gc-hero" style="margin-bottom: 20px;">
            <svg style="position:absolute;inset:0;width:100%;height:100%;opacity:.4;" viewBox="0 0 600 200" preserveAspectRatio="none" aria-hidden="true">
                <defs>
                    <radialGradient id="gc-hb1" cx="85%" cy="20%" r="40%">
                        <stop offset="0%" stop-color="#60A5FA" stop-opacity=".7"/>
                        <stop offset="100%" stop-color="#60A5FA" stop-opacity="0"/>
                    </radialGradient>
                    <radialGradient id="gc-hb2" cx="10%" cy="100%" r="50%">
                        <stop offset="0%" stop-color="#FBBF24" stop-opacity=".4"/>
                        <stop offset="100%" stop-color="#FBBF24" stop-opacity="0"/>
                    </radialGradient>
                    <pattern id="gc-hbd" width="14" height="14" patternUnits="userSpaceOnUse">
                        <circle cx="1" cy="1" r="1" fill="#fff" opacity="0.10"/>
                    </pattern>
                </defs>
                <rect width="600" height="200" fill="url(#gc-hb1)"/>
                <rect width="600" height="200" fill="url(#gc-hb2)"/>
                <rect width="600" height="200" fill="url(#gc-hbd)"/>
            </svg>

            {{-- Hero is decorative only — the primary actions
                 ("Create Gift Card" / "Redeem Code") live in the
                 <x-user-feature-header> slot at the top of the card,
                 matching the voucher / payment-links / send-money
                 pattern. Duplicating them inside the hero made the
                 page feel cluttered. --}}
            <div style="position:relative; z-index:1; max-width:480px;">
                <div class="gc-hero-pill">
                    <i class="fa-solid fa-sparkles"></i> {{ __('New · Gift Cards') }}
                </div>
                <h1>{{ __('Send the perfect gift,') }} <br><em>{{ __('instantly') }}</em></h1>
                <p>{{ __('Designed templates, personal messages, scheduled delivery. Funded straight from your DigiKash wallet.') }}</p>
            </div>
            <div class="gc-hero-visual d-none d-xl-flex">
                <div style="transform: rotate(-7deg) translateX(34px); filter: drop-shadow(0 20px 30px rgba(0,0,0,.35));">
                    <x-gift-card-design preset="anniversary" :amount="100" recipient="Daniel" sender="Ayesha" :width="220"/>
                </div>
                <div style="transform: rotate(6deg); position:relative; z-index:2; filter: drop-shadow(0 20px 30px rgba(0,0,0,.35));">
                    <x-gift-card-design preset="birthday" :amount="50" recipient="Tahsin" sender="Ayesha" :width="230"/>
                </div>
            </div>
        </div>

        {{-- Stat cards --}}
        <div class="gc-stat-grid" style="margin-bottom: 24px;">
            <div class="gc-stat-card">
                <div class="ic" style="background:#DBEAFE; color:#1D4ED8;"><i class="fa-solid fa-paper-plane"></i></div>
                <div>
                    <div class="lbl">{{ __('Sent') }}</div>
                    <div class="val gc-money">{{ $stats['sent'] ?? 0 }}</div>
                </div>
            </div>
            <div class="gc-stat-card">
                <div class="ic" style="background:#FCE7F3; color:#BE185D;"><i class="fa-solid fa-gift"></i></div>
                <div>
                    <div class="lbl">{{ __('Received') }}</div>
                    <div class="val gc-money">{{ $stats['received'] ?? 0 }}</div>
                    @if(! empty($stats['unredeemed']))
                        <div class="pct">{{ $stats['unredeemed'] }} {{ __('unredeemed') }}</div>
                    @endif
                </div>
            </div>
            <div class="gc-stat-card">
                <div class="ic" style="background:#DCFCE7; color:#15803D;"><i class="fa-solid fa-wallet"></i></div>
                <div>
                    <div class="lbl">{{ __('Total Value') }}</div>
                    <div class="val gc-money">{{ siteCurrency('symbol') ?? '$' }}{{ number_format($totalValue, 2) }}</div>
                </div>
            </div>
        </div>

        {{-- Tabs --}}
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2" style="margin-bottom: 14px;">
            <div class="gc-tabs" role="tablist">
                <a href="{{ route('user.gift-card.index', ['tab' => 'sent']) }}" class="tab {{ $tab === 'sent' ? 'is-active' : '' }}" role="tab">
                    {{ __('Sent') }} <span class="count">{{ $stats['sent'] ?? 0 }}</span>
                </a>
                <a href="{{ route('user.gift-card.index', ['tab' => 'received']) }}" class="tab {{ $tab === 'received' ? 'is-active' : '' }}" role="tab">
                    {{ __('Received') }} <span class="count">{{ $stats['received'] ?? 0 }}</span>
                </a>
                <a href="{{ route('user.gift-card.index', ['tab' => 'redeemed']) }}" class="tab {{ $tab === 'redeemed' ? 'is-active' : '' }}" role="tab">
                    {{ __('Redeemed') }}
                </a>
            </div>
        </div>

        {{-- Card list --}}
        <div class="gc-list">
            @forelse($giftCards as $card)
                @php
                    $preset = $card->template?->preset_key ?? 'premium';
                    $meta = $statusMap[$card->status] ?? ['label' => ucfirst($card->status), 'cls' => 'muted'];
                    $symbol = $card->currency?->symbol ?? '$';
                @endphp
                <div class="gc-row">
                    <div class="gc-row-thumb">
                        <x-gift-card-design :preset="$preset" :amount="$card->amount" :recipient="$card->recipient_name" :sender="$card->sender_name" :width="102"/>
                    </div>
                    <div style="min-width: 0;">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <div class="gc-row-name">
                                @if($tab === 'received')
                                    {{ __('From :name', ['name' => $card->sender_name]) }}
                                @else
                                    {{ __('To :name', ['name' => $card->recipient_name]) }}
                                @endif
                            </div>
                            <span class="gc-badge {{ $meta['cls'] }}"><span class="dot"></span>{{ $meta['label'] }}</span>
                        </div>
                        <div class="gc-row-meta">
                            {{ $card->recipient_email }} · {{ __('Sent') }} {{ $card->created_at->format('M d, Y') }}
                        </div>
                        <div class="d-flex align-items-center gap-2 mt-2 flex-wrap">
                            <code class="gc-row-code">{{ $card->code }}</code>
                            <span style="font-size: 11.5px; color: var(--dk-mute);">· {{ $card->template?->name ?? __('Default') }}</span>
                        </div>
                    </div>
                    <div class="gc-row-amount">
                        {{ $symbol }}{{ number_format($card->amount, 2) }}
                        <div style="font-size:11.5px; color:var(--dk-mute); font-weight:600;">{{ $card->currency?->code ?? '' }}</div>
                    </div>
                    <div class="d-flex gap-1 gc-row-actions">
                        <a href="{{ route('gift-card.preview', $card->code) }}" target="_blank" class="gc-btn light sm">
                            <i class="fa-solid fa-eye"></i> {{ __('View') }}
                        </a>
                        @if(in_array($card->status, ['pending', 'scheduled'], true) && $card->user_id === auth()->id())
                            <form action="{{ route('user.gift-card.cancel', $card->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button class="gc-btn light sm" style="color: var(--dk-danger);">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @empty
                <div class="text-center" style="background:#fff; border:1px solid var(--dk-card-line); border-radius:16px; padding:48px 24px;">
                    <i class="fa-solid fa-gift" style="font-size: 36px; color: var(--dk-mute-2);"></i>
                    <h4 style="margin-top: 12px; font-weight: 800; color: var(--dk-ink);">{{ __('No gift cards yet') }}</h4>
                    <p style="color: var(--dk-mute);">{{ __('Send your first gift card or redeem a code you have received.') }}</p>
                    <div class="d-flex justify-content-center gap-2 flex-wrap mt-2">
                        <a href="{{ route('user.gift-card.create') }}" class="gc-btn primary">
                            <i class="fa-solid fa-plus"></i> {{ __('Create Gift Card') }}
                        </a>
                        <button type="button"
                                class="gc-btn light"
                                data-bs-toggle="modal"
                                data-bs-target="#redeemGiftCardModal">
                            <i class="fa-solid fa-qrcode"></i> {{ __('Redeem Code') }}
                        </button>
                    </div>
                </div>
            @endforelse
        </div>

        @if($giftCards->hasPages())
            <div style="margin-top: 18px;">
                {{ $giftCards->links() }}
            </div>
        @endif
                    </div> {{-- /.gc-page --}}
                </div> {{-- /.card-main --}}
            </div> {{-- /.card.single-form-card --}}
        </div> {{-- /.col-12 --}}
    </div> {{-- /.row --}}

    {{-- Redeem Gift Card modal — opened from the "Redeem Code" buttons. --}}
    @include('frontend.user.gift-cards.partials._redeem_gift_card_modal')
@endsection
