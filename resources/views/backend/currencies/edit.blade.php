@php
	use App\Constants\CurrencyRole;
	use App\Constants\CurrencyType;
	use App\Constants\FixPctType;

	$flagFallback = mb_strtoupper(mb_substr($currency->name ?: $currency->code, 0, 2));
@endphp

<form action="{{ route('admin.currency.update', ['currency' => $currency->id]) }}"
      method="post"
      enctype="multipart/form-data"
      class="currency-edit-form currency-drawer__form"
      data-default-currency-form
      data-current-default="{{ $currency->default ? '1' : '0' }}"
      data-currency-code="{{ $currency->code }}"
      data-currency-name="{{ $currency->name }}">
	@method('PUT')
	@csrf
	<input type="hidden" name="default_change_acknowledged" value="0" data-default-change-acknowledged>
	
	{{-- Drawer Header — minimal: rich identity lives in the context strip below --}}
	<header class="currency-drawer__header">
		<div class="currency-drawer__header-eyebrow" id="manage_currency_drawer_title">{{ __('Update Currency') }}</div>
		<button type="button" class="currency-drawer__close" data-coreui-dismiss="modal" aria-label="{{ __('Close') }}">
			<span aria-hidden="true">&times;</span>
		</button>
	</header>
	
	{{-- Drawer Body --}}
	<div class="currency-drawer__body">
		
		{{-- Context Strip --}}
		<div class="currency-drawer__context">
			<div class="currency-drawer__context-flag" data-currency-fallback="{{ $flagFallback }}">
				@if($currency->flag)
					<img src="{{ asset($currency->flag) }}" alt="{{ $currency->name }}" onerror="this.remove()" loading="lazy">
				@endif
			</div>
			<div class="currency-drawer__context-meta">
				<span class="currency-drawer__context-label">{{ $currency->code }} · {{ $currency->symbol }}</span>
				<h2>{{ $currency->name }}</h2>
				<div class="currency-drawer__context-tags">
					<span class="currency-drawer__context-tag">{{ strtoupper($currency->type) }}</span>
					@if($currency->default)
						<span class="currency-drawer__context-tag is-default">★ {{ __('Default') }}</span>
					@endif
					<span class="currency-drawer__context-tag">{{ $currency->auto_wallet ? __('Auto Wallet') : __('Manual Wallet') }}</span>
				</div>
			</div>
			<div class="currency-drawer__context-rate">
				<div class="currency-drawer__context-rate-label">{{ __('Current Rate') }}</div>
				<div class="currency-drawer__context-rate-value">1 {{ siteCurrency() }} = {{ old('exchange_rate', $currency->exchange_rate) }} {{ $currency->code }}</div>
			</div>
		</div>
		
		{{-- Identity dcard --}}
		<section class="currency-dcard">
			<header class="currency-dcard__head">
				<div class="currency-dcard__head-title">
					<span class="currency-dcard__eyebrow">{{ __('Identity') }}</span>
					<h4>{{ __('Currency Details') }}</h4>
				</div>
				<span class="currency-dcard__tag">{{ __('Profile') }}</span>
			</header>
			<div class="currency-dcard__body">
				<div class="currency-identity">
					<div class="currency-identity__uploader">
						<label class="form-label">{{ __('Flag') }}</label>
						<x-img name="flag" old="{{ $currency->flag }}" :ref="'coevs-currency-flag'"/>
					</div>
					<div class="currency-identity__fields">
						<div class="currency-field-grid">
							<div class="currency-field">
								<label class="form-label">{{ __('Currency Type') }}</label>
								<select name="type" class="form-select" required id="site_currency_type">
									<option selected disabled>{{ __('Select Currency Type') }}</option>
									@foreach(CurrencyType::getTypes() as $type)
										<option value="{{ $type }}" @selected($currency->type === $type)>{{ ucfirst($type) }}</option>
									@endforeach
								</select>
							</div>
							<div class="currency-field">
								<label class="form-label">{{ __('Currency Name') }}</label>
								<select name="name" class="form-select" id="site_currency" data-currency-search-select>
									@foreach(getJsonData('currencies')[$currency['type']] as $value)
										<option value="{{ $value['name'] }}" @selected($currency->name == $value['name'])>{{ $value['name'] }} ({{ $value['code'] }})</option>
									@endforeach
								</select>
							</div>
						</div>
						<div class="currency-field-grid">
							<div class="currency-field">
								<label class="form-label">{{ __('Code') }}</label>
								<input type="text" name="code" value="{{ old('code', $currency->code) }}" id="currency_code"
								       class="form-control" placeholder="{{ __('Code') }}" required>
							</div>
							<div class="currency-field">
								<label class="form-label">{{ __('Symbol') }}</label>
								<input type="text" name="symbol" value="{{ old('symbol', $currency->symbol) }}" id="currency_symbol"
								       class="form-control" placeholder="{{ __('Symbol') }}" required>
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>
		
		{{-- Rates & Controls dcard --}}
		<section class="currency-dcard">
			<header class="currency-dcard__head">
				<div class="currency-dcard__head-title">
					<span class="currency-dcard__eyebrow">{{ __('Rates & Controls') }}</span>
					<h4>{{ __('Exchange Configuration') }}</h4>
				</div>
				<a class="currency-dcard__head-link" href="{{ route('admin.settings.plugin_type', 'exchange_rate') }}">
					<x-icon name="manage" height="12" width="12"/>
					{{ __('Manage Exchange') }}
				</a>
			</header>
			<div class="currency-dcard__body">
				<div class="currency-field">
					<label class="form-label">{{ __('Conversion Rate') }}</label>
					<div class="currency-conv-row">
						<span class="currency-conv-row__prefix">1 {{ siteCurrency() }} =</span>
						<input type="text" oninput="this.value = validateDouble(this.value)" name="exchange_rate"
						       value="{{ old('exchange_rate', $currency->exchange_rate) }}" id="conversion_rate"
						       class="form-control">
						<label class="currency-toggle" title="{{ __('Live feed') }}">
							<input type="hidden" name="rate_live" value="0">
							<input id="rate_live" type="checkbox" name="rate_live" value="1" @checked($currency->rate_live)>
							<span class="currency-toggle__track"></span>
							<span class="currency-toggle__label">{{ __('Live') }}</span>
						</label>
						<span class="currency-conv-row__suffix" id="currency-selected">{{ old('name', $currency->code) }}</span>
					</div>
				</div>
				
				<div class="currency-field-grid currency-field-grid--three mt-3">
					@if(!$currency->default)
						<div class="currency-field">
							<label class="form-label" for="auto_wallet_{{ $currency->id }}">
								{{ __('Auto Wallet') }}
								<span class="modal-tooltip" data-coreui-toggle="tooltip" data-coreui-placement="top"
								      title="{{ __('If enabled, when a user registers, a wallet will be created automatically for this currency.') }}">
									<x-icon name="info" height="14"/>
								</span>
							</label>
							<div class="currency-row-toggle">
								<span>{{ __('Enable') }}</span>
								<label class="currency-toggle">
									<input type="hidden" name="auto_wallet" value="0">
									<input type="checkbox" name="auto_wallet" value="1"
									       id="auto_wallet_{{ $currency->id }}" @checked($currency->auto_wallet)>
									<span class="currency-toggle__track"></span>
								</label>
							</div>
						</div>
					@endif
					<div class="currency-field">
						<label class="form-label" for="default_currency_{{ $currency->id }}">{{ __('Default Currency') }}</label>
						<div class="currency-row-toggle">
							<span>{{ __('Enable') }}</span>
							<label class="currency-toggle">
								<input type="hidden" name="default" value="0">
								<input type="checkbox" name="default" value="1"
								       id="default_currency_{{ $currency->id }}"
								       data-default-currency-checkbox
								       @checked($currency->default)>
								<span class="currency-toggle__track"></span>
							</label>
						</div>
					</div>
					<div class="currency-field">
						<label class="form-label" for="currency_status_{{ $currency->id }}">{{ __('Status') }}</label>
						<div class="currency-row-toggle">
							<span>{{ __('Enable') }}</span>
							<label class="currency-toggle">
								<input type="hidden" name="status" value="0">
								<input type="checkbox" name="status" value="1"
								       id="currency_status_{{ $currency->id }}" @checked($currency->status)>
								<span class="currency-toggle__track"></span>
							</label>
						</div>
					</div>
				</div>
				@if(! $currency->default)
					<div class="currency-default-review mt-3">
						<span class="currency-default-review__icon" aria-hidden="true">
							<i class="fa-solid fa-triangle-exclamation"></i>
						</span>
						<div>
							<strong>{{ __('Default currency change requires review') }}</strong>
							<p>{{ __('If this currency becomes the new base, admin dashboards, user dashboards, fees, limits, wallet creation, reports, and checkout summaries may display and calculate from the new base currency.') }}</p>
						</div>
					</div>
				@endif
			</div>
		</section>
		
		{{-- Access Matrix dcard --}}
		<section class="currency-dcard">
			<header class="currency-dcard__head">
				<div class="currency-dcard__head-title">
					<span class="currency-dcard__eyebrow">{{ __('Access Matrix') }}</span>
					<h4>{{ __('Role Fee & Limit Rules') }}</h4>
				</div>
				<span class="currency-dcard__tag">{{ __(':count Roles', ['count' => $currency->roles->count()]) }}</span>
			</header>
			<div class="currency-dcard__body">
				<div class="currency-role-list">
					@foreach($currency->roles as $role)
						@php $roleKey = strtolower($role->role_name); @endphp
						<div class="currency-role-item" data-role-item>
							<button type="button" class="currency-role-head" data-role-toggle>
								<span class="currency-role-head__icon currency-role-head__icon--{{ $roleKey }}">
									<x-icon :name="$role->role_name" height="14" width="14"/>
								</span>
								<span class="currency-role-head__name">{{ Str::upper($role->role_name) }}</span>
								<span class="currency-role-head__state {{ $role->is_active ? 'is-active' : '' }}" data-role-state>{{ $role->is_active ? __('ACTIVE') : __('INACTIVE') }}</span>
								<span class="currency-role-head__chev" aria-hidden="true">
									<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
									     stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
								</span>
							</button>
							<div class="currency-role-body">
								@if($role->role_name !== CurrencyRole::WITHDRAW)
									<div class="currency-limit-grid">
										<div class="currency-field">
											<span class="currency-field-label">{{ __('Fee') }}</span>
											<div class="currency-amount-input">
												<input type="text" name="roles[{{ $role->id }}][fee]" value="{{ $role->fee }}" placeholder="{{ __('Fee') }}"
												       oninput="this.value = validateDouble(this.value)">
												<select name="roles[{{ $role->id }}][fee_type]" class="currency-amount-input__suffix-select">
													@foreach(FixPctType::getTypeWithSymbol() as $key => $value)
														<option value="{{ $key }}" @selected($key == $role->fee_type)>{{ $value }}</option>
													@endforeach
												</select>
											</div>
										</div>
										<div class="currency-field">
											<span class="currency-field-label">{{ __('Min Amount') }}</span>
											<div class="currency-amount-input">
												<input type="text" name="roles[{{ $role->id }}][min_limit]" value="{{ $role->min_limit }}" oninput="this.value = validateDouble(this.value)">
												<span class="currency-amount-input__suffix">{{ siteCurrency() }}</span>
											</div>
										</div>
										<div class="currency-field">
											<span class="currency-field-label">{{ __('Max Amount') }}</span>
											<div class="currency-amount-input">
												<input type="text" name="roles[{{ $role->id }}][max_limit]" value="{{ $role->max_limit }}" oninput="this.value = validateDouble(this.value)">
												<span class="currency-amount-input__suffix">{{ siteCurrency() }}</span>
											</div>
										</div>
									</div>
								@else
									<div class="currency-role-note">
										{{ __('Withdrawal fee & limit only applicable for withdraw payment method') }}
									</div>
								@endif
								
								<div class="currency-row-toggle currency-row-toggle--full">
									<span>{{ __('Role Status') }}</span>
									<label class="currency-toggle">
										<input type="hidden" name="roles[{{ $role->id }}][status]" value="0">
										<input type="checkbox" name="roles[{{ $role->id }}][status]" value="1"
										       id="role_status_{{ $role->id }}" data-role-status @checked($role->is_active)>
										<span class="currency-toggle__track"></span>
									</label>
								</div>
							</div>
						</div>
					@endforeach
				</div>
			</div>
		</section>
	</div>
	
	{{-- Drawer Footer --}}
	<footer class="currency-drawer__footer">
		<div class="currency-drawer__footer-meta">
			<span class="currency-drawer__footer-dot"></span>
			<span>{{ __('Last updated') }} {{ $currency->updated_at?->diffForHumans() }}</span>
		</div>
		<div class="currency-drawer__footer-actions">
			<button type="button" class="btn btn-ghost-currency" data-coreui-dismiss="modal">{{ __('Cancel') }}</button>
			<button type="submit" class="btn btn-primary-currency">
				<x-icon name="check" height="14" width="14"/>
				{{ __('Save Changes') }}
			</button>
		</div>
	</footer>
</form>
