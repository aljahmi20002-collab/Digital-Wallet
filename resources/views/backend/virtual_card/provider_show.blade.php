@extends('backend.virtual_card.index')
@section('title', __('Provider: :name', ['name' => $provider->name]))

@section('virtual_card_header')
	@php
		$caps = $provider->resolved_capabilities ?? [];
		$capLabels = [
			'issue' => __('Issue Card'),
			'card_details' => __('Reveal PAN/CVV'),
			'topup' => __('Top-up'),
			'withdraw' => __('Withdraw'),
			'freeze' => __('Freeze / Unfreeze'),
			'limits' => __('Spend Limits'),
			'controls' => __('Card Controls'),
		];
		$gateway = $provider->paymentGateway;
		$totalCards = (int) $statBuckets->sum();
	@endphp

	<div class="vcp-hero my-3">
		<div class="vcp-hero__content">
			<span class="vcp-eyebrow">{{ __('Provider Detail') }}</span>
			<h3>{{ $provider->name }}</h3>
			<p>{{ __('Review gateway capability, coverage, recent cards, and request activity for this provider.') }}</p>
		</div>

		<div class="vcp-hero__stats">
			<div class="vcp-stat">
				<span>{{ __('Cards') }}</span>
				<strong>{{ number_format($totalCards) }}</strong>
			</div>
			<div class="vcp-stat">
				<span>{{ __('Active') }}</span>
				<strong>{{ number_format((int) ($statBuckets['active'] ?? 0)) }}</strong>
			</div>
			<div class="vcp-stat">
				<span>{{ __('Failed') }}</span>
				<strong>{{ number_format((int) ($statBuckets['failed'] ?? 0)) }}</strong>
			</div>
		</div>

		<a href="{{ route('admin.virtual-card.provider.index') }}" class="btn btn-light vcp-hero__action">
			<i class="fa-solid fa-arrow-left"></i>{{ __('All Providers') }}
		</a>
	</div>
@endsection

