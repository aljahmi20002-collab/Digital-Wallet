@extends('backend.p2p.layout')

@section('title', __('P2P Traders'))

@section('p2p_title')
    {{ __('Traders') }}
@endsection

@section('p2p_icon', 'badge-account')

@php
    $traderTotal    = method_exists($users, 'total') ? (int) $users->total() : $users->count();
    $items          = collect($users->items() ?? $users);
    $verifiedCount  = $items->filter(fn ($u) => $u->isKycVerified())->count();
    $suspendedCount = $items->filter(fn ($u) => $u->isP2pTradingSuspended())->count();

    $tierFor = function (int $totalOrders): array {
        return match (true) {
            $totalOrders >= 1000 => ['merchant', __('Merchant')],
            $totalOrders >= 250  => ['gold', __('Gold')],
            $totalOrders >= 50   => ['silver', __('Silver')],
            default              => ['bronze', __('Bronze')],
        };
    };

    $toneFor = function (string $seed): string {
        $tones = ['blue', 'green', 'amber', 'rose', 'violet', 'teal'];
        return $tones[abs(crc32($seed)) % count($tones)];
    };
@endphp

@section('p2p_action')
    <form method="GET" action="{{ route('admin.p2p.advertisers.index') }}" class="d-flex align-items-center gap-2">
        <input type="text" name="search" value="{{ $search ?? '' }}" class="form-control p2p-search-field fb-input" placeholder="{{ __('Search trader...') }}">
        <button type="submit" class="fb-btn fb-btn--primary fb-btn--sm">
            <x-icon name="search" height="14" width="14"/>
            <span>{{ __('Search') }}</span>
        </button>
    </form>
@endsection

