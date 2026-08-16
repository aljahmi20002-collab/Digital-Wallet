<div class="card border-0 shadow-sm mb-0 pc-builder-card pc-builder-card--form">
	<div class="card-body pc-builder-card__body">
		<form method="POST" action="{{ route('admin.page.site.store') }}" enctype="multipart/form-data">
			@csrf

			<div class="pc-section-head">
				<div>
					<span class="pc-section-head__eyebrow">{{ __('Page Setup') }}</span>
					<h2 class="pc-section-head__title">{{ __('Page Elements') }}</h2>
					<p class="pc-section-head__text">{{ __('Arrange selected components, define route metadata, and keep the publishing flow compact and clean.') }}</p>
				</div>

				<div class="pc-section-head__actions">
					<div class="pc-toggle-stack">
						<div class="pc-toggle-card">
							<div class="form-check form-switch d-flex align-items-center justify-content-between gap-2">
								<label class="form-check-label" for="is_breadcrumb">{{ __('Page Breadcrumb') }}</label>
								<input class="form-check-input coevs-switch" type="checkbox" value="1" name="is_breadcrumb" id="is_breadcrumb">
							</div>
						</div>

						<div class="pc-toggle-card">
							<div class="form-check form-switch d-flex align-items-center justify-content-between gap-2">
								<label class="form-check-label" for="page_is_active">{{ __('Page Status') }}</label>
								<input class="form-check-input coevs-switch" type="checkbox" value="1" name="is_active" id="page_is_active" checked>
							</div>
						</div>
					</div>

					<button type="submit" class="btn btn-primary pc-section-head__save">
						<x-icon name="check" class="me-1" height="18" width="18"/>
						<span>{{ __('Create Page') }}</span>
					</button>
				</div>
			</div>

			<div class="sortable-list drop-here pc-drop-zone mt-3 mb-3" id="pageComponent">
                <span class="text-muted drop-text pc-drop-zone__placeholder">
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
			</div>

			<div class="pc-form-section">
				<div class="pc-form-section__header mb-3">
					<div>
						<h3 class="pc-form-section__title">{{ __('Page Identity') }}</h3>
						<p class="pc-form-section__text">{{ __('Set the public title in each locale and keep the slug synchronized from the default language input.') }}</p>
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
									       value="{{ old('page_title.' . $locale) }}"
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
						<p class="pc-form-section__text">{{ __('Finalize the route slug and optionally attach a breadcrumb cover for the page header.') }}</p>
					</div>
				</div>

				<div class="row g-3">
					<div class="col-md-12">
						<label for="page_slug" class="form-label pc-form-label">{{ __('Page Slug') }}</label>
						<div class="pc-field-shell">
							<input class="form-control pc-form-control page_slug" name="page_slug" id="page_slug" type="text"
							       value="{{ old('page_slug') }}" required>
						</div>
					</div>

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

						<x-img :old="old('breadcrumb')" name="breadcrumb"/>
					</div>
				</div>
			</div>

		</form>
	</div>
</div>
