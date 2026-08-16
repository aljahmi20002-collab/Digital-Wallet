<div class="card dashboard-panel dashboard-panel--feature border-0 shadow-sm mb-4">
    <div class="card-body p-0">
        <div class="dashboard-panel__header dashboard-panel__header--spacious">
            <div>
                <h2 class="dashboard-panel__title mb-1">{{ __('Transaction Summary') }}</h2>
                <p class="dashboard-panel__subtitle mb-0">{{ __('Track completed transaction trends by date range.') }}</p>
            </div>
            <div class="btn-toolbar" role="toolbar">
                <div class="input-group">
                    <input type="hidden" name="daterange" id="hidden-daterange" value="{{ request('daterange') }}">
                    <div id="reportrange" class="report-range form-control d-flex align-items-center justify-content-between cursor-pointer">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fa-solid fa-calendar-days text-primary"></i>
                            <span class="text-nowrap flex-grow-1">{{ __('Loading') }}...</span>
                        </div>
                        <x-icon name="angle-down" class="text-muted flex-shrink-0 ms-2"/>
                    </div>
                </div>
            </div>
        </div>

        <div class="dashboard-chart-shell">
            <div id="dashboard-trx-chart" class="dashboard-chart"></div>
        </div>
    </div>

    <div class="card-footer dashboard-panel__footer" id="trx-chart-footer">
        @include('backend.dashboard.partials._trx_chart_footer', ['chartData' => $chartData])
    </div>
</div>
@push('scripts')
	@include('backend.dashboard.partials._trx_chart_scripts')
@endpush





