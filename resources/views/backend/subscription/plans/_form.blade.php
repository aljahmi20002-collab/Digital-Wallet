<div class="subscription-plan-form">
	<div class="row g-4 align-items-start">
		<div class="col-12">
			<div class="sa-card mb-4 plan-identity-card">
				<div class="sa-card__head">
					<div>
						<h2 class="sa-card__title">@lang('Plan Details')</h2>
						<p class="sa-card__subtitle mb-0">@lang('Name, slug, and optional description shown to users.')</p>
					</div>
				</div>
				<div class="sa-card__body">
					<div class="row g-3">
						<div class="col-lg-4 col-md-6">
							<label class="form-label">@lang('Plan Name') <span class="text-danger">*</span></label>
							<input type="text" name="name" id="plan-name"
							       class="form-control @error('name') is-invalid @enderror"
							       value="{{ old('name', $plan->name) }}" required>
							@error('name')
							<div class="invalid-feedback">{{ $message }}</div>
							@enderror
						</div>
						<div class="col-lg-4 col-md-6">
							<label class="form-label">@lang('Slug') <span class="text-danger">*</span></label>
							<input type="text" name="slug" id="plan-slug"
							       class="form-control @error('slug') is-invalid @enderror"
							       value="{{ old('slug', $plan->slug) }}" required>
							@error('slug')
							<div class="invalid-feedback">{{ $message }}</div>
							@enderror
							<div class="form-text">@lang('Lowercase letters, numbers and hyphens only.')</div>
						</div>
						<div class="col-lg-4 col-md-6">
							<label class="form-label">@lang('Plan Badge')</label>
							<input type="text" name="plan_badge"
							       class="form-control @error('plan_badge') is-invalid @enderror"
							       placeholder="{{ __('e.g. Popular, Recommended') }}"
							       value="{{ old('plan_badge', $plan->plan_badge) }}" maxlength="50">
							@error('plan_badge')
							<div class="invalid-feedback">{{ $message }}</div>
							@enderror
						</div>
						<div class="col-12">
							<div class="plan-form-tip">
								<div class="plan-form-tip__label">@lang('Admin Note')</div>
								<p class="mb-0">@lang('Use clear naming and a stable slug so feature mappings remain consistent across all plans and frontend cards.')</p>
							</div>
						</div>
						<div class="col-12">
							<label class="form-label">@lang('Description')</label>
							<textarea name="description" rows="3"
							          class="form-control @error('description') is-invalid @enderror">{{ old('description', $plan->description) }}</textarea>
							@error('description')
							<div class="invalid-feedback">{{ $message }}</div>
							@enderror
						</div>
					</div>
				</div>
			</div>
		</div>
		
		<div class="col-12">
			<div class="sa-card mb-4">
				<div class="sa-card__head">
					<div>
						<h2 class="sa-card__title">@lang('Billing Cycles')</h2>
						<p class="sa-card__subtitle mb-0">@lang('Set the monthly base price. Add an optional discount for half-yearly and yearly cycles.')</p>
					</div>
				</div>
				<div class="sa-card__body">
					<div class="row g-3 billing-cycle-grid">
						<div class="col-xl-4">
							<div class="billing-cycle-block billing-cycle-block--panel h-100">
								<div class="billing-cycle-block__header">
									<div class="billing-cycle-block__icon billing-cycle-block__icon--monthly">
										<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
											<rect x="3" y="4" width="18" height="18" rx="2"/>
											<line x1="16" y1="2" x2="16" y2="6"/>
											<line x1="8" y1="2" x2="8" y2="6"/>
											<line x1="3" y1="10" x2="21" y2="10"/>
										</svg>
									</div>
									<div>
										<div class="billing-cycle-block__title">@lang('Monthly')</div>
										<div class="billing-cycle-block__sub">@lang('Base price - used to calculate all cycles')</div>
									</div>
								</div>
								<div class="row g-3 mt-1">
									<div class="col-12">
										<label class="form-label">@lang('Price') <span class="text-danger">*</span></label>
										<div class="input-group">
											<span class="input-group-text fw-semibold">{{ siteCurrency('code') }}</span>
											<input type="number" name="price" id="base-price"
											       step="0.01" min="0"
											       class="form-control @error('price') is-invalid @enderror"
											       value="{{ $monthlyPrice }}"
											       placeholder="0.00" required>
											@error('price')
											<div class="invalid-feedback">{{ $message }}</div>
											@enderror
										</div>
										<div class="form-text">@lang('Set 0 for a free plan.')</div>
									</div>
								</div>
							</div>
						</div>
						
						<div class="col-xl-4">
							<div class="billing-cycle-block billing-cycle-block--panel h-100">
								<div class="billing-cycle-block__header">
									<div class="billing-cycle-block__icon billing-cycle-block__icon--half">
										<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
											<circle cx="12" cy="12" r="10"/>
											<polyline points="12 6 12 12 16 14"/>
										</svg>
									</div>
									<div>
										<div class="billing-cycle-block__title">@lang('Half Yearly') <span class="billing-cycle-block__months">@lang('(6 months)')</span></div>
										<div class="billing-cycle-block__sub"
										     id="half-yearly-preview">{{ $halfYearlyDiscount ? __(':disc% off - :price :code', ['disc' => $halfYearlyDiscount, 'price' => number_format($monthlyPrice * 6 * (1 - $halfYearlyDiscount / 100), 2), 'code' => siteCurrency('code')]) : __('No discount - :price :code', ['price' => number_format($monthlyPrice * 6, 2), 'code' => siteCurrency('code')]) }}</div>
									</div>
								</div>
								<div class="row g-3 mt-1">
									<div class="col-12">
										<label class="form-label">@lang('Discount') <span class="text-muted fw-normal">@lang('(optional)')</span></label>
										<div class="input-group">
											<input type="number" name="half_yearly_discount" id="half-yearly-discount"
											       min="1" max="99"
											       class="form-control @error('half_yearly_discount') is-invalid @enderror"
											       value="{{ $halfYearlyDiscount }}"
											       placeholder="@lang('e.g. 10')">
											<span class="input-group-text">%</span>
											@error('half_yearly_discount')
											<div class="invalid-feedback">{{ $message }}</div>
											@enderror
										</div>
									</div>
								</div>
							</div>
						</div>
						
						<div class="col-xl-4">
							<div class="billing-cycle-block billing-cycle-block--panel h-100">
								<div class="billing-cycle-block__header">
									<div class="billing-cycle-block__icon billing-cycle-block__icon--yearly">
										<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
											<path d="M12 2L2 7l10 5 10-5-10-5z"/>
											<path d="M2 17l10 5 10-5"/>
											<path d="M2 12l10 5 10-5"/>
										</svg>
									</div>
									<div>
										<div class="billing-cycle-block__title">@lang('Yearly') <span class="billing-cycle-block__months">@lang('(12 months)')</span></div>
										<div class="billing-cycle-block__sub"
										     id="yearly-preview">{{ $yearlyDiscount ? __(':disc% off - :price :code', ['disc' => $yearlyDiscount, 'price' => number_format($monthlyPrice * 12 * (1 - $yearlyDiscount / 100), 2), 'code' => siteCurrency('code')]) : __('No discount - :price :code', ['price' => number_format($monthlyPrice * 12, 2), 'code' => siteCurrency('code')]) }}</div>
									</div>
								</div>
								<div class="row g-3 mt-1">
									<div class="col-12">
										<label class="form-label">@lang('Discount') <span class="text-muted fw-normal">@lang('(optional)')</span></label>
										<div class="input-group">
											<input type="number" name="yearly_discount" id="yearly-discount"
											       min="1" max="99"
											       class="form-control @error('yearly_discount') is-invalid @enderror"
											       value="{{ $yearlyDiscount }}"
											       placeholder="@lang('e.g. 20')">
											<span class="input-group-text">%</span>
											@error('yearly_discount')
											<div class="invalid-feedback">{{ $message }}</div>
											@enderror
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		
		<div class="col-12">
            @php $features = (array) old('features', $plan->features->toArray() ?? []); @endphp
			<div class="sa-card plan-features-card">
                <div class="sa-card__head plan-features-card__head">
                    <div class="plan-features-card__title-wrap">
                        <div>
                            <h2 class="sa-card__title">@lang('Features & Limits')</h2>
                            <p class="sa-card__subtitle mb-0">@lang('Define what subscribers get. Use consistent feature keys across all plans.')</p>
                        </div>
                        <span class="plan-features-card__count" id="feature-count-badge">{{ count($features) }}</span>
                    </div>
					<button type="button" id="add-feature-btn"
					        class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1 plan-features-card__add-btn">
						<x-icon name="add" height="14" width="14"/>
						@lang('Add Feature')
					</button>
				</div>
                <div class="sa-card__body">
                    <div class="plan-features-card__intro mb-4">
                        <div class="plan-features-card__intro-copy">
                            <div class="plan-features-card__eyebrow">@lang('Feature Builder')</div>
                            <p class="mb-0">@lang('Use toggle for access, limit for display-only caps, and quota for enforced numeric allowances. Drag rows to control how features appear on the plan card.')</p>
                        </div>
                    </div>

                    <div class="feat-type-guide mb-4">
                        <div class="feat-type-guide__title">
                            <div class="feat-type-guide__title-icon"><i class="fas fa-info-circle"></i></div>
                            <div>
                                @lang('Feature Types')
                                <div class="feat-type-guide__subtitle">@lang('Use the right type for stable plan behavior and clearer cards.')</div>
                            </div>
                        </div>
                        <div class="feat-type-guide__list">
                            <div class="feat-type-guide__item">
                                <span class="feat-type-guide__badge feat-type-guide__badge--toggle">@lang('Toggle')</span>
								<span class="feat-type-guide__desc">@lang('On/Off switch for a feature (e.g. Send Money, P2P Access). Submits') <code>enabled</code> @lang('or') <code>disabled</code>.</span>
							</div>
							<div class="feat-type-guide__item">
								<span class="feat-type-guide__badge feat-type-guide__badge--limit">@lang('Limit')</span>
								<span class="feat-type-guide__desc">@lang('A display label shown as a pill on the plan card (e.g.') <code>$500</code>, <code>unlimited</code>, <code>Priority</code>). @lang('Not enforced by the system.')</span>
							</div>
							<div class="feat-type-guide__item">
								<span class="feat-type-guide__badge feat-type-guide__badge--quota">@lang('Quota')</span>
								<span class="feat-type-guide__desc">@lang('A numeric allowance enforced by the system (e.g.') <code>50</code> @lang('transactions/month). The system checks this value on every action.')</span>
							</div>
						</div>
					</div>
					
                    <div id="features-container" class="plan-features-card__rows">
                        @foreach($features as $i => $feature)
                            @include('backend.subscription.plans._feature_row', ['i' => $i, 'feature' => $feature])
                        @endforeach
                    </div>
					<div id="no-features-msg" @class(['plan-features-card__empty text-center', 'd-none' => count($features) > 0])>
                        <div class="plan-features-card__empty-icon">
                            <i class="fas fa-sliders-h"></i>
                        </div>
                        <div class="plan-features-card__empty-title">@lang('No features added yet')</div>
                        <p class="mb-0">@lang('Click "Add Feature" to define plan limits and capabilities.')</p>
					</div>
				</div>
			</div>
		</div>
		
		<div class="col-12">
			<div class="sa-card plan-settings-shell">
				<div class="sa-card__head plan-settings-shell__head">
					<div>
						<h2 class="sa-card__title">@lang('Settings')</h2>
						<p class="sa-card__subtitle mb-0">@lang('Badge, visibility, renewal defaults, and publish state.')</p>
					</div>
				</div>
				<div class="sa-card__body">
					<div class="plan-settings-grid">
						<div class="plan-settings-grid__field">
							<div class="plan-settings-switches">
								<input type="hidden" name="is_featured" value="0">
								<div class="sa-switch sa-switch--toggle plan-settings-switch">
									<span class="sa-switch__label">@lang('Featured Plan')</span>
									<div class="form-check form-switch sa-admin-switch">
										<input class="form-check-input" type="checkbox" role="switch"
										       name="is_featured" id="is_featured" value="1"
											@checked(old('is_featured', $plan->is_featured ?? false))>
									</div>
								</div>
								
								<input type="hidden" name="auto_renew_default" value="0">
								<div class="sa-switch sa-switch--toggle plan-settings-switch">
									<span class="sa-switch__label">@lang('Auto-renew by default')</span>
									<div class="form-check form-switch sa-admin-switch">
										<input class="form-check-input" type="checkbox" role="switch"
										       name="auto_renew_default" id="auto_renew_default" value="1"
											@checked(old('auto_renew_default', $plan->auto_renew_default ?? false))>
									</div>
								</div>
								
								<input type="hidden" name="status" value="0">
								<div class="sa-switch sa-switch--toggle plan-settings-switch">
									<span class="sa-switch__label">@lang('Active (visible to users)')</span>
									<div class="form-check form-switch sa-admin-switch">
										<input class="form-check-input" type="checkbox" role="switch"
										       name="status" id="status" value="1"
											@checked(old('status', $plan->status ?? true))>
									</div>
								</div>
							</div>
						</div>
					</div>
					
					<input type="hidden" name="cancellation_policy" value="end_of_period">
				</div>
				<div class="sa-card__footer justify-content-end plan-form-actions">
					<a href="{{ route('admin.subscription.plans.index') }}" class="btn btn-light">
						@lang('Cancel')
					</a>
					<button type="submit" class="btn btn-primary d-inline-flex align-items-center justify-content-center gap-1">
						<x-icon name="check" height="16" width="16"/>
						@lang('Save Plan')
					</button>
				</div>
			</div>
		</div>
	</div>
