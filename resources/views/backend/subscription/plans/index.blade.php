@extends('backend.subscription.layout')

@section('title', __('Subscription Plans'))
@section('sub_title', __('Subscription Plans'))
@section('sub_icon', 'apps')
@section('sub_subtitle', __('Create and manage plans, pricing, billing cycles, and feature limits.'))

@section('sub_action')
	@can('subscription-manage')
		<a href="{{ route('admin.subscription.plans.create') }}" class="btn btn-primary d-flex align-items-center gap-2">
			<x-icon name="add" height="20" width="20"/>
			@lang('Add Plan')
		</a>
	@endcan
@endsection

@section('sub_content')

	{{-- Stats --}}
	<div class="sa-kpi-grid mb-4">
		<div class="sa-kpi">
            <span class="sa-kpi__icon">
                <x-icon name="apps" height="18" width="18"/>
            </span>
			<div>
				<div class="sa-kpi__label">@lang('Total Plans')</div>
				<div class="sa-kpi__value">{{ $stats['total_plans'] }}</div>
			</div>
		</div>
		<div class="sa-kpi">
            <span class="sa-kpi__icon sa-kpi__icon--success">
                <x-icon name="check" height="18" width="18"/>
            </span>
			<div>
				<div class="sa-kpi__label">@lang('Active Plans')</div>
				<div class="sa-kpi__value">{{ $stats['active_plans'] }}</div>
			</div>
		</div>
		<div class="sa-kpi">
            <span class="sa-kpi__icon sa-kpi__icon--info">
                <x-icon name="people" height="18" width="18"/>
            </span>
			<div>
				<div class="sa-kpi__label">@lang('Active Subscribers')</div>
				<div class="sa-kpi__value">{{ number_format($stats['total_subscribers']) }}</div>
			</div>
		</div>
		<div class="sa-kpi">
            <span class="sa-kpi__icon sa-kpi__icon--warning">
                <x-icon name="money" height="18" width="18"/>
            </span>
			<div>
				<div class="sa-kpi__label">@lang('Total Revenue')</div>
				<div class="sa-kpi__value">{{ siteCurrency() }} {{ number_format($stats['total_revenue'], 2) }}</div>
			</div>
		</div>
	</div>

	{{-- Filter Bar --}}
	<div class="sa-table-card mb-3 p-3">
		<form method="GET" action="{{ route('admin.subscription.plans.index') }}" class="d-flex align-items-center gap-3 flex-wrap">
			<div class="d-flex align-items-center gap-2">
				<label class="form-label mb-0 fw-semibold text-nowrap">@lang('Billing Cycle')</label>
				<select name="billing_cycle" class="form-select form-select-sm" style="min-width:150px" onchange="this.form.submit()">
					<option value="">@lang('All Cycles')</option>
					@foreach(\App\Enums\BillingCycle::options() as $value => $label)
						<option value="{{ $value }}" @selected($cycleFilter === $value)>{{ $label }}</option>
					@endforeach
				</select>
			</div>
			@if($cycleFilter)
				<a href="{{ route('admin.subscription.plans.index') }}" class="btn btn-sm btn-outline-secondary">
					@lang('Clear Filter')
				</a>
			@endif
		</form>
	</div>

	{{-- Plans Table --}}
	<div class="sa-table-card">
		<div class="table-responsive">
			<table class="sa-table">
				<thead>
				<tr>
					<th>@lang('Plan')</th>
					<th>@lang('Pricing')</th>
					<th>@lang('Features')</th>
					<th>@lang('Subscribers')</th>
					<th>@lang('Status')</th>
					@can('subscription-manage')
						<th class="text-end">@lang('Actions')</th>
					@endcan
				</tr>
				</thead>
				<tbody>
				@forelse($plans as $plan)
					<tr>
						<td>
							<div class="fw-semibold">{{ $plan->name }}</div>
							@if($plan->is_featured || $plan->plan_badge)
								<div class="d-flex gap-1 mt-1">
									@if($plan->is_featured)
										<span class="sa-pill sa-pill--warning">@lang('Featured')</span>
									@endif
									@if($plan->plan_badge)
										<span class="sa-pill sa-pill--info">{{ $plan->plan_badge }}</span>
									@endif
								</div>
							@endif
						</td>
						<td>
							@if($plan->prices->isEmpty())
								<span class="sa-muted">@lang('No pricing set')</span>
							@else
								<div class="d-flex flex-column gap-1">
									@foreach($plan->prices as $price)
										<div class="d-flex align-items-center gap-2">
											<span class="sa-pill sa-pill--secondary">{{ $price->billing_cycle->label() }}</span>
											@if($price->isFree())
												<span class="sa-pill sa-pill--success">@lang('Free')</span>
											@else
												<span class="fw-semibold">{{ siteCurrency('code') }} {{ number_format($price->price, 2) }}</span>
											@endif
										</div>
									@endforeach
								</div>
								@if($plan->trial_days > 0)
									<div class="sa-muted mt-1">{{ $plan->trial_days }}-@lang('day trial')</div>
								@endif
							@endif
						</td>
						<td>
							<span class="sa-muted">{{ $plan->features->count() }} @lang('features')</span>
						</td>
						<td>
							<div class="fw-semibold">{{ number_format($plan->active_subscriptions_count) }}</div>
							<div class="sa-muted">{{ number_format($plan->subscriptions_count) }} @lang('total')</div>
						</td>
						<td>
                            <span class="sa-pill {{ $plan->status ? 'sa-pill--success' : 'sa-pill--danger' }}">
                                {{ $plan->status ? __('Active') : __('Disabled') }}
                            </span>
						</td>
						@can('subscription-manage')
							<td class="text-end">
								<div class="d-inline-flex gap-2">
									<a href="{{ route('admin.subscription.plans.edit', $plan) }}"
									   class="btn btn-outline-primary btn-sm d-inline-flex align-items-center gap-1">
										<x-icon name="manage" height="14" width="14"/>
										@lang('Edit')
									</a>
									<form method="POST" action="{{ route('admin.subscription.plans.toggle-status', $plan) }}">
										@csrf
										<button type="submit"
										        class="btn btn-sm d-inline-flex align-items-center gap-1 {{ $plan->status ? 'btn-outline-warning' : 'btn-outline-success' }}">
											<x-icon name="{{ $plan->status ? 'close' : 'check' }}" height="14" width="14"/>
											{{ $plan->status ? __('Disable') : __('Enable') }}
										</button>
									</form>
									<form method="POST" action="{{ route('admin.subscription.plans.destroy', $plan) }}"
									      onsubmit="return confirm('{{ __('Delete this plan? This cannot be undone.') }}')">
										@csrf @method('DELETE')
										<button type="submit" class="btn btn-outline-danger btn-sm d-inline-flex align-items-center gap-1">
											<x-icon name="delete-3" height="14" width="14"/>
											@lang('Delete')
										</button>
									</form>
								</div>
							</td>
						@endcan
					</tr>
				@empty
					<tr>
						<td colspan="{{ auth()->user()?->can('subscription-manage') ? 6 : 5 }}">
							@can('subscription-manage')
								<x-admin-not-found
									:title="__('No subscription plans yet')"
									:message="__('Create a subscription plan to start selling recurring access.')"
									icon="fa-list"
									:action-url="route('admin.subscription.plans.create')"
									:action-label="__('Create your first plan')"
								/>
							@else
								<x-admin-not-found
									:title="__('No subscription plans yet')"
									:message="__('Subscription plans will appear here once they are created.')"
									icon="fa-list"
								/>
							@endcan
						</td>
					</tr>
				@endforelse
				</tbody>
			</table>
		</div>
	</div>

@endsection
