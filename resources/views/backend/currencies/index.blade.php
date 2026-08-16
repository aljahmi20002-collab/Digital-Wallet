@php
	use App\Constants\CurrencyRole;

	$totalCurrencies = $currencies->count();
	$activeCurrencies = $currencies->where('status', true)->count();
	$inactiveCurrencies = max(0, $totalCurrencies - $activeCurrencies);
	$liveRateCurrencies = $currencies->where('rate_live', true)->count();
	$fiatCurrencies = $currencies->where('type', 'fiat')->count();
	$cryptoCurrencies = $currencies->where('type', 'crypto')->count();
	$defaultCurrency = $currencies->firstWhere('default', true);
	$activePercent = $totalCurrencies > 0 ? round(($activeCurrencies / $totalCurrencies) * 100) : 0;
@endphp

@extends('backend.layouts.app')
@section('title', __('Currencies'))

@push('styles')
	<link rel="stylesheet" href="{{ asset('backend/css/currency-management.css?v=' . filemtime(public_path('backend/css/currency-management.css'))) }}">
@endpush

@section('content')
	<div class="currency-admin py-4">
		<section class="currency-admin-hero mb-4">
			<div class="currency-admin-hero__main">
				<span class="currency-admin-eyebrow">{{ __('Treasury Console') }}</span>
				<h3>{{ __('Currency Management') }}</h3>
				<p>{{ __('Operate fiat, crypto, live exchange feeds, wallet defaults, and transaction role policies from a bank-grade control surface.') }}</p>

				<div class="currency-admin-health">
					<div class="currency-admin-health__bar" data-currency-progress-pct="{{ $activePercent }}" aria-label="{{ __('Active currency coverage') }}" role="progressbar" aria-valuenow="{{ $activePercent }}" aria-valuemin="0" aria-valuemax="100">
						<span></span>
					</div>
					<div class="currency-admin-health__meta">
						<strong>{{ $activePercent }}%</strong>
						<span>{{ __('active coverage') }}</span>
						<span>·</span>
						<span>{{ __(':count inactive', ['count' => $inactiveCurrencies]) }}</span>
					</div>
				</div>
			</div>

			<div class="currency-admin-hero__side">
				<div class="currency-admin-base-card">
					<span>{{ __('Base Currency') }}</span>
					<strong>{{ siteCurrency() }}</strong>
					<small>{{ $defaultCurrency?->name ?? __('No default currency selected') }}</small>
				</div>
				<div class="currency-admin-hero__actions">
					<a href="{{ route('admin.settings.plugin_type', 'exchange_rate') }}" class="currency-admin-hero__btn currency-admin-hero__btn--secondary">
						<x-icon name="currency" height="14" width="14"/>
						{{ __('Rate API') }}
					</a>
					<a href="#new_currency_modal" data-coreui-toggle="modal" class="currency-admin-hero__btn currency-admin-hero__btn--primary">
						<x-icon name="add" height="14" width="14"/>
						{{ __('Add Currency') }}
					</a>
				</div>
			</div>
		</section>

		<div class="currency-admin-kpis mb-4">
			<div class="currency-admin-kpi">
				<span class="currency-admin-kpi__icon currency-admin-kpi__icon--primary">
					<i class="fa-solid fa-layer-group"></i>
				</span>
				<div>
					<span>{{ __('Total Currencies') }}</span>
					<strong>{{ number_format($totalCurrencies) }}</strong>
				</div>
			</div>
			<div class="currency-admin-kpi">
				<span class="currency-admin-kpi__icon currency-admin-kpi__icon--success">
					<i class="fa-solid fa-circle-check"></i>
				</span>
				<div>
					<span>{{ __('Active') }}</span>
					<strong>{{ number_format($activeCurrencies) }}</strong>
					<span class="currency-admin-kpi__trend">{{ $activePercent }}% {{ __('of network') }}</span>
				</div>
			</div>
			<div class="currency-admin-kpi">
				<span class="currency-admin-kpi__icon currency-admin-kpi__icon--danger">
					<i class="fa-solid fa-signal"></i>
				</span>
				<div>
					<span>{{ __('Live Feeds') }}</span>
					<strong>{{ number_format($liveRateCurrencies) }}</strong>
				</div>
			</div>
			<div class="currency-admin-kpi">
				<span class="currency-admin-kpi__icon currency-admin-kpi__icon--warning">
					<i class="fa-solid fa-coins"></i>
				</span>
				<div>
					<span>{{ __('Fiat / Crypto') }}</span>
					<strong>{{ $fiatCurrencies }} <span style="color: var(--color-text-faint); font-weight: 500;">/</span> {{ $cryptoCurrencies }}</strong>
				</div>
			</div>
		</div>

		@if (session('exchange_rate_error'))
			<div class="currency-admin-alert mb-4">
				<div class="currency-admin-alert__icon">
					<i class="fa-solid fa-triangle-exclamation"></i>
				</div>
				<div>
					<strong>{{ __('Exchange rate sync issue') }}</strong>
					<p class="mb-0">{{ session('exchange_rate_error') }}</p>
				</div>
			</div>
		@endif

		<section class="currency-admin-card">
			<div class="currency-admin-card__header">
				<div>
					<span class="currency-admin-eyebrow">{{ __('Currency Ledger') }}</span>
					<h5>{{ __('Supported Currency Network') }}</h5>
					<p>{{ __('Review conversion source, role access, and operational state for every wallet currency.') }}</p>
				</div>
				<div class="currency-admin-card__meta">
					<span class="currency-admin-chip">{{ __('Base:') }} <b>{{ siteCurrency() }}</b></span>
					<span class="currency-admin-chip">{{ __('Records:') }} <b>{{ $totalCurrencies }}</b></span>
				</div>
			</div>

			<div class="currency-admin-toolbar">
				<div class="currency-admin-segment" role="group" aria-label="{{ __('Currency filters') }}">
					<button type="button" class="currency-admin-segment__item active" data-currency-filter="all" aria-pressed="true">
						{{ __('All') }}
						<span class="currency-admin-segment__count">{{ $totalCurrencies }}</span>
					</button>
					<button type="button" class="currency-admin-segment__item" data-currency-filter="active" aria-pressed="false">
						{{ __('Active') }}
						<span class="currency-admin-segment__count">{{ $activeCurrencies }}</span>
					</button>
					<button type="button" class="currency-admin-segment__item" data-currency-filter="live" aria-pressed="false">
						{{ __('Live') }}
						<span class="currency-admin-segment__count">{{ $liveRateCurrencies }}</span>
					</button>
					<button type="button" class="currency-admin-segment__item" data-currency-filter="fiat" aria-pressed="false">
						{{ __('Fiat') }}
						<span class="currency-admin-segment__count">{{ $fiatCurrencies }}</span>
					</button>
					<button type="button" class="currency-admin-segment__item" data-currency-filter="crypto" aria-pressed="false">
						{{ __('Crypto') }}
						<span class="currency-admin-segment__count">{{ $cryptoCurrencies }}</span>
					</button>
				</div>
				<div class="currency-admin-search">
					<i class="fa-solid fa-magnifying-glass"></i>
					<input type="search" id="currency-admin-search" placeholder="{{ __('Search currency, code, type...') }}" aria-label="{{ __('Search currencies') }}">
				</div>
			</div>

			<div class="currency-admin-table">
				<table class="table align-middle mb-0">
					<thead>
					<tr>
						<th>{{ __('Currency') }}</th>
						<th>{{ __('Exchange') }}</th>
						<th>{{ __('Role Access') }}</th>
						<th>{{ __('Operational State') }}</th>
						<th class="text-end">{{ __('Actions') }}</th>
					</tr>
					</thead>
					<tbody id="currency-admin-table-body">
					@forelse($currencies as $currency)
						<tr data-currency-row
						    @class(['is-default' => $currency->default])
						    data-search="{{ strtolower($currency->name.' '.$currency->code.' '.$currency->symbol.' '.$currency->type) }}"
						    data-type="{{ strtolower($currency->type) }}"
						    data-status="{{ $currency->status ? 'active' : 'inactive' }}"
						    data-live="{{ $currency->rate_live ? 'live' : 'manual' }}">
							<td>
								<div class="currency-admin-currency">
									<div class="currency-admin-currency__flag" data-currency-fallback="{{ mb_strtoupper(mb_substr($currency->name ?: $currency->code, 0, 2)) }}">
										@if($currency->flag)
											<img src="{{ asset($currency->flag) }}" alt="{{ $currency->name }}" onerror="this.remove()" loading="lazy">
										@endif
										@if($currency->default)
											<span class="currency-admin-currency__default" aria-label="{{ __('Default currency') }}"></span>
										@endif
									</div>
									<div>
										<span class="currency-admin-currency__name">{{ $currency->name }}</span>
										<div class="currency-admin-muted">{{ $currency->code }} · {{ $currency->symbol }}</div>
									</div>
								</div>
							</td>
							<td>
								<div class="currency-admin-rate">
									<div class="currency-admin-rate__meta">
										<span class="currency-admin-chip currency-admin-chip--type currency-admin-chip--type-{{ strtolower($currency->type) }}">{{ $currency->type }}</span>
										@if($currency->rate_live)
											<span class="currency-admin-chip currency-admin-chip--feed currency-admin-chip--feed-live">{{ __('Live') }}</span>
										@else
											<span class="currency-admin-chip currency-admin-chip--feed">{{ __('Manual') }}</span>
										@endif
									</div>
									<div class="currency-admin-rate__value">
										<span class="from">1 {{ siteCurrency() }}</span>
										<span class="arrow">→</span>
										<span>
											<span class="js-rate" data-code="{{ $currency->code }}" data-live="{{ (int) $currency->rate_live }}">
												{{ number_format($currency->getRawOriginal('exchange_rate'), (int) setting('site_decimal', 2), '.', '') }}
											</span>
											{{ $currency->code }}
										</span>
										<span class="js-rate-spinner ms-1 {{ $currency->rate_live ? '' : 'd-none' }}">
											<span class="spinner-border spinner-border-sm text-secondary" role="status" aria-hidden="true"></span>
										</span>
									</div>
								</div>
							</td>
							<td>
								<div class="currency-admin-roles">
									@forelse($currency->activeRoles as $role)
										<span class="currency-admin-role currency-admin-role--{{ strtolower($role->role_name) }}">{{ strtoupper($role->role_name) }}</span>
									@empty
										<span class="currency-admin-role currency-admin-role--empty">{{ __('No active role') }}</span>
									@endforelse
								</div>
							</td>
							<td>
								<div class="currency-admin-state">
									<span class="currency-admin-status {{ $currency->status ? 'currency-admin-status--active' : 'currency-admin-status--inactive' }}">
										<span></span>
										{{ $currency->status ? __('Activated') : __('Not Activated') }}
									</span>
									<small>{{ $currency->auto_wallet ? __('Auto wallet enabled') : __('Manual wallet creation') }}</small>
								</div>
							</td>
							<td class="currency-admin-action-cell">
								<div class="currency-admin-actions" role="group" aria-label="{{ __('Currency actions') }}">
									<button type="button" class="currency-admin-action edit-modal"
									        data-edit-url="{{ route('admin.currency.edit', $currency->id) }}"
									        title="{{ __('Manage') }}"
									        aria-label="{{ __('Manage :currency', ['currency' => $currency->name]) }}">
										<span class="currency-admin-action__icon-wrap" aria-hidden="true">
											<x-icon name="manage" height="13" width="13"/>
										</span>
										<span class="currency-admin-action__label">{{ __('Manage') }}</span>
									</button>
									@if($currency->default != 1)
										<button type="button" class="currency-admin-action currency-admin-action--delete delete"
										        data-bs-toggle="modal"
										        data-url="{{ route('admin.currency.destroy', $currency->id) }}"
										        title="{{ __('Delete') }}"
										        aria-label="{{ __('Delete :currency', ['currency' => $currency->name]) }}">
											<span class="currency-admin-action__icon-wrap" aria-hidden="true">
												<x-icon name="delete-2" height="13" width="13"/>
											</span>
											<span class="currency-admin-action__label">{{ __('Delete') }}</span>
										</button>
									@endif
								</div>
							</td>
						</tr>
					@empty
						<tr>
							<td colspan="5">
								<x-admin-not-found
									:title="__('No currencies found')"
									:message="__('Add your first currency to start managing wallets and exchange rates.')"
									icon="fa-coins"
								/>
							</td>
						</tr>
					@endforelse
					</tbody>
				</table>
			</div>

			<div class="currency-admin-no-results d-none" id="currency-admin-no-results">
				<i class="fa-solid fa-filter-circle-xmark"></i>
				<strong>{{ __('No matching currencies') }}</strong>
				<span>{{ __('Try a different search or filter.') }}</span>
			</div>

			<div class="currency-admin-card__footer">
				<span>{{ __('Showing :total currencies', ['total' => $totalCurrencies]) }}</span>
				<span>{{ __('Base currency') }}: <b>{{ siteCurrency() }}</b></span>
			</div>
		</section>
	</div>

	@include('backend.currencies.partials._create_modal')
	@include('backend.currencies.partials._edit_modal')
	@include('backend.currencies.partials._default_change_modal')
