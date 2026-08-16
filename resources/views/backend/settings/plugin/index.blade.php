@extends('backend.settings.index')
    @section('setting_title', __('Integration Center'))
@section('setting_content')
    @php
        $activePlugins = $plugins->where('status', true)->count();
        $inactivePlugins = $plugins->count() - $activePlugins;
        $pluginGroupMeta = [
            'general' => [
                'label' => __('Core Integrations'),
                'description' => __('Core system integrations and operational services for the platform.'),
                'icon' => 'plugin',
            ],
            'notification' => [
                'label' => __('Notification Providers'),
                'description' => __('Channel and provider integrations for alerts, emails, and messaging.'),
                'icon' => 'notification',
            ],
            'exchange_rate' => [
                'label' => __('Exchange Rate Feeds'),
                'description' => __('Market data providers used to refresh conversion rates.'),
                'icon' => 'currency-exchange',
            ],
            'mobile_recharge' => [
                'label' => __('Mobile Recharge Providers'),
                'description' => __('Airtime and top-up provider credentials used by Mobile Recharge.'),
                'icon' => 'mobile-recharge',
            ],
        ];
        $groupedPlugins = $plugins->groupBy('type');
        $pluginGroups = collect(array_keys($pluginGroupMeta))
            ->merge($groupedPlugins->keys())
            ->unique()
            ->mapWithKeys(fn (string $type): array => [$type => $groupedPlugins->get($type, collect())])
            ->filter(fn ($groupPlugins): bool => $groupPlugins->isNotEmpty());
        $activePluginType = $selectedPluginType && $pluginGroups->has($selectedPluginType)
            ? $selectedPluginType
            : $pluginGroups->keys()->first();
    @endphp

    <div class="settings-plugin-overview">
        <div class="settings-plugin-overview__main">
                <span class="settings-plugin-overview__icon">
                    <x-icon name="plugin" height="28" width="28"/>
                </span>
                <div>
                    <span class="settings-plugin-overview__eyebrow">{{ __('Integration Center') }}</span>
                <h4>{{ __('Integration Center') }}</h4>
                    <p>{{ __('Configure plugin credentials, status, and operational controls from one grouped integration surface.') }}</p>
                </div>
            </div>
        <div class="settings-plugin-overview__stats">
            <span>
                <strong>{{ $plugins->count() }}</strong>
                {{ __('Total') }}
            </span>
            <span>
                <strong>{{ $pluginGroups->count() }}</strong>
                {{ __('Groups') }}
            </span>
            <span>
                <strong>{{ $activePlugins }}</strong>
                {{ __('Active') }}
            </span>
            <span>
                <strong>{{ $inactivePlugins }}</strong>
                {{ __('Inactive') }}
            </span>
        </div>
    </div>

    <div class="settings-plugin-card">
        <div class="settings-plugin-card__header">
            <div>
                <span class="settings-plugin-card__eyebrow">{{ __('Available Integrations') }}</span>
                <h5>{{ __('Plugin Registry') }}</h5>
            </div>
            <span class="settings-plugin-card__count">
                {{ trans_choice(':count plugin|:count plugins', $plugins->count(), ['count' => $plugins->count()]) }}
            </span>
        </div>

        @if($plugins->isNotEmpty())
            <div class="settings-plugin-category-shell">
                <div class="settings-plugin-category-nav nav nav-pills" id="plugin-group-tabs" role="tablist">
                    @foreach($pluginGroups as $type => $groupPlugins)
                        @php
                            $groupMeta = $pluginGroupMeta[$type] ?? [
                                'label' => __(str($type)->replace('_', ' ')->title()->toString()),
                                'description' => __('Additional integrations installed for this platform.'),
                                'icon' => 'plugin',
                            ];
                            $isActiveGroup = $activePluginType === $type;
                            $groupSlug = str($type)->slug();
                        @endphp
                        <button class="settings-plugin-category-tab nav-link {{ $isActiveGroup ? 'active' : '' }}"
                                id="plugin-group-{{ $groupSlug }}-tab"
                                data-coreui-toggle="pill"
                                data-coreui-target="#plugin-group-{{ $groupSlug }}"
                                type="button"
                                role="tab"
                                aria-controls="plugin-group-{{ $groupSlug }}"
                                aria-selected="{{ $isActiveGroup ? 'true' : 'false' }}">
                            <span class="settings-plugin-category-tab__icon">
                                <x-icon name="{{ $groupMeta['icon'] }}" height="18" width="18"/>
                            </span>
                            <span class="settings-plugin-category-tab__copy">
                                <span>{{ $groupMeta['label'] }}</span>
                                <small>{{ $groupPlugins->count() }} {{ __('plugins') }}</small>
                            </span>
                            <span class="settings-plugin-category-tab__count">
                                {{ $groupPlugins->where('status', true)->count() }}
                            </span>
                        </button>
                    @endforeach
                </div>
            </div>

            <div class="settings-plugin-groups tab-content" id="plugin-group-tabs-content">
                @foreach($pluginGroups as $type => $groupPlugins)
                    @php
                        $groupMeta = $pluginGroupMeta[$type] ?? [
                            'label' => __(str($type)->replace('_', ' ')->title()->toString()),
                            'description' => __('Additional integrations installed for this platform.'),
                            'icon' => 'plugin',
                        ];
                        $isActiveGroup = $activePluginType === $type;
                        $groupSlug = str($type)->slug();
                    @endphp
                    <section class="settings-plugin-group tab-pane fade {{ $isActiveGroup ? 'show active' : '' }}"
                             id="plugin-group-{{ $groupSlug }}"
                             role="tabpanel"
                             aria-labelledby="plugin-group-{{ $groupSlug }}-tab"
                             tabindex="0">
                        <header class="settings-plugin-group__header">
                            <div class="settings-plugin-group__title">
                                <span class="settings-plugin-group__icon">
                                    <x-icon name="{{ $groupMeta['icon'] }}" height="20" width="20"/>
                                </span>
                                <span>
                                    <h6>{{ $groupMeta['label'] }}</h6>
                                    <p>{{ $groupMeta['description'] }}</p>
                                </span>
                            </div>
                            <div class="settings-plugin-group__meta">
                                <span>{{ trans_choice(':count plugin|:count plugins', $groupPlugins->count(), ['count' => $groupPlugins->count()]) }}</span>
                                <span>{{ $groupPlugins->where('status', true)->count() }} {{ __('active') }}</span>
                            </div>
                        </header>

                        <div class="settings-plugin-grid">
                            @foreach($groupPlugins as $plugin)
                                <article class="settings-plugin-item {{ $plugin->status ? 'is-active' : 'is-inactive' }}">
                                    <div class="settings-plugin-item__head">
                                        <div class="settings-plugin-logo {{ $plugin->status ? 'is-active' : 'is-inactive' }}">
                                            <img src="{{ asset($plugin->logo) }}" alt="{{ $plugin->name }}" loading="lazy">
                                        </div>
                                        <span class="settings-plugin-status {{ $plugin->status ? 'is-active' : 'is-inactive' }}">
                                            <span class="settings-plugin-status__dot"></span>
                                            {{ $plugin->status ? __('Active') : __('Inactive') }}
                                        </span>
                                    </div>

                                    <div class="settings-plugin-item__body">
                                        <h6>{{ $plugin->name }}</h6>
                                        <p>{{ $plugin->description }}</p>
                                    </div>

                                    <div class="settings-plugin-item__footer">
                                        <button type="button" class="btn btn-primary edit-modal settings-plugin-table__action-btn"
                                                data-edit-url="{{ route('admin.settings.plugin.edit', $plugin->id) }}">
                                            <x-icon name="manage" height="18" width="18"/>
                                            {{ __('Manage') }}
                                        </button>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </section>
                @endforeach
            </div>
        @else
            <x-admin-not-found
                :title="__('No plugins found')"
                :message="__('No integrations are configured for this category yet.')"
                icon="fa-plug"
                class="settings-plugin-empty"
            />
        @endif
    </div>

    {{-- plugin mange modal --}}
    @include('backend.settings.plugin.partials._manage')

@endsection
@push('scripts')
    @include('backend.settings.plugin.partials._scripts')
@endpush
