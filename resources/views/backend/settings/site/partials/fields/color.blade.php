@php
    $currentColorValue = old($field['key'], setting($field['key'], $field['value']));
    $pickerColorValue = preg_match('/^#[0-9A-Fa-f]{6}$/', (string) $currentColorValue) === 1
        ? $currentColorValue
        : $field['value'];
@endphp

@include('backend.settings.site.partials.fields._level')
<div class="input-group settings-site-color-field">
    <span class="input-group-text settings-site-color-field__swatch-wrap">
        <input type="color"
               class="form-control form-control-color settings-site-color-field__picker"
               value="{{ $pickerColorValue }}"
               data-settings-color-target="{{ $field['key'] }}"
               aria-label="{{ title($field['label']) }} {{ __('Picker') }}">
    </span>
    <input type="text"
           name="{{ $field['key'] }}"
           id="{{ $field['key'] }}"
           value="{{ $currentColorValue }}"
           class="form-control settings-site-color-field__hex @error($field['key']) is-invalid @enderror"
           maxlength="7"
           pattern="^#[0-9A-Fa-f]{6}$"
           data-settings-color-input="{{ $field['key'] }}"
           placeholder="{{ title($field['label']) }}">
</div>
@include('backend.settings.site.partials.fields._error')
