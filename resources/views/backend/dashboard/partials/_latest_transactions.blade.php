<div class="col-12 col-xl-6">
	<div class="card dashboard-feed-card dashboard-feed-card--premium dashboard-feed-card--transactions dashboard-activity-panel dashboard-activity-panel--transactions border-0 h-100">
		<div class="card-body p-0">
			<div class="dashboard-panel__header dashboard-activity-panel__header">
				<div class="dashboard-activity-panel__heading">
					<span class="dashboard-section__eyebrow">{{ __('Transaction Monitor') }}</span>
					<h2 class="dashboard-panel__title mb-1">{{ __('Recent Transactions') }}</h2>
				</div>
				<a href="{{ route('admin.transaction') }}" class="dashboard-link-pill dashboard-activity-panel__link">
					{{ __('View All') }}
					<i class="fas fa-arrow-right"></i>
				</a>
			</div>

			<div class="dashboard-feed-list dashboard-feed-list--divided dashboard-activity-panel__list">
				@forelse($transactions as $transaction)
					@php
						$avatarData  = getUserAvatarDetails($transaction->user->first_name, $transaction->user->last_name);
						$color       = $transaction->status->color();
						$amountColor = $transaction->amount_flow->color($transaction->status);
						$amountSign  = $transaction->amount_flow->sign($transaction->status);
					@endphp
					<div class="dashboard-feed-item dashboard-feed-item--flush">
						{{-- Identity + when (what happened, when) --}}
						<div class="dashboard-feed-item__primary">
							<div class="dashboard-avatar {{ $avatarData['class'] }}">
								{{ $avatarData['initials'] }}
							</div>
							<div class="dashboard-feed-item__content">
								<a href="{{ route('admin.user.manage', $transaction->user->username) }}" class="dashboard-feed-item__title">
									{{ $transaction->user->name }}
								</a>
								<div class="dashboard-feed-item__sub">
									<span class="dashboard-feed-item__meta">{{ $transaction->trx_type->label() }}</span>
									<span class="dashboard-feed-item__dot" aria-hidden="true"></span>
									<span class="dashboard-feed-item__meta">{{ $transaction->created_at->diffForHumans() }}</span>
								</div>
							</div>
						</div>

						{{-- Amount (hero metric) --}}
						<div class="dashboard-feed-item__metric">
							<span class="dashboard-feed-item__value {{ $amountColor }}">
								{{ $amountSign }}{{ $transaction->amount }}<span class="dashboard-feed-item__value-unit">{{ $transaction->currency }}</span>
							</span>
							<span class="dashboard-feed-item__meta">{{ __('Fee') }} {{ getSymbol($transaction->currency) }}{{ $transaction->fee }}</span>
						</div>

						{{-- Status + reference --}}
						<div class="dashboard-feed-item__status">
							<span class="dashboard-status-pill dashboard-status-pill--{{ $color }}">
								<span class="dashboard-status-pill__dot" aria-hidden="true"></span>
								{{ $transaction->status->label() }}
							</span>
							<code class="dashboard-feed-item__trx" title="{{ __('Transaction ID') }}">{{ strtoupper($transaction->trx_id) }}</code>
						</div>
					</div>
				@empty
					<x-admin-not-found
						:title="__('No transaction data found')"
						:message="__('New transactions will appear here once activity starts flowing in.')"
						icon="fa-receipt"
						class="dashboard-empty-state"
					/>
				@endforelse
			</div>
		</div>
	</div>
</div>

