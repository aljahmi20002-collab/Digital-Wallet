@php
	$availableComponentCount = $components->reject(fn ($component) => is_array($selected ?? null) && in_array($component->id, $selected))->count();
@endphp

<div class="card border-0 shadow-sm mb-0 pc-builder-card pc-builder-card--catalog">
	<div class="card-body pc-builder-card__body">
		<div class="pc-section-head">
			<div>
				<span class="pc-section-head__eyebrow">{{ __('Component Library') }}</span>
				<div class="pc-section-head__title-row">
					<h2 class="pc-section-head__title">{{ __('Available Components') }}</h2>
					<span class="pc-counter-chip">{{ $availableComponentCount }} {{ __('Ready') }}</span>
				</div>
				<p class="pc-section-head__text">{{ __('Search blocks, inspect their status, and send them into the page builder with click or drag.') }}</p>
			</div>
		</div>

		<div class="pc-catalog-toolbar">
			<label class="pc-search" for="componentSearch">
				<span class="pc-search__icon" aria-hidden="true">
					<svg viewBox="0 0 24 24" fill="none">
						<circle cx="11" cy="11" r="6" stroke="currentColor" stroke-width="1.8"/>
						<path d="M16 16L20 20" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
					</svg>
				</span>
				<input type="text" class="form-control pc-search__input" id="componentSearch" placeholder="{{ __('Search Component') }}">
			</label>
			<a href="{{ route('admin.page.component.index') }}" class="pc-inline-btn">
				<span class="pc-inline-btn__icon" aria-hidden="true">
					<svg viewBox="0 0 24 24" fill="none">
						<rect x="4" y="5" width="7" height="6" rx="1.5" stroke="currentColor" stroke-width="1.7"/>
						<rect x="13" y="5" width="7" height="6" rx="1.5" stroke="currentColor" stroke-width="1.7"/>
						<rect x="4" y="13" width="7" height="6" rx="1.5" stroke="currentColor" stroke-width="1.7"/>
						<rect x="13" y="13" width="7" height="6" rx="1.5" stroke="currentColor" stroke-width="1.7"/>
					</svg>
				</span>
				<span>{{ __('Manage') }}</span>
			</a>
		</div>

		<div class="sortable-list pc-component-list mt-3" id="componentList">
			<button class="btn btn-info w-100 loading" type="button" disabled hidden>
				<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
				{{ __('Loading') }}...
			</button>

			@forelse($components as $component)
				@php
					$isSelected = is_array($selected ?? null) && in_array($component->id, $selected);
				@endphp
				@if(!$isSelected)
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
								<span class="pc-component-item__subtitle">{{ __('Drag or tap add to builder') }}</span>
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
							@endif

							<span class="manage-drag pc-icon-btn pc-icon-btn--success" title="{{ __('Add to Page') }}" data-coreui-toggle="tooltip" role="button">
								<span class="toggle-icon" aria-hidden="true">
									<svg viewBox="0 0 24 24" fill="none">
										<path d="M12 5V19M5 12H19" stroke="currentColor" stroke-width="1.9" stroke-linecap="round"/>
									</svg>
								</span>
							</span>
						</div>
					</div>
				@endif
			@empty
				<h4 class="text-center text-muted h5 component-empty-text">{{ __('No Component Available') }}</h4>
			@endforelse
		</div>
	</div>
</div>
