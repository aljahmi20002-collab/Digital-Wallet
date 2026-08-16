@extends('backend.layouts.app')
@section('title', __('Edit Page'))

@push('styles')
	<link rel="stylesheet" href="{{ asset('backend/css/page-create-admin.css?v='.config('app.version').'-'.filemtime(public_path('backend/css/page-create-admin.css'))) }}">
	<link rel="stylesheet" href="{{ asset('backend/css/page-edit-admin.css?v='.config('app.version').'-'.filemtime(public_path('backend/css/page-edit-admin.css'))) }}">
@endpush

@section('content')
	@php
		// Controller PageController::edit passes $totalComponents, $totalLocales,
		// $isProtected explicitly. The ?? fallbacks fire only if a stale compiled
		// view or a partial-payload controller forgets to send them, so the
		// hero degrades to "0 / 0 / Editable" instead of throwing "Undefined variable".
		$componentIds    = is_array($page->component_ids ?? null) ? $page->component_ids : [];
		$totalComponents = $totalComponents ?? count($componentIds);
		$totalLocales    = $totalLocales    ?? (is_countable($locales ?? null) ? count($locales) : 0);
		$isProtected     = $isProtected     ?? (bool) ($page->is_protected ?? false);
	@endphp

	<div class="page-edit-admin page-create-admin mb-3">
		<section class="card border-0 pe-hero mb-3">
			<div class="card-body p-3">
				<div class="row g-3 align-items-center">
					<div class="col-xl-8">
						<div class="pe-hero__main">
							<div class="pe-hero__head">
								<div class="pe-hero__actions">
									<a href="{{ route('admin.page.component.index') }}" class="btn pe-btn pe-btn--ghost">
										<span class="pe-btn__icon" aria-hidden="true">
											<svg viewBox="0 0 24 24" fill="none">
												<rect x="4" y="5" width="7" height="6" rx="1.5" stroke="currentColor" stroke-width="1.7"/>
												<rect x="13" y="5" width="7" height="6" rx="1.5" stroke="currentColor" stroke-width="1.7"/>
												<rect x="4" y="13" width="7" height="6" rx="1.5" stroke="currentColor" stroke-width="1.7"/>
												<rect x="13" y="13" width="7" height="6" rx="1.5" stroke="currentColor" stroke-width="1.7"/>
											</svg>
										</span>
										<span>{{ __('Manage Components') }}</span>
									</a>
									<a href="{{ route('admin.page.site.index') }}" class="btn pe-btn pe-btn--back" aria-label="{{ __('Back') }}">
										<span class="pe-btn__icon pe-btn__icon--back" aria-hidden="true">
											<x-icon name="back" width="14" height="14" class="d-block"/>
										</span>
									</a>
								</div>
							</div>
							<div class="pe-hero__content">
								<div class="pe-hero__copy">
									<h1 class="pe-hero__title mb-0">{{ __('Edit Page') }}</h1>
									<p class="pe-hero__subtitle mb-0">{{ __('Update route details and block order from one compact workspace.') }}</p>
								</div>
								<div class="pe-hero__metrics">
									<span class="pe-hero-pill"><strong>{{ $totalComponents ?? 0 }}</strong><span>{{ __('Mapped Blocks') }}</span></span>
									<span class="pe-hero-pill"><strong>{{ $totalLocales ?? 0 }}</strong><span>{{ __('Locale Tabs') }}</span></span>
									<span class="pe-hero-pill"><strong>{{ ($isProtected ?? false) ? __('Locked') : __('Editable') }}</strong><span>{{ __('Page State') }}</span></span>
								</div>
							</div>
						</div>
					</div>
					<div class="col-xl-4">
						<div class="pe-hero__aside">
							<div class="pe-hero-panel">
								<div class="pe-hero-panel__head">
									<div>
										<span class="pe-hero-panel__label">{{ __('Page Status') }}</span>
										<h2 class="pe-hero-panel__value mb-0">{{ $page->label }}</h2>
									</div>
									<span class="pe-hero-panel__badge">{{ ($isProtected ?? false) ? __('Protected') : __('Live Edit') }}</span>
								</div>
								<p class="pe-hero-panel__text mb-0">{{ __('Review the current page state before saving changes.') }}</p>
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>

		<div class="row g-4">
			<div class="col-12 col-xl-5">
				@include('backend.page.partials._components_list', ['components' => $components, 'selected' => $page->component_ids])
			</div>
			<div class="col-12 col-xl-7">
				@include('backend.page.partials._components_form_edit', ['page' => $page])
			</div>
		</div>
	</div>
@endsection

@push('scripts')
	@include('backend.page.partials._component_script')
@endpush
