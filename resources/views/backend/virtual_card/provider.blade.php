@extends('backend.virtual_card.index')
@section('title', __('Virtual Card Providers'))

@section('virtual_card_header')
	@php
		$providerItems = $providers->getCollection();
		$activeProviders = $providerItems->where('status', true)->count();
		$linkedGateways = $providerItems->filter(fn ($provider) => filled($provider->paymentGateway))->count();
		$totalNetworks = $providerItems->flatMap(fn ($provider) => $provider->supported_networks ?? [])->filter()->unique()->count();
	@endphp

	<div class="vcp-hero my-3">
		<div class="vcp-hero__content">
			<span class="vcp-eyebrow">{{ __('Virtual Cards') }}</span>
			<h3>{{ __('Provider Control Center') }}</h3>
			<p>{{ __('Manage issuing gateways, coverage, pricing, credentials, and operational capabilities from one workspace.') }}</p>
		</div>

		<div class="vcp-hero__stats">
			<div class="vcp-stat">
				<span>{{ __('Active') }}</span>
				<strong>{{ $activeProviders }}</strong>
			</div>
			<div class="vcp-stat">
				<span>{{ __('Gateways') }}</span>
				<strong>{{ $linkedGateways }}</strong>
			</div>
			<div class="vcp-stat">
				<span>{{ __('Networks') }}</span>
				<strong>{{ $totalNetworks }}</strong>
			</div>
		</div>

		<a href="{{ route('admin.payment.gateway.index') }}" class="btn btn-light vcp-hero__action" title="{{ __('Manage Payment Gateways') }}">
			<x-icon name="payment" height="18" width="18"/>
			{{ __('Payment Gateways') }}
		</a>
	</div>
@endsection

