<div class="col-12 col-xl-12">
    <form method="POST" action="{{ route('admin.settings.plugin.update', $plugin->id) }}" class="settings-plugin-manage-form">
        @method('PUT')
        @csrf
        <div class="settings-plugin-manage-form__summary">
            <div class="settings-plugin-manage-form__identity">
                <span class="settings-plugin-manage-form__logo">
                    <img src="{{ asset($plugin->logo) }}" alt="{{ $plugin->name }}" loading="lazy">
                </span>
                <div>
                    <h6>{{ $plugin->name }}</h6>
                    <p>{{ $plugin->description }}</p>
                </div>
            </div>
            <span class="settings-plugin-status {{ $plugin->status ? 'is-active' : 'is-inactive' }}">
                <span class="settings-plugin-status__dot"></span>
                {{ $plugin->status ? __('Active') : __('Inactive') }}
            </span>
        </div>

        <section class="settings-plugin-form-section">
            <div class="settings-plugin-form-section__header">
                <span>{{ __('Credentials') }}</span>
                <h6>{{ __('Connection Details') }}</h6>
            </div>
            <div class="row">
                @foreach($plugin->credentials as $field_name => $credential)
                    @if($field_name != 'fields')
                        <div class="col-md-12 mb-3">
                            <label class="form-label"
                                   for="{{ $field_name }}">{{ ucwords(str_replace('_', ' ', $field_name)) }}</label>
                            <input type="text" name="credentials[{{ $field_name }}]" id="{{ $field_name }}"
                                   value="{{ $credential }}"
                                   placeholder="{{ __('Enter :field', ['field' => str_replace('_', ' ', $field_name)]) }}" class="form-control">
                        </div>
                    @endif
                @endforeach

                @includeIf('backend.settings.plugin.other_fields.'.$plugin->fields_blade, ['plugin' => $plugin])
            </div>
        </section>

        <section class="settings-plugin-test-card">
            <div class="settings-plugin-test-card__content">
                <span class="settings-plugin-test-card__icon" aria-hidden="true">
                    <i class="fa-solid fa-circle-check"></i>
                </span>
                <div>
                    <span>{{ __('Connection Test') }}</span>
                    <h6>{{ __('Validate Provider Credentials') }}</h6>
                    <p>{{ __('Run a quick API check using the current values before updating.') }}</p>
                </div>
            </div>
            <button class="btn btn-outline-primary settings-plugin-test-card__button"
                    type="button"
                    data-plugin-test
                    data-test-url="{{ route('admin.settings.plugin.test', ['plugin' => $plugin->id]) }}"
                    data-testing-label="{{ __('Testing...') }}"
                    data-testing-message="{{ __('Testing provider connection...') }}"
                    data-fallback-message="{{ __('Unable to test this provider right now.') }}">
                <span class="settings-plugin-test-card__button-icon" aria-hidden="true">
                    <i class="fa-solid fa-circle-check"></i>
                </span>
                <span data-plugin-test-label>{{ __('Test Connection') }}</span>
            </button>
            <div class="settings-plugin-test-card__result d-none" data-plugin-test-result></div>
        </section>

        <div class="settings-plugin-manage-form__actions">
            <div class="settings-plugin-switch-card">
                <span class="settings-plugin-switch-card__copy">
                    <label class="form-check-label" for="plugin-status-{{ $plugin->id }}">{{ __('Plugin Status') }}</label>
                    <small>{{ __('Control whether this integration is available.') }}</small>
                </span>
                <input type="hidden" name="status" value="0">
                <label class="form-check form-switch feature-mgmt-switch feature-mgmt-switch--lg settings-plugin-status-switch" aria-label="{{ __('Toggle plugin status') }}">
                    <input class="form-check-input feature-mgmt-switch__input" type="checkbox" role="switch"
                           name="status" @checked($plugin->status) value="1" id="plugin-status-{{ $plugin->id }}">
                </label>
            </div>
        </div>

        <div class="settings-plugin-manage-form__footer">
            <button class="btn btn-primary settings-plugin-manage-form__submit" type="submit">
                <x-icon name="check" height="18" width="18" class="settings-plugin-manage-form__submit-glyph"/>
                {{ __('Update Now') }}
            </button>
        </div>
    </form>
</div>
