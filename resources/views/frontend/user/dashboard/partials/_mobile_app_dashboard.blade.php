@php
    use App\Enums\AgentStatus;
    use App\Enums\AmountFlow;
    use App\Enums\KycStatus;
    use App\Enums\TrxType;
    use App\Services\WalletService;
    use Illuminate\Support\Facades\Route;

    $dashboardUser = auth()->user();
    $wallets = $dashboardUser ? ($dashboardUser->activeWallets() ?? collect()) : collect();
    $walletService = app(WalletService::class);
    $mobileWalletCounterCashOutRoute = route('user.agent.index', ['tab' => 'counter-cashout']);

    if ($dashboardUser?->isAgent()) {
        $mobileWalletCounterCashOutAgent = $dashboardUser->agent()
            ->where('status', AgentStatus::APPROVED)
            ->where('qr_enabled', true)
            ->whereNotNull('qr_token')
            ->latest()
            ->first();

        $mobileWalletCounterCashOutRoute = $mobileWalletCounterCashOutAgent?->qrCashOutUrl() ?? $mobileWalletCounterCashOutRoute;
    }

    $mobileWalletProfile = match (true) {
        $dashboardUser?->isMerchant() => [
            'role' => 'merchant',
            'label' => __('Merchant Wallet'),
            'primary_label' => __('Add Funds'),
            'primary_icon' => 'fa-plus',
            'primary_route' => route('user.deposit.create'),
            'secondary_label' => __('Shops'),
            'secondary_icon' => 'fa-store',
            'secondary_route' => route('user.merchant.index'),
        ],
        $dashboardUser?->isAgent() => [
            'role' => 'agent',
            'label' => __('Agent Wallet'),
            'primary_label' => __('Cash-In'),
            'primary_icon' => 'fa-wallet',
            'primary_route' => route('user.agent.index', ['tab' => 'cash-in']),
            'secondary_label' => __('Counter Cash-Out'),
            'secondary_icon' => 'fa-money-bill-wave',
            'secondary_route' => $mobileWalletCounterCashOutRoute,
        ],
        default => [
            'role' => 'user',
            'label' => __('Personal Wallet'),
            'primary_label' => __('Deposit'),
            'primary_icon' => 'fa-plus',
            'primary_route' => route('user.deposit.create'),
            'secondary_label' => __('My Wallets'),
            'secondary_icon' => 'fa-sliders',
            'secondary_route' => route('user.wallet.index'),
        ],
    };

    // KYC slim banner state
    $kyc = $dashboardUser?->kycSubmission;
    $kycStatus = $kyc?->status ?? null;
    $kycShow = !$dashboardUser?->isKycVerified() && (isActive('user.settings.kyc.verify') !== 'active');
    $kycState = 'missing';
    $kycTitle = __('Verify your identity');
    $kycSub   = __('Submit your details and documents to unlock wallet features.');
    $kycCta   = __('Start verification');
    $kycIcon  = 'fa-id-card';
    if ($kycStatus === KycStatus::PENDING) {
        $kycState = 'pending';
        $kycTitle = __('Identity review in progress');
        $kycSub   = __('Reviews usually finish within 24 hours.');
        $kycCta   = __('Check status');
        $kycIcon  = 'fa-hourglass-half';
    } elseif ($kycStatus === KycStatus::REJECTED) {
        $kycState = 'rejected';
        $kycTitle = __('Update your KYC details');
        $kycSub   = __('Review the notes and resubmit your documents.');
        $kycCta   = __('Resubmit KYC');
        $kycIcon  = 'fa-triangle-exclamation';
    }

    $featureManager = app(\App\Services\FeatureManager::class);

    $actionHub = match (true) {
        $dashboardUser?->isMerchant() => [
            'title' => __('Merchant actions'),
            'badge' => __('Merchant'),
            'items' => [
                ['label' => __('Create Link'), 'meta' => __('Get paid'), 'route' => 'user.payment-links.create', 'icon' => 'payment-link', 'tone' => 'primary', 'feature' => 'payment_link'],
                ['label' => __('Merchant'), 'meta' => __('Shops'), 'route' => 'user.merchant.index', 'icon' => 'merchant', 'tone' => 'merchant'],
                ['label' => __('Sales'), 'meta' => __('Payments'), 'route' => 'user.transaction.index', 'params' => ['type' => 'receive_payment'], 'icon' => 'receive-payment-1', 'tone' => 'success'],
                ['label' => __('Links'), 'meta' => __('Manage'), 'route' => 'user.payment-links.index', 'icon' => 'payment-link', 'tone' => 'primary', 'feature' => 'payment_link'],
            ],
        ],
        $dashboardUser?->isAgent() => [
            'title' => __('Agent actions'),
            'badge' => __('Agent'),
            'items' => [
                ['label' => __('Cash In'), 'meta' => __('Top-up'), 'route' => 'user.agent.index', 'params' => ['tab' => 'cash-in'], 'icon' => 'receive-money', 'tone' => 'primary', 'feature' => 'agent_program'],
                ['label' => __('Cash Out'), 'meta' => __('Counter'), 'route' => 'user.agent.index', 'params' => ['tab' => 'counter-cashout'], 'icon' => 'qrcode', 'tone' => 'agent', 'feature' => 'agent_program'],
                ['label' => __('Queue'), 'meta' => __('Requests'), 'route' => 'user.agent.index', 'params' => ['tab' => 'cash-out-requests'], 'icon' => 'request-list', 'tone' => 'warning', 'feature' => 'agent_program'],
                ['label' => __('Wallet'), 'meta' => __('Balances'), 'route' => 'user.wallet.index', 'icon' => 'wallet', 'tone' => 'primary'],
            ],
        ],
        default => [
            'title' => __('Wallet actions'),
            'badge' => __('Wallet'),
            'items' => [
                ['label' => __('Deposit'), 'meta' => __('Add funds'), 'route' => 'user.deposit.create', 'icon' => 'deposit', 'tone' => 'success', 'feature' => 'deposit_money'],
                ['label' => __('Withdraw'), 'meta' => __('Cash out'), 'route' => 'user.withdraw.create', 'icon' => 'withdraw', 'tone' => 'danger', 'feature' => 'withdraw_money'],
                ['label' => __('Send'), 'meta' => __('Transfer'), 'route' => 'user.send-money.create', 'icon' => 'send-money', 'tone' => 'primary', 'feature' => 'send_money'],
                ['label' => __('Receive'), 'meta' => __('QR code'), 'route' => 'user.wallet.my-qr-code', 'icon' => 'qrcode', 'tone' => 'primary'],
            ],
        ],
    };

    $secondaryActionItems = [
        ['label' => __('Request'), 'route' => 'user.request-money.create', 'icon' => 'request-money', 'tone' => 'warning', 'feature' => 'request_money'],
        ['label' => __('Exchange'), 'route' => 'user.exchange-money.create', 'icon' => 'exchange', 'tone' => 'violet', 'feature' => 'exchange_money'],
        ['label' => __('Cards'), 'route' => 'user.virtual-card.index', 'icon' => 'virtual-card', 'tone' => 'success', 'feature' => 'virtual_card'],
        ['label' => __('Recharge'), 'route' => 'user.mobile-recharge.create', 'icon' => 'mobile-recharge', 'tone' => 'primary', 'feature' => 'mobile_recharge'],
        ['label' => __('Voucher'), 'route' => 'user.voucher.my', 'icon' => 'voucher', 'tone' => 'warning', 'feature' => 'vouchers'],
    ];

    if ($dashboardUser?->isMerchant()) {
        array_unshift(
            $secondaryActionItems,
            ['label' => __('Payment Links'), 'route' => 'user.payment-links.index', 'icon' => 'payment-link', 'tone' => 'primary', 'feature' => 'payment_link']
        );
    }

    if ($dashboardUser?->isAgent()) {
        array_unshift(
            $secondaryActionItems,
            ['label' => __('Wallet'), 'route' => 'user.wallet.index', 'icon' => 'wallet', 'tone' => 'primary']
        );
    }

    if (Route::has('user.p2p.offers.index') && setting('p2p_enabled') && $featureManager->isVisible('p2p_marketplace')) {
        $secondaryActionItems[] = [
            'label' => __('P2P'),
            'route' => 'user.p2p.offers.index',
            'icon' => 'p2p_trading',
            'tone' => 'primary',
            'feature' => 'p2p_marketplace',
        ];
    }

    $secondaryActionItems[] = ['label' => __('Support'), 'route' => 'user.support-ticket.index', 'icon' => 'support', 'tone' => 'primary'];
    $secondaryActionItems[] = ['label' => __('Security'), 'route' => 'user.settings.security.index', 'icon' => 'security', 'tone' => 'slate'];

    $isActionVisible = static fn (array $item): bool => Route::has($item['route'])
        && (! isset($item['feature']) || $featureManager->isVisible($item['feature']));
    $actionKey = static fn (array $item): string => $item['route'].'|'.http_build_query($item['params'] ?? []);

    $priorityActionItems = collect($actionHub['items'])
        ->filter($isActionVisible)
        ->unique($actionKey)
        ->values()
        ->all();
    $priorityActionKeys = collect($priorityActionItems)->map($actionKey)->all();

    $secondaryActionItems = collect($secondaryActionItems)
        ->filter($isActionVisible)
        ->reject(fn (array $item): bool => in_array($actionKey($item), $priorityActionKeys, true))
        ->unique($actionKey)
        ->take(7)
        ->values()
        ->all();

    $snapshotCardTones = ['mint', 'azure', 'rose', 'amber', 'violet'];
    $excludedSnapshotTitles = [
        TrxType::DEPOSIT->label(),
        TrxType::WITHDRAW->label(),
    ];
    $snapshotPriority = match (true) {
        $dashboardUser?->isMerchant() => [
            TrxType::RECEIVE_PAYMENT->label(),
            __('Merchant Shop'),
            __('Awaiting Merchant'),
            TrxType::REQUEST_MONEY->label(),
            TrxType::RECEIVE_MONEY->label(),
        ],
        $dashboardUser?->isAgent() => [
            TrxType::RECEIVE_MONEY->label(),
            TrxType::SEND_MONEY->label(),
            TrxType::EXCHANGE_MONEY->label(),
            __('Total Tickets'),
            __('Total Referrals'),
        ],
        default => [
            TrxType::SEND_MONEY->label(),
            TrxType::REQUEST_MONEY->label(),
            TrxType::EXCHANGE_MONEY->label(),
            TrxType::RECEIVE_MONEY->label(),
            __('Total Referrals'),
        ],
    };
    $mobileSnapshotStats = collect($statistics ?? [])
        ->reject(fn (array $statistic): bool => in_array((string) $statistic['title'], $excludedSnapshotTitles, true))
        ->sortBy(function (array $statistic) use ($snapshotPriority): int {
            $priority = array_search((string) $statistic['title'], $snapshotPriority, true);

            return $priority === false ? 99 : $priority;
        })
        ->take(5)
        ->values()
        ->all();

    // Group transactions
    $today  = now()->startOfDay();
    $yesterday = $today->copy()->subDay();
    $weekStart = $today->copy()->subDays(6);
    $mobileGroups = [
        __('Today')     => collect(),
        __('Yesterday') => collect(),
        __('This week') => collect(),
        __('Earlier')   => collect(),
    ];
    foreach (($transactions ?? collect()) as $tx) {
        $d = $tx->created_at->copy()->startOfDay();
        if ($d->equalTo($today))                    $mobileGroups[__('Today')]->push($tx);
        elseif ($d->equalTo($yesterday))            $mobileGroups[__('Yesterday')]->push($tx);
        elseif ($d->greaterThanOrEqualTo($weekStart)) $mobileGroups[__('This week')]->push($tx);
        else                                        $mobileGroups[__('Earlier')]->push($tx);
    }
    $toneFor = function ($tx) {
        $status = $tx->status->value ?? '';
        if (in_array($status, ['failed', 'rejected', 'cancelled'])) return 'neutral';
        $flow = $tx->amount_flow ?? null;
        if ($flow === AmountFlow::PLUS)  return 'in';
        if ($flow === AmountFlow::MINUS) return 'out';
        $type = $tx->trx_type->value ?? '';
        if (str_contains($type, 'exchange') || str_contains($type, 'p2p')) return 'swap';
        return 'neutral';
    };
    $dirFor = function ($tx) {
        $status = $tx->status->value ?? '';
        if (in_array($status, ['failed', 'rejected', 'cancelled'])) return 'failed';
        $flow = $tx->amount_flow ?? null;
        if ($flow === AmountFlow::PLUS) return 'in';
        return 'out';
    };
