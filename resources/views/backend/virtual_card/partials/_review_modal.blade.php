@php
	$statusValue = $request->status?->value ?? (string) $request->status;
	$isPending = $statusValue === \App\Enums\VirtualCard\VirtualCardRequestStatus::Pending->value;
	$cardholder = $request->cardholder;
	$holderCountry = $cardholder?->card_type?->isBusiness() && $cardholder?->business?->country
		? $cardholder->business->country
		: ($cardholder?->country ?? null);
	$walletCurrency = $request->wallet?->currency?->code;
	$requestNetwork = strtolower((string) ($request->network?->value ?? $request->network ?? ''));

	$compatibleProviders = $providers->filter(function ($provider) use ($holderCountry, $walletCurrency, $requestNetwork) {
		$networks = is_array($provider->supported_networks) ? array_map('strtolower', $provider->supported_networks) : [];
		$currencies = is_array($provider->supported_currencies) ? array_map('strtoupper', $provider->supported_currencies) : [];
		$networkOk = $requestNetwork === '' || empty($networks) || in_array($requestNetwork, $networks, true);
		$currencyOk = ! $walletCurrency || empty($currencies) || in_array(strtoupper($walletCurrency), $currencies, true);

		return $provider->status
			&& $provider->supports('issue')
			&& $networkOk
			&& $currencyOk
			&& $provider->supportsCountry($holderCountry);
	});
@endphp

<div class="modal fade" id="review-request-{{ $request->uuid }}" tabindex="-1" aria-labelledby="reviewRequestLabel-{{ $request->uuid }}" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered modal-lg">
		<div class="modal-content vc-review-modal">
			<div class="modal-header">
				<div>
					<span class="vc-review-modal__eyebrow">{{ __('Request Review') }}</span>
					<h5 class="modal-title" id="reviewRequestLabel-{{ $request->uuid }}">#{{ $request->uuid }}</h5>
				</div>
				<button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="{{ __('Close') }}"></button>
			</div>

			<form action="{{ route('admin.virtual-card.requests.review', $request->uuid) }}" method="post">
				@csrf
				<div class="modal-body">
					<div class="vc-review-grid">
						<div class="vc-review-panel">
							<div class="vc-review-panel__head">
								<h6>{{ __('Request') }}</h6>
								<span class="badge bg-{{ $request->status?->badgeColor() }}">{{ $request->status?->label() }}</span>
							</div>
							<dl class="vc-review-list">
								<dt>{{ __('User') }}</dt>
								<dd>
									<span>{{ trim(($request->user->first_name ?? '').' '.($request->user->last_name ?? '')) ?: $request->user->username }}</span>
									<small>{{ $request->user->email }}</small>
								</dd>
								<dt>{{ __('Wallet') }}</dt>
								<dd>
									<span>{{ $request->wallet?->currency?->name ?? __('Unknown') }}</span>
									<small>{{ $walletCurrency ?? __('No currency') }}</small>
								</dd>
								<dt>{{ __('Network') }}</dt>
								<dd>
									<span>{{ strtoupper($requestNetwork ?: '-') }}</span>
									<small>{{ __('Requested card network') }}</small>
								</dd>
								<dt>{{ __('Initial Load') }}</dt>
								<dd>
									<span>{{ number_format((float) ($request->initial_load_amount ?? 0), 2) }} {{ $walletCurrency }}</span>
									<small>{{ __('Provider may require a minimum load.') }}</small>
								</dd>
							</dl>
						</div>

						<div class="vc-review-panel">
							<div class="vc-review-panel__head">
								<h6>{{ __('Cardholder') }}</h6>
								@if($holderCountry)
									<span class="vc-admin-chip">{{ strtoupper($holderCountry) }}</span>
								@endif
							</div>
							@if($cardholder)
								<dl class="vc-review-list">
									<dt>{{ __('Name') }}</dt>
									<dd>
										<span>{{ $cardholder->card_type?->isBusiness() && $cardholder->business ? $cardholder->business->business_name : $cardholder->full_name }}</span>
										<small>{{ $cardholder->email }}</small>
									</dd>
									<dt>{{ __('Type') }}</dt>
									<dd>
										<span>{{ $cardholder->card_type?->label() ?? __('Personal') }}</span>
										<small>{{ $cardholder->city }}{{ $cardholder->state ? ', '.$cardholder->state : '' }}</small>
									</dd>
								</dl>
							@else
								<div class="vc-admin-empty vc-admin-empty--compact">
									<i class="fa-regular fa-id-card"></i>
									<h5>{{ __('No cardholder attached') }}</h5>
								</div>
							@endif
						</div>
					</div>

					@if($request->card)
						<div class="vc-review-issued">
							<i class="fa-regular fa-credit-card"></i>
							<span>{{ __('Issued card') }}</span>
							<strong>**** {{ $request->card->last4 ?? '----' }}</strong>
							<small>{{ $request->card->expiry_month ? $request->card->expiry_month.'/'.$request->card->expiry_year : __('No expiry yet') }}</small>
						</div>
					@endif

					@if($isPending)
						<div class="vc-review-panel mt-3">
							<div class="vc-review-panel__head">
								<h6>{{ __('Provider Decision') }}</h6>
								<span class="vc-admin-chip">{{ trans_choice(':count option|:count options', $compatibleProviders->count(), ['count' => $compatibleProviders->count()]) }}</span>
							</div>

							@if($compatibleProviders->isEmpty())
								<div class="alert alert-danger mb-3">
									<i class="fa-solid fa-triangle-exclamation me-1"></i>
									{{ __('No active provider supports this request network, wallet currency, and cardholder country.') }}
								</div>
							@endif

							<div class="row g-3">
								<div class="col-md-7">
									<label for="provider-{{ $request->id }}" class="form-label">{{ __('Issuing Provider') }}</label>
									<select name="provider_id" id="provider-{{ $request->id }}" class="form-select">
										<option value="">{{ __('Choose compatible provider') }}</option>
										@foreach($compatibleProviders as $provider)
											<option value="{{ $provider->id }}" @selected(old('provider_id') == $provider->id)>
												{{ $provider->name }} - {{ $provider->fee_formatted }}
												@if((float) ($provider->issue_fee_pct ?? 0) > 0)
													+ {{ number_format((float) $provider->issue_fee_pct, 2) }}%
												@endif
											</option>
										@endforeach
									</select>
								</div>
								<div class="col-md-5">
									<label for="admin_note_{{ $request->id }}" class="form-label">{{ __('Admin Note') }}</label>
									<input type="text" name="admin_note" id="admin_note_{{ $request->id }}" class="form-control" maxlength="255" value="{{ old('admin_note', $request->admin_note) }}">
								</div>
							</div>
						</div>
					@elseif($request->admin_note)
						<div class="alert alert-light border mt-3 mb-0">
							<i class="fa-solid fa-note-sticky me-1"></i>
							{{ $request->admin_note }}
						</div>
					@endif
				</div>

				<div class="modal-footer">
					@if($isPending)
						<button type="submit" name="action" value="reject" class="btn btn-outline-danger">
							<i class="fa-solid fa-xmark me-1"></i>{{ __('Reject') }}
						</button>
						<button type="submit" name="action" value="approve" class="btn btn-primary" @disabled($compatibleProviders->isEmpty())>
							<i class="fa-solid fa-check me-1"></i>{{ __('Approve & Issue') }}
						</button>
					@else
						<button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">{{ __('Close') }}</button>
					@endif
				</div>
			</form>
		</div>
	</div>
</div>
