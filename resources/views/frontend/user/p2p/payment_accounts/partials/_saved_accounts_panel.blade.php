<div class="p2p-offers-panel p2p-accounts-panel">
	<div class="p2p-offers-panel__head p2p-accounts-panel__head">
		<div class="p2p-accounts-panel__lead">
			<div class="p2p-accounts-panel__title-row">
				<span class="p2p-accounts-panel__title-icon"><i class="fas fa-wallet"></i></span>
				<div class="p2p-accounts-panel__title-copy">
					<h6 class="p2p-offers-panel__title mb-0">@lang('Saved Payment Accounts')</h6>
					<p class="p2p-accounts-panel__subtitle">@lang('Payment details used during orders and verification.')</p>
				</div>
			</div>
		</div>
	</div>
	
	<div class="p2p-offers-panel__body p2p-accounts-panel__body">
		@forelse($accountCards as $accountCard)
			<div class="p2p-account-card">
				<div class="p2p-account-card__top">
					<div class="d-flex align-items-center gap-3">
						@if($accountCard['payment_method_logo_url'])
							<img src="{{ $accountCard['payment_method_logo_url'] }}" alt="{{ $accountCard['payment_method_name'] }}" class="p2p-method-logo" loading="lazy">
						@else
							<div class="p2p-method-fallback">{{ $accountCard['payment_method_initial'] }}</div>
						@endif
						
						<div>
							<div class="p2p-account-label">{{ $accountCard['payment_method_name'] }}</div>
							<div class="p2p-account-meta">
								@if($accountCard['country_label'])
									<span>{{ $accountCard['country_label'] }}</span>
								@else
									<span>@lang('Global availability')</span>
								@endif
							</div>
						</div>
					</div>
					
					<div class="p2p-account-card__meta-badges">
						<span class="p2p-account-card__badge">@lang('Trade Ready')</span>
					</div>
				</div>
				@if($accountCard['details_preview'] !== [])
					<div class="p2p-account-details mt-2">
						@foreach($accountCard['details_preview'] as $detail)
							<div class="p2p-account-details__row">
								<span class="p2p-account-details__label">{{ $detail['label'] ?? '-' }}</span>
								<span class="p2p-account-value" title="{{ (string) ($detail['value'] ?? '') }}">
                                    {{ \Illuminate\Support\Str::limit((string) ($detail['value'] ?? ''), 28) }}
                                </span>
							</div>
						@endforeach
					</div>
				@endif
				
				<div class="p2p-account-card__actions">
					<button type="button" class="btn btn-primary btn-sm p2p-action-btn js-edit-payment-account" data-account-id="{{ $accountCard['account']->id }}">
						<i class="fas fa-pen me-1"></i> @lang('Edit')
					</button>
					<form
						method="POST"
						action="{{ route('user.p2p.payment-accounts.destroy', $accountCard['account']) }}"
						class="js-delete-payment-account-form"
						data-account-label="{{ $accountCard['account_display_name'] }}"
					>
						@csrf
						@method('DELETE')
						<button type="submit" class="btn btn-danger btn-sm p2p-action-btn text-white">
							<i class="fas fa-trash-alt me-1"></i> @lang('Delete')
						</button>
					</form>
				</div>
			</div>
		@empty
			<div class="p2p-empty-state">
				<div class="p2p-empty-state__card">
					<div class="p2p-empty-state__icon">
						<i class="fas fa-wallet"></i>
					</div>
					<h6 class="p2p-empty-state__title">@lang('No accounts added yet')</h6>
					<p class="p2p-empty-state__text">@lang('Add your payment accounts first so your sell ads, order instructions, and payment verification flow stay clean from the first trade.')</p>
					<div class="p2p-empty__actions">
						<button type="button" class="btn btn-base btn-sm" data-bs-toggle="modal" data-bs-target="#p2pPaymentAccountCreateModal">
							<i class="fas fa-plus me-1"></i> @lang('Add My First Account')
						</button>
						<a href="{{ route('user.p2p.offers.index') }}" class="btn btn-light-primary btn-sm">
							<i class="fas fa-store me-1"></i> @lang('Browse Marketplace')
						</a>
					</div>
				</div>
			</div>
		@endforelse
	</div>
</div>
