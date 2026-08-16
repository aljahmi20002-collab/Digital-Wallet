@extends('backend.layouts.app')
@section('title', __('Create Page'))

@push('styles')
	<link rel="stylesheet" href="{{ asset('backend/css/page-create-admin.css?v='.config('app.version').'-'.filemtime(public_path('backend/css/page-create-admin.css'))) }}">
@endpush

@section('content')
	@php
		// Controller PageController::create passes $totalComponents, $totalLocales,
		// and $defaultLocale explicitly. The ?? fallbacks fire only if a stale
		// compiled view or a degraded controller forgets to send them, so the
		// hero degrades to "0 / 0 / EN" instead of throwing "Undefined variable".
		$totalComponents = $totalComponents ?? (isset($components) ? $components->count() : 0);
		$totalLocales    = $totalLocales    ?? (is_countable($locales ?? null) ? count($locales) : 0);
		$defaultLocale   = $defaultLocale   ?? strtoupper((string) app()->getDefaultLocale());
	@endphp

	<div class="page-create-admin mb-3">
		<section class="card border-0 pc-hero mb-3">
			<div class="card-body p-3">
				<div class="row g-3 align-items-stretch">
					<div class="col-xl-8">
						<div class="pc-hero__main">
							<div class="pc-hero__head">
								<div class="pc-hero__actions">
									<a href="{{ route('admin.page.component.index') }}" class="btn pc-btn pc-btn--ghost">
										<span class="pc-btn__icon" aria-hidden="true">
											<svg viewBox="0 0 24 24" fill="none">
												<rect x="4" y="5" width="7" height="6" rx="1.5" stroke="currentColor" stroke-width="1.7"/>
												<rect x="13" y="5" width="7" height="6" rx="1.5" stroke="currentColor" stroke-width="1.7"/>
												<rect x="4" y="13" width="7" height="6" rx="1.5" stroke="currentColor" stroke-width="1.7"/>
												<rect x="13" y="13" width="7" height="6" rx="1.5" stroke="currentColor" stroke-width="1.7"/>
											</svg>
										</span>
										<span>{{ __('Manage Components') }}</span>
									</a>
									<a href="{{ route('admin.page.site.index') }}" class="btn pc-btn pc-btn--back" aria-label="{{ __('Back') }}">
										<span class="pc-btn__icon pc-btn__icon--back" aria-hidden="true">
											<x-icon name="back" width="14" height="14" class="d-block"/>
										</span>
									</a>
								</div>
							</div>
							<div class="pc-hero__content">
								<div class="pc-hero__copy">
									<h1 class="pc-hero__title mb-0">{{ __('Create New Page') }}</h1>
									<p class="pc-hero__subtitle mb-0">{{ __('Build a page route and map components from one compact workspace.') }}</p>
								</div>
								<div class="pc-hero__metrics">
									<span class="pc-hero-pill"><strong>{{ $totalComponents ?? 0 }}</strong><span>{{ __('Components Ready') }}</span></span>
									<span class="pc-hero-pill"><strong>{{ $totalLocales ?? 0 }}</strong><span>{{ __('Locale Tabs') }}</span></span>
									<span class="pc-hero-pill"><strong>{{ $defaultLocale ?? strtoupper(app()->getDefaultLocale()) }}</strong><span>{{ __('Auto Slug Source') }}</span></span>
								</div>
							</div>
						</div>
					</div>
					<div class="col-xl-4">
						<div class="pc-hero__aside">
							<div class="pc-hero-panel">
							<div class="pc-hero-panel__head">
								<div>
									<span class="pc-hero-panel__label">{{ __('Build Status') }}</span>
									<h2 class="pc-hero-panel__value mb-0">{{ __('Ready') }}</h2>
								</div>
								<span class="pc-hero-panel__badge">{{ __('Builder') }}</span>
							</div>
							<p class="pc-hero-panel__text mb-0">{{ __('Pick blocks, arrange them, and save the route.') }}</p>
							<div class="pc-hero-panel__grid">
								<div class="pc-hero-panel__metric">
									<span>{{ __('Step 1') }}</span>
									<strong>{{ __('Pick Blocks') }}</strong>
								</div>
								<div class="pc-hero-panel__metric">
									<span>{{ __('Step 2') }}</span>
									<strong>{{ __('Save Route') }}</strong>
								</div>
							</div>
						</div>
						</div>
					</div>
				</div>
			</div>
		</section>

		<div class="row g-3 align-items-start">
			<div class="col-12 col-xl-5">
				@include('backend.page.partials._components_list')
			</div>
			<div class="col-12 col-xl-7">
				@include('backend.page.partials._components_form')
			</div>
		</div>
	</div>
@endsection

@push('scripts')
	@include('backend.page.partials._component_script')
@endpush
