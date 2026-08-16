<div class="col-sm-12 col-md-6">
	<div class="card dashboard-panel dashboard-panel--compact dashboard-panel--growth dashboard-analytics-panel dashboard-analytics-panel--growth shadow-sm border-0 h-100">
		<div class="card-body p-0">
			<div class="dashboard-panel__header dashboard-analytics-panel__header">
				<div class="dashboard-analytics-panel__heading">
					<span class="dashboard-analytics-panel__icon" aria-hidden="true">
						<i class="fa-solid fa-chart-line"></i>
					</span>
					<div class="dashboard-analytics-panel__title-group">
						<span class="dashboard-section__eyebrow">{{ __('Wallet Analytics') }}</span>
						<h2 class="dashboard-panel__title mb-1">{{ __('Wallet Growth Trend') }}</h2>
					</div>
				</div>
				<div class="btn-toolbar dashboard-analytics-panel__toolbar" role="toolbar">
					<div class="input-group">
						<input type="hidden" id="wallet-hidden-daterange">
						<div id="wallet-reportrange"
						     class="report-range dashboard-analytics-panel__range form-control d-flex align-items-center justify-content-between cursor-pointer">
							<div class="d-flex align-items-center gap-2">
								<i class="fa-solid fa-calendar-days text-primary"></i>
								<span class="text-nowrap flex-grow-1">{{ __('Loading') }}...</span>
							</div>
							<x-icon name="angle-down" class="text-muted flex-shrink-0 ms-2"/>
						</div>
					</div>
				</div>
			</div>
			<div class="dashboard-chart-shell dashboard-chart-shell--compact dashboard-analytics-panel__chart-shell">
				<div id="chart-wallet-growth" class="dashboard-chart"></div>
			</div>
		</div>
	</div>
</div>


@push('scripts')
	@include('backend.dashboard.partials._wallet_growth_script')
@endpush