</div>

<template id="feature-row-template">
	@include('backend.subscription.plans._feature_row', ['i' => '__IDX__', 'feature' => []])
</template>

@push('styles')
	<style>
        .subscription-plan-form {
            display: flex;
            flex-direction: column;
        }

        .plan-settings-shell {
            overflow: hidden;
            border: 1px solid #e2e8f0;
            background: radial-gradient(circle at top right, rgba(37, 99, 235, .10), transparent 32%),
            linear-gradient(180deg, #f8fbff 0%, #ffffff 100%);
        }

        .plan-settings-shell__head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 18px;
        }

        .plan-settings-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 0;
            align-items: start;
        }

        .plan-settings-grid__field {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .plan-settings-switches {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 10px;
        }

        .plan-settings-switch {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 12px 14px;
            background: rgba(255, 255, 255, .94);
            margin-bottom: 0 !important;
        }

        .plan-form-tip {
            height: 100%;
            border: 1px dashed #cbd5e1;
            border-radius: 12px;
            background: #f8fafc;
            padding: 16px;
            color: #475569;
            font-size: 13px;
            line-height: 1.6;
        }

        .plan-form-tip__label {
            margin-bottom: 8px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .06em;
            text-transform: uppercase;
            color: #2563eb;
        }

        .plan-features-card {
            overflow: hidden;
            border: 1px solid #dbe7ff;
        }

        .plan-features-card__head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }

        .plan-features-card__title-wrap {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
        }

        .plan-features-card__count {
            min-width: 28px;
            height: 28px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0 8px;
            border-radius: 999px;
            background: #eff6ff;
            color: #1d4ed8;
            font-size: 11px;
            font-weight: 700;
            flex-shrink: 0;
        }

        .plan-features-card__add-btn {
            white-space: nowrap;
            padding-inline: 12px;
        }

        .plan-features-card__intro {
            padding: 10px 12px;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            background: linear-gradient(180deg, #f8fafc 0%, #ffffff 100%);
        }

        .plan-features-card__intro-copy {
            color: #475569;
            font-size: 12px;
            line-height: 1.5;
        }

        .plan-features-card__eyebrow {
            margin-bottom: 4px;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .06em;
            text-transform: uppercase;
            color: #2563eb;
        }

        .plan-features-card__rows {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .plan-features-card__empty {
            padding: 22px 16px;
            border: 1px dashed #cbd5e1;
            border-radius: 12px;
            background: #f8fafc;
            color: #64748b;
        }

        .plan-features-card__empty-icon {
            width: 42px;
            height: 42px;
            margin: 0 auto 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            background: #e0e7ff;
            color: #4f46e5;
            font-size: 16px;
        }

        .plan-features-card__empty-title {
            margin-bottom: 4px;
            color: #0f172a;
            font-size: 14px;
            font-weight: 700;
        }

        .billing-cycle-block {
            padding: 4px 0;
        }

        .billing-cycle-block--panel {
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 18px;
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        }

        .billing-cycle-block__header {
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }

        .billing-cycle-block__icon {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            margin-top: 2px;
        }

        .billing-cycle-block__icon--monthly {
            background: rgba(13, 110, 253, .10);
            color: #0d6efd;
        }

        .billing-cycle-block__icon--half {
            background: rgba(25, 135, 84, .10);
            color: #198754;
        }

        .billing-cycle-block__icon--yearly {
            background: rgba(111, 66, 193, .10);
            color: #6f42c1;
        }

        .billing-cycle-block__title {
            font-weight: 600;
            font-size: .9375rem;
        }

        .billing-cycle-block__months {
            font-weight: 400;
            color: #6c757d;
            font-size: .8125rem;
        }

        .billing-cycle-block__sub {
            font-size: .8125rem;
            color: #6c757d;
            margin-top: 2px;
        }

        .feature-drag-handle {
            cursor: grab;
            color: #cbd5e1;
            padding: 4px 6px;
            font-size: 14px;
            border-radius: 5px;
            transition: color .15s, background .15s;
            line-height: 1;
            user-select: none;
        }

        .feature-drag-handle:hover {
            color: #64748b;
            background: #f1f5f9;
        }

        .sortable-chosen .feature-drag-handle {
            cursor: grabbing;
            color: #475569;
        }

        .sortable-ghost {
            opacity: .35;
            background: #eff6ff !important;
            border-color: #bfdbfe !important;
        }

        .sortable-drag {
            opacity: .95;
            box-shadow: 0 8px 24px rgba(0, 0, 0, .14) !important;
        }

    .feat-type-guide {
        background: linear-gradient(180deg, #f8fbff 0%, #ffffff 100%);
        border: 1px solid #dbe7ff;
        border-radius: 10px;
        padding: 10px 12px;
        box-shadow: 0 1px 0 rgba(15, 23, 42, .02);
    }

    .feat-type-guide__title {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 8px;
        color: #0f172a;
        font-weight: 700;
        font-size: 12px;
    }

    .feat-type-guide__title-icon {
        width: 24px;
        height: 24px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #eef2ff;
        color: #4f46e5;
        flex-shrink: 0;
    }

    .feat-type-guide__subtitle {
        margin-top: 1px;
        font-size: 10.5px;
        font-weight: 400;
        color: #64748b;
    }

    .feat-type-guide__list {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .feat-type-guide__item {
        display: flex;
        align-items: flex-start;
        gap: 8px;
        padding: 6px 8px;
        border-radius: 9px;
        background: #f8fafc;
        border: 1px solid #edf2f7;
    }

        .feat-type-guide__badge {
            flex-shrink: 0;
            display: inline-block;
            font-size: 9px;
            font-weight: 800;
            letter-spacing: .05em;
            text-transform: uppercase;
            padding: 2px 8px;
            border-radius: 20px;
            line-height: 1.4;
            margin-top: 1px;
        }

        .feat-type-guide__badge--toggle {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .feat-type-guide__badge--limit {
            background: #fef9c3;
            color: #a16207;
        }

        .feat-type-guide__badge--quota {
            background: #dcfce7;
            color: #15803d;
        }

    .feat-type-guide__desc {
        font-size: 11.5px;
        color: #475569;
        line-height: 1.4;
    }

    .feat-type-guide__desc code {
        font-size: 10px;
        background: #e2e8f0;
        padding: 1px 5px;
        border-radius: 4px;
        color: #334155;
    }

    .feature-row {
        margin-bottom: 6px;
    }

    .feature-row__card {
        display: grid;
        grid-template-columns: 28px minmax(180px, 2fr) minmax(180px, 1.5fr) minmax(140px, .95fr) minmax(180px, 1.2fr) auto;
        gap: 7px;
        align-items: start;
        padding: 9px 10px;
        border: 1px solid #dbe7ff;
        border-radius: 10px;
        background: #ffffff;
        box-shadow: 0 1px 0 rgba(15, 23, 42, .02);
    }

    .feature-row__drag {
        display: flex;
        align-items: center;
        justify-content: center;
        padding-top: 20px;
    }

    .feature-row__field,
    .feature-row__action {
        min-width: 0;
    }

    .feature-row__field--value {
        min-width: 160px;
    }

    .feature-row__action {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        padding-top: 20px;
    }

    .feature-row__field .form-label {
        margin-bottom: 3px;
        font-size: 10px;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    .feature-row__field .form-control,
    .feature-row__field .form-select {
        min-height: 34px;
    }

    .feature-remove-btn {
        width: 34px;
        height: 34px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        line-height: 1;
    }

    .feature-remove-btn__icon,
    .feature-remove-btn svg,
    .feature-remove-btn__icon svg {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        line-height: 1;
        margin: 0 auto;
    }

    .plan-form-actions {
        gap: 10px;
    }

    @media (max-width: 1199.98px) {
        .plan-settings-switches {
            grid-template-columns: 1fr;
        }

        .feature-row__card {
            grid-template-columns: 28px minmax(0, 1fr);
        }

        .feature-row__field--type,
        .feature-row__field--value,
        .feature-row__action {
            grid-column: 2 / -1;
        }

        .feature-row__action {
            justify-content: flex-start;
            padding-top: 0;
        }
    }

    @media (max-width: 767.98px) {
        .plan-settings-shell__head {
            flex-direction: column;
            align-items: stretch;
        }

        .plan-features-card__head,
        .plan-features-card__title-wrap {
            flex-direction: column;
            align-items: flex-start;
        }

        .plan-features-card__add-btn {
            width: 100%;
            justify-content: center;
        }

        .billing-cycle-block--panel {
            padding: 16px;
        }

        .plan-form-actions {
            flex-direction: column-reverse;
            align-items: stretch !important;
        }

        .feature-row__card {
            padding: 10px;
        }

        .feature-row__drag,
        .feature-row__action {
            padding-top: 18px;
        }
    }
</style>
@endpush

@push('scripts')
	<script>
        "use strict";
        (function () {
            const basePriceInput = document.getElementById('base-price');
            const halfDiscountInput = document.getElementById('half-yearly-discount');
            const yearlyDiscountInput = document.getElementById('yearly-discount');
            const halfPreview = document.getElementById('half-yearly-preview');
            const yearlyPreview = document.getElementById('yearly-preview');
            const currencyCode = '{{ siteCurrency('code') }}';

            function formatPrice(num) {
                return parseFloat(num).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
            }

            function updatePreviews() {
                const base = parseFloat(basePriceInput.value) || 0;

                const halfDisc = parseInt(halfDiscountInput.value) || 0;
                const halfRaw = base * 6;
                const halfFinal = halfDisc > 0 ? halfRaw * (1 - halfDisc / 100) : halfRaw;
                if (halfDisc > 0) {
                    halfPreview.textContent = halfDisc + '% off - ' + formatPrice(halfFinal) + ' ' + currencyCode;
                } else {
                    halfPreview.textContent = '@lang("No discount") - ' + formatPrice(halfFinal) + ' ' + currencyCode;
                }

                const yearDisc = parseInt(yearlyDiscountInput.value) || 0;
                const yearRaw = base * 12;
                const yearFinal = yearDisc > 0 ? yearRaw * (1 - yearDisc / 100) : yearRaw;
                if (yearDisc > 0) {
                    yearlyPreview.textContent = yearDisc + '% off - ' + formatPrice(yearFinal) + ' ' + currencyCode;
                } else {
                    yearlyPreview.textContent = '@lang("No discount") - ' + formatPrice(yearFinal) + ' ' + currencyCode;
                }
            }

            basePriceInput.addEventListener('input', updatePreviews);
            halfDiscountInput.addEventListener('input', updatePreviews);
            yearlyDiscountInput.addEventListener('input', updatePreviews);

            const container = document.getElementById('features-container');
            const noMsg = document.getElementById('no-features-msg');
            const featureCountBadge = document.getElementById('feature-count-badge');
            const addBtn = document.getElementById('add-feature-btn');
            const template = document.getElementById('feature-row-template').innerHTML;
            let rowIndex = container.querySelectorAll('.feature-row').length;

            function updateNoMsg() {
                const featureCount = container.querySelectorAll('.feature-row').length;
                noMsg.classList.toggle('d-none', featureCount > 0);

                if (featureCountBadge) {
                    featureCountBadge.textContent = String(featureCount);
                }
            }

            function applyFeatureTypeState(row) {
                const typeSelect = row.querySelector('.feature-type-select');
                if (!typeSelect) {
                    return;
                }

                switchValueField(row, typeSelect.value);
            }

            function reindexRows() {
                container.querySelectorAll('.feature-row').forEach(function (row, idx) {
                    row.querySelectorAll('[name]').forEach(function (el) {
                        el.name = el.name.replace(/features\[\d+\]/, 'features[' + idx + ']');
                    });

                    const sortInput = row.querySelector('.feature-sort-order');
                    if (sortInput) {
                        sortInput.value = idx;
                    }

                    const keySel = row.querySelector('.feature-key-select');
                    const labelInp = row.querySelector('[id^="feature-label-"]');
                    if (keySel && labelInp) {
                        const newId = 'feature-label-' + idx;
                        keySel.dataset.labelTarget = newId;
                        labelInp.id = newId;
                    }

                    row.dataset.featureType = row.querySelector('.feature-type-select')?.value ?? 'limit';
                });

                rowIndex = container.querySelectorAll('.feature-row').length;
            }

            if (typeof Sortable !== 'undefined') {
                Sortable.create(container, {
                    handle: '.feature-drag-handle',
                    animation: 150,
                    ghostClass: 'sortable-ghost',
                    dragClass: 'sortable-drag',
                    chosenClass: 'sortable-chosen',
                    onEnd: reindexRows,
                });
            }

            addBtn.addEventListener('click', function () {
                const html = template.replace(/__IDX__/g, rowIndex);
                container.insertAdjacentHTML('beforeend', html);
                applyFeatureTypeState(container.querySelector('.feature-row:last-child'));
                reindexRows();
                updateNoMsg();
            });

            container.addEventListener('click', function (e) {
                const btn = e.target.closest('.remove-feature-btn');
                if (btn) {
                    btn.closest('.feature-row').remove();
                    reindexRows();
                    updateNoMsg();
                }
            });

            container.addEventListener('change', function (e) {
                const keySel = e.target.closest('.feature-key-select');
                if (keySel) {
                    const labelInput = document.getElementById(keySel.dataset.labelTarget);
                    if (labelInput && labelInput.value === '') {
                        labelInput.value = keySel.options[keySel.selectedIndex].dataset.label || '';
                    }
                }

                const typeSel = e.target.closest('.feature-type-select');
                if (typeSel) {
                    switchValueField(typeSel.closest('.feature-row'), typeSel.value);
                }

                const toggleSwitch = e.target.closest('.feature-toggle-switch');
                if (toggleSwitch) {
                    updateToggleValue(toggleSwitch.closest('.feature-row'), toggleSwitch.checked);
                }
            });

            function switchValueField(row, type) {
                const valText = row.querySelector('.feature-val-text');
                const valToggle = row.querySelector('.feature-val-toggle');

                if (!valText || !valToggle) {
                    return;
                }

                row.dataset.featureType = type;

                if (type === 'toggle') {
                    const sw = row.querySelector('.feature-toggle-switch');
                    const checked = sw ? sw.checked : true;
                    valText.value = checked ? 'enabled' : 'disabled';
                    valText.classList.add('d-none');
                    valToggle.classList.remove('d-none');
                    updateToggleValue(row, checked);
                } else {
                    if (valText.value === 'enabled' || valText.value === 'disabled') {
                        valText.value = '';
                    }
                    valText.classList.remove('d-none');
                    valToggle.classList.add('d-none');
                    valText.placeholder = type === 'quota'
                        ? '{{ __('e.g. 5') }}'
                        : '{{ __('e.g. unlimited / Priority') }}';
                }
            }

            function updateToggleValue(row, checked) {
                const valText = row.querySelector('.feature-val-text');
                const label = row.querySelector('.feature-toggle-label');
                valText.value = checked ? 'enabled' : 'disabled';
                label.textContent = checked ? '{{ __('Enabled') }}' : '{{ __('Disabled') }}';
                label.className = 'feature-toggle-label small fw-semibold ' + (checked ? 'text-success' : 'text-secondary');
            }

            const nameInput = document.getElementById('plan-name');
            const slugInput = document.getElementById('plan-slug');
            let slugEdited = slugInput.value.length > 0;

            nameInput.addEventListener('input', function () {
                if (!slugEdited) {
                    slugInput.value = nameInput.value
                        .toLowerCase()
                        .replace(/[^a-z0-9\s-]/g, '')
                        .trim()
                        .replace(/\s+/g, '-');
                }
            });

            slugInput.addEventListener('input', function () {
                slugEdited = true;
            });

            container.querySelectorAll('.feature-row').forEach(function (row) {
                applyFeatureTypeState(row);
            });

            reindexRows();
            updateNoMsg();
        }());
	</script>
@endpush
