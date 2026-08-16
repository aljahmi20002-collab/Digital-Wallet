@php
    $u = auth()->user();
    $firstName = $u ? trim(explode(' ', $u->name)[0] ?? $u->name) : '';
    $unreadCount = $notificationCount ?? 0;
    $quickFunctionAccentMap = [
        'card' => 'indigo',
        'deposit' => 'emerald',
        'history' => 'sky',
        'withdraw' => 'rose',
        'exchange' => 'amber',
        'p2p_trading' => 'violet',
        'list-2' => 'slate',
        'add' => 'teal',
        'wallet' => 'cyan',
        'send-money' => 'blue',
        'request-money' => 'green',
        'mobile-recharge' => 'teal',
        'voucher' => 'orange',
        'referrals' => 'pink',
        'support' => 'cyan',
        'merchant' => 'purple',
        'sidebar-agent' => 'violet',
        'linked' => 'fuchsia',
        'qrcode' => 'zinc',
        'layer' => 'indigo',
        'trending-up' => 'emerald',
    ];
    $quickFunctionTitleMap = [
        'card' => __('My Cards'),
        'deposit' => __('Add Money'),
        'history' => __('Transaction History'),
        'send-money' => __('Send Money'),
        'request-money' => __('Request Money'),
        'linked' => __('Payment Links'),
        'sidebar-agent' => __('Agent Services'),
        'list-2' => __('My Ads'),
        'support' => __('Support Ticket'),
        'layer' => __('Subscriptions'),
        'trending-up' => __('Wallet Earn'),
    ];
    $quickFunctionOrder = [
        'wallet' => 10,
        'send-money' => 20,
        'request-money' => 30,
        'deposit' => 40,
        'withdraw' => 50,
        'history' => 60,
        'sidebar-agent' => 70,
        'linked' => 80,
        'mobile-recharge' => 90,
        'exchange' => 100,
        'card' => 110,
        'voucher' => 120,
        'referrals' => 130,
        'p2p_trading' => 135,
        'list-2' => 140,
        'layer' => 150,
        'trending-up' => 160,
        'support' => 170,
        'merchant' => 180,
        'add' => 190,
    ];
    $quickFunctionLinks = array_values(array_merge($quickLinksMain ?? [], $quickLinksMore ?? []));
    usort($quickFunctionLinks, fn (array $left, array $right): int => ($quickFunctionOrder[$left['icon']] ?? 999) <=> ($quickFunctionOrder[$right['icon']] ?? 999));

    try {
        $quickFunctionDefaultWallet = $u?->default_wallet;
    } catch (\Throwable) {
        $quickFunctionDefaultWallet = null;
    }

    $quickFunctionDefaultCurrency = $quickFunctionDefaultWallet?->currency;
    $quickFunctionDefaultSymbol = $quickFunctionDefaultCurrency?->symbol ?? siteCurrency('symbol');
    $quickFunctionDefaultCode = $quickFunctionDefaultCurrency?->code ?? siteCurrency();
    $quickFunctionDefaultBalance = $quickFunctionDefaultWallet
        ? $quickFunctionDefaultSymbol.' '.number_format((float) $quickFunctionDefaultWallet->balance, 2)
        : __('No wallet yet');
@endphp

{{-- ==========================================================
 | Mobile Sticky App Header (visible < 992px only)
 | Sits ABOVE dev's legacy _mobile_navbar (which CSS hides on mobile).
 ========================================================== --}}
<header class="dk-mobile-header d-lg-none" data-scrolled="0">
    <div class="dk-mobile-header__profile">
        <a href="{{ route('user.settings.profile') }}" class="dk-avatar" aria-label="{{ __('Profile') }}">
            @if($u && $u->avatar_alt)
                <img src="{{ asset($u->avatar_alt) }}" alt="{{ $u->name }}" loading="lazy">
            @else
                {{ $u ? mb_strtoupper(mb_substr($u->name, 0, 1)) : 'U' }}
            @endif
        </a>

        <div class="dk-greet">
            <span class="dk-greet__hi">{{ __('Welcome back') }}</span>
            <span class="dk-greet__name">
                <span class="dk-greet__txt">{{ $firstName }}</span>
                @if($u && $u->rank)
                    <a class="dk-rank-chip" href="{{ route('user.rank.showcase') }}">
                        <i class="fa fa-crown"></i>{{ $u->rank->name }}
                    </a>
                @endif
            </span>
        </div>
    </div>

    <div class="dk-mobile-header__actions">
        {{-- My QR Code --}}
        <a href="{{ route('user.wallet.my-qr-code') }}"
           class="dk-icon-btn dk-open-qr-scan"
           aria-label="{{ __('My QR Code') }}">
            <i class="fa fa-qrcode"></i>
        </a>

        {{-- Language --}}
        <button type="button" class="dk-icon-btn dk-open-lang" aria-label="{{ __('Language') }}" aria-expanded="false">
            <i class="fa fa-globe"></i>
        </button>

        {{-- Apps / Quick Functions --}}
        <button type="button" class="dk-icon-btn dk-open-apps" aria-label="{{ __('Quick Functions') }}" aria-expanded="false">
            <i class="fa fa-grip"></i>
        </button>

        {{-- Notifications --}}
        <button type="button" class="dk-icon-btn dk-open-notif" aria-label="{{ __('Notifications') }}" aria-expanded="false">
            <i class="fa fa-bell"></i>
            @if($unreadCount > 0)
                <span class="badge-num">{{ $unreadCount < 9 ? $unreadCount : '9+' }}</span>
            @endif
        </button>
    </div>
