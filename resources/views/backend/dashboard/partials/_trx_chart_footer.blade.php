@php
	$symbol = siteCurrency('symbol');

	$colorMap = [
		'deposit'  => 'success',
		'withdraw' => 'danger',
		'payment'  => 'primary',
		'reward'   => 'warning',
	];

	$iconMap = [
		'deposit'  => 'fa-arrow-down',
		'withdraw' => 'fa-arrow-up',
		'payment'  => 'fa-credit-card',
		'reward'   => 'fa-gift',
	];

	$series     = $chartData['series'] ?? [];
	$categories = $chartData['dates'] ?? [];

	$stats = collect($series)->map(function ($item) use ($colorMap, $iconMap) {
		$type  = strtolower($item['name']);
		$total = collect($item['data'])->sum();

		return [
			'type'    => $type,
			'label'   => __(ucfirst($type)) . 's',
			'amount'  => $total,
			'color'   => $colorMap[$type] ?? 'secondary',
			'icon'    => $iconMap[$type] ?? 'fa-chart-line',
			'tooltip' => __('Total') . ' ' . __(ucfirst($type)) . 's',
		];
	});
@endphp

<div class="row g-3">
	@foreach($stats as $stat)
		<div class="col-12 col-md-6 col-xl-3">
			<button type="button"
			        class="stat-card stat-filter chart-stat-card"
			        data-type="{{ $stat['type'] }}">
				<div class="chart-stat-card__content">
					<div class="chart-stat-card__left">
                            <span class="chart-stat-card__icon text-{{ $stat['color'] }}">
                                <i class="fa-solid {{ $stat['icon'] }}"></i>
                            </span>
						<div>
							<span class="chart-stat-card__label">{{ $stat['label'] }}</span>
							<span class="chart-stat-card__hint">{{ __('Tap to isolate series') }}</span>
						</div>
					</div>
					<div class="text-end">
						<span class="chart-stat-card__value">
							{{ $symbol }}<span id="total-{{ $stat['type'] }}">{{ number_format($stat['amount'], 2) }}</span>
						</span>
					</div>
				</div>
			</button>
		</div>
	@endforeach
</div>
