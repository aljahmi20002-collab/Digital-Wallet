@php use App\Enums\MethodType; @endphp
@extends('backend.layouts.app')
@section('title', __('Payment Gateways'))

@section('content')
	<div class="pgw-page my-3">
		<div class="pgw-hero">
			<div class="pgw-hero__content">
				<span class="pgw-eyebrow">{{ __('Payment Operations') }}</span>
				<h1>{{ __('Payment Gateways') }}</h1>
				<p>{{ __('Configure deposit and withdrawal providers, credentials, supported currencies, and availability from one control surface.') }}</p>
			</div>
			
			<div class="pgw-hero__stats">
				<div>
					<span>{{ __('Active') }}</span>
					<strong>{{ $gatewayStats['active'] }}</strong>
				</div>
				<div>
					<span>{{ __('Withdraw') }}</span>
					<strong>{{ $gatewayStats['withdraw'] }}</strong>
				</div>
				<div>
					<span>{{ __('Currencies') }}</span>
					<strong>{{ $gatewayStats['currencies'] }}</strong>
				</div>
			</div>
			
			<div class="pgw-hero__actions">
				<a href="{{ route('admin.deposit.method.index', ['type' => MethodType::AUTOMATIC]) }}" class="pgw-action pgw-action--deposit">
					<span class="pgw-action__icon" aria-hidden="true">
						<i class="fa-solid fa-arrow-down"></i>
					</span>
					<span class="pgw-action__copy">
						<strong>{{ __('Deposit Method') }}</strong>
						<small>{{ __('Money in') }}</small>
					</span>
				</a>
				<a href="{{ route('admin.withdraw.method.index', ['type' => MethodType::AUTOMATIC]) }}" class="pgw-action pgw-action--withdraw">
					<span class="pgw-action__icon" aria-hidden="true">
						<i class="fa-solid fa-arrow-up"></i>
					</span>
					<span class="pgw-action__copy">
						<strong>{{ __('Withdraw Method') }}</strong>
						<small>{{ __('Money out') }}</small>
					</span>
				</a>
			</div>
		</div>
		
		<div class="pgw-board">
			<div class="pgw-board__head">
				<div>
					<h2>{{ __('Gateway Directory') }}</h2>
					<p>{{ __('Review provider readiness before opening credential settings.') }}</p>
				</div>
				<span>{{ trans_choice(':count gateway|:count gateways', $paymentGateways->total(), ['count' => $paymentGateways->total()]) }}</span>
			</div>
			
			<div class="pgw-grid">
				@forelse($paymentGateways as $paymentGateway)
					@php
						$allCurrencies = $paymentGateway->currencies ?? [];
						$allCurrencies = array_values(array_unique(array_map('strtoupper', $allCurrencies)));
						$visibleCurrencies = array_slice($allCurrencies, 0, 4);
						$hiddenCurrencies = array_slice($allCurrencies, 4);
					@endphp
					
					<div class="pgw-card">
						<div class="pgw-card__top">
							<div class="pgw-gateway">
								<div class="pgw-gateway__logo">
									<img src="{{ asset($paymentGateway->logo) }}" alt="{{ $paymentGateway->name }}" loading="lazy">
								</div>
								<div>
									<strong>{{ $paymentGateway->name }}</strong>
									<span>{{ Str::upper($paymentGateway->code) }}</span>
								</div>
							</div>
							
							<span class="pgw-status pgw-status--compact {{ $paymentGateway->status ? 'pgw-status--success' : 'pgw-status--danger' }}">
                                <span></span>
                                {{ $paymentGateway->status ? __('Active') : __('Inactive') }}
                            </span>
						</div>
						
						<div class="pgw-card__meta">
							<div class="pgw-card__meta-tile pgw-card__meta-tile--{{ $paymentGateway->withdraw_available ? 'available' : 'unavailable' }}">
								<i class="pgw-card__meta-icon fa-regular {{ $paymentGateway->withdraw_available ? 'fa-circle-check' : 'fa-circle-xmark' }}" aria-hidden="true"></i>
								<div class="pgw-card__meta-copy">
									<span>{{ __('Withdraw') }}</span>
									<strong>{{ $paymentGateway->withdraw_available ? __('Available') : __('Unavailable') }}</strong>
								</div>
							</div>
							<div class="pgw-card__meta-tile pgw-card__meta-tile--neutral">
								<i class="pgw-card__meta-icon fa-regular fa-money-bill-1" aria-hidden="true"></i>
								<div class="pgw-card__meta-copy">
									<span>{{ __('Currencies') }}</span>
									<strong>{{ count($allCurrencies) }}</strong>
								</div>
							</div>
						</div>
						
						<div class="pgw-card__section">
							<div class="pgw-card__label">{{ __('Supported Currencies') }}</div>
							@if(count($allCurrencies) === 0)
								<span class="pgw-chip pgw-chip--muted">{{ __('None') }}</span>
							@else
								<div class="pgw-chips">
									@foreach($visibleCurrencies as $currency)
										<span class="pgw-chip">{{ $currency }}</span>
									@endforeach
									@if(count($hiddenCurrencies) > 0)
										@php($restLabel = implode(', ', $hiddenCurrencies))
										<span class="pgw-chip pgw-chip--more currency-more"
										      tabindex="0"
										      data-coreui-toggle="popover"
										      data-coreui-trigger="hover focus"
										      data-coreui-placement="top"
										      data-coreui-title="{{ __('More currencies') }}"
										      data-coreui-content="{{ $restLabel }}">
                                            +{{ count($hiddenCurrencies) }}
                                        </span>
									@endif
								</div>
							@endif
						</div>
						
						@can('payment-gateway-configure')
							<button class="edit-modal btn btn-primary pgw-manage-btn"
							        data-coreui-toggle="tooltip"
							        title="{{ __('Manage Gateway Credentials and Others') }}"
							        data-edit-url="{{ route('admin.payment.gateway.edit', $paymentGateway->id) }}">
								<x-icon name="manage" height="18" width="18"/>
								{{ __('Manage Credentials') }}
							</button>
						@endcan
					</div>
				@empty
					<x-admin-not-found
						:title="__('No payment gateways found')"
						:message="__('Gateway records will appear here after they are configured.')"
						icon="fa-credit-card"
					/>
				@endforelse
			</div>
			
			<div class="d-flex justify-content-end mt-3">
				{{ $paymentGateways->withQueryString()->links() }}
			</div>
		</div>
	</div>
	
	@can('payment-gateway-configure')
		@include('backend.payment_gateway.partial._edit_payment_gateway_modal')
	@endcan
@endsection

@push('scripts')
	@can('payment-gateway-configure')
		<script src="{{ asset('backend/js/payment-gateway.js?v=' . config('app.version')) }}"></script>
	@endcan
@endpush
