@php
	$isHome = ($page->slug ?? null) === '/';
	$pageComponentIds = is_array($page->component_ids ?? null) ? $page->component_ids : [];
@endphp

<div class="card border-0 shadow-sm mb-0 pc-builder-card pc-builder-card--form">
	<div class="card-body pc-builder-card__body">
		<form method="POST" action="{{ route('admin.page.site.update', $page->id) }}" enctype="multipart/form-data">
			@csrf
			@method('PUT')

			<div class="pc-section-head">
				<div>
					<span class="pc-section-head__eyebrow">{{ __('Page Setup') }}</span>
					<h2 class="pc-section-head__title">{{ __('Page Elements') }}</h2>
					<p class="pc-section-head__text">{{ __('Arrange selected components, update route metadata, and keep the page state in sync.') }}</p>
				</div>

				<div class="pc-section-head__actions">
					@if (! $page->is_home)
						<div class="pc-toggle-stack">
							<div class="pc-toggle-card">
								<div class="form-check form-switch d-flex align-items-center justify-content-between gap-2">
									<label class="form-check-label" for="is_breadcrumb">{{ __('Page Breadcrumb') }}</label>
									<input class="form-check-input coevs-switch" type="checkbox" value="1" name="is_breadcrumb"
									       id="is_breadcrumb" {{ old('is_breadcrumb', $page->is_breadcrumb) ? 'checked' : '' }}>
								</div>
							</div>

							<div class="pc-toggle-card">
								<div class="form-check form-switch d-flex align-items-center justify-content-between gap-2">
									<label class="form-check-label" for="page_is_active">{{ __('Page Status') }}</label>
									<input class="form-check-input coevs-switch" type="checkbox" value="1" name="is_active"
									       id="page_is_active" {{ old('is_active', $page->is_active) ? 'checked' : '' }}>
								</div>
							</div>
						</div>
					@endif

					<button type="submit" class="btn btn-primary pc-section-head__save">
						<x-icon name="check" class="me-1" height="18" width="18"/>
						<span>{{ __('Save Changes') }}</span>
					</button>
				</div>
			</div>

			<div class="sortable-list drop-here pc-drop-zone mt-3 mb-3" id="pageComponent">
				<span class="text-muted drop-text pc-drop-zone__placeholder {{ count($pageComponentIds) > 0 ? 'd-none' : '' }}">
					<span class="pc-drop-zone__icon" aria-hidden="true">
						<svg viewBox="0 0 24 24" fill="none">
							<path d="M12 16V7" stroke="currentColor" stroke-width="1.9" stroke-linecap="round"/>
							<path d="M8.5 10.5L12 7L15.5 10.5" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/>
							<path d="M5 18.5H19" stroke="currentColor" stroke-width="1.9" stroke-linecap="round"/>
						</svg>
					</span>
					<span>
						<strong>{{ __('Drop Component Here') }}</strong>
						<small>{{ __('Arrange sections from top to bottom in the exact order this page should render.') }}</small>
					</span>
				</span>

				@foreach($pageComponentIds as $id)
					@php
						$component = $components->firstWhere('id', $id);
					@endphp
					@if($component)
						<div class="item pc-component-item" draggable="true"
						     data-index="{{ $component->id }}"
						     data-name="{{ strtolower($component->component_name) }}">
							<input type="hidden" name="component[]" value="{{ $component->id }}">

							<div class="details pc-component-item__details">
								@include('backend.page_component.partials._component_icon', [
									'component' => $component,
									'wrapperClass' => 'component-admin-thumb--compact',
								])
								<div class="pc-component-item__meta">
									<span class="pc-component-item__title text-capitalize">{{ $component->component_name }}</span>
									<span class="pc-component-item__subtitle">{{ __('Mapped to this page') }}</span>
								</div>
							</div>

							<div class="pc-component-item__actions">
								@unless($component->is_protected)
									<a href="{{ route('admin.page.component.edit', $component->id) }}" target="_blank"
									   class="component-manage pc-icon-btn modal-tooltip text-decoration-none"
									   title="{{ __('Manage Component') }}" data-coreui-toggle="tooltip">
										<svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
											<rect x="4" y="6" width="16" height="12" rx="2.5" stroke="currentColor" stroke-width="1.8"/>
											<path d="M9 9.5H16" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
											<path d="M9 14.5H14" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
											<circle cx="7.2" cy="9.5" r="1.1" fill="currentColor"/>
											<circle cx="16.8" cy="14.5" r="1.1" fill="currentColor"/>
										</svg>
									</a>
								@else
									<span class="pc-icon-btn pc-icon-btn--muted" title="{{ __('Protected Component') }}">
										<svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
											<path d="M8 10V8.4C8 6 9.8 4 12 4C14.2 4 16 6 16 8.4V10" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
											<rect x="6" y="10" width="12" height="9" rx="2.5" stroke="currentColor" stroke-width="1.7"/>
										</svg>
										<span>{{ __('Protected') }}</span>
									</span>
								@endunless

								<span class="manage-drag pc-icon-btn pc-icon-btn--danger" title="{{ __('Remove from Page') }}" data-coreui-toggle="tooltip" role="button">
									<span class="toggle-icon" aria-hidden="true">
										<svg viewBox="0 0 24 24" fill="none">
											<path d="M5 12H19" stroke="currentColor" stroke-width="1.9" stroke-linecap="round"/>
										</svg>
									</span>
								</span>
							</div>
						</div>
					@endif
				@endforeach
			</div>

			<div class="pc-form-section">
				<div class="pc-form-section__header mb-3">
					<div>
						<h3 class="pc-form-section__title">{{ __('Page Identity') }}</h3>
						<p class="pc-form-section__text">{{ __('Update the public title in each locale and keep the default locale connected to the slug when editable.') }}</p>
					</div>
				</div>

				<ul class="nav nav-tabs pc-locale-tabs mb-3" role="tablist">
					@foreach($locales as $locale => $label)
						<li class="nav-item" role="presentation">
							<button class="nav-link {{ $loop->first ? 'active' : '' }}"
							        id="page-title-tab-{{ $locale }}"
							        data-coreui-toggle="tab"
							        data-coreui-target="#page-title-{{ $locale }}"
							        type="button" role="tab">
								{{ $label }}
							</button>
						</li>
					@endforeach
				</ul>

				<div class="tab-content pc-locale-panels">
					@foreach($locales as $locale => $label)
						<div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="page-title-{{ $locale }}" role="tabpanel">
							<div class="mb-3">
								<label for="name_{{ $locale }}" class="form-label pc-form-label">{{ __('Page Title') }}
									<small class="text-muted text-uppercase">({{ $locale }})</small>
								</label>
								<div class="pc-field-shell">
									<input type="text"
									       id="page_title_{{ $locale }}"
									       name="page_title[{{ $locale }}]"
									       class="form-control pc-form-control {{ $locale == app()->getDefaultLocale() ? 'title-to-slug' : '' }}"
									       {{ $locale == app()->getDefaultLocale() ? 'data-slug-target=#page_slug' : '' }}
									       value="{{ old("page_title.$locale", $page->title[$locale] ?? '') }}"
									       placeholder="{{ __('Enter page title for :lang', ['lang' => strtoupper($locale)]) }}">
								</div>
							</div>
						</div>
					@endforeach
				</div>
			</div>

			<div class="pc-form-section">
				<div class="pc-form-section__header mb-3">
					<div>
						<h3 class="pc-form-section__title">{{ __('Route and Presentation') }}</h3>
						<p class="pc-form-section__text">{{ __('Adjust the route slug and breadcrumb presentation before saving this page update.') }}</p>
					</div>
				</div>

				<div class="row g-3">
					<div class="col-md-12">
						<label for="page_slug" class="form-label pc-form-label">
							{{ __('Page Slug') }}
							@if($page->is_protected)
								<span class="text-muted small d-inline-flex align-items-center gap-1">
									<svg viewBox="0 0 24 24" fill="none" width="14" height="14" aria-hidden="true">
										<path d="M8 10V8.4C8 6 9.8 4 12 4C14.2 4 16 6 16 8.4V10" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
										<rect x="6" y="10" width="12" height="9" rx="2.5" stroke="currentColor" stroke-width="1.7"/>
									</svg>
									{{ __('Protected') }}
								</span>
							@endif
						</label>
						<div class="pc-field-shell">
							<input
								class="form-control pc-form-control {{ $page->is_protected ? 'bg-light text-muted' : 'page_slug' }}"
								name="page_slug"
								id="page_slug"
								type="text"
								value="{{ old('page_slug', $page->slug) }}"
								{{ $page->is_protected ? 'readonly' : '' }}
								required
							>
						</div>
					</div>

					@if(! $isHome)
						<div class="col-md-12">
							<label for="breadcrumb" class="form-label pc-form-label">
								{{ __('Breadcrumb') }}
								<span class="text-muted small">({{ __('Optional') }})</span>
								<span class="pc-form-hint__icon pc-form-hint"
								      data-coreui-toggle="tooltip"
								      data-coreui-placement="top"
								      title="{{ __('If no image is uploaded, the default breadcrumb background from general settings will be used.') }}">
									<svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
										<circle cx="12" cy="12" r="8" stroke="currentColor" stroke-width="1.8"/>
										<path d="M12 10.25V15" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
										<path d="M12 8.25H12.01" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/>
									</svg>
								</span>
							</label>

							<x-img :old="old('breadcrumb', $page->breadcrumb)" name="breadcrumb" ref="coevs-remove-img"/>
						</div>
					@endif
				</div>
			</div>

		</form>
	</div>
</div>