</header>

{{-- Backdrops --}}
<div class="dk-sheet-backdrop dk-lang-backdrop d-lg-none"></div>
<div class="dk-sheet-backdrop dk-apps-backdrop d-lg-none"></div>
<div class="dk-sheet-backdrop dk-notif-backdrop d-lg-none"></div>
<div class="dk-sheet-backdrop dk-more-backdrop d-lg-none"></div>

{{-- Payment QR scanner modal --}}
<div class="dk-qr-scanner-backdrop"
     data-dk-qr-scanner-backdrop
     aria-hidden="true"></div>
<section class="dk-qr-scanner"
         data-dk-qr-scanner-modal
         data-starting-label="{{ __('Starting camera...') }}"
         data-ready-label="{{ __('Point your camera at a payment link, wallet, or agent QR code.') }}"
         data-detected-label="{{ __('QR code detected. Opening...') }}"
         data-unsupported-label="{{ __('Camera is open, but automatic QR reading is not supported on this browser. Paste the QR link below.') }}"
         data-invalid-label="{{ __('This QR code is not a valid DigiKash payment, wallet, or agent QR.') }}"
         data-camera-error-label="{{ __('Camera access is blocked or unavailable. Paste the QR link below.') }}"
         data-camera-permission-label="{{ __('Camera permission is required to scan QR codes. Tap Allow Camera and approve access.') }}"
         data-camera-denied-label="{{ __('Camera permission is blocked. Enable camera access from browser site settings, then tap Allow Camera.') }}"
         data-payment-link-base-url="{{ url('/payment-link') }}"
         data-send-money-base-url="{{ route('user.send-money.create') }}"
         data-agent-cash-out-url-template="{{ route('user.agent.qr.cash-out', ['token' => '__TOKEN__']) }}"
         role="dialog"
         aria-modal="true"
         aria-labelledby="dkQrScannerTitle"
         aria-hidden="true">
    <div class="dk-qr-scanner__head">
        <div>
            <h3 class="dk-qr-scanner__title" id="dkQrScannerTitle">{{ __('Scan QR') }}</h3>
            <p class="dk-qr-scanner__subtitle">{{ __('Open payment checkout, send money, or agent cash-out from any DigiKash QR code.') }}</p>
        </div>
        <button type="button"
                class="dk-icon-btn dk-qr-scanner__close"
                data-dk-qr-scanner-close
                aria-label="{{ __('Close') }}">
            <i class="fa fa-xmark"></i>
        </button>
    </div>
    <div class="dk-qr-scanner__viewport">
        <video data-dk-qr-scanner-video playsinline muted></video>
        <canvas data-dk-qr-scanner-canvas hidden></canvas>
        <span class="dk-qr-scanner__frame" aria-hidden="true"></span>
    </div>
    <p class="dk-qr-scanner__status" data-dk-qr-scanner-status>
        {{ __('Tap scan to start the camera.') }}
    </p>
    <button type="button"
            class="dk-qr-scanner__permission"
            data-dk-qr-scanner-permission
            hidden>
        <i class="fa fa-camera"></i>{{ __('Allow Camera') }}
    </button>
    <form class="dk-qr-scanner__manual" data-dk-qr-scanner-manual novalidate>
        <label for="dkQrScannerUrl">{{ __('Payment, wallet, or agent QR link') }}</label>
        <div class="dk-qr-scanner__manual-row">
            <input id="dkQrScannerUrl"
                   type="url"
                   inputmode="url"
                   autocomplete="off"
                   data-dk-qr-scanner-input
                   placeholder="{{ url('/user/agent/qr') }}/...">
            <button type="submit">{{ __('Open') }}</button>
        </div>
    </form>