@endphp

{{-- ==========================================================
 | DigiKash Mobile App Dashboard
 | Visible only at < 992px (Bootstrap d-lg-none).
 ========================================================== --}}
<div class="d-lg-none dk-mobile-dashboard">

    {{-- Hero wallet carousel --}}
    <section class="dk-hero">
        <div class="dk-wallet-track" data-mobile-wallet-role="{{ $mobileWalletProfile['role'] }}">
            @forelse($wallets as $wallet)
                @php
                    $maskedUuid = $walletService->formatMaskedWalletId($wallet->uuid);
                @endphp
                <article class="dk-wallet-card sidebar-wallet-card dk-wallet-card--{{ $mobileWalletProfile['role'] }} sidebar-wallet-card--{{ $mobileWalletProfile['role'] }}">
                    <div class="dk-wc__top sidebar-wallet-card__top">
                        <span class="dk-wc__badge sidebar-wallet-card__badge">
                            <span class="dk-wc__symbol sidebar-wallet-card__symbol">{{ $wallet->currency->symbol ?? '$' }}</span>
                            <span>{{ $wallet->currency->code }} {{ $mobileWalletProfile['label'] }}</span>
                        </span>

                        <div class="dk-wc__tools">
                            <button type="button" class="dk-wc__act dk-balance-eye" aria-label="{{ __('Toggle balance') }}">
                                <i class="fa fa-eye"></i>
                            </button>
                            <a href="{{ route('user.wallet.index') }}" class="dk-wc__act" aria-label="{{ __('Manage') }}">
                                <i class="fa fa-ellipsis-h"></i>
                            </a>
                        </div>
                    </div>

                    <div class="dk-wc__body sidebar-wallet-card__body">
                        <div class="dk-wc__id sidebar-wallet-card__id">
                            <i class="fa-solid fa-gem" aria-hidden="true"></i>
                            <span class="dk-wc__id-label sidebar-wallet-card__id-label">{{ __('Wallet ID') }}</span>
                            <span class="dk-wc__id-text sidebar-wallet-card__id-text">{{ $maskedUuid }}</span>
                            <button type="button"
                                    class="dk-wc__copy copy-icon"
                                    data-dk-copy="{{ $wallet->uuid }}"
                                    aria-label="{{ __('Copy Wallet ID') }}">
                                <i class="fa fa-copy"></i>
                            </button>
                        </div>

                        <div class="dk-wc__amount sidebar-wallet-card__amount" data-hidden="0">
                            {{ $wallet->currency->symbol ?? '' }}{{ number_format($wallet->balance, 2) }}
                        </div>
                    </div>

                    <div class="dk-wc__actions sidebar-wallet-card__actions">
                        <a class="dk-wc__action sidebar-wallet-card__action sidebar-wallet-card__action--primary"
                           href="{{ $mobileWalletProfile['primary_route'] }}">
                            <i class="fa-solid {{ $mobileWalletProfile['primary_icon'] }}" aria-hidden="true"></i>
                            <span>{{ $mobileWalletProfile['primary_label'] }}</span>
                        </a>
                        <a class="dk-wc__action sidebar-wallet-card__action sidebar-wallet-card__action--secondary"
                           href="{{ $mobileWalletProfile['secondary_route'] }}">
                            <i class="fa-solid {{ $mobileWalletProfile['secondary_icon'] }}" aria-hidden="true"></i>
                            <span>{{ $mobileWalletProfile['secondary_label'] }}</span>
                        </a>
                    </div>
                </article>
            @empty
                <div class="dk-wallet-empty">
                    <x-user-not-found
                        :title="__('No active wallet')"
                        :message="__('Create or activate a wallet to start transacting from mobile.')"
                        :action-url="route('user.wallet.index')"
                        :action-label="__('Manage Wallets')"
                        action-icon="fa-wallet"
                        icon="fa-wallet"
                        class="dk-wallet-empty__state"
                    >
                        <x-slot:preview>
                            <div class="dk-wallet-empty__preview">
                                <span class="dk-wallet-empty__preview-badge">
                                    <i class="fa-solid fa-wallet" aria-hidden="true"></i>
                                    {{ __('Wallet') }}
                                </span>
                                <strong>{{ siteCurrency() }}0.00</strong>
                            </div>
                        </x-slot:preview>
                    </x-user-not-found>
                </div>
            @endforelse
        </div>

        @if(count($wallets) > 1)
            <div class="dk-dots">
                @foreach($wallets as $i => $wallet)
                    <span data-active="{{ $i === 0 ? '1' : '0' }}"></span>
                @endforeach
            </div>
        @else
            <div style="height:8px;"></div>
        @endif
    </section>

    {{-- KYC slim banner --}}
    @if($kycShow)
        <div class="dk-kyc" data-state="{{ $kycState }}">
            <button type="button" class="dk-kyc__close" aria-label="{{ __('Dismiss') }}"><i class="fa fa-xmark"></i></button>
            <span class="dk-kyc__ic"><i class="fa-solid {{ $kycIcon }}"></i></span>
            <div class="dk-kyc__body">
                <div class="dk-kyc__title">{{ $kycTitle }}</div>
                <div class="dk-kyc__sub">{{ $kycSub }}</div>
            </div>
            <a href="{{ route('user.settings.kyc.verify') }}" class="dk-kyc__cta">{{ $kycCta }}</a>
        </div>
    @endif

    {{-- Role-aware action hub --}}
    <section class="dk-section dk-action-hub" data-role="{{ $mobileWalletProfile['role'] }}">
        <div class="dk-section__head">
            <h3 class="dk-section__title">{{ $actionHub['title'] }}</h3>
            <a href="javascript:void(0)" class="dk-section__link dk-open-more">
                {{ __('All') }} <i class="fa fa-angle-right"></i>
            </a>
        </div>

        @if($priorityActionItems !== [])
            <div class="dk-action-hub__primary">
                @foreach($priorityActionItems as $item)
                    <a href="{{ route($item['route'], $item['params'] ?? []) }}" class="dk-action-card" data-tone="{{ $item['tone'] }}">
                        <span class="dk-action-card__icon"><x-icon name="{{ $item['icon'] }}" height="16" width="16"/></span>
                        <span class="dk-action-card__copy">
                            <strong>{{ $item['label'] }}</strong>
                            <small>{{ $item['meta'] }}</small>
                        </span>
                    </a>
                @endforeach
            </div>
        @endif
    </section>

    @if($secondaryActionItems !== [])
        <section class="dk-section dk-quick-actions-panel">
            <div class="dk-action-hub__subhead">
                <span>{{ __('Quick actions') }}</span>
                <button type="button" class="dk-action-hub__more dk-open-more">
                    {{ __('All') }} <i class="fa fa-angle-right"></i>
                </button>
            </div>
            <div class="dk-qa-grid dk-qa-grid--compact">
                @foreach($secondaryActionItems as $item)
                    <a href="{{ route($item['route'], $item['params'] ?? []) }}" class="dk-qa-tile" data-tone="{{ $item['tone'] }}">
                        <span class="dk-qa-tile__ic" data-tone="{{ $item['tone'] }}">
                            <x-icon name="{{ $item['icon'] }}" height="17" width="17"/>
                        </span>
                        <span class="dk-qa-tile__lb">{{ $item['label'] }}</span>
                    </a>
                @endforeach
                <button type="button" class="dk-qa-tile dk-open-more" data-tone="more">
                    <span class="dk-qa-tile__ic" data-tone="more"><x-icon name="apps" height="17" width="17"/></span>
                    <span class="dk-qa-tile__lb">{{ __('More') }}</span>
                </button>
            </div>
        </section>
    @endif

    {{-- Snapshot rail --}}
    @if($mobileSnapshotStats !== [])
        <div class="dk-section dk-snapshot-section">
            <div class="dk-section__head">
                <h3 class="dk-section__title">{{ __('Snapshot') }}</h3>
                <a href="{{ route('user.transaction.index') }}" class="dk-section__link">
                    {{ __('Stats') }} <i class="fa fa-angle-right"></i>
                </a>
            </div>
        </div>
        <div class="dk-stats-rail">
            @foreach($mobileSnapshotStats as $i => $statistic)
                <article class="dk-stat-card" data-tone="{{ $snapshotCardTones[$i % count($snapshotCardTones)] }}">
                    <div class="dk-stat-card__head">
                        <span class="dk-stat-card__ic"><x-icon name="{{ $statistic['icon'] }}" height="11" width="11"/></span>
                        <span class="dk-stat-card__lb">{{ $statistic['title'] }}</span>
                    </div>
                    <div class="dk-stat-card__val">{{ $statistic['value'] }}</div>
                    @if(isset($statistic['link']))
                        <a href="{{ $statistic['link'] }}" class="dk-stat-card__link" aria-label="{{ $statistic['title'] }}"></a>
                    @endif
                </article>
            @endforeach
        </div>
    @endif

    {{-- Insights segmented chart --}}
    @if(isset($totalSuccessDeposit) || isset($totalSuccessWithdraw))
        <section class="dk-insight" data-mode="deposit">
            <div class="dk-insight__head">
                <div class="dk-seg" role="tablist">
                    <button type="button" class="dk-seg__btn" data-mode="deposit" data-active="1" role="tab" aria-selected="true">{{ __('Deposit') }}</button>
                    <button type="button" class="dk-seg__btn" data-mode="withdraw" data-active="0" role="tab" aria-selected="false">{{ __('Withdraw') }}</button>
                </div>
                <span class="dk-insight__period"><span></span>{{ __('Last 7d') }}</span>
            </div>
            <div class="dk-insight__summary" data-big="deposit">
                <div class="dk-insight__metric">
                    <span class="dk-insight__num">{{ $totalSuccessDeposit ?? '' }}</span>
                    <span class="dk-insight__pulse" aria-hidden="true"><i class="fa-solid fa-arrow-trend-up"></i></span>
                </div>
                <div class="dk-insight__sub">{{ __('Total Deposited') }}</div>
            </div>
            <div class="dk-insight__summary" data-big="withdraw" style="display:none;">
                <div class="dk-insight__metric">
                    <span class="dk-insight__num">{{ $totalSuccessWithdraw ?? '' }}</span>
                    <span class="dk-insight__pulse" aria-hidden="true"><i class="fa-solid fa-arrow-trend-down"></i></span>
                </div>
                <div class="dk-insight__sub">{{ __('Total Withdraw') }}</div>
            </div>
            <div class="dk-chart-shell">
                <div id="dk-mobile-chart"></div>
            </div>
        </section>
    @endif

    {{-- Recent transactions grouped --}}
    <section class="dk-section dk-activity-section">
        <div class="dk-section__head">
            <h3 class="dk-section__title">{{ __('Recent activity') }}</h3>
            <a href="{{ route('user.transaction.index') }}" class="dk-section__link">
                {{ __('View all') }} <i class="fa fa-angle-right"></i>
            </a>
        </div>

        @if(($transactions ?? collect())->isEmpty())
            <x-user-not-found
                :title="__('No transactions found')"
                :message="__('Your most recent mobile wallet activity will appear here once you start transacting.')"
                :eyebrow="__('Recent activity')"
                icon="fa-receipt"
                class="dk-mobile-empty"
            />
        @else
            @foreach($mobileGroups as $label => $group)
                @if($group->isNotEmpty())
                    <div class="dk-trx-group">
                        <div class="dk-trx-group__title">{{ $label }}</div>
                        <div class="dk-trx-list">
                            @foreach($group as $transaction)
                                @php
                                    $tone = $toneFor($transaction);
                                    $dir  = $dirFor($transaction);
                                    $statusValue = $transaction->status->value ?? '';
                                    $transactionTypeClass = $transaction->trx_type->kebabCase();
                                    $amountSign  = $transaction->amount_flow->sign($transaction->status);
                                    $icon = $transaction->trx_type->icon();
                                @endphp
                                <button type="button"
                                        class="dk-trx-row"
                                        data-tone="{{ $tone }}"
                                        data-bs-toggle="modal"
                                        data-bs-target="#transactionModal{{ $transaction->id }}">
                                    <span class="dk-trx-row__ic {{ $transactionTypeClass }}" aria-hidden="true">
                                        <x-icon name="{{ $icon }}" height="15" width="15"/>
                                        <span class="status-dot" data-status="{{ $statusValue }}"></span>
                                    </span>
                                    <span class="dk-trx-row__body">
                                        <span class="dk-trx-row__main">
                                            <span class="dk-trx-row__title">{{ $transaction->description }}</span>
                                            <span class="dk-trx-row__amt" data-dir="{{ $dir }}">
                                                {{ $amountSign.number_format($transaction->amount, 2) }} {{ $transaction->currency }}
                                            </span>
                                        </span>
                                        <span class="dk-trx-row__meta">
                                            <span class="dk-trx-row__time">{{ $transaction->created_at->format('h:i A') }}</span>
                                        </span>
                                    </span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endforeach
        @endif
    </section>