@section('p2p_content')
    @php
        $hasTraders = $traderTotal > 0;
        $hasSearch  = ! empty($search);
    @endphp
    <div class="p2p-refresh">
        <section class="fb-card pa-table-card">
            <div class="fb-card__head">
                <div>
                    <span class="fb-hero__eyebrow">@lang('Directory')</span>
                    <h5>@lang('Trader directory')</h5>
                </div>
                @if($hasTraders)
                    <div class="fb-card__meta">
                        <span class="fb-pill fb-pill--neutral">
                            <i class="fa-solid fa-users" aria-hidden="true"></i>
                            <span>{{ number_format($traderTotal) }} {{ trans_choice('trader|traders', $traderTotal) }}</span>
                        </span>
                        @if($verifiedCount > 0)
                            <span class="fb-pill fb-pill--success">
                                <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                                <span>{{ number_format($verifiedCount) }} @lang('verified')</span>
                            </span>
                        @endif
                        @if($suspendedCount > 0)
                            <span class="fb-pill fb-pill--danger">
                                <i class="fa-solid fa-ban" aria-hidden="true"></i>
                                <span>{{ number_format($suspendedCount) }} @lang('suspended')</span>
                            </span>
                        @endif
                    </div>
                @endif
            </div>

            @if($hasTraders)
                <div class="fb-toolbar">
                    <div class="fb-search">
                        <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                        <input type="search" id="fb-traders-search" placeholder="{{ __('Filter loaded traders by name, handle, country...') }}" aria-label="{{ __('Filter traders') }}">
                    </div>
                    <div class="fb-segment" role="group" aria-label="{{ __('Trader status filter') }}">
                        <button type="button" class="fb-segment__item is-active" data-fb-traders-filter="all" aria-pressed="true">
                            <span>@lang('All')</span>
                            <span class="fb-segment__count">{{ number_format($traderTotal) }}</span>
                        </button>
                        @if($verifiedCount > 0)
                            <button type="button" class="fb-segment__item" data-fb-traders-filter="verified" aria-pressed="false">
                                <span>@lang('Verified')</span>
                                <span class="fb-segment__count">{{ number_format($verifiedCount) }}</span>
                            </button>
                        @endif
                        @if($suspendedCount > 0)
                            <button type="button" class="fb-segment__item" data-fb-traders-filter="suspended" aria-pressed="false">
                                <span>@lang('Suspended')</span>
                                <span class="fb-segment__count">{{ number_format($suspendedCount) }}</span>
                            </button>
                        @endif
                    </div>
                    @if($users->lastPage() > 1)
                        <div class="fb-toolbar__spacer"></div>
                        <span class="fb-pill fb-pill--neutral">
                            <i class="fa-solid fa-layer-group" aria-hidden="true"></i>
                            <span>@lang('Page :n of :t', ['n' => $users->currentPage(), 't' => $users->lastPage()])</span>
                        </span>
                    @endif
                </div>
            @endif

            @if(! $hasTraders)
                <x-admin-not-found
                    :title="$hasSearch ? __('No traders match your search') : __('No traders yet')"
                    :message="$hasSearch ? __('Try a different name, username, email or phone number.') : __('Traders will appear here once users create their first P2P trade ad.')"
                    icon="fa-users"
                    :action-url="$hasSearch ? route('admin.p2p.advertisers.index') : null"
                    :action-label="$hasSearch ? __('Clear search') : null"
                    action-icon="fa-arrow-rotate-left"
                />
            @else
                <div class="fb-card__body fb-card__body--flush">
                    <div class="fb-table table-responsive">
                        <table class="pa-table">
                        <thead>
                            <tr>
                                <th>@lang('Trader')</th>
                                <th>@lang('Tier')</th>
                                <th>@lang('KYC')</th>
                                <th class="text-end">@lang('Ads')</th>
                                <th class="text-end">@lang('Orders')</th>
                                <th>@lang('Completion')</th>
                                <th class="text-end">@lang('Rating')</th>
                                <th class="text-end">@lang('Action')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $u)
                                @php
                                    $s = $stats[(int) $u->id] ?? [];
                                    $isSuspended = $u->isP2pTradingSuspended();
                                    $kycOk = $u->isKycVerified();
                                    $totalOrders = (int) ($s['total_orders'] ?? 0);
                                    $completion  = (float) ($s['completion_rate'] ?? 0);
                                    $progressTone = $completion >= 90 ? '' : ($completion >= 70 ? 'fb-progress__fill--warn' : 'fb-progress__fill--low');
                                    $initials = strtoupper(substr((string) $u->first_name, 0, 1).substr((string) $u->last_name, 0, 1));
                                    $initials = $initials !== '' ? $initials : strtoupper(substr((string) ($u->name ?? '?'), 0, 1));
                                    [$tierKey, $tierLabel] = $tierFor($totalOrders);
                                    $tone = $toneFor((string) ($u->username ?? $u->email ?? $u->id));
                                @endphp
                                <tr data-trader-row
                                    data-trader-status="{{ $isSuspended ? 'suspended' : ($kycOk ? 'verified' : 'unverified') }}"
                                    data-trader-search="{{ strtolower(($u->name ?? '').' '.($u->username ?? '').' '.($u->email ?? '').' '.($u->country ?? '')) }}"
                                    @if($isSuspended) style="opacity: 0.7;" @endif>
                                    <td>
                                        <div class="fb-user">
                                            @if(!empty($u->avatar))
                                                <span class="fb-avatar fb-avatar--{{ $tone }}"><img src="{{ asset($u->avatar_alt) }}" alt="{{ $u->name }}" loading="lazy"></span>
                                            @else
                                                <span class="fb-avatar fb-avatar--{{ $tone }}">{{ $initials }}</span>
                                            @endif
                                            <span class="fb-user__meta">
                                                <span class="fb-user__name">{{ $u->name }}</span>
                                                <span class="fb-user__handle">{{ $u->username ? '@'.$u->username : $u->email }}</span>
                                            </span>
                                        </div>
                                    </td>
                                    <td><span class="fb-tier fb-tier--{{ $tierKey }}">{{ $tierLabel }}</span></td>
                                    <td>
                                        @if($isSuspended)
                                            <span class="fb-pill fb-pill--danger">
                                                <i class="fa-solid fa-ban" aria-hidden="true"></i>
                                                <span>@lang('Suspended')</span>
                                            </span>
                                        @elseif($kycOk)
                                            <span class="fb-pill fb-pill--success">
                                                <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                                                <span>@lang('Verified')</span>
                                            </span>
                                        @else
                                            <span class="fb-pill fb-pill--warning">
                                                <i class="fa-solid fa-clock" aria-hidden="true"></i>
                                                <span>@lang('Pending')</span>
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-end fb-num">{{ number_format((int) ($s['offers_count'] ?? 0)) }}</td>
                                    <td class="text-end fb-num">{{ number_format($totalOrders) }}</td>
                                    <td>
                                        <span class="fb-progress">
                                            <span class="fb-progress__track">
                                                <span class="fb-progress__fill {{ $progressTone }}" style="width: {{ max(0, min(100, $completion)) }}%;"></span>
                                            </span>
                                            <span class="fb-progress__label">
                                                <span>@lang('completion')</span>
                                                <span class="fb-num">{{ number_format($completion, 1) }}%</span>
                                            </span>
                                        </span>
                                    </td>
                                    <td class="text-end text-nowrap">
                                        @if(($s['avg_rating'] ?? null) !== null)
                                            <span class="fb-stars" aria-label="{{ __(':n out of 5', ['n' => number_format((float) $s['avg_rating'], 1)]) }}">
                                                <i class="fa-solid fa-star" aria-hidden="true"></i>
                                                <span class="fb-stars__num">{{ number_format((float) $s['avg_rating'], 1) }}</span>
                                            </span>
                                            <span style="font-size: var(--font-xs); color: var(--color-text-faint);">({{ number_format((int) ($s['feedback_count'] ?? 0)) }})</span>
                                        @else
                                            <span style="color: var(--color-text-faint);">—</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <div class="fb-btn-group">
                                            <a href="{{ route('admin.p2p.advertisers.show', $u) }}" class="fb-btn fb-btn--ghost fb-btn--sm">
                                                <i class="fa-solid fa-eye" aria-hidden="true"></i>
                                                <span>@lang('View')</span>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    </div>
                </div>

                @if($users->hasPages())
                    <div class="fb-card__footer pa-table__foot">
                        <span>{{ __('Showing :n of :t traders', ['n' => number_format(count($users->items())), 't' => number_format($traderTotal)]) }}</span>
                        {{ $users->links() }}
                    </div>
                @endif
            @endif
        </section>
    </div>
@endsection