</section>

{{-- Language sheet --}}
<div class="dk-sheet dk-lang-sheet d-lg-none" role="dialog" aria-label="{{ __('Choose language') }}" aria-hidden="true">
    <div class="dk-sheet__handle"></div>
    <div class="dk-sheet__head">
        <h3 class="dk-sheet__title">{{ __('Language') }}</h3>
        <button type="button" class="dk-icon-btn dk-sheet-close" aria-label="{{ __('Close') }}"><i class="fa fa-xmark"></i></button>
    </div>
    <div class="dk-lang-list">
        @if(isset($languages))
            @foreach($languages as $language)
                @php
                    $isActiveLanguage = $language->code === app()->getLocale();
                @endphp
                <a href="{{ route('locale-set', $language->code) }}"
                   class="dk-lang-row {{ $isActiveLanguage ? 'is-active' : '' }}"
                   data-selected="{{ $isActiveLanguage ? '1' : '0' }}"
                   aria-current="{{ $isActiveLanguage ? 'true' : 'false' }}">
                    <span class="dk-lang-row__flag">
                        @if($language->flag)
                            <img src="{{ asset($language->flag) }}" alt="{{ $language->name }}" loading="lazy">
                        @else
                            {{ strtoupper(substr($language->code, 0, 2)) }}
                        @endif
                    </span>
                    <span class="dk-lang-row__txt">
                        <span class="dk-lang-row__native">{{ $language->name }}</span>
                        <span class="dk-lang-row__en">{{ strtoupper($language->code) }}</span>
                    </span>
                    <span class="dk-lang-row__check" aria-hidden="true">
                        @if($isActiveLanguage)
                            <i class="fa fa-check"></i>
                        @endif
                    </span>
                </a>
            @endforeach
        @endif
    </div>
</div>

{{-- Apps / Quick Functions sheet --}}
<div class="dk-sheet dk-apps-sheet dk-apps-sheet--quick-menu quick-function-menu d-lg-none" role="dialog" aria-label="{{ __('Quick Functions') }}" aria-hidden="true">
    <div class="dk-sheet__handle"></div>

    <div class="quick-function-menu__header">
        <span class="quick-function-menu__header-mark" aria-hidden="true">
            <x-icon name="apps" height="18" width="18" />
        </span>

        <div class="quick-function-menu__heading-group">
            <h3 class="quick-function-menu__heading">{{ __('Quick Menu') }}</h3>
            <span class="quick-function-menu__subtitle">{{ __('Wallet Dashboard') }}</span>
        </div>

        <button type="button" class="quick-function-menu__close dk-sheet-close" data-quick-function-close aria-label="{{ __('Close') }}">
            <i class="fa-solid fa-xmark" aria-hidden="true"></i>
        </button>
    </div>

    <div class="quick-function-menu__body">
        <div class="quick-function-menu__feature-row">
            <a href="{{ route('user.wallet.index') }}" class="quick-function-menu__wallet-card">
                <span class="quick-function-menu__wallet-art" aria-hidden="true">
                    <span class="quick-function-menu__wallet-paper quick-function-menu__wallet-paper--one"></span>
                    <span class="quick-function-menu__wallet-paper quick-function-menu__wallet-paper--two"></span>
                    <span class="quick-function-menu__wallet-body"></span>
                    <span class="quick-function-menu__wallet-flap"></span>
                </span>
                <span class="quick-function-menu__wallet-copy">
                    <span class="quick-function-menu__wallet-label">{{ __('Default Wallet') }}</span>
                    <span class="quick-function-menu__wallet-amount">
                        @if($quickFunctionDefaultWallet)
                            <span class="quick-function-menu__wallet-balance" data-balance-mask data-hidden="0">
                                {{ $quickFunctionDefaultBalance }}
                            </span>
                            <span class="quick-function-menu__wallet-eye dk-balance-eye"
                                  role="button"
                                  tabindex="0"
                                  aria-label="{{ __('Toggle balance') }}"
                                  aria-pressed="false">
                                <i class="fa fa-eye" aria-hidden="true"></i>
                            </span>
                        @else
                            {{ $quickFunctionDefaultBalance }}
                        @endif
                    </span>
                    <span class="quick-function-menu__wallet-code">{{ $quickFunctionDefaultCode }}</span>
                </span>
                <i class="fas fa-chevron-right quick-function-menu__wallet-arrow" aria-hidden="true"></i>
            </a>

            <button type="button" class="quick-function-menu__scan" data-dk-qr-scanner-open>
                <span class="quick-function-menu__scan-icon" aria-hidden="true">
                    <i class="fas fa-qrcode"></i>
                </span>
                <span>{{ __('Scan & Pay') }}</span>
            </button>
        </div>

        <div class="quick-function-menu__grid">
            @foreach($quickFunctionLinks as $quickFunctionLink)
                @php
                    $quickFunctionDisplayTitle = $quickFunctionTitleMap[$quickFunctionLink['icon']] ?? $quickFunctionLink['title'];

                    if ($quickFunctionLink['icon'] === 'history' && $quickFunctionLink['title'] !== __('History')) {
                        $quickFunctionDisplayTitle = $quickFunctionLink['title'];
                    }
                @endphp
                <a href="{{ $quickFunctionLink['link'] }}" class="quick-function-menu__action" data-accent="{{ $quickFunctionAccentMap[$quickFunctionLink['icon']] ?? 'blue' }}" title="{{ $quickFunctionLink['title'] }}">
                    <span class="quick-function-menu__icon" aria-hidden="true">
                        <x-icon name="{{ $quickFunctionLink['icon'] }}" height="22" width="22" />
                    </span>
                    <span class="quick-function-menu__label">{{ $quickFunctionDisplayTitle }}</span>
                </a>
            @endforeach
        </div>

        <a href="{{ route('user.wallet.index') }}" class="quick-function-menu__footer-link">
            <span class="quick-function-menu__footer-icon" aria-hidden="true">
                <i class="fas fa-shield-alt"></i>
            </span>
            <span class="quick-function-menu__footer-copy">
                <strong>{{ __('Your Wallet, Your Control') }}</strong>
                <span>{{ __('Fast, secure & reliable payments') }}</span>
            </span>
            <i class="fas fa-chevron-right quick-function-menu__footer-arrow" aria-hidden="true"></i>
        </a>
    </div>
