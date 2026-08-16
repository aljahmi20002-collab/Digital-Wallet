@php
    $statCards = collect($stats)
        ->groupBy(fn ($stat): string => $stat['group'] ?? 'overview')
        ->flatten(1);
@endphp

<div class="dashboard-section dashboard-kpi-section dashboard-kpi-board mb-4" aria-label="{{ __('Dashboard metrics') }}">
    <div class="dashboard-kpi-board__cards">
        @foreach($statCards as $stat)
            @php
                $hasLink = ! empty($stat['link']);
            @endphp
            <div class="card stat-card dashboard-kpi-card dashboard-kpi-card--{{ $stat['color_class'] }} border-0 h-100 @if($hasLink) dashboard-kpi-card--linked @endif">
                <span class="dashboard-kpi-card__accent" aria-hidden="true"></span>
                <div class="card-body">
                    <div class="dashboard-kpi-card__summary">
                        <div class="dashboard-kpi-card__icon {{ $stat['color_class'] }}">
                            <x-icon name="{{ $stat['icon'] }}" height="20" width="20" class="dashboard-kpi-card__svg"/>
                        </div>

                        <div class="dashboard-kpi-card__content">
                            <span class="dashboard-kpi-card__title">{{ $stat['title'] }}</span>
                            <div class="dashboard-kpi-card__count">{{ $stat['value'] }}</div>
                        </div>

                        @if ($hasLink)
                            <a href="{{ $stat['link'] }}"
                               class="dashboard-kpi-card__action stretched-link"
                               data-coreui-toggle="tooltip"
                               title="{{ __('Go to') }} {{ $stat['title'] }}"
                               aria-label="{{ __('Go to') }} {{ $stat['title'] }}">
                                <x-icon name="arrow-up-right" height="14" width="14"/>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
