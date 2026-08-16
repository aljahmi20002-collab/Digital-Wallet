@php
    $statistics = featureStatistics($trxType);
    $title = __('7-Day :type Summary', ['type' => $trxType->label()]);
    $currencySymbol = siteCurrency('symbol');
    $collapseId = 'featureStatsCollapse-' . \Illuminate\Support\Str::slug((string) $trxType->value);
@endphp

<section class="feature-summary-widget mb-4">
    <div class="feature-summary-widget__panel">
        <div class="feature-summary-widget__header">
            <span class="feature-summary-widget__brand d-none d-md-inline-flex">
                <x-icon name="bar" />
            </span>

            <div class="feature-summary-widget__header-main">
                <h6 class="main-title feature-summary-widget__title">{{ $title }}</h6>
                <p class="feature-summary-widget__subtitle">
                    {{ __('Weekly snapshot of completed, pending, and failed volume.') }}
                </p>
            </div>

            <div class="feature-summary-widget__header-side d-none d-md-flex">
                <span class="feature-summary-widget__meta">
                    <span class="feature-summary-widget__meta-icon">
                        <x-icon name="calendar" class="icon" />
                    </span>
                    <span>{{ __('Note: Rolling 7-day window') }}</span>
                    <span class="feature-summary-widget__meta-dot" aria-hidden="true"></span>
                </span>
            </div>

            <button class="feature-summary-widget__toggle d-flex d-md-none align-items-center justify-content-between w-100"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#{{ $collapseId }}"
                    aria-expanded="false"
                    aria-controls="{{ $collapseId }}">
                <span class="feature-summary-widget__toggle-copy">
                    <span class="feature-summary-widget__toggle-label">{{ __('View 7-day breakdown') }}</span>
                    <strong>{{ __('3 summary metrics') }}</strong>
                </span>

                <span class="feature-summary-widget__toggle-icon">
                    <x-icon name="angle-down" class="icon" />
                </span>
            </button>
        </div>

        <div id="{{ $collapseId }}" class="collapse d-md-block">
            <div class="feature-summary-widget__body">
                <div class="row g-3 feature-summary-widget__grid">
                    @foreach($statistics as $statistic)
                        @php
                            $changeValue = (float) $statistic['value_change'];
                            $formattedValue = $currencySymbol . number_format((float) $statistic['value'], 2);

                            if ($changeValue > 0) {
                                $changeClass = 'positive';
                                $iconName = 'chart-up';
                                $formattedChange = '+' . $currencySymbol . number_format($changeValue, 2);
                            } elseif ($changeValue < 0) {
                                $changeClass = 'negative';
                                $iconName = 'chart-down';
                                $formattedChange = '-' . $currencySymbol . number_format(abs($changeValue), 2);
                            } else {
                                $changeClass = 'info';
                                $iconName = 'chart';
                                $formattedChange = $currencySymbol . number_format($changeValue, 2);
                            }
                        @endphp

                        <div class="col-12 col-md-4">
                            <article @class([
                                'feature-summary-widget__card',
                                'feature-summary-widget__card--success' => $statistic['color_class'] === 'success-svg',
                                'feature-summary-widget__card--info' => $statistic['color_class'] === 'info-svg',
                                'feature-summary-widget__card--danger' => $statistic['color_class'] === 'danger-svg',
                            ])>
                                <div class="feature-summary-widget__card-top">
                                    <div class="feature-summary-widget__metric">
                                        <span class="feature-summary-widget__label">{{ $statistic['title'] }}</span>
                                        <h5 class="feature-summary-widget__value">{{ $formattedValue }}</h5>
                                    </div>

                                    <span class="feature-summary-widget__icon {{ $statistic['color_class'] }}">
                                        <x-icon name="{{ $statistic['icon'] }}" class="icon" />
                                    </span>
                                </div>

                                <div class="feature-summary-widget__card-bottom">
                                    <span class="feature-summary-widget__trend {{ $changeClass }}">
                                        <x-icon name="{{ $iconName }}" class="icon" />
                                        <span>{{ $formattedChange }}</span>
                                    </span>

                                    <span class="feature-summary-widget__context">{{ __('Compared to prior 7 days') }}</span>
                                </div>
                            </article>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>
