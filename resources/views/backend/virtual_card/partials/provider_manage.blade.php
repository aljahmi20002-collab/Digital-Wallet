@php
	$selectedNetworks = old('supported_networks', $provider->supported_networks ?? []);
	$selectedNetworks = is_array($selectedNetworks) ? $selectedNetworks : [];
	$selectedCaps = old('capabilities', collect($provider->resolved_capabilities ?? [])->filter()->keys()->all());
	$selectedCaps = is_array($selectedCaps) ? $selectedCaps : [];
	$currenciesValue = old('supported_currencies', is_array($provider->supported_currencies) ? implode(', ', $provider->supported_currencies) : '');
	$countriesValue = old('supported_countries', is_array($provider->supported_countries) ? implode(', ', $provider->supported_countries) : '');
@endphp

<form action="{{ route('admin.virtual-card.provider.update', $provider) }}" method="post" class="vc-provider-form">
	@csrf
	@method('PUT')

	<div class="vc-provider-form__section">
		<div class="vc-provider-form__section-head">
			<h6>{{ __('Provider Identity') }}</h6>
			<p>{{ __('Core display, gateway link, and provider availability.') }}</p>
		</div>

		<div class="row g-3">
			<div class="col-md-6">
				<label class="form-label" for="name">{{ __('Provider Name') }}</label>
				<input class="form-control" id="name" type="text" name="name" value="{{ old('name', $provider->name) }}" required>
			</div>
			<div class="col-md-6">
				<label class="form-label" for="payment_gateway_id">{{ __('Linked Payment Gateway') }}</label>
				<select class="form-select" id="payment_gateway_id" name="payment_gateway_id">
					<option value="">{{ __('No gateway linked') }}</option>
					@foreach($gateways as $gateway)
						<option value="{{ $gateway->id }}" @selected((int) old('payment_gateway_id', $provider->payment_gateway_id) === $gateway->id)>
							{{ $gateway->name }} ({{ $gateway->code }}){{ $gateway->status ? '' : ' - '.__('Inactive') }}
						</option>
					@endforeach
				</select>
			</div>
			<div class="col-md-4">
				<label class="form-label" for="brand">{{ __('Brand') }}</label>
				<input class="form-control" id="brand" type="text" name="brand" value="{{ old('brand', $provider->brand) }}" maxlength="30" placeholder="{{ __('Multi') }}">
			</div>
			<div class="col-md-4">
				<label class="form-label" for="display_label">{{ __('Short Label') }}</label>
				<input class="form-control" id="display_label" type="text" name="display_label" value="{{ old('display_label', $provider->display_label) }}" maxlength="24" placeholder="{{ strtoupper($provider->code) }}">
			</div>
			<div class="col-md-4">
				<label class="form-label" for="brand_color">{{ __('Brand Color') }}</label>
				<div class="input-group">
					<input class="form-control" id="brand_color" type="text" name="brand_color" value="{{ old('brand_color', $provider->brand_color) }}" maxlength="16" placeholder="#3B6FE0" data-vc-color-input>
					<span class="input-group-text">
						<span class="vc-admin-color-dot" data-vc-color-preview></span>
					</span>
				</div>
			</div>
			<div class="col-md-6">
				<label class="form-label" for="order">{{ __('Display Order') }}</label>
				<input class="form-control" id="order" type="number" min="0" name="order" value="{{ old('order', $provider->order) }}">
			</div>
			<div class="col-md-6">
				<label class="form-label" for="status">{{ __('Provider Status') }}</label>
				<div class="vc-provider-form__switch">
					<span>{{ __('Available for new approvals') }}</span>
					<div class="form-check form-switch mb-0">
						<input class="form-check-input coevs-switch" id="status" type="checkbox" name="status" value="1" @checked(old('status', $provider->status))>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="vc-provider-form__section">
		<div class="vc-provider-form__section-head">
			<h6>{{ __('Coverage') }}</h6>
			<p>{{ __('Networks, wallet currencies, and cardholder countries this gateway can issue for.') }}</p>
		</div>

		<div class="row g-3">
			<div class="col-12">
				<label class="form-label">{{ __('Supported Networks') }}</label>
				<div class="vc-provider-form__checks">
					@foreach($networkOptions as $network)
						<label class="vc-provider-form__check">
							<input type="checkbox" name="supported_networks[]" value="{{ $network->value }}" @checked(in_array($network->value, $selectedNetworks, true))>
							<span><i class="{{ $network->icon() }}"></i>{{ $network->label() }}</span>
						</label>
					@endforeach
				</div>
			</div>
			<div class="col-md-6">
				<label class="form-label" for="supported_currencies">{{ __('Supported Currencies') }}</label>
				<input class="form-control" id="supported_currencies" type="text" name="supported_currencies" value="{{ $currenciesValue }}" placeholder="USD, EUR, NGN">
				<div class="form-text">{{ __('Comma-separated currency codes. Empty means no currency restriction.') }}</div>
			</div>
			<div class="col-md-6">
				<label class="form-label" for="supported_countries">{{ __('Supported Countries') }}</label>
				<input class="form-control" id="supported_countries" type="text" name="supported_countries" value="{{ $countriesValue }}" placeholder="US, GB, NG">
				<div class="form-text">{{ __('Comma-separated ISO country codes. Empty means all countries.') }}</div>
			</div>
		</div>
	</div>

	<div class="vc-provider-form__section">
		<div class="vc-provider-form__section-head">
			<h6>{{ __('Pricing') }}</h6>
			<p>{{ __('Issuance charge and minimum wallet balance rules.') }}</p>
		</div>

		<div class="row g-3">
			<div class="col-md-4">
				<label class="form-label" for="issue_fee">{{ __('Fixed Issue Fee') }}</label>
				<div class="input-group">
					<input type="number" class="form-control" id="issue_fee" name="issue_fee" value="{{ old('issue_fee', $provider->issue_fee) }}" min="0" step="0.01" required>
					<span class="input-group-text">{{ siteCurrency() }}</span>
				</div>
			</div>
			<div class="col-md-4">
				<label class="form-label" for="issue_fee_pct">{{ __('Issue Fee Percent') }}</label>
				<div class="input-group">
					<input type="number" class="form-control" id="issue_fee_pct" name="issue_fee_pct" value="{{ old('issue_fee_pct', $provider->issue_fee_pct) }}" min="0" max="100" step="0.01">
					<span class="input-group-text">%</span>
				</div>
			</div>
			<div class="col-md-4">
				<label class="form-label" for="min_balance">{{ __('Minimum Wallet Balance') }}</label>
				<div class="input-group">
					<input type="number" class="form-control" id="min_balance" name="min_balance" value="{{ old('min_balance', $provider->min_balance) }}" min="0" step="0.01">
					<span class="input-group-text">{{ siteCurrency() }}</span>
				</div>
			</div>
		</div>
	</div>

	<div class="vc-provider-form__section">
		<div class="vc-provider-form__section-head">
			<h6>{{ __('Capabilities') }}</h6>
			<p>{{ __('Controls which admin and user actions appear for this gateway.') }}</p>
		</div>

		<div class="vc-provider-form__checks vc-provider-form__checks--capabilities">
			@foreach($capabilityLabels as $key => $label)
				<label class="vc-provider-form__check">
					<input type="checkbox" name="capabilities[]" value="{{ $key }}" @checked(in_array($key, $selectedCaps, true))>
					<span>{{ $label }}</span>
				</label>
			@endforeach
		</div>
	</div>

	<div class="vc-provider-form__footer">
		<button class="btn btn-primary" type="submit">
			<x-icon name="check" height="20"/> {{ __('Save Provider') }}
		</button>
	</div>
</form>
