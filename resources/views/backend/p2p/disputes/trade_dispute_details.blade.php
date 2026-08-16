@extends('backend.p2p.layout')

@section('title', __('Trade Dispute #').$dispute->id)

@section('p2p_title')
    @lang('Dispute') #{{ $dispute->id }}
@endsection

@section('p2p_icon', 'support')

@php
    use App\Enums\P2P\DisputeStatus;
    use App\Enums\P2P\OrderStatus;
    use App\Models\P2P\Dispute as DisputeModel;
    use App\Models\P2P\Order as OrderModel;

    $isOpen      = $dispute->status === DisputeStatus::OPEN;
    $order       = $dispute->order;
    $maker       = $order->maker;
    $taker       = $order->taker;
    $raiser      = $dispute->raiser;
    $raiserIsTaker = $taker && $raiser && $raiser->id === $taker->id;
    $currencyCode = $order->wallet?->currency?->code ?? siteCurrency('code') ?? 'BDT';

    $toneFor = function (string $seed): string {
        $tones = ['blue', 'green', 'amber', 'rose', 'violet', 'teal'];
        return $tones[abs(crc32($seed)) % count($tones)];
    };

    $initialsFor = function ($user) {
        if (! $user) return '?';
        $first = (string) ($user->first_name ?? '');
        $last  = (string) ($user->last_name ?? '');
        $i = strtoupper(substr($first, 0, 1).substr($last, 0, 1));
        return $i !== '' ? $i : strtoupper(substr((string) ($user->name ?? '?'), 0, 1));
    };

    $tierFor = function (int $totalOrders): array {
        return match (true) {
            $totalOrders >= 1000 => ['merchant', __('Merchant')],
            $totalOrders >= 250  => ['gold', __('Gold')],
            $totalOrders >= 50   => ['silver', __('Silver')],
            default              => ['bronze', __('Bronze')],
        };
    };

    // Per-party stats (computed inline to avoid controller changes)
    $statsForUser = function ($user) {
        if (! $user) return ['orders' => 0, 'completion' => 0.0, 'disputes' => 0];
        $totalOrders = (int) OrderModel::query()->where('maker_id', $user->id)->count();
        $completed   = (int) OrderModel::query()->where('maker_id', $user->id)->where('status', OrderStatus::COMPLETED->value)->count();
        $completion  = $totalOrders > 0 ? round(($completed / $totalOrders) * 100, 2) : 0;
        $disputes    = (int) DisputeModel::query()
            ->whereHas('order', fn ($q) => $q->where('maker_id', $user->id)->orWhere('taker_id', $user->id))
            ->count();
        return ['orders' => $totalOrders, 'completion' => $completion, 'disputes' => $disputes];
    };

    $makerStats = $statsForUser($maker);
    $takerStats = $statsForUser($taker);

    [$makerTierKey, $makerTierLabel] = $tierFor($makerStats['orders']);
    [$takerTierKey, $takerTierLabel] = $tierFor($takerStats['orders']);

    $makerTone = $toneFor((string) ($maker->username ?? $maker->email ?? $maker->id ?? 'm'));
    $takerTone = $toneFor((string) ($taker->username ?? $taker->email ?? $taker->id ?? 't'));

    // Payment snapshot — try snapshot data first, fall back to payment method name
    $paymentSnapshot = $order->payer_payment_account_snapshot ?? $order->receiver_payment_account_snapshot ?? [];
    $paymentName     = $paymentSnapshot['name'] ?? $paymentSnapshot['title'] ?? $order->paymentMethod?->name ?? null;
    $paymentAccount  = $paymentSnapshot['account'] ?? $paymentSnapshot['number'] ?? $paymentSnapshot['phone'] ?? null;
    $paymentHolder   = $paymentSnapshot['holder'] ?? $paymentSnapshot['account_name'] ?? null;

    // Timeline steps based on order status
    $statusValue = $order->status?->value ?? null;
    $timeline = [
        [
            'label'  => __('Created'),
            'time'   => $order->created_at?->format('H:i') ?? '—',
            'done'   => true,
            'active' => false,
        ],
        [
            'label'  => __('Marked paid'),
            'time'   => $order->paid_at?->format('H:i') ?? __('pending'),
            'done'   => $order->paid_at !== null,
            'active' => $statusValue === OrderStatus::PAID->value,
        ],
        [
            'label'  => __('Disputed'),
            'time'   => $order->disputed_at?->format('H:i') ?? __('pending'),
            'done'   => $order->disputed_at !== null,
            'active' => $statusValue === OrderStatus::DISPUTED->value && $isOpen,
        ],
        [
            'label'  => __('Resolution'),
            'time'   => $isOpen ? __('pending') : ($order->completed_at?->format('H:i') ?? '—'),
            'done'   => ! $isOpen && $dispute->status === DisputeStatus::RESOLVED,
            'active' => false,
        ],
    ];

    $disputeAge = $dispute->created_at?->diffForHumans();
