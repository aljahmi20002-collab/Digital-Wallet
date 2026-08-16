@include('backend.settings.site.partials.fields._level')
@if(($field['copyable'] ?? false) === true)
    <div class="settings-site-copy-field">
        <input type="text" name="{{ $field['key'] }}" class="form-control settings-site-copy-field__input" id="{{ $field['key'] }}"
               value="{{ setting($field['key'],$field['value']) }}" placeholder="{{ title($field['label']) }}">
        <button type="button"
                class="settings-site-copy-field__button copyNow"
                data-clipboard-target="#{{ $field['key'] }}"
                data-coreui-toggle="tooltip"
                data-coreui-placement="top"
                data-coreui-trigger="hover"
                title="{{ __('Copy Secret Key') }}"
                aria-label="{{ __('Copy Secret Key') }}">
            <i class="fa-solid fa-copy"></i>
        </button>
    </div>
@else
    <input type="text" name="{{ $field['key'] }}" class="form-control" id="{{ $field['key'] }}"
           value="{{ setting($field['key'],$field['value']) }}" placeholder="{{ title($field['label']) }}">
@endif
@include('backend.settings.site.partials.fields._error')
