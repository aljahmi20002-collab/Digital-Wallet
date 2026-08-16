@php
	use App\Constants\CurrencyRole;
	use App\Constants\CurrencyType;
	use App\Constants\FixPctType;

	$createRoles = CurrencyRole::getRoles();
@endphp

<div class="modal fade currency-drawer currency-modal-form" id="new_currency_modal" aria-hidden="true" aria-labelledby="new_currency_drawer_title" tabindex="-1">
	<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
		<div class="modal-content">
			<div class="modal-body">
				<form action="{{ route('admin.currency.store') }}" method="post" enctype="multipart/form-data" class="currency-edit-form currency-drawer__form">
					@csrf
					{{-- Marker the index page uses to auto-reopen this modal
					     when server-side validation kicks the user back. --}}
					<input type="hidden" name="_currency_form" value="create">

					@if($errors->any() && old('_currency_form') === 'create')
						<div class="alert alert-danger small mb-3" role="alert">
							<strong>{{ __('Could not create the currency:') }}</strong>
							<ul class="mb-0 ps-3">
								@foreach($errors->all() as $error)
									<li>{{ $error }}</li>
								@endforeach
							</ul>
						</div>
					@endif

					<header class="currency-drawer__header">
						<div class="currency-drawer__header-eyebrow" id="new_currency_drawer_title">{{ __('Add New Currency') }}</div>
						<button type="button" class="currency-drawer__close" data-coreui-dismiss="modal" aria-label="{{ __('Close') }}">
							<span aria-hidden="true">&times;</span>
						</button>
					</header>

					<div class="currency-drawer__body">
						<div class="currency-drawer__context">
							<div class="currency-drawer__context-flag" data-currency-fallback="{{ __('New') }}"></div>
							<div class="currency-drawer__context-meta">
								<span class="currency-drawer__context-label">{{ __('New currency profile') }}</span>
								<h2>{{ __('Add New Currency') }}</h2>
								<div class="currency-drawer__context-tags">
									<span class="currency-drawer__context-tag">{{ __('Setup') }}</span>
									<span class="currency-drawer__context-tag">{{ __('Wallet Ready') }}</span>
									<span class="currency-drawer__context-tag">{{ __('Role Rules') }}</span>
								</div>
							</div>
							<div class="currency-drawer__context-rate">
								<div class="currency-drawer__context-rate-label">{{ __('Base Rate') }}</div>
								<div class="currency-drawer__context-rate-value">1 {{ siteCurrency() }} = <span data-currency-selected>{{ old('code', __('Code')) }}</span></div>
							</div>
						</div>

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
										<x-img name="flag"/>
									</div>
									<div class="currency-identity__fields">
										<div class="currency-field-grid">
											<div class="currency-field">
												<label class="form-label">{{ __('Currency Type') }}</label>
												<select name="type" class="form-select" required id="site_currency_type">
													<option selected disabled>{{ __('Select Currency Type') }}</option>
													@foreach(CurrencyType::getTypes() as $type)
														<option value="{{ $type }}" @selected(old('type') === $type)>{{ ucfirst($type) }}</option>
													@endforeach
												</select>
											</div>
											<div class="currency-field">
												<label class="form-label">{{ __('Currency Name') }}</label>
												<select name="name" class="form-select" id="site_currency" data-currency-search-select>
													<option selected disabled>{{ __('First Select Currency Type') }}</option>
												</select>
											</div>
										</div>
										<div class="currency-field-grid">
											<div class="currency-field">
												<label class="form-label">{{ __('Code') }}</label>
												<input type="text" name="code" value="{{ old('code') }}" id="currency_code"
												       class="form-control" placeholder="{{ __('Code') }}" required>
											</div>
											<div class="currency-field">
												<label class="form-label">{{ __('Symbol') }}</label>
												<input type="text" name="symbol" value="{{ old('symbol') }}" id="currency_symbol"
												       class="form-control" placeholder="{{ __('Symbol') }}" required>
											</div>
										</div>
									</div>
								</div>
							</div>
						</section>

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
										<input type="text" oninput="this.value = validateDouble(this.value)"
										       name="exchange_rate" value="{{ old('exchange_rate') }}" id="conversion_rate" class="form-control">
										<label class="currency-toggle" title="{{ __('Live feed') }}">
											<input type="hidden" name="rate_live" value="0">
											<input id="rate_live" type="checkbox" name="rate_live" value="1" @checked(old('rate_live'))>
											<span class="currency-toggle__track"></span>
											<span class="currency-toggle__label">{{ __('Live') }}</span>
										</label>
										<span class="currency-conv-row__suffix" id="currency-selected">{{ old('code', __('Code')) }}</span>
									</div>
								</div>

								<div class="currency-field-grid currency-field-grid--three mt-3">
									<div class="currency-field">
										<label class="form-label" for="auto_wallet_create">
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
												       id="auto_wallet_create" @checked(old('auto_wallet'))>
												<span class="currency-toggle__track"></span>
											</label>
										</div>
									</div>
									<div class="currency-field">
										<label class="form-label" for="default_currency_create">{{ __('Default Currency') }}</label>
										<div class="currency-row-toggle">
											<span>{{ __('Enable') }}</span>
											<label class="currency-toggle">
												<input type="hidden" name="default" value="0">
												<input type="checkbox" name="default" value="1"
												       id="default_currency_create" @checked(old('default'))>
												<span class="currency-toggle__track"></span>
											</label>
										</div>
									</div>
									<div class="currency-field">
										<label class="form-label" for="status_create">{{ __('Status') }}</label>
										<div class="currency-row-toggle">
											<span>{{ __('Enable') }}</span>
											<label class="currency-toggle">
												<input type="hidden" name="status" value="0">
												<input type="checkbox" name="status" value="1"
												       id="status_create" @checked(old('status'))>
												<span class="currency-toggle__track"></span>
											</label>
										</div>
									</div>
								</div>
							</div>
						</section>

						<section class="currency-dcard">
							<header class="currency-dcard__head">
								<div class="currency-dcard__head-title">
									<span class="currency-dcard__eyebrow">{{ __('Access Matrix') }}</span>
									<h4>{{ __('Role Fee & Limit Rules') }}</h4>
								</div>
								<span class="currency-dcard__tag">{{ __(':count Roles', ['count' => count($createRoles)]) }}</span>
							</header>
							<div class="currency-dcard__body">
								<div class="currency-role-list">
									@foreach($createRoles as $role)
										@php
											$roleKey = strtolower($role);
											$roleActive = (bool) old('roles.' . $role . '.status');
										@endphp
										<input type="hidden" name="roles[{{ $role }}][role_name]" value="{{ $role }}">
										<div class="currency-role-item" data-role-item>
											<button type="button" class="currency-role-head" data-role-toggle>
												<span class="currency-role-head__icon currency-role-head__icon--{{ $roleKey }}">
													<x-icon :name="$role" height="14" width="14"/>
												</span>
												<span class="currency-role-head__name">{{ Str::upper($role) }}</span>
												<span class="currency-role-head__state {{ $roleActive ? 'is-active' : '' }}" data-role-state>
													{{ $roleActive ? __('ACTIVE') : __('INACTIVE') }}
												</span>
												<span class="currency-role-head__chev" aria-hidden="true">
													<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
													     stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
												</span>
											</button>
											<div class="currency-role-body">
												@if($role !== CurrencyRole::WITHDRAW)
													<div class="currency-limit-grid">
														<div class="currency-field">
															<span class="currency-field-label">{{ __('Fee') }}</span>
															<div class="currency-amount-input">
																<input type="text" name="roles[{{ $role }}][fee]"
																       value="{{ old('roles.' . $role . '.fee') }}"
																       placeholder="{{ __('Fee') }}"
																       oninput="this.value = validateDouble(this.value)">
																<select name="roles[{{ $role }}][fee_type]" class="currency-amount-input__suffix-select">
																	@foreach(FixPctType::getTypeWithSymbol() as $key => $value)
																		<option value="{{ $key }}" @selected(old('roles.' . $role . '.fee_type') == $key)>{{ $value }}</option>
																	@endforeach
																</select>
															</div>
														</div>
														<div class="currency-field">
															<span class="currency-field-label">{{ __('Min Amount') }}</span>
															<div class="currency-amount-input">
																<input type="text" name="roles[{{ $role }}][min_limit]"
																       value="{{ old('roles.' . $role . '.min_limit', 10) }}"
																       oninput="this.value = validateDouble(this.value)">
																<span class="currency-amount-input__suffix">{{ siteCurrency() }}</span>
															</div>
														</div>
														<div class="currency-field">
															<span class="currency-field-label">{{ __('Max Amount') }}</span>
															<div class="currency-amount-input">
																<input type="text" name="roles[{{ $role }}][max_limit]"
																       value="{{ old('roles.' . $role . '.max_limit', 1000) }}"
																       oninput="this.value = validateDouble(this.value)">
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
														<input type="hidden" name="roles[{{ $role }}][status]" value="0">
														<input type="checkbox" name="roles[{{ $role }}][status]" value="1"
														       id="role_status_create_{{ $role }}" data-role-status @checked($roleActive)>
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

					<footer class="currency-drawer__footer">
						<div class="currency-drawer__footer-meta">
							<span class="currency-drawer__footer-dot"></span>
							<span>{{ __('Ready to create currency profile') }}</span>
						</div>
						<div class="currency-drawer__footer-actions">
							<button type="button" class="btn btn-ghost-currency" data-coreui-dismiss="modal">{{ __('Cancel') }}</button>
							<button type="submit" class="btn btn-primary-currency">
								<x-icon name="check" height="14" width="14"/>
								{{ __('Create Now') }}
							</button>
						</div>
					</footer>
				</form>
			</div>
		</div>
	</div>
</div>
