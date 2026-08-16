@extends('backend.layouts.app')
@section('title', __('Notifications Template'))
@section('styles')
	<style>
		.notification-template-page .notification-template-hero {
			background: linear-gradient(135deg, rgba(var(--cui-primary-rgb), 0.08), rgba(13, 110, 253, 0.02));
			border: 1px solid rgba(var(--cui-primary-rgb), 0.12);
			border-radius: 1rem;
			padding: 1.5rem;
		}

		.notification-template-page .notification-filter-pill {
			display: inline-flex;
			align-items: center;
			gap: 0.5rem;
			padding: 0.7rem 1rem;
			border-radius: 999px;
			background-color: #fff;
			border: 1px solid rgba(15, 23, 42, 0.08);
			color: #475467;
			text-decoration: none;
			font-weight: 600;
			transition: all 0.2s ease;
		}

		.notification-template-page .notification-filter-pill:hover,
		.notification-template-page .notification-filter-pill.is-active {
			background-color: rgba(var(--cui-primary-rgb), 0.1);
			border-color: rgba(var(--cui-primary-rgb), 0.18);
			color: var(--cui-primary);
		}

		.notification-template-page .notification-filter-pill__count {
			display: inline-flex;
			align-items: center;
			justify-content: center;
			min-width: 1.75rem;
			height: 1.75rem;
			padding: 0 0.45rem;
			border-radius: 999px;
			background-color: rgba(15, 23, 42, 0.06);
			font-size: 0.78rem;
		}

		.notification-template-page .notification-template-stat {
			height: 100%;
			border: 1px solid rgba(15, 23, 42, 0.06);
			border-radius: 1rem;
			padding: 1rem 1.1rem;
			background: #fff;
			box-shadow: 0 8px 24px rgba(15, 23, 42, 0.04);
		}

		.notification-template-page .notification-template-stat__icon {
			width: 2.75rem;
			height: 2.75rem;
			border-radius: 0.9rem;
			display: inline-flex;
			align-items: center;
			justify-content: center;
			font-size: 1rem;
		}

		.notification-template-page .notification-template-stat__value {
			font-size: 1.55rem;
			font-weight: 700;
			line-height: 1;
		}

		.notification-template-page .notification-template-shell {
			border-radius: 1rem;
			overflow: hidden;
		}

		.notification-template-page .notification-template-toolbar {
			padding: 1.25rem 1.25rem 0;
		}

		.notification-template-page .notification-template-table thead th {
			font-size: 0.78rem;
			font-weight: 700;
			text-transform: uppercase;
			letter-spacing: 0.04em;
			color: #667085;
			background-color: #f8fafc;
			border-bottom-width: 1px;
		}

		.notification-template-page .notification-template-table tbody tr {
			vertical-align: middle;
		}

		.notification-template-page .notification-template-table tbody td {
			padding-top: 1rem;
			padding-bottom: 1rem;
			border-color: rgba(15, 23, 42, 0.06);
		}

		.notification-template-page .notification-template-item {
			display: flex;
			align-items: flex-start;
			gap: 0.85rem;
		}

		.notification-template-page .notification-template-item__icon {
			width: 2.75rem;
			height: 2.75rem;
			border-radius: 0.9rem;
			display: inline-flex;
			align-items: center;
			justify-content: center;
			background-color: rgba(15, 23, 42, 0.04);
			flex-shrink: 0;
		}

		.notification-template-page .notification-template-meta {
			font-size: 0.82rem;
			color: #667085;
		}

		.notification-template-page .notification-channel-pill {
			display: flex;
			align-items: center;
			justify-content: space-between;
			gap: 0.75rem;
			padding: 0.55rem 0.7rem;
			border-radius: 0.85rem;
			background-color: #f8fafc;
		}

		.notification-template-page .notification-channel-pill__label {
			display: inline-flex;
			align-items: center;
			gap: 0.5rem;
			font-weight: 600;
			color: #344054;
		}

		.notification-template-page .notification-variable-group {
			display: flex;
			flex-wrap: wrap;
			gap: 0.45rem;
		}

		.notification-template-page .notification-variable-counter {
			display: inline-flex;
			align-items: center;
			justify-content: center;
			padding: 0.32rem 0.55rem;
			border-radius: 999px;
			background-color: rgba(var(--cui-primary-rgb), 0.08);
			color: var(--cui-primary);
			font-size: 0.78rem;
			font-weight: 700;
		}

		.notification-template-page .notification-template-empty {
			padding: 2.5rem 1.5rem;
		}

		.notification-template-page .notification-template-empty__icon {
			width: 4rem;
			height: 4rem;
			margin: 0 auto 1rem;
			border-radius: 1.25rem;
			display: inline-flex;
			align-items: center;
			justify-content: center;
			background-color: rgba(var(--cui-primary-rgb), 0.08);
			color: var(--cui-primary);
			font-size: 1.2rem;
		}

		@media (max-width: 991.98px) {
			.notification-template-page .notification-template-hero {
				padding: 1.15rem;
			}

			.notification-template-page .notification-template-toolbar {
				padding: 1rem 1rem 0;
			}
		}
	</style>
