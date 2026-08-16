@extends('backend.p2p.layout')

@section('title', __('P2P Marketplace'))

@section('p2p_title')
    {{ __('P2P Marketplace') }}
@endsection

@section('p2p_icon', 'transaction-2')

@php
    $p2pEnabled       = (bool) ($settings->enabled ?? false);
    $ordersToday      = (int) ($insights['orders_today'] ?? 0);
    $inFlightOrders   = (int) ($insights['in_flight_orders'] ?? 0);
    $openDisputes     = (int) ($insights['open_disputes'] ?? 0);
    $activeOffers     = (int) ($insights['active_offers'] ?? 0);
    $orderSeries      = $activityChart['orders'] ?? [];
    $disputeSeries    = $activityChart['disputes'] ?? [];
    $chartLabels      = $activityChart['labels'] ?? [];
    $periodOrders     = (int) array_sum($orderSeries);
    $periodDisputes   = (int) array_sum($disputeSeries);
    $hasActivity      = ($periodOrders + $periodDisputes) > 0;
    $makerFee         = rtrim(rtrim(number_format((float) $settings->maker_fee_pct, 4, '.', ''), '0'), '.') ?: '0';
    $takerFee         = rtrim(rtrim(number_format((float) $settings->taker_fee_pct, 4, '.', ''), '0'), '.') ?: '0';
    $statusClass      = $p2pEnabled ? 'p2p-market-chip--success' : 'p2p-market-chip--danger';
    $statusLabel      = $p2pEnabled ? __('Open · Accepting trades') : __('Paused');
    $statusSub        = $p2pEnabled
        ? __('All fee tiers active · Settlement window normal · Configure rules from settings.')
        : __('Trading is currently disabled from marketplace settings.');
    $expiryMinutes    = (int) $settings->order_expiry_minutes;
    $disputeMinutes   = (int) $settings->dispute_window_minutes;
    $updatedAgo       = $settings->updated_at ? $settings->updated_at->diffForHumans() : __('recently');
    $currencyCode     = siteCurrency('code') ?? 'BDT';

    // Build SVG sparkline points from order counts
    $chartWidth  = 720;
    $chartHeight = 220;
    $padX        = 16;
    $padY        = 18;
    $series      = $orderSeries;
    $count       = max(count($series), 2);
    $maxValue    = $hasActivity ? max(1, (int) ((max($series ?: [1])) * 1.15)) : 1;
    $stepX       = ($chartWidth - $padX * 2) / max(1, $count - 1);

    $points = [];
    foreach ($series as $i => $v) {
        $x = $padX + $i * $stepX;
        $y = $chartHeight - $padY - (($v - 0) / max(0.0001, $maxValue)) * ($chartHeight - $padY * 2);
        $points[] = [$x, $y];
    }
    if (empty($points)) {
        for ($i = 0; $i < 14; $i++) {
            $points[] = [$padX + $i * $stepX, $chartHeight - $padY];
        }
    }
    $linePath = '';
    foreach ($points as $i => $p) {
        $linePath .= ($i === 0 ? 'M ' : ' L ').number_format($p[0], 2, '.', '').' '.number_format($p[1], 2, '.', '');
    }
    $lastX     = $points[count($points) - 1][0];
    $areaPath  = $linePath.' L '.number_format($lastX, 2, '.', '').' '.($chartHeight - $padY).' L '.$padX.' '.($chartHeight - $padY).' Z';
@endphp

@section('p2p_action')
    @can('p2p-manage')
        <a href="{{ route('admin.p2p.settings.edit') }}" class="fb-btn fb-btn--ghost fb-btn--sm">
            <i class="fa-solid fa-sliders" aria-hidden="true"></i>
            <span>@lang('Configure')</span>
        </a>
    @endcan
    @can('p2p-dispute-manage')
        <a href="{{ route('admin.p2p.disputes.index') }}" class="fb-btn fb-btn--primary fb-btn--sm">
            <i class="fa-solid fa-gavel" aria-hidden="true"></i>
            <span>@lang('Disputes')</span>
            @if($openDisputes > 0)
                <span class="fb-pill fb-pill--danger ms-1">{{ number_format($openDisputes) }}</span>
            @endif
        </a>
    @endcan
@endsection

