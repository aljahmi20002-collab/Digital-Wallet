@php
    $decimals = (int) setting('site_decimal', 2);

    $visibilityValue = (string) old('visibility', $package->visibility ?? 'PUBLIC');
    $billingTypeValue = (string) old('billing_type', $package->billing_type ?? 'FIXED');
    $appliesToValue = (string) old('applies_to', $package->applies_to ?? 'BOTH');

    $featuresValue = (array) old('features', $package->features ?? []);
    $allowedCategoriesValue = (array) old('allowed_categories', $package->allowed_categories ?? []);

    $baseCurrency = (string) (siteCurrency('code') ?: config('app.default_currency'));
    $baseCurrency = strtoupper(trim($baseCurrency));

    $submitLabel = (string) ($submitLabel ?? __('Save'));
@endphp

@foreach($featuresValue as $featureKey => $featureEnabled)
    @if((bool) $featureEnabled)
        <input type="hidden" name="features[{{ $featureKey }}]" value="1">
    @endif
@endforeach
<input type="hidden" name="accent_color" value="{{ old('accent_color', $package->accent_color) }}">
<input type="hidden" name="search_priority" value="{{ old('search_priority', $package->search_priority ?? 1) }}">
<input type="hidden" name="max_active_per_user" value="{{ old('max_active_per_user', $package->max_active_per_user) }}">
<input type="hidden" name="cooldown_after_expiry_minutes" value="{{ old('cooldown_after_expiry_minutes', $package->cooldown_after_expiry_minutes) }}">
<input type="hidden" name="auto_renew_allowed" value="{{ old('auto_renew_allowed', (int) ($package->auto_renew_allowed ?? 0)) }}">
@foreach($allowedCategoriesValue as $category)
    <input type="hidden" name="allowed_categories[]" value="{{ $category }}">
@endforeach

