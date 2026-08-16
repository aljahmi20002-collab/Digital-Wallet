@php
    $catalogFeatures   = config('feature_catalog.features', []);
    $catalogCategories = config('feature_catalog.categories', []);
    $grouped = [];
    foreach ($catalogFeatures as $fk => $fm) {
        $cat = $fm['category'] ?? 'other';
        $grouped[$cat]['label']         = $catalogCategories[$cat]['label'] ?? ucfirst($cat);
        $grouped[$cat]['features'][$fk] = $fm['label'] ?? ucwords(str_replace('_', ' ', $fk));
    }
    $planLimits = [
        'daily_transaction_limit'   => 'Daily Transaction Limit',
        'monthly_transaction_limit' => 'Monthly Transaction Limit',
        'monthly_withdraw_limit'    => 'Monthly Withdrawal Limit',
        'monthly_send_limit'        => 'Monthly Send Limit',
        'virtual_card_limit'        => 'Virtual Card Limit',
        'p2p_ad_limit'              => 'P2P Ad Limit',
        'support_priority'          => 'Support Priority',
        'api_access'                => 'API Access',
    ];
    $currentKey   = old('features.'.$i.'.feature_key',   $feature['feature_key']   ?? '');
    $currentLabel = old('features.'.$i.'.feature_label', $feature['feature_label'] ?? '');
    $currentType  = old('features.'.$i.'.feature_type',  $feature['feature_type']  ?? 'limit');
    $currentValue = old('features.'.$i.'.feature_value', $feature['feature_value'] ?? '');
    $isToggle     = $currentType === 'toggle';
    $isEnabled    = in_array(strtolower((string) $currentValue), ['enabled', '1', 'true', 'yes', 'on']);
    $allKnownKeys = array_merge(array_keys($catalogFeatures), array_keys($planLimits));
@endphp

<div class="feature-row" data-feature-type="{{ $currentType }}">

    {{-- Hidden sort_order updated by JS after drag --}}
    <input type="hidden" name="features[{{ $i }}][sort_order]" class="feature-sort-order" value="{{ $i }}">

    <div class="feature-row__card">

        {{-- Drag handle --}}
        <div class="feature-row__drag">
            <div class="feature-drag-handle" title="@lang('Drag to reorder')">
                <i class="fas fa-grip-vertical"></i>
            </div>
        </div>

        {{-- Feature Key --}}
        <div class="feature-row__field">
            <label class="form-label small mb-1 text-muted fw-semibold">@lang('Feature')</label>
            <select name="features[{{ $i }}][feature_key]"
                    class="form-select form-select-sm feature-key-select"
                    data-label-target="feature-label-{{ $i }}">
                <option value="">— @lang('Select') —</option>
                @foreach($grouped as $catData)
                    <optgroup label="{{ __($catData['label']) }}">
                        @foreach($catData['features'] as $fk => $fl)
                            <option value="{{ $fk }}" data-label="{{ __($fl) }}" @selected($currentKey === $fk)>
                                {{ __($fl) }}
                            </option>
                        @endforeach
                    </optgroup>
                @endforeach
                <optgroup label="@lang('Quotas & Limits')">
                    @foreach($planLimits as $fk => $fl)
                        <option value="{{ $fk }}" data-label="{{ __($fl) }}" @selected($currentKey === $fk)>
                            {{ __($fl) }}
                        </option>
                    @endforeach
                </optgroup>
                @if($currentKey && !in_array($currentKey, $allKnownKeys))
                    <option value="{{ $currentKey }}" data-label="{{ $currentLabel }}" selected>{{ $currentKey }}</option>
                @endif
            </select>
        </div>

        {{-- Label --}}
        <div class="feature-row__field">
            <label class="form-label small mb-1 text-muted fw-semibold">@lang('Label')</label>
            <input type="text" name="features[{{ $i }}][feature_label]"
                   id="feature-label-{{ $i }}"
                   class="form-control form-control-sm"
                   placeholder="{{ __('e.g. Daily Limit') }}"
                   value="{{ $currentLabel }}">
        </div>

        {{-- Type --}}
        <div class="feature-row__field feature-row__field--type">
            <label class="form-label small mb-1 text-muted fw-semibold">@lang('Type')</label>
            <select name="features[{{ $i }}][feature_type]" class="form-select form-select-sm feature-type-select">
                <option value="toggle" @selected($currentType === 'toggle')>@lang('Toggle')</option>
                <option value="limit"  @selected($currentType === 'limit')>@lang('Limit')</option>
                <option value="quota"  @selected($currentType === 'quota')>@lang('Quota')</option>
            </select>
        </div>

        {{-- Value --}}
        <div class="feature-row__field feature-row__field--value">
            <label class="form-label small mb-1 text-muted fw-semibold">@lang('Value')</label>

            {{-- Text input (limit / quota) — always the submitted field --}}
            <input type="text"
                   name="features[{{ $i }}][feature_value]"
                   class="form-control form-control-sm feature-val-text{{ $isToggle ? ' d-none' : '' }}"
                   placeholder="{{ __('e.g. 5 / unlimited') }}"
                   value="{{ $currentValue }}">

            {{-- Toggle switch UI (toggle type only) --}}
            <div class="feature-val-toggle d-flex align-items-center gap-2 pt-1{{ $isToggle ? '' : ' d-none' }}">
                <div class="form-check form-switch mb-0">
                    <input class="form-check-input feature-toggle-switch" type="checkbox" role="switch"
                           @checked($isToggle && $isEnabled)>
                </div>
                <span class="feature-toggle-label small fw-semibold {{ $isToggle && $isEnabled ? 'text-success' : 'text-secondary' }}">
                    {{ $isToggle && $isEnabled ? __('Enabled') : __('Disabled') }}
                </span>
            </div>
        </div>

        {{-- Remove --}}
        <div class="feature-row__action">
            <button type="button" class="btn btn-sm btn-outline-danger remove-feature-btn feature-remove-btn" title="@lang('Remove')">
                <x-icon name="trash" height="13" width="13" class="feature-remove-btn__icon"/>
            </button>
        </div>

    </div>
</div>