@section('p2p_content')
    <div class="p2p-refresh">
        {{-- Market status hero --}}
        <section class="p2p-market-card market-status">
            <div class="p2p-market-card__header">
                <div>
                    <span class="p2p-market-eyebrow">@lang('Marketplace status')</span>
                    <div class="market-status__row">
                        <h2 class="market-status__title">{{ $statusLabel }}</h2>
                        <span class="p2p-market-chip {{ $statusClass }}">
                            <span class="p2p-market-chip__dot"></span>
                            {{ $p2pEnabled ? __('Live') : __('Paused') }}
                        </span>
                    </div>
                    <p class="market-status__sub">
                        {{ $statusSub }}
                        <br>
                        @lang('Last config update'): <b>{{ $updatedAgo }}</b>.
                    </p>
                </div>
                <div class="market-status__chips">
                    <div class="fee-chip">
                        <span class="fee-chip__label">@lang('Maker fee')</span>
                        <span class="fee-chip__value fb-num">{{ $makerFee }}<sub>%</sub></span>
                    </div>
                    <div class="fee-chip">
                        <span class="fee-chip__label">@lang('Taker fee')</span>
                        <span class="fee-chip__value fb-num">{{ $takerFee }}<sub>%</sub></span>
                    </div>
                    <div class="fee-chip">
                        <span class="fee-chip__label">@lang('Dispute window')</span>
                        <span class="fee-chip__value fb-num">{{ $disputeMinutes }}<sub>@lang('min')</sub></span>
                    </div>
                    <div class="fee-chip">
                        <span class="fee-chip__label">@lang('Expiry')</span>
                        <span class="fee-chip__value fb-num">{{ $expiryMinutes }}<sub>@lang('min')</sub></span>
                    </div>
                </div>
            </div>
        </section>

        {{-- KPI cards --}}
        <div class="p2p-market-kpis">
            <div class="p2p-market-kpi">
                <span class="p2p-market-kpi__icon p2p-market-kpi__icon--primary">
                    <i class="fa-solid fa-receipt" aria-hidden="true"></i>
                </span>
                <div class="p2p-market-kpi__body">
                    <span class="p2p-market-kpi__label">@lang('Orders today')</span>
                    <span class="p2p-market-kpi__value">{{ number_format($ordersToday) }}</span>
                    <span class="p2p-market-kpi__sub">
                        <span class="p2p-market-kpi__delta--up">
                            <i class="fa-solid fa-arrow-up" aria-hidden="true"></i> @lang('Last 24h')
                        </span>
                    </span>
                </div>
            </div>

            <div class="p2p-market-kpi">
                <span class="p2p-market-kpi__icon p2p-market-kpi__icon--success">
                    <i class="fa-solid fa-handshake" aria-hidden="true"></i>
                </span>
                <div class="p2p-market-kpi__body">
                    <span class="p2p-market-kpi__label">@lang('Active offers')</span>
                    <span class="p2p-market-kpi__value">{{ number_format($activeOffers) }}</span>
                    <span class="p2p-market-kpi__sub">
                        <i class="fa-solid fa-store" aria-hidden="true" style="color: var(--color-text-faint);"></i>
                        @lang('Live across marketplace')
                    </span>
                </div>
            </div>

            <div class="p2p-market-kpi">
                <span class="p2p-market-kpi__icon p2p-market-kpi__icon--warning">
                    <i class="fa-solid fa-hourglass-half" aria-hidden="true"></i>
                </span>
                <div class="p2p-market-kpi__body">
                    <span class="p2p-market-kpi__label">@lang('Pending escrow')</span>
                    <span class="p2p-market-kpi__value">{{ number_format($inFlightOrders) }}</span>
                    <span class="p2p-market-kpi__sub">
                        <span class="p2p-market-kpi__delta--up">
                            <i class="fa-solid fa-circle-info" aria-hidden="true"></i> @lang('In-flight orders')
                        </span>
                    </span>
                </div>
            </div>

            <div class="p2p-market-kpi">
                <span class="p2p-market-kpi__icon p2p-market-kpi__icon--danger">
                    <i class="fa-solid fa-gavel" aria-hidden="true"></i>
                </span>
                <div class="p2p-market-kpi__body">
                    <span class="p2p-market-kpi__label">@lang('Open disputes')</span>
                    <span class="p2p-market-kpi__value">{{ number_format($openDisputes) }}</span>
                    <span class="p2p-market-kpi__sub">
                        @if($openDisputes > 0)
                            <span class="p2p-market-kpi__delta--down">
                                <i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i> @lang('Requires attention')
                            </span>
                        @else
                            <span style="color: var(--color-text-faint);">@lang('All clear')</span>
                        @endif
                    </span>
                </div>
            </div>
        </div>

        {{-- 14-day chart --}}
        <section class="fb-card">
            <div class="fb-card__head">
                <div>
                    <span class="fb-hero__eyebrow">@lang('Trading activity')</span>
                    <h5>@lang('14-day flow')</h5>
                </div>
                <div class="fb-card__meta">
                    <span class="fb-pill fb-pill--{{ $hasActivity ? 'success' : 'neutral' }}">
                        <i class="fa-solid fa-arrow-trend-{{ $hasActivity ? 'up' : 'down' }}" aria-hidden="true"></i>
                        <span>{{ number_format($periodOrders) }} @lang('orders')</span>
                    </span>
                    <span class="fb-pill fb-pill--{{ $periodDisputes > 0 ? 'danger' : 'neutral' }}">
                        <i class="fa-solid fa-gavel" aria-hidden="true"></i>
                        <span>{{ number_format($periodDisputes) }} @lang('disputes')</span>
                    </span>
                </div>
            </div>
            <div class="fb-card__body">
                <div class="chart-headline">
                    <div>
                        <span class="chart-headline__eyebrow">@lang('Total orders · Last 14 days')</span>
                        <span class="chart-headline__value fb-num">{{ number_format($periodOrders) }}</span>
                    </div>
                    <div class="chart-headline__legend">
                        <span><i class="fa-solid fa-square" style="color: var(--color-primary);"></i> @lang('Orders')</span>
                        <span><i class="fa-solid fa-square" style="color: var(--color-danger);"></i> @lang('Disputes')</span>
                    </div>
                </div>

                @if($hasActivity)
                    <svg viewBox="0 0 {{ $chartWidth }} {{ $chartHeight }}" class="p2p-chart" preserveAspectRatio="none" aria-label="{{ __('14-day order volume chart') }}">
                        <defs>
                            <linearGradient id="p2pChartFill" x1="0" x2="0" y1="0" y2="1">
                                <stop offset="0%"  stop-color="var(--color-primary)" stop-opacity="0.22"></stop>
                                <stop offset="100%" stop-color="var(--color-primary)" stop-opacity="0"></stop>
                            </linearGradient>
                        </defs>
                        @for($i = 0; $i < 4; $i++)
                            @php $y = $padY + ($i * ($chartHeight - $padY * 2)) / 3; @endphp
                            <line x1="{{ $padX }}" x2="{{ $chartWidth - $padX }}" y1="{{ $y }}" y2="{{ $y }}"
                                  stroke="var(--color-border-soft)" stroke-dasharray="3 4"></line>
                        @endfor
                        <path d="{{ $areaPath }}" fill="url(#p2pChartFill)"></path>
                        <path d="{{ $linePath }}" fill="none" stroke="var(--color-primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                        @foreach($points as $i => $p)
                            <circle cx="{{ $p[0] }}" cy="{{ $p[1] }}" r="2.5"
                                    fill="var(--color-card)" stroke="var(--color-primary)" stroke-width="1.5"></circle>
                        @endforeach
                        @foreach($chartLabels as $i => $label)
                            @if($i % 3 === 0)
                                <text x="{{ $padX + $i * $stepX }}" y="{{ $chartHeight - 3 }}"
                                      text-anchor="middle" font-size="10"
                                      fill="var(--color-text-faint)" font-weight="600">{{ $label }}</text>
                            @endif
                        @endforeach
                    </svg>
                @else
                    <div class="pa-table-empty">
                        <div class="pa-table-empty__icon"><i class="fa-solid fa-chart-line"></i></div>
                        <div class="pa-table-empty__title">@lang('No activity yet')</div>
                        <div>@lang('Orders and disputes from the last 14 days will appear here.')</div>
                    </div>
                @endif
            </div>
        </section>
    </div>
@endsection
