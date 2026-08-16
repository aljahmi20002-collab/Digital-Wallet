<div class="modal fade currency-default-change-modal" id="currency_default_change_modal" tabindex="-1" aria-labelledby="currency_default_change_title" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered modal-lg">
		<div class="modal-content">
			<div class="currency-default-change">
				<header class="currency-default-change__header">
					<span class="currency-default-change__icon" aria-hidden="true">
						<i class="fa-solid fa-triangle-exclamation"></i>
					</span>
					<div>
						<span class="currency-default-change__eyebrow">{{ __('High impact setting') }}</span>
						<h3 id="currency_default_change_title">{{ __('Review default currency change') }}</h3>
						<p>
							{{ __('You are changing the site base currency from') }}
							<strong data-default-change-current>{{ siteCurrency() }}</strong>
							{{ __('to') }}
							<strong data-default-change-next>{{ __('selected currency') }}</strong>.
						</p>
					</div>
				</header>

				<div class="currency-default-change__body">
					<div class="currency-default-change__summary">
						<div>
							<span>{{ __('Current default') }}</span>
							<strong>{{ siteCurrency() }}</strong>
						</div>
						<i class="fa-solid fa-arrow-right-long" aria-hidden="true"></i>
						<div>
							<span>{{ __('New default') }}</span>
							<strong data-default-change-next-name>{{ __('Selected currency') }}</strong>
						</div>
					</div>

					<div class="currency-default-change__grid">
						<section>
							<h4>{{ __('Calculation impact') }}</h4>
							<ul>
								<li>{{ __('Dashboard totals, admin reports, chart values, and summary cards can change because values will be converted into the new base currency.') }}</li>
								<li>{{ __('Fees, min/max limits, rank thresholds, rewards, and subscription prices that are shown in the site currency should be reviewed.') }}</li>
								<li>{{ __('Payment links, merchant checkout, deposit, withdraw, send, request, exchange, voucher, P2P, agent, and virtual card screens can show different base amounts.') }}</li>
							</ul>
						</section>
						<section>
							<h4>{{ __('Operational checks') }}</h4>
							<ul>
								<li>{{ __('Existing wallets and transactions are not rewritten. Historical records stay in their original currencies, but reports may convert them differently.') }}</li>
								<li>{{ __('Every exchange rate must be rechecked because stored rates are based on the current default currency.') }}</li>
								<li>{{ __('Gateway currencies, deposit methods, withdraw methods, auto-wallet rules, merchant supported currencies, and agent currencies should be audited after switching.') }}</li>
							</ul>
						</section>
					</div>

					<div class="currency-default-change__checklist">
						<span>{{ __('Before confirming') }}</span>
						<ul>
							<li>{{ __('Back up current settings and change this during a low-traffic maintenance window.') }}</li>
							<li>{{ __('Update all manual exchange rates using the new base currency.') }}</li>
							<li>{{ __('Tell finance/support teams that reports and dashboard totals may look different after conversion.') }}</li>
						</ul>
					</div>

					<label class="currency-default-change__ack">
						<input type="checkbox" data-default-change-confirm-check>
						<span>{{ __('I understand these impacts and want to change the default currency.') }}</span>
					</label>
				</div>

				<footer class="currency-default-change__footer">
					<button type="button" class="btn btn-ghost-currency" data-coreui-dismiss="modal">{{ __('Cancel') }}</button>
					<button type="button" class="btn btn-danger-currency" data-default-change-confirm disabled>
						<i class="fa-solid fa-shield-halved" aria-hidden="true"></i>
						{{ __('Confirm Default Change') }}
					</button>
				</footer>
			</div>
		</div>
	</div>
</div>
