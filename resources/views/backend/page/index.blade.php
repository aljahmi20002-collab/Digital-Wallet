@extends('backend.layouts.app')
@section('title', __('Page Manager'))

@push('styles')
	<link rel="stylesheet" href="{{ asset('backend/css/page-manager-admin.css') }}">
@endpush

@push('scripts')
	<script src="{{ asset('backend/js/page-manager-admin.js') }}"></script>
@endpush

@section('content')
	@php
		// Controller PageController::index passes all KPIs directly. The ??
		// fallbacks recompute from $pages only when something upstream
		// (stale compiled view, regressed payload) leaves them undefined so
		// the hero never throws "Undefined variable" again.
		$totalPages      = $totalPages      ?? (isset($pages) ? $pages->count() : 0);
		$activePages     = $activePages     ?? (isset($pages) ? $pages->where('is_active', true)->count() : 0);
		$dynamicPages    = $dynamicPages    ?? (isset($pages) ? $pages->filter(fn ($page) => $page->type === \App\Enums\PageType::Dynamic)->count() : 0);
		$protectedPages  = $protectedPages  ?? max(0, $totalPages - $dynamicPages);
		$homePages       = $homePages       ?? (isset($pages) ? $pages->where('is_home', true)->count() : 0);
		$totalComponents = $totalComponents ?? (isset($pages) ? (int) $pages->sum(fn ($page) => is_array($page->component_ids) ? count($page->component_ids) : 0) : 0);
	@endphp

	<div class="page-manager-admin my-3">
		<section class="card border-0 pm-hero mb-3">
			<div class="card-body p-3 p-xl-4">
				<div class="row g-3 align-items-stretch">
					<div class="col-xl-8">
						<div class="pm-hero__main">
							<div class="pm-hero__head">
								<div class="pm-hero__actions">
									<a href="{{ route('admin.site-seo.index') }}" class="btn pm-btn pm-btn--seo">
										<span class="pm-btn__icon" aria-hidden="true">
											<svg viewBox="0 0 24 24" fill="none">
												<path d="M12 4L18 7.5V16.5L12 20L6 16.5V7.5L12 4Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
												<path d="M9.5 12H14.5M9.5 9H12.5M9.5 15H13" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
											</svg>
										</span>
										<span>{{ __('SEO Manager') }}</span>
									</a>
									@can('page-create')
										<a href="{{ route('admin.page.site.create') }}" class="btn pm-btn pm-btn--primary">
											<span class="pm-btn__icon" aria-hidden="true">
												<svg viewBox="0 0 24 24" fill="none">
													<path d="M12 5V19M5 12H19" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
												</svg>
											</span>
											<span>{{ __('Create Page') }}</span>
										</a>
									@endcan
									<a href="{{ route('admin.dashboard') }}" class="btn pm-btn pm-btn--back" aria-label="{{ __('Back') }}">
										<span class="pm-btn__icon pm-btn__icon--back" aria-hidden="true">
											<x-icon name="back" width="14" height="14" class="d-block"/>
										</span>
									</a>
								</div>
							</div>
							<div class="pm-hero__content">
								<div class="pm-hero__copy">
									<h1 class="pm-hero__title mb-0">{{ __('Page Manager') }}</h1>
									<p class="pm-hero__subtitle mb-0">{{ __('Manage pages, routes, and publish state from one compact workspace.') }}</p>
								</div>
								<div class="pm-hero__metrics">
									<span class="pm-hero-pill"><strong>{{ $totalPages ?? 0 }}</strong><span>{{ __('Pages') }}</span></span>
									<span class="pm-hero-pill"><strong>{{ $activePages ?? 0 }}</strong><span>{{ __('Live') }}</span></span>
									<span class="pm-hero-pill"><strong>{{ $totalComponents ?? 0 }}</strong><span>{{ __('Components') }}</span></span>
								</div>
							</div>
						</div>
					</div>
					<div class="col-xl-4">
						<div class="pm-hero__aside">
							<div class="pm-hero-panel">
							<div class="pm-hero-panel__head">
								<div>
									<span class="pm-hero-panel__label">{{ __('Page Mix') }}</span>
									<h2 class="pm-hero-panel__value mb-0">{{ $dynamicPages ?? 0 }}/{{ $protectedPages ?? 0 }}</h2>
								</div>
								<span class="pm-hero-panel__badge">{{ __('Dynamic / Core') }}</span>
							</div>
							<p class="pm-hero-panel__text mb-0">{{ __('Keep editable and core routes clearly separated.') }}</p>
							<div class="pm-hero-panel__grid">
								<div class="pm-hero-panel__metric">
									<span>{{ __('Dynamic') }}</span>
									<strong>{{ $dynamicPages ?? 0 }}</strong>
								</div>
								<div class="pm-hero-panel__metric">
									<span>{{ __('Home') }}</span>
									<strong>{{ $homePages ?? 0 }}</strong>
								</div>
							</div>
						</div>
						</div>
					</div>
				</div>
			</div>
		</section>

		<div class="row g-3 mb-3">
			<div class="col-12 col-sm-6 col-xl-3">
				<div class="card stat-card dashboard-kpi-card pm-kpi-card dashboard-kpi-card--total border-0 h-100">
					<div class="card-body">
						<div class="dashboard-kpi-card__summary">
							<div class="dashboard-kpi-card__icon total" aria-hidden="true">
								<svg viewBox="0 0 24 24" fill="none">
									<rect x="4" y="5" width="16" height="14" rx="3" stroke="currentColor" stroke-width="1.7"/>
									<path d="M8 10H16M8 14H13" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
								</svg>
							</div>
							<div class="dashboard-kpi-card__content">
								<span class="dashboard-kpi-card__title">{{ __('Total Pages') }}</span>
								<div class="dashboard-kpi-card__count">{{ $totalPages ?? 0 }}</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-12 col-sm-6 col-xl-3">
				<div class="card stat-card dashboard-kpi-card pm-kpi-card dashboard-kpi-card--active-svg border-0 h-100">
					<div class="card-body">
						<div class="dashboard-kpi-card__summary">
							<div class="dashboard-kpi-card__icon active-svg" aria-hidden="true">
								<svg viewBox="0 0 24 24" fill="none">
									<path d="M7 12.5L10.2 15.7L17 8.8" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/>
									<circle cx="12" cy="12" r="8" stroke="currentColor" stroke-width="1.7"/>
								</svg>
							</div>
							<div class="dashboard-kpi-card__content">
								<span class="dashboard-kpi-card__title">{{ __('Active Pages') }}</span>
								<div class="dashboard-kpi-card__count">{{ $activePages ?? 0 }}</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-12 col-sm-6 col-xl-3">
				<div class="card stat-card dashboard-kpi-card pm-kpi-card dashboard-kpi-card--merchant border-0 h-100">
					<div class="card-body">
						<div class="dashboard-kpi-card__summary">
							<div class="dashboard-kpi-card__icon merchant" aria-hidden="true">
								<svg viewBox="0 0 24 24" fill="none">
									<path d="M12 4L18 7.5V16.5L12 20L6 16.5V7.5L12 4Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
									<path d="M12 9V15M9 12H15" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
								</svg>
							</div>
							<div class="dashboard-kpi-card__content">
								<span class="dashboard-kpi-card__title">{{ __('Dynamic Pages') }}</span>
								<div class="dashboard-kpi-card__count">{{ $dynamicPages ?? 0 }}</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-12 col-sm-6 col-xl-3">
				<div class="card stat-card dashboard-kpi-card pm-kpi-card dashboard-kpi-card--info-svg border-0 h-100">
					<div class="card-body">
						<div class="dashboard-kpi-card__summary">
							<div class="dashboard-kpi-card__icon info-svg" aria-hidden="true">
								<svg viewBox="0 0 24 24" fill="none">
									<path d="M5 18L11 12L15 16L19 10" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
									<path d="M15 10H19V14" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
								</svg>
							</div>
							<div class="dashboard-kpi-card__content">
								<span class="dashboard-kpi-card__title">{{ __('Mapped Components') }}</span>
								<div class="dashboard-kpi-card__count">{{ $totalComponents ?? 0 }}</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="card border-0 pm-shell mb-4">
			<div class="pm-toolbar">
				<div>
					<h2 class="pm-toolbar__title mb-1">{{ __('Page Catalog') }}</h2>
					<p class="pm-toolbar__subtitle mb-0">{{ __('Search and manage page routes, publishing state, SEO linkage, and component composition.') }}</p>
				</div>
				<div class="pm-toolbar__actions">
					<label class="pm-search" for="pageManagerSearch">
						<span class="pm-search__icon" aria-hidden="true">
							<svg viewBox="0 0 24 24" fill="none">
								<circle cx="11" cy="11" r="6" stroke="currentColor" stroke-width="1.8"/>
								<path d="M16 16L20 20" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
							</svg>
						</span>
						<input type="search" id="pageManagerSearch" class="form-control pm-search__input" placeholder="{{ __('Search title, slug, type, or status') }}">
					</label>
					<div class="pm-toolbar__meta">
						<span>{{ __('Showing') }}</span>
						<strong data-page-results-count>{{ $totalPages ?? 0 }}</strong>
					</div>
				</div>
			</div>

			<div class="table-responsive">
				<table class="table align-middle mb-0 pm-table">
					<thead>
					<tr>
						<th>{{ __('Page') }}</th>
						<th>{{ __('Slug') }}</th>
						<th>{{ __('Structure') }}</th>
						<th>{{ __('Status') }}</th>
						<th class="text-start">{{ __('Action') }}</th>
					</tr>
					</thead>
					<tbody>
					@forelse($pages as $page)
						@php
							$componentCount = is_array($page->component_ids) ? count($page->component_ids) : 0;
							$isDynamic = $page->type === \App\Enums\PageType::Dynamic;
							$seoId = $page->is_home
							   ? \App\Models\SiteSeo::global()?->id
							   : $page->seo?->id;
					   
							$seoUrl = $seoId
							   ? route('admin.site-seo.edit', $seoId)
							   : route('admin.site-seo.create', ['page_id' => $page->id]);
							$pagePath = $page->slug === '/' ? '/' : '/' . ltrim($page->slug, '/');
							$initials = collect(preg_split('/\s+/', trim((string) $page->label)))
								->filter()
								->take(2)
								->map(fn (string $word) => mb_substr($word, 0, 1))
								->implode('');
							$initials = $initials !== '' ? mb_strtoupper($initials) : mb_strtoupper(mb_substr((string) $page->label, 0, 2));
							$searchTokens = strtolower(implode(' ', array_filter([
								$page->label,
								$page->slug,
								$page->is_active ? 'active' : 'inactive',
								$page->is_home ? 'home' : 'inner',
								$isDynamic ? 'dynamic' : 'protected',
							])));
						@endphp
						<tr data-page-row data-search="{{ $searchTokens }}">
							<td data-label="{{ __('Page') }}" class="pm-table__cell--page">
								<div class="pm-page-cell">
									<div class="pm-page-cell__icon pm-page-cell__icon--initials" aria-hidden="true">
										<span>{{ $initials }}</span>
									</div>
									<div class="pm-page-cell__content">
										<div class="pm-page-cell__title">{{ $page->label }}</div>
										<div class="pm-page-cell__meta">
											<span @class([
												'pm-meta-chip',
												'pm-meta-chip--success' => $page->is_breadcrumb,
												'pm-meta-chip--muted' => ! $page->is_breadcrumb,
											])>
												{{ $page->is_breadcrumb ? __('Breadcrumb On') : __('Breadcrumb Off') }}
											</span>
											@if($page->is_home)
												<span class="pm-meta-chip pm-meta-chip--info">{{ __('Home') }}</span>
											@endif
										</div>
									</div>
								</div>
							</td>
							
							<td data-label="{{ __('Slug') }}">
								<a href="{{ url($pagePath) }}" target="_blank" class="pm-slug-link">
									<span>{{ $pagePath }}</span>
									<span class="pm-slug-link__icon" aria-hidden="true">
										<svg viewBox="0 0 24 24" fill="none">
											<path d="M14 5H19V10" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
											<path d="M10 14L19 5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
											<path d="M18 13V17.5C18 18.3 17.3 19 16.5 19H6.5C5.7 19 5 18.3 5 17.5V7.5C5 6.7 5.7 6 6.5 6H11" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
										</svg>
									</span>
								</a>
							</td>
							
							<td data-label="{{ __('Structure') }}">
								<div class="pm-structure-stack">
									<span class="pm-structure-badge pm-structure-badge--info">
										{{ trans_choice('{1} :count Component|[2,*] :count Components', $componentCount, ['count' => $componentCount]) }}
									</span>
									<span @class([
										'pm-structure-badge',
										'pm-structure-badge--dynamic' => $isDynamic,
										'pm-structure-badge--protected' => ! $isDynamic,
									])>
										{{ $isDynamic ? __('Dynamic Page') : __('Core Page') }}
									</span>
								</div>
							</td>
							
							<td data-label="{{ __('Status') }}">
								<div class="pm-status-stack">
									<span @class([
										'pm-status-pill',
										'pm-status-pill--success' => $page->is_active,
										'pm-status-pill--danger' => ! $page->is_active,
									])>
										{{ $page->is_active ? __('Active') : __('Inactive') }}
									</span>
									<span @class([
										'pm-status-pill',
										'pm-status-pill--warning' => ! $isDynamic,
										'pm-status-pill--neutral' => $isDynamic,
									])>
										{{ $isDynamic ? __('Editable') : __('Protected') }}
									</span>
								</div>
							</td>
							
							<td data-label="{{ __('Action') }}" class="text-start">
								<div class="pm-action-group">
									@can('page-edit')
										<a href="{{ route('admin.page.site.edit', $page->id) }}" class="pm-action-btn pm-action-btn--primary">
											<span class="pm-action-btn__icon" aria-hidden="true">
												<svg viewBox="0 0 24 24" fill="none">
													<path d="M5 19H9L18 10C18.6 9.4 18.6 8.4 18 7.8L16.2 6C15.6 5.4 14.6 5.4 14 6L5 15V19Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
													<path d="M13 7L17 11" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
												</svg>
											</span>
											<span>{{ __('Manage') }}</span>
										</a>
									@endcan
									
									<a href="{{ $seoUrl }}" class="pm-action-btn pm-action-btn--seo">
										<span class="pm-action-btn__icon" aria-hidden="true">
											<svg viewBox="0 0 24 24" fill="none">
												<path d="M12 4L18 7.5V16.5L12 20L6 16.5V7.5L12 4Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
												<path d="M9.5 12H14.5M9.5 9H12.5M9.5 15H13" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
											</svg>
										</span>
										<span>{{ __('SEO') }}</span>
									</a>
									
									@if($isDynamic)
										@can('page-delete')
											<a href="javascript:void(0)" data-url="{{ route('admin.page.site.destroy', $page->id) }}" class="pm-action-btn pm-action-btn--danger delete">
												<span class="pm-action-btn__icon" aria-hidden="true">
													<svg viewBox="0 0 24 24" fill="none">
														<path d="M5 7H19" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
														<path d="M9 7V5.8C9 5.4 9.4 5 9.8 5H14.2C14.6 5 15 5.4 15 5.8V7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
														<path d="M8 10V17M12 10V17M16 10V17" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
														<path d="M7 7L7.7 18.1C7.8 18.6 8.2 19 8.8 19H15.2C15.8 19 16.2 18.6 16.3 18.1L17 7" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
													</svg>
												</span>
												<span>{{ __('Delete') }}</span>
											</a>
										@endcan
									@else
										<span class="pm-action-btn pm-action-btn--muted is-disabled">
											<span class="pm-action-btn__icon" aria-hidden="true">
												<svg viewBox="0 0 24 24" fill="none">
													<path d="M8 10V8.3C8 5.9 9.8 4 12 4C14.2 4 16 5.9 16 8.3V10" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
													<rect x="6" y="10" width="12" height="9" rx="2.5" stroke="currentColor" stroke-width="1.7"/>
												</svg>
											</span>
											<span>{{ __('Protected') }}</span>
										</span>
									@endif
								</div>
							</td>
						</tr>
					@empty
						<tr>
							<td colspan="5">
								<x-admin-not-found
									:title="__('No pages found')"
									:message="__('Create the first page to start mapping routes, sections, and SEO settings.')"
									icon="fa-file-alt"
								/>
							</td>
						</tr>
					@endforelse

					@if($pages->isNotEmpty())
						<tr class="d-none" data-page-empty>
							<td colspan="5">
								<div class="pm-empty-state pm-empty-state--search">
									<div class="pm-empty-state__icon" aria-hidden="true">
										<svg viewBox="0 0 24 24" fill="none">
											<circle cx="11" cy="11" r="6" stroke="currentColor" stroke-width="1.8"/>
											<path d="M16 16L20 20" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
											<path d="M9.5 9.5H12.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
										</svg>
									</div>
									<h3>{{ __('No matching pages') }}</h3>
									<p class="mb-0">{{ __('Try a different keyword for the title, slug, type, or publishing state.') }}</p>
								</div>
							</td>
						</tr>
					@endif
					</tbody>
				</table>
			</div>
		</div>
	</div>
@endsection