<div class="p2p-settings-grid">
    <section class="p2p-settings-panel">
        <div class="p2p-settings-panel__head">
            <span class="p2p-settings-panel__icon p2p-settings-panel__icon--primary">
                <i class="fa-solid fa-cube" aria-hidden="true"></i>
            </span>
            <div>
                <h6>@lang('Plan')</h6>
                <span>@lang('Name and visibility')</span>
            </div>
        </div>
        <div class="p2p-settings-fields">
            <div class="p2p-settings-field">
                <label for="pkg_name">@lang('Plan Name')</label>
                <input type="text" name="name" id="pkg_name" class="p2p-settings-input form-control @error('name') is-invalid @enderror" value="{{ old('name', $package->name) }}" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="p2p-settings-field">
                <label for="pkg_visibility">@lang('Visibility')</label>
                <select name="visibility" id="pkg_visibility" class="p2p-settings-input form-select @error('visibility') is-invalid @enderror" required>
                    <option value="PUBLIC" @selected($visibilityValue === 'PUBLIC')>@lang('Public')</option>
                    <option value="HIDDEN" @selected($visibilityValue === 'HIDDEN')>@lang('Hidden')</option>
                    <option value="INTERNAL" @selected($visibilityValue === 'INTERNAL')>@lang('Internal')</option>
                </select>
                @error('visibility')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </section>

    <section class="p2p-settings-panel">
        <div class="p2p-settings-panel__head">
            <span class="p2p-settings-panel__icon p2p-settings-panel__icon--success">
                <i class="fa-solid fa-wallet" aria-hidden="true"></i>
            </span>
            <div>
                <h6>@lang('Pricing')</h6>
                <span>@lang('Billing model and amount')</span>
            </div>
        </div>
        <div class="p2p-settings-fields">
            <div class="p2p-settings-field">
                <label for="pkg_billing_type">@lang('Billing Type')</label>
                <select name="billing_type" id="pkg_billing_type" class="p2p-settings-input form-select @error('billing_type') is-invalid @enderror" required>
                    <option value="FIXED" @selected($billingTypeValue === 'FIXED')>@lang('Fixed Price')</option>
                    <option value="DAILY_PRICE" @selected($billingTypeValue === 'DAILY_PRICE')>@lang('Daily Price')</option>
                    <option value="PER_TRADE_FEE" @selected($billingTypeValue === 'PER_TRADE_FEE')>@lang('Per Trade Fee')</option>
                </select>
                @error('billing_type')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="p2p-settings-field" id="pkg_fixed_price_wrap">
                <label for="pkg_price">@lang('Price') ({{ $baseCurrency }})</label>
                <input type="text" inputmode="decimal" name="price" id="pkg_price" class="p2p-settings-input form-control @error('price') is-invalid @enderror" value="{{ old('price', $package->price !== null ? number_format((float) $package->price, $decimals, '.', '') : '') }}">
                @error('price')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="p2p-settings-field" id="pkg_daily_price_wrap">
                <label for="pkg_daily_price">@lang('Daily Price') ({{ $baseCurrency }})</label>
                <input type="text" inputmode="decimal" name="daily_price" id="pkg_daily_price" class="p2p-settings-input form-control @error('daily_price') is-invalid @enderror" value="{{ old('daily_price', $package->daily_price !== null ? number_format((float) $package->daily_price, $decimals, '.', '') : '') }}">
                @error('daily_price')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="p2p-settings-field" id="pkg_per_trade_fee_wrap">
                <label for="pkg_per_trade_fee">@lang('Per Trade Fee') ({{ $baseCurrency }})</label>
                <input type="text" inputmode="decimal" name="per_trade_fee" id="pkg_per_trade_fee" class="p2p-settings-input form-control @error('per_trade_fee') is-invalid @enderror" value="{{ old('per_trade_fee', $package->per_trade_fee !== null ? number_format((float) $package->per_trade_fee, $decimals, '.', '') : '') }}">
                @error('per_trade_fee')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </section>

    <section class="p2p-settings-panel">
        <div class="p2p-settings-panel__head">
            <span class="p2p-settings-panel__icon p2p-settings-panel__icon--warning">
                <i class="fa-solid fa-clock" aria-hidden="true"></i>
            </span>
            <div>
                <h6>@lang('Duration')</h6>
                <span>@lang('Promotion window')</span>
            </div>
        </div>
        <div class="p2p-settings-fields">
            <div class="p2p-settings-field">
                <label for="pkg_duration_value">@lang('Duration')</label>
                <div class="input-group">
                    <input type="number" name="duration_value" id="pkg_duration_value" class="p2p-settings-input form-control @error('duration_value') is-invalid @enderror" value="{{ old('duration_value', $durationValue ?? 1) }}" min="1" required>
                    <select name="duration_unit" id="pkg_duration_unit" class="p2p-settings-input form-select @error('duration_unit') is-invalid @enderror" required>
                        <option value="MINUTES" @selected(old('duration_unit', $durationUnit ?? 'DAYS') === 'MINUTES')>@lang('Minutes')</option>
                        <option value="HOURS" @selected(old('duration_unit', $durationUnit ?? 'DAYS') === 'HOURS')>@lang('Hours')</option>
                        <option value="DAYS" @selected(old('duration_unit', $durationUnit ?? 'DAYS') === 'DAYS')>@lang('Days')</option>
                    </select>
                </div>
                @error('duration_value')
                    <div class="p2p-settings-error">{{ $message }}</div>
                @enderror
                @error('duration_unit')
                    <div class="p2p-settings-error">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </section>

    <section class="p2p-settings-panel">
        <div class="p2p-settings-panel__head">
            <span class="p2p-settings-panel__icon p2p-settings-panel__icon--info">
                <i class="fa-solid fa-crosshairs" aria-hidden="true"></i>
            </span>
            <div>
                <h6>@lang('Scope')</h6>
                <span>@lang('Audience and status')</span>
            </div>
        </div>
        <div class="p2p-settings-fields">
            <div class="p2p-settings-field">
                <label for="pkg_applies_to">@lang('Applies To')</label>
                <select name="applies_to" id="pkg_applies_to" class="p2p-settings-input form-select @error('applies_to') is-invalid @enderror" required>
                    <option value="BOTH" @selected($appliesToValue === 'BOTH')>@lang('Both')</option>
                    <option value="BUY" @selected($appliesToValue === 'BUY')>@lang('Buy Ads')</option>
                    <option value="SELL" @selected($appliesToValue === 'SELL')>@lang('Sell Ads')</option>
                </select>
                @error('applies_to')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="p2p-settings-field">
                <div class="form-check form-switch">
                    <input type="hidden" name="status" value="0">
                    <input class="form-check-input" type="checkbox" id="pkg_status" name="status" value="1" {{ old('status', $package->exists ? (int) $package->status : 1) ? 'checked' : '' }}>
                    <label class="form-check-label" for="pkg_status">@lang('Active')</label>
                </div>
            </div>
        </div>
    </section>
</div>

<div class="p2p-settings-savebar">
    <a href="{{ route('admin.p2p.promotion-packages.index') }}" class="fb-btn fb-btn--ghost">@lang('Cancel')</a>
    <button type="submit" class="fb-btn fb-btn--primary">
        <i class="fa-solid fa-check" aria-hidden="true"></i>
        {{ $submitLabel }}
    </button>
</div>

@push('scripts')
    @include('backend.p2p.promotion_plans.partials._plan_form_scripts')
@endpush