@endphp

@section('p2p_action')
    <a href="{{ route('admin.p2p.disputes.index') }}" class="fb-btn fb-btn--ghost fb-btn--sm">
        <i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
        <span>@lang('Back to queue')</span>
    </a>
@endsection

@section('p2p_content')
    <div class="p2p-refresh">
        <div class="dispute-detail-grid">
            <div>
                {{-- ── Order summary card with timeline ── --}}
                <section class="fb-card">
                    <div class="fb-card__head">
                        <div>
                            <span class="fb-hero__eyebrow">@lang('Order') · @lang('escrow held')</span>
                            <h5 style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                                <span class="fb-mono">#{{ $order->trx_id ?? $order->id }}</span>
                                <span class="fb-pill fb-pill--{{ $isOpen ? 'warning' : 'success' }}">
                                    <i class="fa-solid fa-{{ $isOpen ? 'clock' : 'circle-check' }}" aria-hidden="true"></i>
                                    <span>{{ $dispute->status->label() }}{{ $disputeAge ? ' · '.$disputeAge : '' }}</span>
                                </span>
                            </h5>
                        </div>
                        <div class="fb-card__meta">
                            <span class="fb-pill fb-pill--neutral">
                                <i class="fa-solid fa-user" aria-hidden="true"></i>
                                <span>@lang('Opened by') {{ $raiserIsTaker ? __('buyer') : __('seller') }}</span>
                            </span>
                        </div>
                    </div>

                    <div class="order-summary">
                        <div class="order-summary__cell">
                            <span class="order-summary__label">@lang('Amount')</span>
                            <span class="order-summary__value fb-num">{{ number_format((float) $order->total, 2) }}</span>
                            <span class="order-summary__sub">{{ $currencyCode }} · {{ number_format((float) $order->amount, 8) }}</span>
                        </div>
                        <div class="order-summary__cell">
                            <span class="order-summary__label">@lang('Platform fee')</span>
                            <span class="order-summary__value fb-num">{{ number_format((float) $order->maker_fee + (float) $order->taker_fee, 4) }}</span>
                            <span class="order-summary__sub">@lang('Maker') {{ number_format((float) $order->maker_fee, 2) }} · @lang('Taker') {{ number_format((float) $order->taker_fee, 2) }}</span>
                        </div>
                        <div class="order-summary__cell">
                            <span class="order-summary__label">@lang('Payment method')</span>
                            <span class="order-summary__value">{{ $paymentName ?? __('Not specified') }}</span>
                            <span class="order-summary__sub fb-mono">{{ $paymentAccount ?? '—' }}</span>
                        </div>
                        <div class="order-summary__cell">
                            <span class="order-summary__label">@lang('Opened')</span>
                            <span class="order-summary__value">{{ $disputeAge ?? '—' }}</span>
                            <span class="order-summary__sub">{{ $dispute->created_at?->format('M d, H:i') ?? '—' }}</span>
                        </div>
                    </div>

                    <div class="fb-card__body" style="border-top: 1px solid var(--color-border-soft);">
                        <div class="fb-timeline">
                            @foreach($timeline as $step)
                                <div class="fb-timeline__step {{ $step['done'] ? 'is-done' : '' }} {{ $step['active'] ? 'is-active' : '' }}">
                                    <span class="fb-timeline__dot">
                                        <i class="fa-solid fa-{{ $step['done'] ? 'check' : ($step['active'] ? 'circle-exclamation' : 'circle') }}" aria-hidden="true"></i>
                                    </span>
                                    <span class="fb-timeline__label">{{ $step['label'] }}</span>
                                    <span class="fb-timeline__time fb-num">{{ $step['time'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </section>

                {{-- ── Parties (Maker vs Taker) ── --}}
                <section class="fb-card">
                    <div class="fb-card__head">
                        <div>
                            <span class="fb-hero__eyebrow">@lang('Parties')</span>
                            <h5>@lang('Maker · Taker')</h5>
                        </div>
                    </div>
                    <div class="fb-card__body">
                        <div class="parties-grid">
                            {{-- Maker (Seller) --}}
                            <div class="party-card">
                                <div class="party-card__head">
                                    <span class="fb-avatar fb-avatar--lg fb-avatar--{{ $makerTone }}">{{ $initialsFor($maker) }}</span>
                                    <div style="min-width: 0;">
                                        <div class="party-card__role">@lang('Maker · Seller')</div>
                                        <div class="party-card__name">{{ $maker?->name ?? '—' }}</div>
                                        <div class="party-card__handle">{{ $maker?->username ? '@'.$maker->username : ($maker?->email ?? '') }}</div>
                                    </div>
                                </div>
                                <div class="party-card__badges">
                                    <span class="fb-tier fb-tier--{{ $makerTierKey }}">{{ $makerTierLabel }}</span>
                                    @if($maker?->isKycVerified())
                                        <span class="fb-pill fb-pill--success">
                                            <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                                            <span>@lang('Verified')</span>
                                        </span>
                                    @else
                                        <span class="fb-pill fb-pill--warning">
                                            <i class="fa-solid fa-clock" aria-hidden="true"></i>
                                            <span>@lang('KYC pending')</span>
                                        </span>
                                    @endif
                                </div>
                                <div class="party-card__stats">
                                    <div class="party-card__stat">
                                        <span class="party-card__stat-label">@lang('Orders')</span>
                                        <span class="party-card__stat-value">{{ number_format($makerStats['orders']) }}</span>
                                    </div>
                                    <div class="party-card__stat">
                                        <span class="party-card__stat-label">@lang('Completion')</span>
                                        <span class="party-card__stat-value">{{ number_format($makerStats['completion'], 1) }}%</span>
                                    </div>
                                    <div class="party-card__stat">
                                        <span class="party-card__stat-label">@lang('Disputes')</span>
                                        <span class="party-card__stat-value" style="{{ $makerStats['disputes'] > 1 ? 'color: var(--color-danger);' : '' }}">{{ number_format($makerStats['disputes']) }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="parties-grid__divider">VS</div>

                            {{-- Taker (Buyer) --}}
                            <div class="party-card">
                                <div class="party-card__head">
                                    <span class="fb-avatar fb-avatar--lg fb-avatar--{{ $takerTone }}">{{ $initialsFor($taker) }}</span>
                                    <div style="min-width: 0;">
                                        <div class="party-card__role">@lang('Taker · Buyer')</div>
                                        <div class="party-card__name">{{ $taker?->name ?? '—' }}</div>
                                        <div class="party-card__handle">{{ $taker?->username ? '@'.$taker->username : ($taker?->email ?? '') }}</div>
                                    </div>
                                </div>
                                <div class="party-card__badges">
                                    <span class="fb-tier fb-tier--{{ $takerTierKey }}">{{ $takerTierLabel }}</span>
                                    @if($taker?->isKycVerified())
                                        <span class="fb-pill fb-pill--success">
                                            <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                                            <span>@lang('Verified')</span>
                                        </span>
                                    @else
                                        <span class="fb-pill fb-pill--warning">
                                            <i class="fa-solid fa-clock" aria-hidden="true"></i>
                                            <span>@lang('KYC pending')</span>
                                        </span>
                                    @endif
                                </div>
                                <div class="party-card__stats">
                                    <div class="party-card__stat">
                                        <span class="party-card__stat-label">@lang('Orders')</span>
                                        <span class="party-card__stat-value">{{ number_format($takerStats['orders']) }}</span>
                                    </div>
                                    <div class="party-card__stat">
                                        <span class="party-card__stat-label">@lang('Completion')</span>
                                        <span class="party-card__stat-value">{{ number_format($takerStats['completion'], 1) }}%</span>
                                    </div>
                                    <div class="party-card__stat">
                                        <span class="party-card__stat-label">@lang('Disputes')</span>
                                        <span class="party-card__stat-value" style="{{ $takerStats['disputes'] > 1 ? 'color: var(--color-danger);' : '' }}">{{ number_format($takerStats['disputes']) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                {{-- ── Dispute reason ── --}}
                <section class="fb-card">
                    <div class="fb-card__head">
                        <div>
                            <span class="fb-hero__eyebrow">{{ $raiserIsTaker ? __("Buyer's claim") : __("Seller's claim") }}</span>
                            <h5>@lang('Dispute reason')</h5>
                        </div>
                        <div class="fb-card__meta">
                            @if($isOpen)
                                <span class="fb-pill fb-pill--warning">
                                    <i class="fa-solid fa-clock" aria-hidden="true"></i>
                                    <span>@lang('Awaiting your review')</span>
                                </span>
                            @else
                                <span class="fb-pill fb-pill--success">
                                    <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                                    <span>{{ $dispute->status->label() }}</span>
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="fb-card__body">
                        <div class="dispute-reason">
                            <span class="dispute-reason__icon"><i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i></span>
                            <div style="flex: 1; min-width: 0;">
                                <div class="dispute-reason__meta">@lang('Reason')</div>
                                <p class="dispute-reason__text">{{ $dispute->reason ?? __('No reason provided.') }}</p>
                                <div class="dispute-reason__by">
                                    @lang('Raised by') <b style="color: var(--color-text);">{{ $raiser?->name ?? '—' }}</b>
                                    {{ $raiser?->username ? '· @'.$raiser->username : '' }}
                                    · {{ $dispute->created_at?->format('M d, H:i') ?? '—' }}
                                </div>
                                @if($dispute->resolution)
                                    <div style="margin-top: 12px; padding-top: 12px; border-top: 1px solid var(--color-border-soft);">
                                        <div class="dispute-reason__meta" style="color: var(--color-success-ink);">@lang('Resolution')</div>
                                        <p style="font-size: var(--font-sm); color: var(--color-text); line-height: 1.5; margin-top: 4px;">{{ $dispute->resolution }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </section>

                {{-- ── Payment snapshot ── --}}
                @if($paymentName)
                    <section class="fb-card">
                        <div class="fb-card__head">
                            <div>
                                <span class="fb-hero__eyebrow">@lang('Payment snapshot')</span>
                                <h5>@lang('Buyer-attested receipt')</h5>
                            </div>
                        </div>
                        <div class="fb-card__body">
                            <div style="display: flex; align-items: center; gap: 14px; padding: 14px 16px; background: var(--color-surface-tint); border: 1px solid var(--color-border-soft); border-radius: var(--radius-md);">
                                <span style="flex: 0 0 auto; width: 44px; height: 44px; border-radius: var(--radius-md); background: var(--color-primary); color: #fff; display: inline-flex; align-items: center; justify-content: center; font-weight: var(--font-extrabold); font-size: 14px;">
                                    {{ strtoupper(substr($paymentName, 0, 2)) }}
                                </span>
                                <div style="flex: 1 1 auto; min-width: 0;">
                                    <div style="font-size: var(--font-sm); font-weight: var(--font-extrabold); color: var(--color-text);">{{ $paymentName }}</div>
                                    @if($paymentAccount || $paymentHolder)
                                        <div style="font-size: var(--font-xs); color: var(--color-text-muted); font-family: var(--font-mono);">
                                            {{ $paymentAccount ?? '' }}{{ $paymentAccount && $paymentHolder ? ' · ' : '' }}{{ $paymentHolder ?? '' }}
                                        </div>
                                    @endif
                                </div>
                                <div style="text-align: right;">
                                    <div class="fb-num" style="font-weight: 800; font-size: var(--font-md);">{{ number_format((float) $order->total, 2) }}</div>
                                    <div style="font-size: 11px; color: var(--color-text-faint);">{{ $currencyCode }}</div>
                                </div>
                            </div>
                        </div>
                    </section>
                @endif
            </div>

            {{-- ── Sticky resolution panel ── --}}
            <aside class="resolution-card" data-resolution-card>
                <div class="resolution-card__head">
                    <span class="fb-hero__eyebrow">@lang('Resolve dispute')</span>
                    <h5>@lang('Choose an outcome')</h5>
                </div>

                @if($isOpen)
                    <form method="POST" action="{{ route('admin.p2p.disputes.resolve-release', $dispute) }}" data-resolution-form
                          data-release-url="{{ route('admin.p2p.disputes.resolve-release', $dispute) }}"
                          data-refund-url="{{ route('admin.p2p.disputes.resolve-refund', $dispute) }}">
                        @csrf
                        <div class="resolution-card__body">
                            <div class="resolution-card__choice is-selected--release" data-resolution-choice="release" role="radio" aria-checked="true" tabindex="0">
                                <span class="resolution-card__choice-icon resolution-card__choice-icon--success">
                                    <i class="fa-solid fa-arrow-up-from-bracket" aria-hidden="true"></i>
                                </span>
                                <div style="flex: 1; min-width: 0;">
                                    <div class="resolution-card__choice-title">@lang('Release escrow to buyer')</div>
                                    <div class="resolution-card__choice-sub">
                                        @lang('Crypto goes to the taker (buyer). Maker forfeits the trade.')
                                    </div>
                                </div>
                                <span class="resolution-card__choice-radio"></span>
                            </div>

                            <div class="resolution-card__choice" data-resolution-choice="refund" role="radio" aria-checked="false" tabindex="0">
                                <span class="resolution-card__choice-icon resolution-card__choice-icon--danger">
                                    <i class="fa-solid fa-arrow-rotate-left" aria-hidden="true"></i>
                                </span>
                                <div style="flex: 1; min-width: 0;">
                                    <div class="resolution-card__choice-title">@lang('Refund to maker')</div>
                                    <div class="resolution-card__choice-sub">
                                        @lang('Escrow returns to the maker (seller). Logs payment failure.')
                                    </div>
                                </div>
                                <span class="resolution-card__choice-radio"></span>
                            </div>

                            <div class="resolution-card__form">
                                <label for="resolution_note">@lang('Admin notes (optional)')</label>
                                <textarea id="resolution_note" name="resolution_note" rows="4" maxlength="280"
                                          data-resolution-note
                                          placeholder="{{ __('Context or evidence summary for the record') }}"></textarea>
                                <div class="resolution-card__counter">
                                    <span>@lang('Be specific · max 280 chars')</span>
                                    <span><span class="fb-num" data-resolution-counter>0</span> / 280</span>
                                </div>
                            </div>
                        </div>
                        <div class="resolution-card__foot">
                            <a href="{{ route('admin.p2p.disputes.index') }}" class="fb-btn fb-btn--ghost fb-btn--sm">@lang('Cancel')</a>
                            <button type="submit" class="fb-btn fb-btn--success fb-btn--sm" data-resolution-submit>
                                <i class="fa-solid fa-check" aria-hidden="true" data-resolution-submit-icon></i>
                                <span data-resolution-submit-label>@lang('Release escrow')</span>
                            </button>
                        </div>
                    </form>
                @else
                    <div class="resolution-card__body">
                        <div class="dispute-reason" style="border: 1px solid var(--color-success-soft); background: var(--color-success-soft);">
                            <span class="dispute-reason__icon" style="background: var(--color-success);">
                                <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                            </span>
                            <div style="flex: 1;">
                                <div class="dispute-reason__meta" style="color: var(--color-success-ink);">{{ $dispute->status->label() }}</div>
                                <p class="dispute-reason__text">@lang('This dispute has been resolved. No further action is possible.')</p>
                            </div>
                        </div>
                    </div>
                @endif
            </aside>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            var card = document.querySelector('[data-resolution-card]');
            if (!card) return;

            var form        = card.querySelector('[data-resolution-form]');
            var choices     = card.querySelectorAll('[data-resolution-choice]');
            var note        = card.querySelector('[data-resolution-note]');
            var counter     = card.querySelector('[data-resolution-counter]');
            var submitBtn   = card.querySelector('[data-resolution-submit]');
            var submitIcon  = card.querySelector('[data-resolution-submit-icon]');
            var submitLabel = card.querySelector('[data-resolution-submit-label]');

            if (!form) return;

            var releaseUrl = form.getAttribute('data-release-url');
            var refundUrl  = form.getAttribute('data-refund-url');

            function applyChoice(choice) {
                choices.forEach(function (el) {
                    var on = el.getAttribute('data-resolution-choice') === choice;
                    el.classList.remove('is-selected--release', 'is-selected--refund');
                    if (on) {
                        el.classList.add(choice === 'release' ? 'is-selected--release' : 'is-selected--refund');
                    }
                    el.setAttribute('aria-checked', on ? 'true' : 'false');
                });

                if (choice === 'release') {
                    form.setAttribute('action', releaseUrl);
                    submitBtn.classList.remove('fb-btn--danger');
                    submitBtn.classList.add('fb-btn--success');
                    submitIcon.className = 'fa-solid fa-check';
                    submitLabel.textContent = @json(__('Release escrow'));
                } else {
                    form.setAttribute('action', refundUrl);
                    submitBtn.classList.remove('fb-btn--success');
                    submitBtn.classList.add('fb-btn--danger');
                    submitIcon.className = 'fa-solid fa-rotate-left';
                    submitLabel.textContent = @json(__('Refund maker'));
                }
            }

            choices.forEach(function (el) {
                el.addEventListener('click', function () {
                    applyChoice(el.getAttribute('data-resolution-choice'));
                });
                el.addEventListener('keydown', function (e) {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        applyChoice(el.getAttribute('data-resolution-choice'));
                    }
                });
            });

            if (note && counter) {
                var update = function () { counter.textContent = String(note.value.length); };
                note.addEventListener('input', update);
                update();
            }
        })();
    </script>
@endpush