@endsection

@push('scripts')
	<script type="application/json" id="currency-admin-config">
		{!! json_encode([
			'baseCurrency' => siteCurrency(),
			'currencies' => getJsonData('currencies'),
			'ratesEndpoint' => route('admin.currency.rates'),
			'selectCurrencyLabel' => __('Select Currency'),
			'searchCurrencyLabel' => __('Search currency...'),
			'noCurrencyResultsLabel' => __('No currencies found'),
			'activeLabel' => __('ACTIVE'),
			'inactiveLabel' => __('INACTIVE'),
		], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_THROW_ON_ERROR) !!}
	</script>
	<script src="{{ asset('backend/js/currency-management.js?v=' . config('app.version')) }}"></script>

	{{--
		When the create form fails server-side validation, Laravel
		redirects back with $errors + flashed input. The modal would
		normally be closed (page reloaded), so the admin saw an empty
		index and assumed nothing happened. Detect the flashed input
		that only the create form sends (`_currency_form = create`)
		and auto-reopen the modal so the field-level errors are
		visible in context.
	--}}
	@if($errors->any() && old('_currency_form') === 'create')
		<script>
			document.addEventListener('DOMContentLoaded', function () {
				var el = document.getElementById('new_currency_modal');
				if (! el) {
					return;
				}
				var ModalClass = (window.coreui && window.coreui.Modal) || (window.bootstrap && window.bootstrap.Modal);
				if (ModalClass) {
					ModalClass.getOrCreateInstance(el).show();
				}
			});
		</script>
	@endif
@endpush
