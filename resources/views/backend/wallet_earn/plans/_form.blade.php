@php
	$selectedCurrency = old('currency_id', $plan->currency_id);
@endphp

<div class="we-form">
	<div class="we-card">
		<div class="we-card__head">
			<div>
				<h2 class="we-card__title">@lang('Plan Details')</h2>
				<p class="we-card__subtitle">@lang('Configure currency support, reward logic, limits, and approval flow.')</p>
			</div>
		</div>
		<div class="we-card__body">
			<div class="row g-3">
				<div class="col-12">
					<label class="form-label">@lang('Plan Icon')</label>
					<x-img name="icon" :old="$plan->icon ? asset($plan->icon) : ''" />
					@error('icon')
					<div class="form-text text-danger">{{ $message }}</div> @enderror
					<div class="form-text">@lang('Optional. PNG, JPG, GIF, WebP, SVG - max 2 MB. Displays on the plan card for users.')</div>
				</div>

				<div class="col-lg-6">
					<label class="form-label">@lang('Plan Name')</label>
					<input type="text" name="name" class="form-control" value="{{ old('name', $plan->name) }}" required>
					@error('name')
					<div class="form-text text-danger">{{ $message }}</div> @enderror
				</div>

				<div class="col-lg-6">
					<label class="form-label">@lang('Supported Currency')</label>
					<select name="currency_id" class="form-select">
						<option value="">@lang('All active wallet currencies')</option>
						@foreach($currencies as $currency)
							<option value="{{ $currency->id }}" @selected((string) $selectedCurrency === (string) $currency->id)>
								{{ $currency->code }} - {{ $currency->name }}
							</option>
						@endforeach
					</select>
					@error('currency_id')
					<div class="form-text text-danger">{{ $message }}</div> @enderror
				</div>
				
				<div class="col-12">
					<label class="form-label">@lang('Description')</label>
					<textarea name="description" class="form-control" rows="3">{{ old('description', $plan->description) }}</textarea>
					@error('description')
					<div class="form-text text-danger">{{ $message }}</div> @enderror
				</div>
				
				<div class="col-md-6 col-xl-3">
					<label class="form-label">@lang('Minimum Amount')</label>
					<input type="number" step="0.00000001" min="0" name="minimum_amount" class="form-control" value="{{ old('minimum_amount', $plan->minimum_amount) }}" required>
					@error('minimum_amount')
					<div class="form-text text-danger">{{ $message }}</div> @enderror
				</div>
				
				<div class="col-md-6 col-xl-3">
					<label class="form-label">@lang('Maximum Amount')</label>
					<input type="number" step="0.00000001" min="0" name="maximum_amount" class="form-control" value="{{ old('maximum_amount', $plan->maximum_amount) }}">
					@error('maximum_amount')
					<div class="form-text text-danger">{{ $message }}</div> @enderror
				</div>
				
				<div class="col-md-6 col-xl-3">
					<label class="form-label">@lang('Profit Value')</label>
					<input type="number" step="0.00000001" min="0" name="profit_rate" class="form-control" value="{{ old('profit_rate', $plan->profit_rate) }}" required>
					@error('profit_rate')
					<div class="form-text text-danger">{{ $message }}</div> @enderror
				</div>
				
				<div class="col-md-6 col-xl-3">
					<label class="form-label">@lang('Profit Type')</label>
					<select name="profit_type" class="form-select" required>
						@foreach($profitTypes as $value => $label)
							<option value="{{ $value }}" @selected(old('profit_type', $plan->profit_type?->value ?? 'percentage') === $value)>
								{{ $label }}
							</option>
						@endforeach
					</select>
					@error('profit_type')
					<div class="form-text text-danger">{{ $message }}</div> @enderror
				</div>
				
				<div class="col-md-6 col-xl-3">
					<label class="form-label">@lang('Duration')</label>
					<input type="number" min="1" name="duration_value" class="form-control" value="{{ old('duration_value', $plan->duration_value ?: 7) }}" required>
					@error('duration_value')
					<div class="form-text text-danger">{{ $message }}</div> @enderror
				</div>
				
				<div class="col-md-6 col-xl-3">
					<label class="form-label">@lang('Duration Unit')</label>
					<select name="duration_unit" class="form-select" required>
						@foreach($durationUnits as $value => $label)
							<option value="{{ $value }}" @selected(old('duration_unit', $plan->duration_unit ?: 'days') === $value)>
								{{ $label }}
							</option>
						@endforeach
					</select>
					@error('duration_unit')
					<div class="form-text text-danger">{{ $message }}</div> @enderror
				</div>
				
				<div class="col-md-6 col-xl-3">
					<label class="form-label">@lang('Payout Frequency')</label>
					<select name="payout_frequency" class="form-select" required>
						@foreach($payoutFrequencies as $value => $label)
							<option value="{{ $value }}" @selected(old('payout_frequency', $plan->payout_frequency?->value ?? 'end_of_term') === $value)>
								{{ $label }}
							</option>
						@endforeach
					</select>
					@error('payout_frequency')
					<div class="form-text text-danger">{{ $message }}</div> @enderror
				</div>
				
				<div class="col-md-6 col-xl-3">
					<label class="form-label">@lang('Plan Badge')</label>
					<input
						type="text"
						name="plan_badge"
						class="form-control"
						value="{{ old('plan_badge', $plan->plan_badge) }}"
						placeholder="{{ __('MOST POPULAR') }}"
					>
					@error('plan_badge')
					<div class="form-text text-danger">{{ $message }}</div> @enderror
				</div>
				
				<div class="col-md-4">
					<input type="hidden" name="is_featured" value="0">
					<div class="we-switch we-switch--toggle">
						<span class="we-switch__label">@lang('Featured plan')</span>
						<div class="form-check form-switch we-admin-switch">
							<input
								id="walletEarnPlanIsFeatured"
								class="form-check-input"
								type="checkbox"
								role="switch"
								name="is_featured"
								value="1"
								@checked(old('is_featured', $plan->exists ? $plan->is_featured : false))
							>
						</div>
					</div>
				</div>
				
				<div class="col-md-4">
					<input type="hidden" name="return_principal" value="0">
					<div class="we-switch we-switch--toggle">
						<span class="we-switch__label">@lang('Return principal at maturity')</span>
						<div class="form-check form-switch we-admin-switch">
							<input
								id="walletEarnPlanReturnPrincipal"
								class="form-check-input"
								type="checkbox"
								role="switch"
								name="return_principal"
								value="1"
								@checked(old('return_principal', $plan->exists ? $plan->return_principal : true))
							>
						</div>
					</div>
				</div>
				
				<div class="col-md-4">
					<input type="hidden" name="auto_approve" value="0">
					<div class="we-switch we-switch--toggle">
						<span class="we-switch__label">@lang('Auto approve user stakes')</span>
						<div class="form-check form-switch we-admin-switch">
							<input
								id="walletEarnPlanAutoApprove"
								class="form-check-input"
								type="checkbox"
								role="switch"
								name="auto_approve"
								value="1"
								@checked(old('auto_approve', $plan->exists ? $plan->auto_approve : true))
							>
						</div>
					</div>
				</div>
				
				<div class="col-md-4">
					<input type="hidden" name="status" value="0">
					<div class="we-switch we-switch--toggle">
						<span class="we-switch__label">@lang('Plan is active')</span>
						<div class="form-check form-switch we-admin-switch">
							<input
								id="walletEarnPlanStatus"
								class="form-check-input"
								type="checkbox"
								role="switch"
								name="status"
								value="1"
								@checked(old('status', $plan->exists ? $plan->status : true))
							>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="we-form-footer">
			<a href="{{ route('admin.wallet-earn.plans.index') }}" class="btn btn-light">@lang('Cancel')</a>
			<button type="submit" class="btn btn-primary">
				<x-icon name="check" height="18" width="18" class="me-1"/>
				@lang('Save Plan')
			</button>
		</div>
	</div>
</div>
