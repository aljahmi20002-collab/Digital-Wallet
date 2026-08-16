@php use App\Constants\TimeUnits; @endphp
<div class="col-md-12 mb-3">
    <label class="form-label" for="inputGroupSelect02">{{ __('Auto Update Time') }}</label>
    <span data-coreui-toggle="tooltip" data-coreui-placement="top"
          title="{{ __('This will update exchange rate every input time. Example: every 1 minute or etc.') }}"
          class="text-muted modal-tooltip">
        <x-icon name="info" height="18"/>
    </span>
    <div class="input-group mb-3">
        <input type="text" class="form-control" name="fields[auto_update_time]"
               value="{{ $plugin->credentials['fields']['auto_update_time'] }}" id="auto_update_time"
               placeholder="Example: 1 minute or etc.">
        <select class="form-select input-group-select" name="fields[auto_update_time_unit]" id="inputGroupSelect02">
            @foreach(TimeUnits::getAll() as $key => $value)
                <option value="{{ $key }}" @selected($key == $plugin->credentials['fields']['auto_update_time_unit'])>{{ $value }}</option>
            @endforeach
        </select>
    </div>
</div>
<div class="col-md-6 mb-3 mt-1">
    <div class="settings-plugin-switch-card">
        <span class="settings-plugin-switch-card__copy">
            <label class="form-check-label" for="auto_update_status">{{ __('Auto Update') }}</label>
            <small>{{ __('Refresh exchange rates automatically.') }}</small>
        </span>
        <input type="hidden" name="fields[auto_update_status]" value="0">
        <input class="form-check-input coevs-switch flex-shrink-0" type="checkbox" role="switch"
               @checked($plugin->credentials['fields']['auto_update_status'])
               name="fields[auto_update_status]" value="1" id="auto_update_status">
        <span data-coreui-toggle="tooltip" data-coreui-placement="top"
              title="{{ __('When this is Enabled, exchange rate will be updated automatically') }}"
              class="text-muted modal-tooltip">
            <x-icon name="info" height="18"/>
        </span>
    </div>
</div>