@section('virtual_card_content')
	<div class="card-body">
		<div class="row g-3">
			<div class="col-lg-5">
				<div class="vcp-card h-100">
					<div class="vcp-card__top">
						<div class="vcp-provider">
							<div class="vcp-provider__logo">
								<img src="{{ $provider->logo_url }}" alt="{{ $provider->name }}" loading="lazy">
							</div>
							<div class="vcp-provider__meta">
								<div class="vcp-provider__name">{{ $provider->name }}</div>
								<div class="vcp-provider__code">{{ $provider->code }}{{ $provider->brand ? ' / '.$provider->brand : '' }}</div>
							</div>
						</div>
						<span class="vcp-status {{ $provider->status ? 'vcp-status--active' : 'vcp-status--inactive' }}">
							<span></span>{{ $provider->status ? __('Active') : __('Inactive') }}
						</span>
					</div>

					<div class="vcp-section">
						<div class="vcp-section__label">{{ __('Capability Matrix') }}</div>
						<div class="vcp-chips vcp-chips--capabilities">
							@foreach($capLabels as $key => $label)
								<span class="vcp-chip {{ ! empty($caps[$key]) ? 'vcp-chip--green' : 'vcp-chip--muted' }}">
									<i class="fa-solid {{ ! empty($caps[$key]) ? 'fa-circle-check' : 'fa-circle-minus' }} me-1"></i>{{ $label }}
								</span>
							@endforeach
						</div>
					</div>

					<div class="vcp-section">
						<div class="vcp-section__label">{{ __('Coverage') }}</div>
						<dl class="vcp-detail-list">
							<dt>{{ __('Networks') }}</dt>
							<dd>
								@forelse($provider->supported_networks ?? [] as $net)
									<span class="vcp-chip vcp-chip--blue">{{ Str::upper($net) }}</span>
								@empty
									<span class="vcp-chip vcp-chip--muted">{{ __('No restriction') }}</span>
								@endforelse
							</dd>
							<dt>{{ __('Currencies') }}</dt>
							<dd>
								@forelse($provider->supported_currencies ?? [] as $cur)
									<span class="vcp-chip">{{ $cur }}</span>
								@empty
									<span class="vcp-chip vcp-chip--muted">{{ __('No restriction') }}</span>
								@endforelse
							</dd>
							<dt>{{ __('Countries') }}</dt>
							<dd>
								@forelse($provider->supported_countries ?? [] as $country)
									<span class="vcp-chip">{{ $country }}</span>
								@empty
									<span class="vcp-chip vcp-chip--muted">{{ __('All countries') }}</span>
								@endforelse
							</dd>
							<dt>{{ __('Issue Fee') }}</dt>
							<dd><strong>{{ $provider->fee_formatted }}</strong></dd>
						</dl>
					</div>

					<div class="vcp-section">
						<div class="vcp-section__label">{{ __('Diagnostics') }}</div>
						<div class="d-flex flex-wrap gap-2">
							<button type="button" class="btn btn-outline-secondary vcp-test-connection" data-test-url="{{ route('admin.virtual-card.provider.test-connection', $provider->id) }}" data-result-target="#vcp-test-result-detail">
								<i class="fa-solid fa-plug me-1"></i>{{ __('Test Connection') }}
							</button>
							@if($gateway)
								<button type="button" class="btn btn-outline-dark vcp-gateway-edit" data-edit-url="{{ route('admin.payment.gateway.edit', $gateway->id) }}">
									<i class="fa-solid fa-key me-1"></i>{{ __('Credentials') }}
								</button>
							@endif
							<button type="button" class="btn btn-primary edit-modal" data-edit-url="{{ route('admin.virtual-card.provider.manage', $provider->id) }}">
								<i class="fa-solid fa-sliders me-1"></i>{{ __('Configure') }}
							</button>
						</div>
						<div id="vcp-test-result-detail" class="vcp-test-result mt-2 d-none"></div>
					</div>
				</div>
			</div>

			<div class="col-lg-7">
				<div class="vc-admin-board p-0 overflow-hidden">
					<div class="vc-admin-board__head">
						<h5>{{ __('Recent Cards') }}</h5>
						<span>{{ trans_choice(':count row|:count rows', $recentCards->count(), ['count' => $recentCards->count()]) }}</span>
					</div>
					<div class="table-responsive vc-admin-table">
						<table class="table table-sm mb-0 align-middle">
							<thead>
								<tr>
									<th>{{ __('Card') }}</th>
									<th>{{ __('User') }}</th>
									<th>{{ __('Wallet') }}</th>
									<th>{{ __('Status') }}</th>
									<th class="text-end">{{ __('Action') }}</th>
								</tr>
							</thead>
							<tbody>
								@forelse($recentCards as $card)
									@php
										$statusValue = $card->status?->value ?? (string) $card->status;
										$badgeClass = match ($statusValue) {
											'active' => 'success',
											'pending' => 'info',
											'inactive' => 'warning',
											'blocked', 'failed' => 'danger',
											'expired' => 'secondary',
											default => 'secondary',
										};
									@endphp
									<tr>
										<td>
											<div class="fw-semibold">**** {{ $card->last4 ?? '----' }}</div>
											<small class="text-muted">{{ $card->expiry_month ? $card->expiry_month.'/'.$card->expiry_year : __('No expiry') }}</small>
										</td>
										<td>
											<div class="small">{{ $card->user?->email ?? __('Unknown') }}</div>
											<small class="text-muted">{{ optional($card->request?->cardholder)->full_name }}</small>
										</td>
										<td><span class="vcp-chip">{{ $card->wallet?->currency?->code ?? '-' }}</span></td>
										<td><span class="badge bg-{{ $badgeClass }} text-white">{{ ucfirst($statusValue ?: '-') }}</span></td>
										<td class="text-end">
											<form action="{{ route('admin.virtual-card.cards.refresh', $card->id) }}" method="POST" class="d-inline">
												@csrf
												<button type="submit" class="btn btn-sm btn-outline-primary" title="{{ __('Refresh from gateway') }}">
													<i class="fa-solid fa-rotate"></i>
												</button>
											</form>
										</td>
									</tr>
								@empty
									<tr>
										<td colspan="5">
											<x-admin-not-found
												:title="__('No cards issued through this provider')"
												:message="__('Issued cards for this provider will appear here.')"
												icon="fa-credit-card"
											/>
										</td>
									</tr>
								@endforelse
							</tbody>
						</table>
					</div>
				</div>

				<div class="vc-admin-board p-0 overflow-hidden mt-3">
					<div class="vc-admin-board__head">
						<h5>{{ __('Recent Requests') }}</h5>
						<span>{{ trans_choice(':count row|:count rows', $recentRequests->count(), ['count' => $recentRequests->count()]) }}</span>
					</div>
					<div class="table-responsive vc-admin-table">
						<table class="table table-sm mb-0 align-middle">
							<thead>
								<tr>
									<th>{{ __('Request') }}</th>
									<th>{{ __('User') }}</th>
									<th>{{ __('Cardholder') }}</th>
									<th>{{ __('Status') }}</th>
								</tr>
							</thead>
							<tbody>
								@forelse($recentRequests as $req)
									<tr>
										<td>
											<div class="fw-semibold small">#{{ $req->uuid }}</div>
											<small class="text-muted">{{ $req->created_at?->diffForHumans() }}</small>
										</td>
										<td><div class="small">{{ $req->user?->email ?? __('Unknown') }}</div></td>
										<td>
											<div class="small">{{ optional($req->cardholder)->full_name }}</div>
											<small class="text-muted">{{ optional($req->cardholder)->country }}</small>
										</td>
										<td><span class="badge bg-{{ $req->status?->badgeColor() }}">{{ $req->status?->label() }}</span></td>
									</tr>
								@empty
									<tr>
										<td colspan="4">
											<x-admin-not-found
												:title="__('No recent requests')"
												:message="__('Recent provider requests will appear here.')"
												icon="fa-inbox"
											/>
										</td>
									</tr>
								@endforelse
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>

	@include('backend.virtual_card.partials._manage_modal')
	@include('backend.payment_gateway.partial._edit_payment_gateway_modal')
@endsection
