@include('backend.settings.site.partials.fields._level')
<div class="settings-site-switch-control">
    <input type="hidden" name="{{ $field['key'] }}" value="0">
    <label class="settings-site-switch-card" for="{{ $field['key'] }}_switch">
        <input class="form-check-input coevs-switch settings-site-switch-card__input"
               type="checkbox"
               role="switch"
               id="{{ $field['key'] }}_switch"
               name="{{ $field['key'] }}"
               value="1"
               @isset($field['enable_warning'])
                   data-settings-enable-warning="{{ __($field['enable_warning']) }}"
                   data-settings-enable-warning-title="{{ __($field['enable_warning_title'] ?? 'Confirm Status Change') }}"
                   data-settings-enable-warning-confirm="{{ __($field['enable_warning_confirm'] ?? 'Continue') }}"
               @endisset
               @checked(setting($field['key'],$field['value']))>
        <span class="settings-site-switch-card__track" aria-hidden="true"></span>
        <span class="settings-site-switch-card__meta">
            <span class="settings-site-switch-card__state settings-site-switch-card__state--enabled">{{ __('Enabled') }}</span>
            <span class="settings-site-switch-card__state settings-site-switch-card__state--disabled">{{ __('Disabled') }}</span>
        </span>
    </label>
</div>
@include('backend.settings.site.partials.fields._error')
