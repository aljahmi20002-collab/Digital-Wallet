@if($walletBalances->isNotEmpty())
	@php
		$defaultWallet     = $walletBalances->firstWhere('is_default', true) ?? $walletBalances->first();
		$baseCurrencyCode  = $defaultWallet['code'] ?? siteCurrency('code');
		$baseCurrencySym   = $defaultWallet['symbol'] ?? siteCurrency('symbol');
		$currencyCardCount = $walletBalances->count();
		$totalWallets      = (int) $walletBalances->sum(fn ($wallet) => (int) ($wallet['count'] ?? 0));
		$totalCurrencies   = max((int) ($defaultWallet['source_currency_count'] ?? $currencyCardCount), $currencyCardCount);
		$activeCurrencies  = max((int) ($defaultWallet['active_currency_count'] ?? 0), $walletBalances->where('status', true)->count());
	@endphp

	<div class="dashboard-section mb-4">
		<div class="card dashboard-panel dashboard-panel--treasury border-0 shadow-sm">
			<div class="card-body p-0" data-wallet-rail>
				<div class="dashboard-section__header">
					<div>
						<h2 class="dashboard-section__title mb-1">{{ __('Active Wallet Balances') }}</h2>
						<p class="dashboard-section__subtitle mb-0">{{ __('Live balances across every active wallet currency on the platform.') }}</p>
					</div>
					<div class="dashboard-section__meta">
						<span class="wallet-summary-shell__meta">
							<span class="wallet-summary-shell__meta-item"><i class="fa-regular fa-credit-card me-1"></i>{{ number_format($totalWallets) }} {{ __('Wallet Accounts') }}</span>
							<span class="wallet-summary-shell__meta-sep">&middot;</span>
							<span class="wallet-summary-shell__meta-item"><i class="fa-regular fa-money-bill-1 me-1"></i>{{ $activeCurrencies }}/{{ $totalCurrencies }} {{ __('Currencies Active') }}</span>
						</span>
						<span class="wallet-summary-shell__rail-controls">
							<button type="button"
							        class="wallet-summary-shell__rail-btn"
							        data-wallet-rail-prev
							        aria-label="{{ __('Scroll wallets left') }}">
								<i class="fa-solid fa-chevron-left"></i>
							</button>
							<button type="button"
							        class="wallet-summary-shell__rail-btn"
							        data-wallet-rail-next
							        aria-label="{{ __('Scroll wallets right') }}">
								<i class="fa-solid fa-chevron-right"></i>
							</button>
						</span>
					</div>
				</div>

				<div class="wallet-summary-grid{{ $currencyCardCount > 6 ? ' wallet-summary-grid--scrollable' : '' }}" data-wallet-rail-track aria-label="{{ __('Active wallet balances by currency') }}">
					@foreach($walletBalances as $wallet)
						@php
							[$amountMain, $amountFraction] = explode('.', number_format($wallet['total'], 2));
							$isActive        = ! empty($wallet['status']);
							$isDefault       = ! empty($wallet['is_default']);
							$statusLabel     = $isActive ? __('Active') : __('Disabled');
							$statusModifier  = $isActive ? 'is-enabled' : 'is-disabled';
							$convertedAmount = (float) ($wallet['total_in_default'] ?? $wallet['total']);
							$showConversion  = ! $isDefault && $baseCurrencyCode && strcasecmp($wallet['code'], $baseCurrencyCode) !== 0;
							$rateValue       = (float) ($wallet['exchange_rate'] ?? 1);
						@endphp
						<div class="wallet-summary-grid__item">
							<article class="card wallet-summary-card wallet-card-finish h-100 border-0 @if($isDefault) wallet-summary-card--default wallet-card-finish--default @endif" aria-label="{{ $wallet['code'] }} {{ __('wallet balance') }}">
								<div class="card-body">
									<span class="wallet-card-finish__chip" aria-hidden="true"></span>
									<span class="wallet-card-finish__sheen" aria-hidden="true"></span>

									<div class="wallet-summary-card__top-row">
										<div class="wallet-summary-card__currency-group">
											<div class="wallet-summary-card__icon-frame" aria-hidden="true">
												<span class="wallet-summary-card__icon-shell">
													@if($wallet['icon_url'])
														<img class="wallet-summary-card__currency-icon" src="{{ $wallet['icon_url'] }}" alt="{{ $wallet['code'] }}" loading="lazy">
													@else
														<span class="wallet-summary-card__icon-fallback">{{ $wallet['code'] }}</span>
													@endif
												</span>
											</div>
											<div class="wallet-summary-card__overview">
												<div class="wallet-summary-card__title-row">
													<h3 class="wallet-summary-card__title">{{ $wallet['code'] }}</h3>
													@if($isDefault)
														<span class="wallet-summary-card__pill wallet-summary-card__pill--default" title="{{ __('Default Currency') }}">
															<i class="fa-regular fa-star"></i>{{ __('Default') }}
														</span>
													@endif
												</div>
												<span class="wallet-summary-card__subtitle">{{ $wallet['name'] ?: __('Wallet Currency') }}</span>
											</div>
										</div>
										<div class="wallet-summary-card__meta-group">
											<span class="wallet-summary-card__account-badge" title="{{ __('Wallet Accounts') }}">
												<i class="fa-regular fa-user me-1"></i>{{ number_format($wallet['count']) }}
											</span>
											<div class="wallet-summary-card__status-badge wallet-summary-card__status-badge--currency {{ $statusModifier }}" aria-label="{{ __('Currency status') }}">
												<span class="wallet-summary-card__status-dot" aria-hidden="true"></span>
												<span class="wallet-summary-card__status-text">{{ $statusLabel }}</span>
											</div>
										</div>
									</div>

									<div class="wallet-summary-card__balance-row">
										<div class="wallet-summary-card__balance-stack">
											<span class="wallet-summary-card__balance-caption">{{ __('Total Wallet Balance') }}</span>
											<div class="wallet-summary-card__amount" aria-label="{{ $wallet['symbol'] }}{{ number_format($wallet['total'], 2) }}">
												<span class="wallet-summary-card__amount-symbol">{{ $wallet['symbol'] }}</span>
												<span class="wallet-summary-card__amount-main">{{ $amountMain }}</span>
												<span class="wallet-summary-card__amount-fraction">.{{ $amountFraction }}</span>
											</div>
										</div>
									</div>

									<div class="wallet-summary-card__footer">
										<div class="wallet-summary-card__rate wallet-summary-card__rate--base" title="{{ __('Conversion to default currency') }}">
											<i class="fa-regular fa-circle-dot wallet-summary-card__rate-icon"></i>
											<span class="wallet-summary-card__rate-text">
												@if($showConversion)
													{{ __('≈ :symbol:amount in :base', [
														'symbol' => $baseCurrencySym,
														'amount' => number_format($convertedAmount, 2),
														'base'   => $baseCurrencyCode,
													]) }}
												@else
													{{ __('Base currency · 1.00 rate') }}
												@endif
											</span>
										</div>

										@if($showConversion)
											<span class="wallet-summary-card__type-chip" title="{{ __('Exchange rate to default') }}">1 {{ $wallet['code'] }} = {{ number_format($rateValue, 4) }} {{ $baseCurrencyCode }}</span>
										@endif
									</div>
								</div>
							</article>
						</div>
					@endforeach
				</div>
			</div>
		</div>
	</div>

	@push('scripts')
		<script>
			'use strict';
			document.querySelectorAll('[data-wallet-rail]').forEach(function (rail) {
				var track = rail.querySelector('[data-wallet-rail-track]');
				var prev  = rail.querySelector('[data-wallet-rail-prev]');
				var next  = rail.querySelector('[data-wallet-rail-next]');
				if (!track) return;

				function step() {
					var first = track.querySelector('.wallet-summary-grid__item');
					if (!first) return track.clientWidth * 0.8;
					var gap = parseFloat(getComputedStyle(track).columnGap || getComputedStyle(track).gap || 0);
					return first.getBoundingClientRect().width + gap;
				}
				function updateNav() {
					var max = Math.max(0, track.scrollWidth - track.clientWidth - 2);
					var canScroll = track.scrollWidth > track.clientWidth + 2;
					if (prev) prev.toggleAttribute('disabled', !canScroll || track.scrollLeft <= 2);
					if (next) next.toggleAttribute('disabled', !canScroll || track.scrollLeft >= max);
					rail.classList.toggle('is-scrollable', canScroll);
				}

				/* Eased programmatic scroll for buttons */
				var rafId = null;
				function easeOutQuart(t) { return 1 - Math.pow(1 - t, 4); }
				function smoothScrollTo(target, duration) {
					if (rafId) cancelAnimationFrame(rafId);
					duration = duration || 420;
					var start = track.scrollLeft;
					var max = Math.max(0, track.scrollWidth - track.clientWidth);
					var end = Math.max(0, Math.min(max, target));
					var distance = end - start;
					if (!distance) return;
					var startTs = performance.now();
					function tick(now) {
						var t = Math.min(1, (now - startTs) / duration);
						track.scrollLeft = start + distance * easeOutQuart(t);
						if (t < 1) rafId = requestAnimationFrame(tick); else rafId = null;
					}
					rafId = requestAnimationFrame(tick);
				}

				if (prev) prev.addEventListener('click', function () { smoothScrollTo(track.scrollLeft - step()); });
				if (next) next.addEventListener('click', function () { smoothScrollTo(track.scrollLeft + step()); });
				track.addEventListener('scroll', updateNav, { passive: true });
				window.addEventListener('resize', updateNav);

				// Click + drag to scroll (mouse / pen)
				var isDown = false, startX = 0, startScroll = 0, moved = false;
				track.addEventListener('pointerdown', function (e) {
					if (e.pointerType === 'touch') return;
					if (e.button !== 0) return;
					isDown = true; moved = false;
					startX = e.clientX;
					startScroll = track.scrollLeft;
					track.classList.add('is-dragging');
					track.setPointerCapture && track.setPointerCapture(e.pointerId);
				});
				track.addEventListener('pointermove', function (e) {
					if (!isDown) return;
					var dx = e.clientX - startX;
					if (Math.abs(dx) > 4) moved = true;
					track.scrollLeft = startScroll - dx;
				});
				function endDrag(e) {
					if (!isDown) return;
					isDown = false;
					track.classList.remove('is-dragging');
					if (e && track.releasePointerCapture && e.pointerId !== undefined) {
						try { track.releasePointerCapture(e.pointerId); } catch (_) {}
					}
				}
				track.addEventListener('pointerup', endDrag);
				track.addEventListener('pointercancel', endDrag);
				track.addEventListener('pointerleave', endDrag);
				// Suppress click after drag so cards don't trigger navigation
				track.addEventListener('click', function (e) {
					if (moved) { e.preventDefault(); e.stopPropagation(); moved = false; }
				}, true);

				updateNav();
			});
		</script>
	@endpush
@endif