</div>

{{-- Notifications bottom sheet --}}
<aside class="dk-notif-panel d-lg-none" role="dialog" aria-label="{{ __('Notifications') }}" aria-hidden="true">
    <div class="dk-sheet__handle" aria-hidden="true"></div>
    <header class="dk-notif-panel__head">
        <div class="dk-notif-panel__title">
            <span>{{ __('Notifications') }}</span>
            @if($unreadCount > 0)
                <span class="dk-notif-panel__count">{{ __(':count new', ['count' => $unreadCount < 9 ? $unreadCount : '9+']) }}</span>
            @endif
        </div>
        <div class="dk-notif-panel__actions">
            @if($unreadCount > 0)
                <a class="dk-notif-panel__mark" href="{{ route('user.notifications.read-all') }}">{{ __('Mark all') }}</a>
            @endif
            <button type="button" class="dk-notif-panel__close dk-sheet-close" aria-label="{{ __('Close') }}"><i class="fa fa-xmark"></i></button>
        </div>
    </header>
    <div class="dk-notif-list">
        @forelse(($notifications ?? collect())->take(20) as $notification)
            @php
                $data = $notification->data;
                $statusClass = \App\Enums\NotificationActionType::getClass((string) ($data['action_type'] ?? ''));
                $isUnread = $notification->read_at === null;
                $notificationTone = match ($statusClass) {
                    'success' => 'green',
                    'danger' => 'rose',
                    'warning' => 'amber',
                    'info', 'primary' => 'blue',
                    default => 'violet',
                };
            @endphp
            <a href="{{ $data['action_link'] ?? 'javascript:void(0)' }}"
               class="dk-notif-item {{ $isUnread ? 'is-unread' : '' }} read-notification"
               data-tone="{{ $notificationTone }}"
               data-id="{{ $notification->id }}">
                <span class="dk-notif-item__icon">
                    <x-icon name="{{ $data['icon'] ?? 'bell' }}" height="16" width="16"/>
                </span>
                <span class="dk-notif-item__body">
                    <span class="dk-notif-item__line">
                        <strong>{{ $data['title'] ?? __('Notification') }}</strong>
                        @if(! empty($data['message']))
                            <span>{{ $data['message'] }}</span>
                        @endif
                    </span>
                    <small class="dk-notif-item__time">{{ $notification->created_at?->diffForHumans() }}</small>
                </span>
                @if($isUnread)
                    <span class="dk-notif-item__dot"></span>
                @endif
            </a>
        @empty
            <x-user-not-found
                class="dk-notif-empty"
                :title="__('No notifications found')"
                :message="__('New alerts will appear here as soon as they arrive.')"
                icon="fa-bell"
            />
        @endforelse
    </div>
</aside>
