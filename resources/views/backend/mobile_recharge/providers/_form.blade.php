@php
    use App\Enums\FixPctType;

    $provider = $provider ?? null;
    $isDefault = (bool) old('is_default', $provider?->is_default ?? false);
    $status = old('status', $provider?->status ?? true);
    $selectedFeeType = old('fee_type', ((float) ($provider?->fee_percent ?? 0) > 0 ? FixPctType::PERCENT->value : FixPctType::FIXED->value));
    $feeAmount = old('fee_amount', $selectedFeeType === FixPctType::PERCENT->value ? ($provider?->fee_percent ?? 0) : ($provider?->fee_fixed ?? 0));
@endphp

<div class="mra-provider-editor">
    <input type="hidden" name="status" value="{{ (int) (bool) $status }}">

    <section class="mra-form-section">
        <header class="mra-form-section__head">
            <span class="mra-form-section__icon"><i class="fa-solid fa-circle-info"></i></span>
            <div>
                <h4>@lang('Provider Profile')</h4>
                <small>@lang('Name, logo, driver, and routing.')</small>
            </div>
        </header>

        <div class="mra-provider-basic-grid">
            <div class="mra-logo-manager">
                <label class="form-label">@lang('Logo')</label>
                <x-img name="logo" :old="$provider?->logo" :ref="'mobile-recharge-provider-logo'"/>
                @error('logo')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
            </div>

            <div class="row g-3 m-0">
                <div class="col-md-6">
                    <label class="form-label">@lang('Provider Name') <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name', $provider?->name) }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">@lang('Code') <span class="text-danger">*</span></label>
                    <input type="text" name="code" class="form-control @error('code') is-invalid @enderror"
                           value="{{ old('code', $provider?->code) }}" placeholder="reloadly_global" required @if($provider) readonly @endif>
                    @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">@lang('Driver') <span class="text-danger">*</span></label>
                    <select name="driver" class="form-select mra-driver-select @error('driver') is-invalid @enderror" required>
                        @foreach($driverLabels as $code => $label)
                            <option value="{{ $code }}" @selected(old('driver', $provider?->driver ?? 'sandbox') === $code)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('driver')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <div class="mra-default-toggle">
                        <span class="mra-default-toggle__copy">
                            <label class="form-check-label" for="mra-provider-default">@lang('Default Provider')</label>
                            <small>@lang('Routes new recharges.')</small>
                        </span>
                        <input type="hidden" name="is_default" value="0">
                        <label class="form-check form-switch feature-mgmt-switch feature-mgmt-switch--lg" aria-label="{{ __('Feature control switch') }}">
                            <input class="form-check-input feature-mgmt-switch__input" type="checkbox" role="switch"
                                   name="is_default" value="1" id="mra-provider-default" @checked($isDefault)>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="mra-form-section">
        <header class="mra-form-section__head">
            <span class="mra-form-section__icon"><i class="fa-solid fa-coins"></i></span>
            <div>
                <h4>@lang('Charge & Limits')</h4>
                <small>@lang('User charge and allowed recharge range.')</small>
            </div>
        </header>

        <div class="row g-3 align-items-start">
            <div class="col-md-6">
                <label class="form-label">@lang('User Charge')</label>
                <div class="input-group">
                    <input class="form-control @error('fee_amount') is-invalid @enderror" type="text"
                           oninput="this.value = validateDouble(this.value)"
                           name="fee_amount" value="{{ $feeAmount }}" placeholder="{{ __('Enter recharge charge') }}">
                    <select name="fee_type" class="form-select input-group-select @error('fee_type') is-invalid @enderror">
                        @foreach(FixPctType::options() as $key => $value)
                            <option value="{{ $key }}" @selected($selectedFeeType === $key)>{{ $value }}</option>
                        @endforeach
                    </select>
                    @error('fee_amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    @error('fee_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <small class="text-muted">@lang('Applied per successful top-up.')</small>
            </div>
            <div class="col-md-3">
                <label class="form-label">@lang('Min Amount')</label>
                <input type="number" step="0.01" min="0" name="min_amount" class="form-control"
                       value="{{ old('min_amount', $provider?->min_amount ?? 0) }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">@lang('Max Amount')</label>
                <input type="number" step="0.01" min="0" name="max_amount" class="form-control"
                       value="{{ old('max_amount', $provider?->max_amount) }}" placeholder="{{ __('No cap') }}">
            </div>
        </div>
    </section>

</div>