</div>

{{-- ==========================================================
 | Mobile insight chart bootstrap (ApexCharts is already loaded)
 ========================================================== --}}
@push('scripts')
<script>
(function () {
    'use strict';
    var el = document.getElementById('dk-mobile-chart');
    if (!el || typeof ApexCharts === 'undefined') return;

    @if(isset($sortedDeposits))
        var depositData  = @json($sortedDeposits);
    @else
        var depositData  = [];
    @endif
    @if(isset($sortedWithdrawals))
        var withdrawData = @json($sortedWithdrawals);
    @else
        var withdrawData = [];
    @endif

    function tokens() {
        var cs = getComputedStyle(document.documentElement);
        return {
            text:   (cs.getPropertyValue('--dk-text')       || '#E9ECF1').trim(),
            muted:  (cs.getPropertyValue('--dk-text-muted') || '#A3ABB8').trim(),
            line:   (cs.getPropertyValue('--dk-line')       || 'rgba(255,255,255,0.08)').trim(),
            brand:  (cs.getPropertyValue('--dk-brand')      || '#00D2A0').trim(),
            danger: (cs.getPropertyValue('--dk-danger')     || '#FF5C73').trim(),
            font:   (cs.getPropertyValue('--front-font-primary') || 'Inter, sans-serif').trim()
        };
    }
    function buildOptions(mode) {
        var t = tokens();
        var isDeposit = mode === 'deposit';
        var dataset = isDeposit ? depositData : withdrawData;
        var sKey = isDeposit ? 'success_total'      : 'withdraw_success_total';
        var fKey = isDeposit ? 'fail_total'         : 'withdraw_fail_total';
        var accent = isDeposit ? t.brand : t.danger;
        return {
            series: [
                { name: isDeposit ? 'Success' : 'Withdrawn', data: dataset.map(function (i) { return i[sKey] || 0; }) },
                { name: 'Failed', data: dataset.map(function (i) { return i[fKey] || 0; }) }
            ],
            chart:  {
                height: 158,
                type: 'area',
                toolbar: {show: false},
                zoom: {enabled: false},
                parentHeightOffset: 0,
                fontFamily: t.font
            },
            colors: [accent, t.danger],
            stroke: { curve: 'smooth', width: [3, 2], lineCap: 'round' },
            fill:   { type: 'gradient', gradient: { shadeIntensity: 0.35, opacityFrom: 0.26, opacityTo: 0.02, stops: [0, 92] } },
            dataLabels: { enabled: false },
            grid:   {
                borderColor: t.line,
                strokeDashArray: 4,
                padding: { top: 2, right: 8, bottom: 0, left: 0 }
            },
            xaxis:  {
                categories: dataset.map(function (i) { return i.day; }),
                axisBorder: {show: false}, axisTicks: {show: false},
                labels: { style: { colors: t.muted, fontSize: '10px', fontWeight: 700 } }
            },
            yaxis:  {
                min: 0,
                tickAmount: 4,
                forceNiceScale: true,
                labels: { style: { colors: t.muted, fontSize: '10px', fontWeight: 700 } }
            },
            legend: { show: false },
            tooltip:{ theme: 'light', marker: { show: false } },
            markers:{ size: 0, hover: { size: 5 } }
        };
    }
    var chart = new ApexCharts(el, buildOptions('deposit'));
    chart.render();
    window.addEventListener('dk:insight-mode', function (e) {
        var mode = (e.detail && e.detail.mode) || 'deposit';
        chart.updateOptions(buildOptions(mode), false, true);
    });
    window.addEventListener('theme:changed', function () {
        var insight = el.closest('.dk-insight');
        var mode = insight ? (insight.getAttribute('data-mode') || 'deposit') : 'deposit';
        chart.updateOptions(buildOptions(mode), false, true);
    });
})();
</script>
@endpush
