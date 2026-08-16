<section class="ud-charts mb-3">
    <div class="row g-3">
        <div class="col-xl-6">
            <article class="ud-chart-card" data-chart-card>
                <header class="ud-chart-card__head">
                    <div class="ud-chart-card__intro">
                        <h3 class="ud-chart-card__heading">
                            <span class="ud-chart-card__trend ud-chart-card__trend--in"><i class="fas fa-arrow-down"></i></span>
                            {{ __('Deposits') }}
                        </h3>
                        <span class="ud-chart-card__period">{{ __('Last 7 days') }}</span>
                    </div>
                    <div class="ud-chart-card__meta">
                        <strong class="ud-chart-card__total ud-chart-card__total--in">{{ $totalSuccessDeposit }}</strong>
                        <button
                            type="button"
                            class="ud-chart-card__toggle"
                            data-chart-toggle
                            aria-expanded="false"
                            aria-controls="deposit-chart-panel"
                            data-collapsed-label="{{ __('Show chart') }}"
                            data-expanded-label="{{ __('Hide chart') }}"
                        >
                            <span class="ud-chart-card__toggle-label" data-chart-toggle-label>{{ __('Show chart') }}</span>
                            <span class="ud-chart-card__toggle-icon"><i class="fas fa-chevron-down" aria-hidden="true"></i></span>
                        </button>
                    </div>
                </header>
                <div class="ud-chart-card__body-shell" id="deposit-chart-panel" data-chart-panel>
                    <div class="ud-chart-card__body">
                        <div id="deposit-chart" class="ud-chart-card__canvas"></div>
                    </div>
                </div>
            </article>
        </div>

        <div class="col-xl-6">
            <article class="ud-chart-card" data-chart-card>
                <header class="ud-chart-card__head">
                    <div class="ud-chart-card__intro">
                        <h3 class="ud-chart-card__heading">
                            <span class="ud-chart-card__trend ud-chart-card__trend--out"><i class="fas fa-arrow-up"></i></span>
                            {{ __('Withdrawals') }}
                        </h3>
                        <span class="ud-chart-card__period">{{ __('Last 7 days') }}</span>
                    </div>
                    <div class="ud-chart-card__meta">
                        <strong class="ud-chart-card__total ud-chart-card__total--out">{{ $totalSuccessWithdraw }}</strong>
                        <button
                            type="button"
                            class="ud-chart-card__toggle"
                            data-chart-toggle
                            aria-expanded="false"
                            aria-controls="withdraw-chart-panel"
                            data-collapsed-label="{{ __('Show chart') }}"
                            data-expanded-label="{{ __('Hide chart') }}"
                        >
                            <span class="ud-chart-card__toggle-label" data-chart-toggle-label>{{ __('Show chart') }}</span>
                            <span class="ud-chart-card__toggle-icon"><i class="fas fa-chevron-down" aria-hidden="true"></i></span>
                        </button>
                    </div>
                </header>
                <div class="ud-chart-card__body-shell" id="withdraw-chart-panel" data-chart-panel>
                    <div class="ud-chart-card__body">
                        <div id="withdraw-chart" class="ud-chart-card__canvas"></div>
                    </div>
                </div>
            </article>
        </div>
    </div>
</section>
