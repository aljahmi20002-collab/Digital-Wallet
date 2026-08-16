@extends('backend.p2p.layout')

@section('title', __('Trader').': '.$user->name)

@section('p2p_title')
    {{ __('Trader Profile') }}
@endsection

@section('p2p_icon', 'badge-account')

@section('p2p_action')
    <a href="{{ route('admin.p2p.advertisers.index') }}" class="fb-btn fb-btn--ghost">
        <x-icon name="back" height="14" width="14"/>
        {{ __('Back') }}
    </a>
@endsection

@section('p2p_content')
    @php
        $userName    = $user->name ?? __('User');
        $initials    = strtoupper(substr((string) $user?->first_name, 0, 1) . substr((string) $user?->last_name, 0, 1));
        $initials    = $initials !== '' ? $initials : strtoupper(substr((string) $userName, 0, 1));
        $isVerified  = $user ? $user->isKycVerified() : false;
        $isSuspended = (bool) $user?->isP2pTradingSuspended();
        $progressTone = $completionRate >= 98 ? 'fb-progress--success' : ($completionRate >= 95 ? 'fb-progress--warning' : 'fb-progress--danger');
    @endphp

    <div class="fb-page fb-console">
        {{-- Suspension banner --}}
        @if($isSuspended)
            <div class="fb-banner fb-banner--danger pa-notice">
                <span class="fb-banner__icon"><i class="fa-solid fa-ban"></i></span>
                <div class="flex-grow-1">
                    <strong>{{ __('P2P trading suspended') }}</strong>
                    <div class="fb-banner-meta">
                        {{ __('Since') }}
                        <strong>{{ $user->p2p_trading_suspended_at?->format('Y-m-d H:i') ?? '-' }}</strong>
                        @if(!empty($user->p2p_trading_suspend_reason))
                            / <em>{{ $user->p2p_trading_suspend_reason }}</em>
                        @endif
                    </div>
                </div>
                <form method="POST" action="{{ route('admin.p2p.advertisers.reactivate', $user) }}" onsubmit="return confirm(@json(__('Reactivate this trader?')))">
                    @csrf
                    <button type="submit" class="fb-btn fb-btn--success fb-btn--sm">
                        <x-icon name="check" height="14" width="14"/>
                        {{ __('Reactivate') }}
                    </button>
                </form>
            </div>
        @endif

        <div class="fb-grid-2">
            {{-- Identity --}}
            <div class="fb-card pa-card">
                <div class="fb-card__body">
                    <div class="fb-user pa-user gap-3">
                        @if($user && !empty($user->avatar))
                            <span class="fb-avatar fb-avatar-lg"><img src="{{ asset($user->avatar_alt) }}" alt="{{ $userName }}" loading="lazy"></span>
                        @else
                            <span class="fb-avatar fb-avatar-lg">{{ $initials }}</span>
                        @endif
                        <div class="fb-min-w-0 flex-grow-1">
                            <div class="fb-user__name fb-text-md">{{ $userName }}</div>
                            <div class="fb-user__meta">{{ $user->username ? '@'.$user->username : $user->email }}</div>
                            <div class="mt-2 d-flex flex-wrap gap-2">
                                <span class="fb-pill {{ $isVerified ? 'fb-pill--success' : 'fb-pill--neutral' }} pa-pill {{ $isVerified ? 'pa-pill--success' : 'pa-pill--neutral' }}">
                                    {{ $isVerified ? __('KYC Verified') : __('KYC Unverified') }}
                                </span>
                                @if($isSuspended)
                                    <span class="fb-pill fb-pill--danger pa-pill pa-pill--danger">{{ __('Suspended') }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="fb-card__body fb-cardbody-divided">
                    <dl class="fb-dl pa-dl">
                        <div class="fb-dl__row pa-dl__row">
                            <span class="fb-dl__label pa-dl__label">{{ __('Trades') }}</span>
                            <span class="fb-dl__value pa-dl__value fb-num">{{ number_format($totalOrders) }}</span>
                        </div>
                        <div class="fb-dl__row pa-dl__row">
                            <span class="fb-dl__label pa-dl__label">{{ __('Completion') }}</span>
                            <span class="fb-dl__value pa-dl__value">
                                <span class="fb-progress {{ $progressTone }}">
                                    <progress class="fb-progress__native" value="{{ max(0, min(100, $completionRate)) }}" max="100"></progress>
                                    <span class="fb-progress__value">{{ number_format($completionRate, 1) }}%</span>
                                </span>
                            </span>
                        </div>
                        <div class="fb-dl__row pa-dl__row">
                            <span class="fb-dl__label pa-dl__label">{{ __('Rating') }}</span>
                            <span class="fb-dl__value pa-dl__value fb-num">{{ $avgRating !== null ? number_format((float) $avgRating, 1) : '-' }}</span>
                        </div>
                        <div class="fb-dl__row pa-dl__row">
                            <span class="fb-dl__label pa-dl__label">{{ __('Reviews') }}</span>
                            <span class="fb-dl__value pa-dl__value fb-num">{{ number_format($feedbackCount) }}</span>
                        </div>
                    </dl>
                </div>
                @if(!$isSuspended)
                    <div class="fb-card__footer">
                        <button type="button" class="fb-btn fb-btn--danger fb-btn--sm" data-bs-toggle="modal" data-bs-target="#traderSuspendModal">
                            <x-icon name="banned-users" height="14" width="14"/>
                            {{ __('Suspend Trading') }}
                        </button>
                    </div>
                @endif
            </div>

            {{-- Recent Reviews --}}
            <div class="fb-card pa-card h-100">
                <div class="fb-card__head">
                    <div>
                        <span class="fb-hero__eyebrow">{{ __('Feedback') }}</span>
                        <h5 class="pa-card__title">{{ __('Recent Reviews') }}</h5>
                    </div>
                </div>
                <div class="fb-card__body">
                    @forelse($feedbacks as $fb)
                        <div class="d-flex justify-content-between gap-3 py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                            <div class="min-w-0">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="fb-user__name">{{ $fb->user->name ?? __('User') }}</span>
                                    <span class="fb-user__meta">/ {{ $fb->created_at->diffForHumans() }}</span>
                                </div>
                                @if($fb->comment)
                                    <div class="mt-1 fb-text-xs fb-text-muted">{{ $fb->comment }}</div>
                                @endif
                            </div>
                            <div>
                                <span class="fb-pill {{ $fb->rating >= 4 ? 'fb-pill--success' : ($fb->rating <= 2 ? 'fb-pill--danger' : 'fb-pill--neutral') }} pa-pill {{ $fb->rating >= 4 ? 'pa-pill--success' : ($fb->rating <= 2 ? 'pa-pill--danger' : 'pa-pill--neutral') }}">
                                    {{ $fb->rating }}/5
                                </span>
                            </div>
                        </div>
                    @empty
                        <x-admin-not-found
                            :title="__('No reviews yet')"
                            :message="__('Reviews from completed trades will appear here.')"
                            icon="fa-comments"
                            class="pa-table-empty"
                        />
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Trade ads inventory --}}
        <section class="fb-card pa-table-card">
            <div class="fb-card__head">
                <div>
                    <span class="fb-hero__eyebrow">{{ __('Trade Ads') }}</span>
                    <h5 class="pa-card__title">{{ __('Trade Ads') }}</h5>
                </div>
            </div>
            <div class="fb-table table-responsive">
                <table class="pa-table">
                    <thead>
                        <tr>
                            <th>{{ __('Side') }}</th>
                            <th>{{ __('Asset') }}</th>
                            <th class="text-end">{{ __('Price') }}</th>
                            <th class="text-end">{{ __('Min') }}</th>
                            <th class="text-end">{{ __('Max') }}</th>
                            <th>{{ __('Status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($offers as $offer)
                        @php($isSell = $offer->side->value === 'SELL')
                        <tr>
                            <td>
                                <span class="fb-pill {{ $isSell ? 'fb-pill--success' : 'fb-pill--warning' }} pa-pill {{ $isSell ? 'pa-pill--success' : 'pa-pill--warning' }}">
                                    {{ $offer->side->value }}
                                </span>
                            </td>
                            <td class="fb-mono">{{ $offer->wallet?->currency?->code ?? '-' }}</td>
                            <td class="text-end fb-num">{{ number_format((float) $offer->price, 2) }}</td>
                            <td class="text-end fb-num">{{ number_format((float) $offer->min_amount, 8) }}</td>
                            <td class="text-end fb-num">{{ $offer->max_amount !== null ? number_format((float) $offer->max_amount, 8) : '-' }}</td>
                            <td>
                                <span class="fb-pill fb-pill--neutral pa-pill pa-pill--neutral">
                                    {{ method_exists($offer->status, 'label') ? $offer->status->label() : (string) $offer->status }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <x-admin-not-found
                                    :title="__('No trade ads found')"
                                    :message="__('This trader has not posted any trade ads yet.')"
                                    icon="fa-list"
                                />
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            @if($offers->hasPages())
                <div class="fb-card__footer pa-table__foot">
                    {{ $offers->links() }}
                </div>
            @endif
        </section>
    </div>

    {{-- Suspend modal --}}
    @if(!$isSuspended)
        <div class="modal fade" id="traderSuspendModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form method="POST" action="{{ route('admin.p2p.advertisers.suspend', $user) }}" class="modal-content">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title d-flex align-items-center">
                            <x-icon name="banned-users" height="18" width="18" class="me-2 text-danger"/>
                            {{ __('Suspend P2P Trading') }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-3 fb-text-sm fb-text-muted">
                            {{ __('Active offers will be disabled. In-flight orders continue to completion.') }}
                        </p>
                        <div class="mb-0">
                            <label class="form-label" for="trader_suspend_reason">{{ __('Reason') }} <span class="text-danger">*</span></label>
                            <textarea id="trader_suspend_reason" name="reason" rows="3" maxlength="500" class="fb-textarea form-control" required placeholder="{{ __('Describe the reason for suspension') }}"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="fb-btn fb-btn--ghost fb-btn--sm" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="fb-btn fb-btn--danger fb-btn--sm">
                            <x-icon name="banned-users" height="14" width="14"/>
                            {{ __('Suspend Trader') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
@endsection
