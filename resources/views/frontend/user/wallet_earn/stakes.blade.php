@php use App\Enums\TrxType; @endphp
@extends('frontend.layouts.user.index')

@section('title', __('My Wallet Earn Stakes'))

@push('styles')
    <link rel="stylesheet" href="{{ asset('frontend/css/wallet-earn.css?v=' . config('app.version')) }}">
@endpush

@section('content')
    <div class="user-dashboard wallet-earn-page">
        <div class="row">
            <div class="col-12">
                <div class="card single-form-card">
                    <x-user-feature-header
                        :title="__('My Wallet Earn Stakes')"
                        :subtitle="__('Track active, pending, and completed earning positions.')"
                        icon="fas fa-layer-group"
                    >
                        <a href="{{ route('user.wallet-earn.plans') }}" class="btn btn-light-success btn-sm">
                            <i class="fas fa-chart-line"></i> {{ __('Earn Plans') }}
                        </a>
                        <a href="{{ route('user.transaction.index', ['type' => TrxType::WALLET_EARN_REWARD]) }}" class="btn btn-light-primary btn-sm">
                            <i class="fas fa-list"></i> {{ __('Reward History') }}
                        </a>
                    </x-user-feature-header>

                    <div class="card-body">

                        {{-- Summary Cards --}}
                        <div class="we-stk-summary">
                            <div class="we-stk-summary-card" style="animation: we-fadeUp .38s 0ms ease both">
                                <div class="we-stk-summary-card__head">
                                    <span class="we-stk-summary-card__icon we-stk-summary-card__icon--active">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                                    </span>
                                    <span class="we-stk-summary-card__label">{{ __('Active Positions') }}</span>
                                </div>
                                <div class="we-stk-summary-card__value">{{ $metrics['active_count'] }}</div>
                                <div class="we-stk-summary-card__sub">{{ $metrics['pending_count'] }} {{ __('pending') }}</div>
                            </div>

                            <div class="we-stk-summary-card" style="animation: we-fadeUp .38s 60ms ease both">
                                <div class="we-stk-summary-card__head">
                                    <span class="we-stk-summary-card__icon we-stk-summary-card__icon--earned">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16"><circle cx="12" cy="12" r="8"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/><path d="M9 9.5C9 8.1 10.3 7 12 7s3 1.1 3 2.5c0 2.5-3 2-3 5"/></svg>
                                    </span>
                                    <span class="we-stk-summary-card__label">{{ __('Total Earned') }}</span>
                                </div>
                                <div class="we-stk-summary-card__value">
                                    {{ number_format((float) $metrics['rewards_paid'], (int) setting('site_decimal', 2)) }}
                                </div>
                                <div class="we-stk-summary-card__sub">{{ __('Across all plans') }}</div>
                            </div>

                            <div class="we-stk-summary-card" style="animation: we-fadeUp .38s 120ms ease both">
                                <div class="we-stk-summary-card__head">
                                    <span class="we-stk-summary-card__icon we-stk-summary-card__icon--payout">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                    </span>
                                    <span class="we-stk-summary-card__label">{{ __('Next Payout') }}</span>
                                </div>
                                <div class="we-stk-summary-card__value">
                                    @if($metrics['next_payout_at'])
                                        {{ \Carbon\Carbon::parse($metrics['next_payout_at'])->format('d M Y') }}
                                    @else
                                        —
                                    @endif
                                </div>
                                <div class="we-stk-summary-card__sub">{{ __('Nearest upcoming') }}</div>
                            </div>

                            <div class="we-stk-summary-card" style="animation: we-fadeUp .38s 180ms ease both">
                                <div class="we-stk-summary-card__head">
                                    <span class="we-stk-summary-card__icon we-stk-summary-card__icon--total">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
                                    </span>
                                    <span class="we-stk-summary-card__label">{{ __('Total Positions') }}</span>
                                </div>
                                <div class="we-stk-summary-card__value">{{ $metrics['total_count'] }}</div>
                                <div class="we-stk-summary-card__sub">{{ $metrics['completed_count'] }} {{ __('completed') }}</div>
                            </div>
                        </div>

                        {{-- Tabs --}}
                        <div class="we-stk-tabs" style="animation: we-fadeUp .38s 220ms ease both">
                            @php $activeTab = $status ?? 'all'; @endphp
                            <a href="{{ route('user.wallet-earn.stakes') }}"
                               class="we-stk-tab {{ $activeTab === 'all' || !$status ? 'active' : '' }}">
                                {{ __('All') }}
                                <span class="we-stk-tab__count">{{ $metrics['total_count'] }}</span>
                            </a>
                            <a href="{{ route('user.wallet-earn.stakes', ['status' => 'active']) }}"
                               class="we-stk-tab {{ $activeTab === 'active' ? 'active' : '' }}">
                                {{ __('Active') }}
                                <span class="we-stk-tab__count">{{ $metrics['active_count'] }}</span>
                            </a>
                            <a href="{{ route('user.wallet-earn.stakes', ['status' => 'pending']) }}"
                               class="we-stk-tab {{ $activeTab === 'pending' ? 'active' : '' }}">
                                {{ __('Pending') }}
                                <span class="we-stk-tab__count">{{ $metrics['pending_count'] }}</span>
                            </a>
                            <a href="{{ route('user.wallet-earn.stakes', ['status' => 'completed']) }}"
                               class="we-stk-tab {{ $activeTab === 'completed' ? 'active' : '' }}">
                                {{ __('Completed') }}
                                <span class="we-stk-tab__count">{{ $metrics['completed_count'] }}</span>
                            </a>
                        </div>

                        {{-- Stakes Grid --}}
                        <div class="we-stk-grid">
                            @forelse($stakes as $i => $stake)
                                @php
                                    $dec        = (int) setting('site_decimal', 2);
                                    $totalDays  = ($stake->starts_at && $stake->matures_at)
                                                    ? max(1, $stake->starts_at->diffInDays($stake->matures_at))
                                                    : 1;
                                    $doneDays   = $stake->starts_at
                                                    ? min($totalDays, max(0, $stake->starts_at->diffInDays(now())))
                                                    : 0;
                                    $pct        = $stake->status->isTerminal()
                                                    ? 100
                                                    : min(100, max(0, (int) round(($doneDays / $totalDays) * 100)));
                                    $daysLeft   = ($stake->matures_at && $stake->isActive())
                                                    ? max(0, (int) now()->diffInDays($stake->matures_at, false))
                                                    : 0;
                                    $isFeatured = ((bool) ($stake->plan->is_featured ?? false)) && $stake->isActive();
                                    $walletName = $stake->currency->code . ' ' . __('Wallet');
                                    $statusVal  = $stake->status->value;
                                    $rateDisplay = $stake->profit_type === \App\Enums\WalletEarnProfitType::Percentage
                                        ? number_format((float) $stake->profit_rate, 2) . '%'
                                        : number_format((float) $stake->profit_rate, $dec) . ' ' . $stake->currency->code;
                                    $stakeData  = json_encode([
                                        'id'            => $stake->id,
                                        'plan_name'     => $stake->plan_name,
                                        'wallet_name'   => $walletName,
                                        'status'        => $statusVal,
                                        'status_label'  => $stake->status->label(),
                                        'amount'        => number_format($stake->principal_amount, $dec),
                                        'currency'      => $stake->currency->code,
                                        'rate'          => $stake->profit_rate,
                                        'rate_display'  => $rateDisplay,
                                        'starts_at'     => $stake->starts_at?->format('d M Y') ?? __('Pending'),
                                        'matures_at'    => $stake->matures_at?->format('d M Y') ?? __('Pending'),
                                        'next_payout'   => $stake->next_payout_at?->format('d M Y, h:i A') ?? __('Not scheduled'),
                                        'payout_freq'   => $stake->payout_frequency->label(),
                                        'paid_profit'   => number_format((float) $stake->paid_profit, $dec),
                                        'exp_profit'    => number_format((float) $stake->expected_profit, $dec),
                                        'total_return'  => number_format($stake->principal_amount + $stake->paid_profit, $dec),
                                        'payouts_made'  => $stake->payouts_made,
                                        'total_payouts' => $stake->total_payouts,
                                        'pct'           => $pct,
                                        'days_left'     => $daysLeft,
                                        'is_featured'   => $isFeatured,
                                        'show_url'      => route('user.wallet-earn.show', $stake),
                                        'is_pending'    => $stake->status->value === 'pending',
                                        'is_active'     => $stake->isActive(),
                                        'is_terminal'   => $stake->status->isTerminal(),
                                        'rewards'       => $stake->rewards->map(fn ($r) => [
                                            'label'  => __('Payout') . ' #' . $r->payout_number,
                                            'amount' => number_format((float) $r->amount, $dec),
                                            'date'   => $r->paid_at?->format('d M Y') ?? $r->created_at->format('d M Y'),
                                        ])->values()->all(),
                                    ]);
                                @endphp
                                <div class="we-stk-card {{ $isFeatured ? 'we-stk-card--featured' : '' }} we-stk-card--{{ $statusVal }}"
                                     data-stake="{{ $stakeData }}"
                                     style="animation: we-fadeUp .45s {{ $i * 60 }}ms ease both; cursor:pointer">

                                    {{-- Top row --}}
                                    <div class="we-stk-card__top">
                                        <div class="we-stk-card__plan-info">
                                            <span class="we-stk-card__icon">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="18" height="18"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
                                            </span>
                                            <div style="min-width:0">
                                                <div class="we-stk-card__name">{{ $stake->plan_name }}</div>
                                                <div class="we-stk-card__wallet">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="9" height="9"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
                                                    {{ $walletName }}
                                                </div>
                                            </div>
                                        </div>
                                        <span class="we-stk-chip we-stk-chip--{{ $statusVal }}">
                                            <span class="we-stk-chip__dot"></span>
                                            {{ $stake->status->label() }}
                                        </span>
                                    </div>

                                    {{-- Amount + reward rate --}}
                                    <div class="we-stk-card__amount-row">
                                        <span class="we-stk-card__amount">
                                            {{ number_format($stake->principal_amount, $dec) }}
                                        </span>
                                        <span class="we-stk-card__currency">{{ $stake->currency->code }}</span>
                                        <span class="we-stk-card__apy">@ {{ $rateDisplay }} {{ __('per payout') }}</span>
                                    </div>

                                    {{-- Dates --}}
                                    <div class="we-stk-card__dates">
                                        <div class="we-stk-card__date">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="10" height="10"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                            {{ __('Start') }}: <strong>{{ $stake->starts_at?->format('d M Y') ?? __('Pending') }}</strong>
                                        </div>
                                        <div class="we-stk-card__date">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="10" height="10"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/><polyline points="9 16 11 18 15 14"/></svg>
                                            {{ __('Matures') }}: <strong>{{ $stake->matures_at?->format('d M Y') ?? __('Pending') }}</strong>
                                        </div>
                                    </div>

                                    {{-- Progress bar --}}
                                    @if($stake->starts_at)
                                        <div class="we-stk-card__progress-wrap">
                                            <div class="we-stk-card__progress-row">
                                                <span class="we-stk-card__progress-label">{{ __('Maturity') }}</span>
                                                <span class="we-stk-card__progress-pct">
                                                    {{ $pct }}%{{ $stake->isActive() && $daysLeft > 0 ? ' · ' . $daysLeft . 'd ' . __('left') : '' }}
                                                </span>
                                            </div>
                                            <div class="we-stk-card__progress-bar">
                                                <div class="we-stk-card__progress-fill" data-pct="{{ $pct }}"></div>
                                            </div>
                                        </div>
                                    @endif

                                    {{-- Footer --}}
                                    <div class="we-stk-card__footer">
                                        <div>
                                            <div class="we-stk-card__earned-label">
                                                {{ $stake->status->isTerminal() ? __('Total Earned') : __('Earned') }}
                                            </div>
                                            <div class="we-stk-card__earned-val">
                                                +{{ number_format((float) $stake->paid_profit, $dec) }}
                                                <span style="font-size:11px;font-weight:600">{{ $stake->currency->code }}</span>
                                            </div>
                                        </div>

                                        @if($stake->isActive())
                                            <div style="text-align:right">
                                                <div class="we-stk-card__payout-label">{{ __('Next Payout') }}</div>
                                                <div class="we-stk-card__payout-val">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="10" height="10" style="opacity:.6"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                                    {{ $stake->next_payout_at?->format('d M Y') ?? '—' }}
                                                </div>
                                            </div>
                                        @elseif($stake->status->value === 'pending')
                                            <span class="we-stk-card__pending-badge">{{ __('Awaiting Approval') }}</span>
                                        @else
                                            <div class="we-stk-card__closed-badge">
                                                <span class="we-stk-card__closed-icon">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" width="10" height="10"><polyline points="20 6 9 17 4 12"/></svg>
                                                </span>
                                                {{ $stake->status->label() }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <x-user-not-found
                                    class="mt-3"
                                    :title="__('No stakes yet')"
                                    :message="__('Your Wallet Earn stakes will appear here after you create one.')"
                                    icon="fa-layer-group"
                                    :action-url="route('user.wallet-earn.plans')"
                                    :action-label="__('Browse Plans')"
                                    action-icon="fa-chart-line"
                                />
                            @endforelse
                        </div>

                        {{-- Pagination --}}
                        @if($stakes->hasPages())
                            <div class="mt-4 d-flex justify-content-center">
                                {{ $stakes->links() }}
                            </div>
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Bottom Sheet Drawer --}}
    <div id="weStakeSheet" class="we-stk-sheet d-none" aria-hidden="true">
        <div id="weSheetBackdrop" class="we-stk-sheet__backdrop"></div>
        <div id="weSheetPanel" class="we-stk-sheet__panel">

            {{-- Handle --}}
            <div class="we-stk-sheet__handle">
                <div class="we-stk-sheet__handle-bar"></div>
            </div>

            {{-- Header --}}
            <div class="we-stk-sheet__header">
                <span class="we-stk-sheet__icon" id="wsdIcon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="20" height="20"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
                </span>
                <div style="flex:1;min-width:0">
                    <div class="we-stk-sheet__plan-name" id="wsdPlanName">—</div>
                    <div class="we-stk-sheet__wallet" id="wsdWallet">—</div>
                </div>
                <span id="wsdChip" class="we-stk-chip"></span>
                <button type="button" class="we-stk-sheet__close" id="weSheetClose" aria-label="{{ __('Close') }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" width="16" height="16"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>

            {{-- Scrollable Body --}}
            <div class="we-stk-sheet__body">

                {{-- Summary grid --}}
                <div class="we-stk-detail-hero">
                    <div class="we-stk-detail-grid">
                        <div>
                            <span class="we-stk-detail-grid__key">{{ __('Staked Amount') }}</span>
                            <span class="we-stk-detail-grid__val" id="wsdAmount">—</span>
                        </div>
                        <div>
                            <span class="we-stk-detail-grid__key">{{ __('Reward Rate') }}</span>
                            <span class="we-stk-detail-grid__val" id="wsdRate">—</span>
                        </div>
                        <div>
                            <span class="we-stk-detail-grid__key">{{ __('Start Date') }}</span>
                            <span class="we-stk-detail-grid__val" id="wsdStart">—</span>
                        </div>
                        <div>
                            <span class="we-stk-detail-grid__key">{{ __('Maturity Date') }}</span>
                            <span class="we-stk-detail-grid__val" id="wsdMature">—</span>
                        </div>
                        <div>
                            <span class="we-stk-detail-grid__key">{{ __('Payout Frequency') }}</span>
                            <span class="we-stk-detail-grid__val" id="wsdFreq">—</span>
                        </div>
                        <div>
                            <span class="we-stk-detail-grid__key">{{ __('Payout Progress') }}</span>
                            <span class="we-stk-detail-grid__val" id="wsdPayouts">—</span>
                        </div>
                    </div>
                </div>

                {{-- Maturity Progress --}}
                <div class="we-stk-detail-progress" id="wsdProgressSection">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
                        <span style="font-size:12px;font-weight:700;color:#475569">{{ __('Maturity Progress') }}</span>
                        <span style="font-size:12px;font-weight:800;color:#1a6fdb" id="wsdProgressPct">0%</span>
                    </div>
                    <div class="we-stk-detail-progress__bar">
                        <div class="we-stk-detail-progress__fill" id="wsdProgressFill" data-front-progress-pct="0"></div>
                    </div>
                    <div style="display:flex;justify-content:space-between;margin-top:8px">
                        <span style="font-size:11px;color:#94a3b8" id="wsdStartLabel">—</span>
                        <span style="font-size:11px;font-weight:700;color:#f59e0b" id="wsdDaysLeft"></span>
                        <span style="font-size:11px;color:#94a3b8" id="wsdMatureLabel">—</span>
                    </div>
                </div>

                {{-- Earnings --}}
                <div class="we-stk-detail-earnings" id="wsdEarnings">
                    <div>
                        <div class="we-stk-detail-earnings__label" id="wsdEarnedLabel">{{ __('Earned So Far') }}</div>
                        <div class="we-stk-detail-earnings__val" id="wsdEarnedVal">—</div>
                    </div>
                    <div style="text-align:right">
                        <div class="we-stk-detail-earnings__return-label">{{ __('Total Returned') }}</div>
                        <div class="we-stk-detail-earnings__return-val" id="wsdReturnVal">—</div>
                    </div>
                </div>

                {{-- Next payout (active only) --}}
                <div id="wsdNextPayout" class="we-stk-detail-next d-none">
                    <svg viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="13" height="13" style="margin-right:4px;vertical-align:middle"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    {{ __('Next payout') }}: <strong id="wsdNextPayoutVal">—</strong>
                </div>

                {{-- Payout History --}}
                <div id="wsdPayoutHistory" class="d-none">
                    <div class="we-stk-detail-history__title">{{ __('Recent Payouts') }}</div>
                    <div id="wsdPayoutHistoryItems"></div>
                </div>

                {{-- Pending notice --}}
                <div class="we-stk-detail-pending d-none" id="wsdPendingNotice">
                    <svg viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="15" height="15" style="flex-shrink:0;margin-top:2px"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    <div>
                        <div style="font-weight:700;color:#92400e;margin-bottom:3px;font-size:13px">{{ __('Under Review') }}</div>
                        <div style="color:#b45309;line-height:1.5;font-size:12px">{{ __('Your stake is being reviewed by our team. This typically takes up to 24 hours.') }}</div>
                    </div>
                </div>

                {{-- View Full Details button --}}
                <a href="#" id="wsdViewFullBtn" class="btn btn-base w-100" style="margin-top:4px">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="14" height="14" style="margin-right:6px;vertical-align:middle"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                    {{ __('View Full Details') }}
                </a>

            </div>{{-- /.we-stk-sheet__body --}}
        </div>{{-- /.we-stk-sheet__panel --}}
    </div>{{-- /#weStakeSheet --}}

@endsection

@push('scripts')
    <script>
    'use strict';
        (function () {
            /* ── Progress bars on page load ── */
            document.querySelectorAll('.we-stk-card__progress-fill[data-pct]').forEach(function (el) {
                var pct = el.getAttribute('data-pct');
                setTimeout(function () { setProgress(el, pct); }, 120);
            });

            /* ── Bottom Sheet ── */
            var sheet     = document.getElementById('weStakeSheet');
            var backdrop  = document.getElementById('weSheetBackdrop');
            var closeBtn  = document.getElementById('weSheetClose');
            var isOpen    = false;

            var chipCfg = {
                active:    { cls: 'we-stk-chip--active' },
                pending:   { cls: 'we-stk-chip--pending' },
                completed: { cls: 'we-stk-chip--completed' },
                canceled:  { cls: 'we-stk-chip--canceled' },
                rejected:  { cls: 'we-stk-chip--rejected' },
            };

            function setVisible(el, visible) {
                if (el) {
                    el.classList.toggle('d-none', !visible);
                }
            }

            function setProgress(el, value) {
                if (!el) {
                    return;
                }

                var pct = Math.max(0, Math.min(100, Math.round(parseFloat(value) || 0)));
                el.setAttribute('data-front-progress-pct', String(pct));
            }

            function openSheet(s) {
                /* Header */
                document.getElementById('wsdPlanName').textContent = s.plan_name;
                document.getElementById('wsdWallet').textContent   = s.wallet_name;

                var iconEl = document.getElementById('wsdIcon');
                iconEl.classList.toggle('we-stk-sheet__icon--featured', Boolean(s.is_featured));

                var chip = document.getElementById('wsdChip');
                chip.className = 'we-stk-chip ' + (chipCfg[s.status] ? chipCfg[s.status].cls : '');
                chip.innerHTML = '<span class="we-stk-chip__dot"></span> ' + s.status_label;

                /* Summary grid */
                document.getElementById('wsdAmount').textContent  = s.amount + ' ' + s.currency;
                document.getElementById('wsdRate').textContent    = s.rate_display;
                document.getElementById('wsdStart').textContent   = s.starts_at;
                document.getElementById('wsdMature').textContent  = s.matures_at;
                document.getElementById('wsdFreq').textContent    = s.payout_freq;
                document.getElementById('wsdPayouts').textContent = s.payouts_made + ' / ' + s.total_payouts;

                /* Progress */
                var progSection = document.getElementById('wsdProgressSection');
                if (s.is_pending) {
                    setVisible(progSection, false);
                } else {
                    setVisible(progSection, true);
                    document.getElementById('wsdProgressPct').textContent = s.pct + '%';
                    var fill = document.getElementById('wsdProgressFill');
                    fill.setAttribute('data-front-progress-pct', '0');
                    fill.classList.toggle('we-stk-detail-progress__fill--terminal', Boolean(s.is_terminal));
                    setTimeout(function () { setProgress(fill, s.pct); }, 80);
                    document.getElementById('wsdStartLabel').textContent  = s.starts_at;
                    document.getElementById('wsdMatureLabel').textContent = s.matures_at;
                    var daysLeftEl = document.getElementById('wsdDaysLeft');
                    daysLeftEl.textContent = (s.is_active && s.days_left > 0) ? s.days_left + ' {{ __('days left') }}' : '';
                }

                /* Earnings */
                var earningsEl = document.getElementById('wsdEarnings');
                earningsEl.className = 'we-stk-detail-earnings ' + (s.is_active ? 'we-stk-detail-earnings--active' : 'we-stk-detail-earnings--other');
                document.getElementById('wsdEarnedLabel').textContent = s.is_terminal ? '{{ __('Total Earned') }}' : '{{ __('Earned So Far') }}';
                document.getElementById('wsdEarnedVal').textContent   = '+' + s.paid_profit + ' ' + s.currency;
                document.getElementById('wsdReturnVal').textContent   = s.total_return + ' ' + s.currency;

                /* Next payout */
                var nextEl = document.getElementById('wsdNextPayout');
                setVisible(nextEl, Boolean(s.is_active));
                if (s.is_active) {
                    document.getElementById('wsdNextPayoutVal').textContent = s.next_payout;
                }

                /* Payout history */
                var historyWrap = document.getElementById('wsdPayoutHistory');
                var historyItems = document.getElementById('wsdPayoutHistoryItems');
                if (s.rewards && s.rewards.length > 0) {
                    setVisible(historyWrap, true);
                    historyItems.innerHTML = s.rewards.map(function (r) {
                        return '<div class="we-stk-detail-history__item">' +
                            '<span class="we-stk-detail-history__check">' +
                                '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" width="10" height="10"><polyline points="20 6 9 17 4 12"/></svg>' +
                            '</span>' +
                            '<div style="flex:1;min-width:0">' +
                                '<div class="we-stk-detail-history__label">' + r.label + '</div>' +
                                '<div class="we-stk-detail-history__date">' + r.date + '</div>' +
                            '</div>' +
                            '<div class="we-stk-detail-history__amt">+' + r.amount + '</div>' +
                        '</div>';
                    }).join('');
                } else {
                    setVisible(historyWrap, false);
                }

                /* Pending notice */
                setVisible(document.getElementById('wsdPendingNotice'), Boolean(s.is_pending));

                /* View full link */
                document.getElementById('wsdViewFullBtn').href = s.show_url;

                /* Open */
                setVisible(sheet, true);
                requestAnimationFrame(function () {
                    sheet.classList.add('we-stk-sheet--open');
                });
                document.body.classList.add('front-scroll-lock');
                isOpen = true;
            }

            function closeSheet() {
                sheet.classList.remove('we-stk-sheet--open');
                document.body.classList.remove('front-scroll-lock');
                isOpen = false;
                setTimeout(function () {
                    if (!isOpen) { setVisible(sheet, false); }
                }, 350);
            }

            /* Card click */
            document.querySelectorAll('.we-stk-card[data-stake]').forEach(function (card) {
                card.addEventListener('click', function () {
                    var s = JSON.parse(card.getAttribute('data-stake'));
                    openSheet(s);
                });
            });

            backdrop.addEventListener('click', closeSheet);
            closeBtn.addEventListener('click', closeSheet);

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && isOpen) { closeSheet(); }
            });
        })();
    </script>
@endpush