@endsection
@section('content')
	@php
		$allTemplatesQuery = request()->except('page', 'user_type');
		$userTemplatesQuery = array_merge(request()->except('page', 'user_type'), ['user_type' => \App\Enums\UserType::USER->value]);
		$adminTemplatesQuery = array_merge(request()->except('page', 'user_type'), ['user_type' => \App\Enums\UserType::ADMIN->value]);
		$selectedUserType = request('user_type', '');
	@endphp

	<div class="notification-template-page">
		<div class="notification-template-hero mb-4">
			<div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
				<div>
					<div class="d-flex align-items-center gap-2 mb-2">
						<span class="text-success small"><i class="fa-solid fa-circle"></i></span>
						<span class="text-muted small">{{ __('Manage and configure all system notification templates and channel preferences.') }}</span>
					</div>
					<h1 class="h3 mb-1">{{ __('Notification Templates') }}</h1>
					<p class="text-muted mb-0">{{ __('Track template coverage, review active channels, and quickly manage delivery configuration from one place.') }}</p>
				</div>
				@can('plugins-manage')
					<a href="{{ route('admin.settings.plugin_type', 'notification') }}" class="btn btn-primary d-inline-flex align-items-center">
						<x-icon name="bell-3" height="22" width="22" class="me-2"/>
						{{ __('Notification Integrations') }}
					</a>
				@endcan
			</div>

			<div class="d-flex flex-wrap gap-2 mt-4">
				<a href="{{ route('admin.notifications.template.index', $allTemplatesQuery) }}"
				   class="notification-filter-pill {{ $selectedUserType === '' ? 'is-active' : '' }}">
					<i class="fa-regular fa-rectangle-list"></i>
					<span>{{ __('All Templates') }}</span>
					<span class="notification-filter-pill__count">{{ $userTypeCounts[''] }}</span>
				</a>
				<a href="{{ route('admin.notifications.template.index', $userTemplatesQuery) }}"
				   class="notification-filter-pill {{ $selectedUserType === \App\Enums\UserType::USER->value ? 'is-active' : '' }}">
					<i class="fa-solid fa-user"></i>
					<span>{{ __('User') }}</span>
					<span class="notification-filter-pill__count">{{ $userTypeCounts[\App\Enums\UserType::USER->value] }}</span>
				</a>
				<a href="{{ route('admin.notifications.template.index', $adminTemplatesQuery) }}"
				   class="notification-filter-pill {{ $selectedUserType === \App\Enums\UserType::ADMIN->value ? 'is-active' : '' }}">
					<i class="fa-solid fa-user-shield"></i>
					<span>{{ __('Admin') }}</span>
					<span class="notification-filter-pill__count">{{ $userTypeCounts[\App\Enums\UserType::ADMIN->value] }}</span>
				</a>
			</div>
		</div>

		<div class="row g-3 mb-4">
			<div class="col-12 col-md-6 col-xl-3">
				<div class="notification-template-stat">
					<div class="d-flex align-items-start justify-content-between gap-3">
						<div>
							<div class="notification-template-stat__value text-primary">{{ $templateStats['total'] }}</div>
							<div class="text-muted small mt-1">{{ __('Total Templates') }}</div>
						</div>
						<span class="notification-template-stat__icon bg-primary bg-opacity-10 text-primary">
							<i class="fa-regular fa-rectangle-list"></i>
						</span>
					</div>
				</div>
			</div>
			<div class="col-12 col-md-6 col-xl-3">
				<div class="notification-template-stat">
					<div class="d-flex align-items-start justify-content-between gap-3">
						<div>
							<div class="notification-template-stat__value text-success">{{ $templateStats['active'] }}</div>
							<div class="text-muted small mt-1">{{ __('Active Templates') }}</div>
						</div>
						<span class="notification-template-stat__icon bg-success bg-opacity-10 text-success">
							<i class="fa-regular fa-circle-check"></i>
						</span>
					</div>
				</div>
			</div>
			<div class="col-12 col-md-6 col-xl-3">
				<div class="notification-template-stat">
					<div class="d-flex align-items-start justify-content-between gap-3">
						<div>
							<div class="notification-template-stat__value text-warning">{{ $templateStats['inactive'] }}</div>
							<div class="text-muted small mt-1">{{ __('Inactive Templates') }}</div>
						</div>
						<span class="notification-template-stat__icon bg-warning bg-opacity-10 text-warning">
							<i class="fa-regular fa-circle-pause"></i>
						</span>
					</div>
				</div>
			</div>
			<div class="col-12 col-md-6 col-xl-3">
				<div class="notification-template-stat">
					<div class="d-flex align-items-start justify-content-between gap-3">
						<div>
							<div class="notification-template-stat__value text-info">{{ $templateStats['channels'] }}</div>
							<div class="text-muted small mt-1">{{ __('Channels') }}</div>
						</div>
						<span class="notification-template-stat__icon bg-info bg-opacity-10 text-info">
							<i class="fa-solid fa-bell"></i>
						</span>
					</div>
				</div>
			</div>
		</div>

		<div class="card border-0 shadow-sm mb-4 notification-template-shell">
			<div class="notification-template-toolbar">
				<div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3 mb-3">
					<div>
						<h2 class="h5 mb-1">{{ __('Template Management') }}</h2>
						<p class="text-muted mb-0">{{ __('Search templates, narrow down delivery channels, and open any template for channel-level management.') }}</p>
					</div>
					<div class="d-flex align-items-center gap-2 flex-wrap">
						<span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2">{{ __('Results') }}: {{ $notifyTemplates->total() }}</span>
						@if($activeFilterCount > 0)
							<span class="badge bg-light text-dark border px-3 py-2">{{ __('Active Filters') }}: {{ $activeFilterCount }}</span>
						@endif
					</div>
				</div>

				<form action="{{ route('admin.notifications.template.index') }}" method="GET" class="row g-3 align-items-end mb-4">
					<div class="col-12 col-lg-4">
						<label class="form-label fw-semibold">{{ __('Search') }}</label>
						<div class="input-group">
							<span class="input-group-text bg-white"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
							<input type="text" name="search" value="{{ request('search') }}" class="form-control"
							       placeholder="{{ __('Search templates, identifier, variables...') }}"
							       aria-label="{{ __('Search templates, identifier, variables...') }}">
						</div>
					</div>
					<div class="col-12 col-md-6 col-xl-2">
						<label class="form-label fw-semibold">{{ __('Channel') }}</label>
						<select name="channel" class="form-select">
							<option value="">{{ __('All Channels') }}</option>
							@foreach($filterOptions['channels'] as $value => $label)
								<option value="{{ $value }}" @selected(request('channel') === $value)>{{ $label }}</option>
							@endforeach
						</select>
					</div>
					<div class="col-12 col-md-6 col-xl-2">
						<label class="form-label fw-semibold">{{ __('Status') }}</label>
						<select name="status" class="form-select">
							<option value="">{{ __('All Statuses') }}</option>
							@foreach($filterOptions['statuses'] as $value => $label)
								<option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
							@endforeach
						</select>
					</div>
					<div class="col-12 col-md-6 col-xl-2">
						<label class="form-label fw-semibold">{{ __('Sort') }}</label>
						<select name="sort" class="form-select">
							@foreach($filterOptions['sorts'] as $value => $label)
								<option value="{{ $value }}" @selected(request('sort', 'newest') === $value)>{{ $label }}</option>
							@endforeach
						</select>
					</div>
					<div class="col-12 col-md-6 col-xl-2">
						<div class="d-flex gap-2">
							<button type="submit" class="btn btn-primary flex-grow-1">
								<i class="fa-solid fa-sliders me-2"></i>{{ __('Apply') }}
							</button>
							@if($activeFilterCount > 0)
								<a href="{{ route('admin.notifications.template.index') }}" class="btn btn-outline-secondary">
									<i class="fa-solid fa-rotate-left"></i>
								</a>
							@endif
						</div>
					</div>
					@if($selectedUserType !== '')
						<input type="hidden" name="user_type" value="{{ $selectedUserType }}">
					@endif
				</form>
			</div>

			<div class="table-responsive">
				<table class="table align-middle mb-0 notification-template-table">
					<thead>
					<tr>
						<th>{{ __('Template') }}</th>
						<th>{{ __('Channels') }}</th>
						<th>{{ __('Variables') }}</th>
						<th>{{ __('Updated') }}</th>
						@can('notification-template-manage')
							<th class="text-end">{{ __('Action') }}</th>
						@endcan
					</tr>
					</thead>
					<tbody>
					@forelse($notifyTemplates as $template)
						@php
							// collect variables and count
							$vars  = collect($template->variables);
							$count = $vars->count();

							if ($count > 3) {
								// split into two pretty-even chunks
								$half   = (int) ceil($count / 2);
								$first  = $vars->slice(0, $half);
								$second = $vars->slice($half);
							} else {
								// 3 or fewer: just one row
								$first  = $vars;
								$second = collect();
							}
						@endphp

						<tr>
							<td>
								<div class="notification-template-item">
									<span class="notification-template-item__icon text-{{ $template->action_type->class() }}">
										<x-icon :name="$template->icon" height="28" width="28"/>
									</span>
									<div>
										<div class="fw-bold text-dark mb-1">{{ $template->name }}</div>
										<div class="d-flex flex-wrap align-items-center gap-2 mb-2">
											<span class="badge bg-{{ $template->user_type->color() }} text-uppercase">
												<i class="fa-solid fa-{{ $template->user_type->icon() }} me-1"></i>{{ $template->user_type->label() }}
											</span>
											<span class="badge bg-light text-dark border text-uppercase">{{ $template->action_type->label() }}</span>
										</div>
										<div class="text-muted small mb-2">{{ $template->info }}</div>
										<div class="notification-template-meta">
											<span class="fw-semibold text-dark">{{ __('Identifier') }}:</span> {{ $template->identifier }}
										</div>
									</div>
								</div>
							</td>
							<td>
								<div class="d-flex flex-column gap-2">
									@foreach($template->channels as $channel)
										<div class="notification-channel-pill">
											<div class="notification-channel-pill__label">
												<i class="{{ $channel->channel->icon() }} text-{{ $channel->channel->color() }}"></i>
												<span>{{ $channel->channel->label() }}</span>
											</div>
											<span class="badge bg-{{ $channel->is_active ? 'success' : 'secondary' }} text-uppercase">{{ $channel->is_active ? __('Active') : __('Inactive') }}</span>
										</div>
									@endforeach
								</div>
								<div class="small text-muted mt-2">
									{{ __('Active') }}: {{ $template->active_channels_count }} / {{ __('Inactive') }}: {{ $template->inactive_channels_count }}
								</div>
							</td>
							<td>
								<div class="d-flex align-items-center gap-2 flex-wrap mb-2">
									<span class="notification-variable-counter">{{ $count }}</span>
									<span class="small text-muted">{{ __('Available placeholders') }}</span>
								</div>

								<div class="notification-variable-group mb-2">
									@foreach($first as $variable)
										<span class="variable-tag">{{ '{'.$variable.'}' }}</span>
									@endforeach
								</div>

								@if($second->isNotEmpty())
									<div class="notification-variable-group">
										@foreach($second as $variable)
											<span class="variable-tag">{{ '{'.$variable.'}' }}</span>
										@endforeach
									</div>
								@endif
							</td>
							<td>
								<div class="fw-semibold text-dark">{{ $template->updated_at?->diffForHumans() ?? __('N/A') }}</div>
								<div class="small text-muted">{{ __('Last synced template configuration') }}</div>
							</td>
							@can('notification-template-manage')
								<td class="text-end">
									<a href="{{ route('admin.notifications.template.edit', $template->id) }}" class="btn btn-primary btn-sm d-inline-flex align-items-center">
										<x-icon name="manage" height="18" width="18" class="me-1"/>
										{{ __('Manage') }}
									</a>
								</td>
							@endcan
						</tr>
					@empty
						<tr>
							@can('notification-template-manage')
								<td colspan="5">
							@else
								<td colspan="4">
							@endcan
								<x-admin-not-found
									:title="__('No notification templates found')"
									:message="__('Try adjusting your search or filters to find the template configuration you need.')"
									icon="fa-bell"
									:action-url="route('admin.notifications.template.index')"
									:action-label="__('Clear Filters')"
								/>
							</td>
						</tr>
					@endforelse
					</tbody>
				</table>
			</div>
			{{-- Pagination --}}
			<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 px-4 py-3 border-top">
				<div class="small text-muted">
					{{ __('Showing') }} {{ $notifyTemplates->count() }} {{ __('of') }} {{ $notifyTemplates->total() }} {{ __('templates') }}
				</div>
				<div class="ms-md-auto">
					{{ $notifyTemplates->links() }}
				</div>
			</div>
		</div>
	</div>
@endsection
