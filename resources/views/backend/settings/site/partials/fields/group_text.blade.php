@include('backend.settings.site.partials.fields._level')

<div class="input-group settings-site-toggle-input">
    @if(isset($field['related_field']))
        @php
            $relatedFieldId = 'switch-' . str_replace(['.', '_'], '-', $field['related_field']);
        @endphp
        <span class="input-group-text settings-site-toggle-input__toggle">
            <span class="form-check form-switch settings-site-toggle-input__check m-0">
                <input type="hidden" name="{{$field['related_field']}}" value="0">
                <input type="checkbox"
                       class="form-check-input coevs-switch"
                       id="{{ $relatedFieldId }}"
                       name="{{$field['related_field']}}"
                       value="1"
                       @checked(setting($field['related_field'], false))>
                <label class="form-check-label" for="{{ $relatedFieldId }}">
                    <span class="settings-site-toggle-input__state settings-site-toggle-input__state--enabled">{{ __('Enabled') }}</span>
                    <span class="settings-site-toggle-input__state settings-site-toggle-input__state--disabled">{{ __('Disabled') }}</span>
                </label>
            </span>
        </span>
    @endif

    <input type="text"
           name="{{ $field['key'] }}"
           id="{{ $field['key'] }}"
           value="{{ setting($field['key'], $field['value']) }}"
           class="form-control"
           @if($field['data'] == 'integer') oninput="this.value = validateNumber(this.value)" @endif
           placeholder="{{ title($field['label']) }}">

    @if(isset($field['unit']))
        <span class="input-group-text settings-site-toggle-input__unit text-uppercase">{{ __($field['unit']) }}</span>
    @endif
</div>
@include('backend.settings.site.partials.fields._error')