@section('virtual_card_content')
	<div class="card-body vcp-board">
		<div class="vcp-board__head">
			<div>
				<h5>{{ __('Issuing Providers') }}</h5>
				<p>{{ __('Each provider card is generated from the same capability contract, so new gateways can be onboarded without a new admin screen.') }}</p>
			</div>
			<span class="vcp-count">{{ trans_choice(':count provider|:count providers', $providers->total(), ['count' => $providers->total()]) }}</span>
		</div>

		<div class="vcp-grid">
			@forelse($providers as $provider)
				@php
					$caps = $provider->resolved_capabilities ?? [];
					$capLabels = [
						'issue' => __('Issue'),
						'card_details' => __('Reveal'),
						'topup' => __('Top-up'),
						'withdraw' => __('Withdraw'),
						'freeze' => __('Freeze'),
						'limits' => __('Limits'),
						'controls' => __('Controls'),
					];
					$gateway = $provider->paymentGateway;
					$supportedCapabilities = collect($capLabels)->filter(fn ($label, $key) => ! empty($caps[$key]))->count();
				@endphp

				<div class="vcp-card">
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
							<span></span>
							{{ $provider->status ? __('Active') : __('Inactive') }}
						</span>
					</div>

					<div class="vcp-card__summary">
						<div>
							<span>{{ __('Issue Fee') }}</span>
							<strong>{{ $provider->fee_formatted }}</strong>
							@if((float) ($provider->issue_fee_pct ?? 0) > 0)
								<small>{{ __('Plus :fee%', ['fee' => number_format((float) $provider->issue_fee_pct, 2)]) }}</small>
							@endif
						</div>
						<div>
							<span>{{ __('Capabilities') }}</span>
							<strong>{{ $supportedCapabilities }}/{{ count($capLabels) }}</strong>
							<small>{{ __('enabled') }}</small>
						</div>
					</div>

					<div class="vcp-section">
						<div class="vcp-section__label">{{ __('Issuance Stats') }}</div>
						<div class="vcp-chips">
							<span class="vcp-chip vcp-chip--blue"><strong>{{ number_format($provider->stat_total ?? 0) }}</strong> {{ __('total') }}</span>
							<span class="vcp-chip vcp-chip--green"><strong>{{ number_format($provider->stat_active ?? 0) }}</strong> {{ __('active') }}</span>
							@if(($provider->stat_pending ?? 0) > 0)
								<span class="vcp-chip"><strong>{{ number_format($provider->stat_pending) }}</strong> {{ __('pending') }}</span>
							@endif
							@if(($provider->stat_failed ?? 0) > 0)
								<span class="vcp-chip vcp-chip--red"><strong>{{ number_format($provider->stat_failed) }}</strong> {{ __('failed') }}</span>
							@endif
							@if(($provider->stat_pending_requests ?? 0) > 0)
								<span class="vcp-chip"><strong>{{ number_format($provider->stat_pending_requests) }}</strong> {{ __('reviews') }}</span>
							@endif
						</div>
					</div>

					<div class="vcp-section">
						<div class="vcp-section__label">{{ __('Coverage') }}</div>
						<div class="vcp-chips">
							@forelse($provider->supported_networks ?? [] as $net)
								<span class="vcp-chip vcp-chip--blue">{{ Str::upper($net) }}</span>
							@empty
								<span class="vcp-chip vcp-chip--muted">{{ __('No networks') }}</span>
							@endforelse
							@forelse($provider->supported_currencies ?? [] as $cur)
								<span class="vcp-chip">{{ $cur }}</span>
							@empty
								<span class="vcp-chip vcp-chip--muted">{{ __('All currencies') }}</span>
							@endforelse
						</div>
					</div>

					<div class="vcp-section">
						<div class="vcp-section__label">{{ __('Provider Tools') }}</div>
						<div class="vcp-chips vcp-chips--capabilities">
							@foreach($capLabels as $key => $label)
								<span class="vcp-chip {{ ! empty($caps[$key]) ? 'vcp-chip--green' : 'vcp-chip--muted' }}">{{ $label }}</span>
							@endforeach
						</div>
					</div>

					<div class="vcp-card__footer">
						@if($gateway)
							<button type="button" class="vcp-gateway-edit btn btn-outline-dark" data-edit-url="{{ route('admin.payment.gateway.edit', $gateway->id) }}" title="{{ __('Manage credentials') }}">
								<x-icon name="settings" height="16" width="16"/>
								{{ __('Gateway') }}
							</button>
						@else
							<span class="vcp-gateway--missing">
								<x-icon name="warning" height="14" width="14"/>
								{{ __('No gateway') }}
							</span>
						@endif

						<button type="button" class="btn btn-outline-secondary vcp-test-connection" data-test-url="{{ route('admin.virtual-card.provider.test-connection', $provider->id) }}" data-result-target="#vcp-test-result-{{ $provider->id }}">
							<i class="fa-solid fa-plug me-1"></i>{{ __('Test') }}
						</button>

						<a href="{{ route('admin.virtual-card.provider.show', $provider->id) }}" class="btn btn-outline-primary">
							<i class="fa-solid fa-chart-line me-1"></i>{{ __('Details') }}
						</a>

						<button type="button" class="btn btn-primary edit-modal" data-edit-url="{{ route('admin.virtual-card.provider.manage', $provider->id) }}">
							<x-icon name="manage" height="16" width="16"/>
							{{ __('Manage') }}
						</button>
					</div>

					<div id="vcp-test-result-{{ $provider->id }}" class="vcp-test-result mt-2 d-none"></div>
				</div>
			@empty
				<x-admin-not-found
					:title="__('No providers found')"
					:message="__('Seed or add a virtual card provider to start issuing cards.')"
					icon="fa-inbox"
					class="vcp-empty"
				/>
			@endforelse
		</div>

		@if($providers->hasPages())
			<div class="d-flex justify-content-center mt-4">
				{{ $providers->withQueryString()->links() }}
			</div>
		@endif
	</div>

	@include('backend.virtual_card.partials._manage_modal')
	@include('backend.payment_gateway.partial._edit_payment_gateway_modal')
@endsection
