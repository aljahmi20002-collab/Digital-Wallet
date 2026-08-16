@extends('frontend.layouts.user.index')

@section('title', __('All Ranks'))

@section('content')
    @php
        $user = auth()->user();
        $currencySymbol = siteCurrency('symbol');
        $earnedRankIds = collect((array) ($user?->old_ranks ?? []))
            ->push($user?->rank_id)
            ->filter()
            ->map(fn ($rankId): int => (int) $rankId)
            ->unique()
            ->values();
        $currentRank = $userRanks->firstWhere('id', $user?->rank_id)
            ?? $userRanks->first(fn ($rank) => $earnedRankIds->contains((int) $rank->id));
        $nextRank = $userRanks->first(fn ($rank) => ! $earnedRankIds->contains((int) $rank->id));
        $walletValues = $userRanks->map(fn ($rank) => data_get($rank->features, 'wallet_create', 0));
        $maxWallets = $walletValues->contains('unlimited') ? __('Unlimited') : ($walletValues->max() ?? 0);
        $maxReferral = $userRanks->max('features.referral_level') ?? 0;
        $maxReward = $currencySymbol . number_format((float) ($userRanks->max('reward') ?? 0), 2);
        $topVolume = $currencySymbol . number_format((float) ($userRanks->max('transaction_amount') ?? 0), 2);
        $totalRanks = $userRanks->count();
        $unlockedCount = $userRanks
            ->filter(fn ($rank) => $earnedRankIds->contains((int) $rank->id))
            ->count();
        $completionPercent = $totalRanks > 0 ? min(100, (int) round(($unlockedCount / $totalRanks) * 100)) : 0;
    @endphp

    <div class="row">
        <div class="col-12">
            <div class="single-form-card rank-showcase-page">
                @if($userRanks->isNotEmpty())
                    <div class="card-main rank-showcase-shell">
                        <div class="rank-overview-panel">
                            <section class="rank-showcase-hero rank-showcase-cockpit" aria-labelledby="rank-showcase-title">
                                <div class="rank-showcase-hero__copy">
                                    <span class="rank-showcase-kicker">{{ __('Membership Progress') }}</span>
                                    <h5 id="rank-showcase-title">{{ $currentRank ? __(':name Member', ['name' => $currentRank->name]) : __('No rank yet') }}</h5>
                                    <p>
                                        {{ __(':unlocked of :total ranks unlocked. Next target: :next with rewards up to :reward, :wallets wallets, and :referrals referral levels.', [
                                            'unlocked' => $unlockedCount,
                                            'total' => $totalRanks,
                                            'next' => $nextRank ? $nextRank->name : __('Top tier'),
                                            'reward' => $maxReward,
                                            'wallets' => $maxWallets,
                                            'referrals' => $maxReferral,
                                        ]) }}
                                    </p>
                                </div>

                                <div class="rank-progress-card">
                                    <div class="rank-progress-card__header">
                                        <span>{{ __('Unlocked') }}</span>
                                        <strong>{{ $completionPercent }}%</strong>
                                    </div>
                                    <div class="rank-progress-card__bar" aria-hidden="true">
                                        <span style="width: {{ $completionPercent }}%;"></span>
                                    </div>
                                    <div class="rank-progress-card__meta">
                                        <span>{{ __('Current') }}: {{ $currentRank ? $currentRank->name : __('No rank yet') }}</span>
                                        <span>{{ __('Next') }}: {{ $nextRank ? $nextRank->name : __('Top tier') }}</span>
                                    </div>
                                </div>

                                <div class="rank-summary-grid" aria-label="{{ __('Rank summary') }}">
                                    <div class="rank-summary-card">
                                        <span class="rank-summary-card__icon"><i class="fas fa-layer-group"></i></span>
                                        <span>{{ __('Ranks') }}</span>
                                        <strong>{{ $totalRanks }}</strong>
                                    </div>
                                    <div class="rank-summary-card">
                                        <span class="rank-summary-card__icon"><i class="fas fa-unlock"></i></span>
                                        <span>{{ __('Unlocked') }}</span>
                                        <strong>{{ $unlockedCount }}</strong>
                                    </div>
                                    <div class="rank-summary-card">
                                        <span class="rank-summary-card__icon"><i class="fas fa-gift"></i></span>
                                        <span>{{ __('Reward') }}</span>
                                        <strong>{{ $maxReward }}</strong>
                                    </div>
                                    <div class="rank-summary-card">
                                        <span class="rank-summary-card__icon"><i class="fas fa-chart-line"></i></span>
                                        <span>{{ __('Volume') }}</span>
                                        <strong>{{ $topVolume }}</strong>
                                    </div>
                                </div>
                            </section>

                            <div class="rank-progress-rail rank-progress-rail--compact" aria-label="{{ __('Rank progression') }}">
                                @foreach($userRanks as $rank)
                                    @php
                                        $isUnlocked = $earnedRankIds->contains((int) $rank->id);
                                        $isCurrent = (int) $rank->id === (int) ($user?->rank_id ?? 0);
                                    @endphp

                                    <div @class([
                                        'rank-progress-rail__item',
                                        'is-unlocked' => $isUnlocked,
                                        'is-current' => $isCurrent,
                                    ])
                                        @if($isCurrent) aria-current="step" @endif
                                        title="{{ $rank->name }}">
                                        <span class="rank-progress-rail__dot">
                                            @if($isUnlocked)
                                                <i class="fas fa-check"></i>
                                            @else
                                                <i class="fas fa-lock"></i>
                                            @endif
                                        </span>
                                        <span class="rank-progress-rail__label">{{ $rank->name }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="rank-tier-grid">
                            @foreach($userRanks as $rank)
                                @php
                                    $isUnlocked = $earnedRankIds->contains((int) $rank->id);
                                    $isCurrent = (int) $rank->id === (int) ($user?->rank_id ?? 0);
                                    $walletLimit = data_get($rank->features, 'wallet_create', 0);
                                    $walletLabel = $walletLimit === 'unlimited' ? __('Unlimited') : number_format((float) $walletLimit);
                                    $referralLevel = data_get($rank->features, 'referral_level', 0);
                                    $accentClass = 'rank-tier-card--accent-' . (($loop->index % 4) + 1);
                                @endphp

                                <article @class([
                                    'rank-tier-card',
                                    $accentClass,
                                    'is-unlocked' => $isUnlocked,
                                    'is-current' => $isCurrent,
                                ])>
                                    <div class="rank-tier-card__top">
                                        <span class="rank-tier-card__icon">
                                            <img src="{{ asset($rank->icon ?: 'general/static/default/rank.png') }}"
                                                 alt="{{ $rank->name }}" loading="lazy">
                                        </span>
                                        <span class="rank-tier-card__status">
                                            <i @class(['fas', 'fa-star' => $isCurrent, 'fa-check' => $isUnlocked && ! $isCurrent, 'fa-lock' => ! $isUnlocked])></i>
                                            {{ $isCurrent ? __('Current') : ($isUnlocked ? __('Unlocked') : __('Locked')) }}
                                        </span>
                                    </div>

                                    <div class="rank-tier-card__body">
                                        <h6>{{ __(':name Member', ['name' => $rank->name]) }}</h6>
                                        <p>{{ $rank->description }}</p>
                                    </div>

                                    <div class="rank-tier-card__metrics">
                                        <span>
                                            <i class="fas fa-gift"></i>
                                            {{ $currencySymbol . number_format((float) $rank->reward, 2) }}
                                        </span>
                                        <span>
                                            <i class="fas fa-wallet"></i>
                                            {{ __(':count wallets', ['count' => $walletLabel]) }}
                                        </span>
                                        <span>
                                            <i class="fas fa-sitemap"></i>
                                            {{ __(':count levels', ['count' => number_format((float) $referralLevel)]) }}
                                        </span>
                                    </div>

                                    <div class="rank-tier-card__footer">
                                        <span>{{ __('Required Volume') }}</span>
                                        <strong>{{ $currencySymbol . number_format((float) $rank->transaction_amount, 2) }}</strong>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="card-main rank-showcase-shell rank-showcase-shell--empty">
                        <x-user-not-found
                            :title="__('No ranks available yet')"
                            :message="__('Rank benefits will appear here once the admin configures active ranks.')"
                            icon="fa-award"
                        />
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .rank-showcase-page {
            overflow: hidden;
        }

        .rank-showcase-shell {
            background:
                linear-gradient(135deg, rgba(42, 89, 204, 0.08), rgba(28, 160, 116, 0.1)),
                #f7f9fd;
            border: 1px solid rgba(47, 90, 173, 0.14);
            border-radius: 8px;
            padding: 14px;
            position: relative;
        }

        .rank-showcase-shell--empty {
            padding: 20px;
        }

        .rank-overview-panel {
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid rgba(31, 75, 150, 0.12);
            border-radius: 8px;
            box-shadow: none;
            margin-bottom: 10px;
            overflow: hidden;
            padding: 12px;
        }

        .rank-showcase-hero {
            align-items: center;
            background: transparent;
            border: 0;
            border-radius: 0;
            box-shadow: none;
            color: #151d2f;
            display: grid;
            gap: 12px;
            grid-template-columns: minmax(260px, 1fr) minmax(190px, 230px) minmax(320px, 1.32fr);
            margin-bottom: 0;
            overflow: hidden;
            padding: 0 0 11px;
            position: relative;
        }

        .rank-showcase-hero::after {
            display: none;
        }

        .rank-showcase-hero__copy,
        .rank-progress-card,
        .rank-summary-grid {
            position: relative;
            z-index: 1;
        }

        .rank-showcase-hero__copy {
            min-width: 0;
        }

        .rank-showcase-kicker {
            align-items: center;
            background: #eef4ff;
            border: 1px solid rgba(36, 87, 214, 0.14);
            border-radius: 8px;
            color: #2457d6;
            display: inline-flex;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0;
            line-height: 1;
            margin-bottom: 7px;
            padding: 5px 8px;
        }

        .rank-showcase-hero h5 {
            color: #151d2f;
            font-size: 16px;
            font-weight: 800;
            line-height: 1.2;
            margin-bottom: 4px;
            overflow-wrap: anywhere;
        }

        .rank-showcase-hero p {
            color: #687286;
            font-size: 12px;
            line-height: 1.4;
            margin-bottom: 0;
            max-width: 440px;
        }

        .rank-progress-card {
            align-self: center;
            background: linear-gradient(135deg, #f7faff, #effaf5);
            border: 1px solid rgba(31, 75, 150, 0.1);
            border-radius: 8px;
            box-shadow: none;
            padding: 10px;
        }

        .rank-progress-card__header,
        .rank-progress-card__meta {
            align-items: center;
            display: flex;
            gap: 10px;
            justify-content: space-between;
        }

        .rank-progress-card__header span,
        .rank-progress-card__meta span {
            color: #667085;
            font-size: 11px;
            overflow-wrap: anywhere;
        }

        .rank-progress-card__header strong {
            color: #2457d6;
            font-size: 21px;
            font-weight: 800;
            line-height: 1;
        }

        .rank-progress-card__bar {
            background: #e6edf7;
            border-radius: 999px;
            height: 7px;
            margin: 10px 0 8px;
            overflow: hidden;
        }

        .rank-progress-card__bar span {
            background: linear-gradient(90deg, #2457d6 0%, #1f9a68 100%);
            border-radius: inherit;
            display: block;
            height: 100%;
        }

        .rank-summary-grid {
            display: grid;
            gap: 6px;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            margin-bottom: 0;
        }

        .rank-summary-card {
            align-items: center;
            background: #f7faff;
            border: 1px solid rgba(31, 75, 150, 0.11);
            border-radius: 8px;
            box-shadow: none;
            display: grid;
            column-gap: 8px;
            grid-template-columns: 28px minmax(0, 1fr);
            grid-template-rows: auto auto;
            min-height: 44px;
            padding: 7px 8px;
        }

        .rank-summary-card__icon {
            align-items: center;
            background: #edf4ff;
            border-radius: 8px;
            color: #2457d6;
            display: inline-flex;
            grid-row: 1 / span 2;
            height: 28px;
            justify-content: center;
            width: 28px;
        }

        .rank-summary-card span:not(.rank-summary-card__icon) {
            color: #6d7587;
            font-size: 11px;
            font-weight: 700;
            line-height: 1.2;
            overflow-wrap: anywhere;
        }

        .rank-summary-card strong {
            color: #151d2f;
            font-size: 14px;
            font-weight: 800;
            line-height: 1.2;
            overflow-wrap: anywhere;
        }

        .rank-progress-rail {
            align-items: start;
            background: transparent;
            border: 0;
            border-radius: 0;
            border-top: 1px solid rgba(31, 75, 150, 0.08);
            box-shadow: none;
            display: flex;
            gap: 0;
            margin-bottom: 0;
            overflow-x: auto;
            padding: 11px 10px 0;
            position: relative;
            scroll-snap-type: x proximity;
            scrollbar-width: thin;
        }

        .rank-progress-rail__item {
            align-items: center;
            background: transparent;
            border: 0;
            box-shadow: none;
            display: grid;
            flex: 1 0 108px;
            gap: 5px;
            justify-items: center;
            min-height: 48px;
            margin: 0;
            padding: 0 8px;
            position: relative;
            scroll-snap-align: start;
            text-align: center;
        }

        .rank-progress-rail__item::before,
        .rank-progress-rail__item::after {
            background: #dfe7f2;
            content: "";
            height: 2px;
            position: absolute;
            top: 14px;
            z-index: 0;
        }

        .rank-progress-rail__item::before {
            left: 0;
            right: 50%;
        }

        .rank-progress-rail__item::after {
            left: 50%;
            right: 0;
        }

        .rank-progress-rail__item:first-child::before,
        .rank-progress-rail__item:last-child::after {
            display: none;
        }

        .rank-progress-rail__item.is-unlocked {
            background: transparent;
            border-color: transparent;
        }

        .rank-progress-rail__item.is-unlocked::before,
        .rank-progress-rail__item.is-unlocked::after {
            background: linear-gradient(90deg, #2457d6, #1f9a68);
        }

        .rank-progress-rail__item.is-current {
            background: transparent;
            border-color: transparent;
            box-shadow: none;
        }

        .rank-progress-rail__item.is-current::before {
            background: linear-gradient(90deg, #2457d6, #1f9a68);
        }

        .rank-progress-rail__item.is-current::after {
            background: #dfe7f2;
        }

        .rank-progress-rail__dot {
            align-items: center;
            background: #eef2f8;
            border: 1px solid #d9e2ef;
            border-radius: 50%;
            box-shadow: none;
            color: #64748b;
            display: inline-flex;
            flex: 0 0 28px;
            height: 28px;
            justify-content: center;
            position: relative;
            width: 28px;
            z-index: 1;
        }

        .rank-progress-rail__item.is-unlocked .rank-progress-rail__dot {
            background: linear-gradient(135deg, #2457d6, #1f9a68);
            border-color: rgba(255, 255, 255, 0.76);
            color: #ffffff;
        }

        .rank-progress-rail__item.is-current .rank-progress-rail__dot {
            background: linear-gradient(135deg, #2457d6, #1742a8);
            border: 3px solid #ffffff;
            box-shadow: none;
            color: #ffffff;
        }

        .rank-progress-rail__label {
            color: #2a3346;
            font-size: 11px;
            font-weight: 800;
            line-height: 1.25;
            max-width: 100%;
            overflow-wrap: anywhere;
            text-align: center;
        }

        .rank-progress-rail__item.is-unlocked .rank-progress-rail__label,
        .rank-progress-rail__item.is-current .rank-progress-rail__label {
            color: #17345d;
        }

        .rank-tier-grid {
            display: grid;
            gap: 10px;
            grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
        }

        .rank-tier-card {
            background: #ffffff;
            border: 1px solid rgba(31, 75, 150, 0.12);
            border-radius: 8px;
            box-shadow: none;
            display: flex;
            flex-direction: column;
            min-height: 100%;
            overflow: hidden;
            position: relative;
            transition: border-color 160ms ease;
        }

        .rank-tier-card::before {
            background: linear-gradient(90deg, #2457d6, #1f9a68);
            content: "";
            height: 5px;
            left: 0;
            position: absolute;
            right: 0;
            top: 0;
        }

        .rank-tier-card--accent-2::before {
            background: linear-gradient(90deg, #7152cc, #2d8fb4);
        }

        .rank-tier-card--accent-3::before {
            background: linear-gradient(90deg, #d98723, #d34268);
        }

        .rank-tier-card--accent-4::before {
            background: linear-gradient(90deg, #1f7a8c, #4f8a2e);
        }

        .rank-tier-card:not(.is-unlocked) {
            background: #fbfcff;
        }

        .rank-tier-card.is-current {
            border-color: rgba(36, 87, 214, 0.28);
        }

        .rank-tier-card:hover {
            border-color: rgba(36, 87, 214, 0.2);
        }

        .rank-tier-card__top {
            align-items: center;
            display: flex;
            gap: 10px;
            justify-content: space-between;
            padding: 14px 14px 0;
        }

        .rank-tier-card__icon {
            align-items: center;
            background: #f2f6ff;
            border: 1px solid rgba(31, 75, 150, 0.12);
            border-radius: 8px;
            display: inline-flex;
            height: 42px;
            justify-content: center;
            padding: 8px;
            width: 42px;
        }

        .rank-tier-card__icon img {
            height: 100%;
            max-width: 100%;
            object-fit: contain;
            width: 100%;
        }

        .rank-tier-card__status {
            align-items: center;
            background: #eef4ff;
            border: 1px solid rgba(36, 87, 214, 0.14);
            border-radius: 8px;
            color: #2457d6;
            display: inline-flex;
            flex: 0 0 auto;
            font-size: 11px;
            font-weight: 800;
            gap: 6px;
            line-height: 1;
            padding: 7px 8px;
        }

        .rank-tier-card.is-unlocked .rank-tier-card__status {
            background: #effaf5;
            border-color: rgba(31, 154, 104, 0.18);
            color: #157654;
        }

        .rank-tier-card.is-current .rank-tier-card__status {
            background: #fff7df;
            border-color: rgba(217, 135, 35, 0.2);
            color: #9a5e08;
        }

        .rank-tier-card:not(.is-unlocked) .rank-tier-card__icon {
            filter: grayscale(0.55);
            opacity: 0.72;
        }

        .rank-tier-card__body {
            padding: 10px 14px 7px;
        }

        .rank-tier-card__eyebrow {
            color: #7b8497;
            display: block;
            font-size: 11px;
            font-weight: 800;
            margin-bottom: 5px;
            overflow-wrap: anywhere;
            text-transform: uppercase;
        }

        .rank-tier-card__body h6 {
            color: #151d2f;
            font-size: 16px;
            font-weight: 800;
            line-height: 1.25;
            margin-bottom: 5px;
            overflow-wrap: anywhere;
        }

        .rank-tier-card__body p {
            color: #687286;
            display: -webkit-box;
            font-size: 12px;
            line-height: 1.45;
            margin-bottom: 0;
            min-height: 35px;
            overflow: hidden;
            overflow-wrap: anywhere;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 2;
        }

        .rank-tier-card__metrics {
            display: grid;
            gap: 6px;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            margin-top: auto;
            padding: 10px 14px 12px;
        }

        .rank-tier-card__metrics span {
            align-items: center;
            background: #f5f7fb;
            border: 1px solid rgba(31, 75, 150, 0.08);
            border-radius: 8px;
            color: #313b50;
            display: flex;
            flex-direction: column;
            font-size: 11px;
            font-weight: 800;
            gap: 5px;
            justify-content: center;
            min-height: 50px;
            overflow-wrap: anywhere;
            padding: 7px 6px;
            text-align: center;
        }

        .rank-tier-card__metrics i {
            color: #2457d6;
            font-size: 13px;
        }

        .rank-tier-card__footer {
            align-items: center;
            background: #f8fafc;
            border-top: 1px solid rgba(31, 75, 150, 0.09);
            display: flex;
            gap: 10px;
            justify-content: space-between;
            padding: 10px 14px;
        }

        .rank-tier-card__footer span {
            color: #7b8497;
            font-size: 12px;
            font-weight: 800;
            overflow-wrap: anywhere;
        }

        .rank-tier-card__footer strong {
            color: #151d2f;
            font-size: 13px;
            font-weight: 900;
            overflow-wrap: anywhere;
            text-align: right;
        }

        @media (max-width: 991.98px) {
            .rank-showcase-shell {
                padding: 12px;
            }

            .rank-showcase-hero {
                grid-template-columns: minmax(0, 1fr) minmax(150px, 190px);
            }

            .rank-showcase-hero .rank-summary-grid {
                grid-column: 1 / -1;
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }

            .rank-tier-grid {
                grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
            }
        }

        @media (max-width: 575.98px) {
            .rank-showcase-shell {
                padding: 10px;
            }

            .rank-overview-panel {
                padding: 10px;
            }

            .rank-showcase-hero {
                gap: 8px;
                grid-template-columns: 1fr;
                padding-bottom: 10px;
            }

            .rank-showcase-hero h5 {
                font-size: 16px;
            }

            .rank-progress-card__header strong {
                font-size: 20px;
            }

            .rank-tier-card__footer {
                align-items: flex-start;
                flex-direction: column;
            }

            .rank-showcase-hero .rank-summary-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .rank-tier-grid {
                grid-template-columns: 1fr;
            }

            .rank-summary-card {
                min-height: 44px;
            }

            .rank-progress-rail {
                -ms-overflow-style: none;
                overflow-x: hidden;
                padding: 10px 0 0;
                scrollbar-width: none;
            }

            .rank-progress-rail::-webkit-scrollbar {
                display: none;
            }

            .rank-progress-rail__item {
                flex: 1 1 0;
                min-width: 0;
                min-height: 44px;
                padding: 0 2px;
            }

            .rank-progress-rail__item::before,
            .rank-progress-rail__item::after {
                top: 13px;
            }

            .rank-progress-rail__dot {
                flex-basis: 26px;
                height: 26px;
                width: 26px;
            }

            .rank-progress-rail__label {
                font-size: 10px;
            }

            .rank-tier-card__footer strong {
                text-align: left;
            }
        }
    </style>
@endpush
