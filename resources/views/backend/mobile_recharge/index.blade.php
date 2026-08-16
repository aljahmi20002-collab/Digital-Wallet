@extends('backend.mobile_recharge.layout')

@section('title', __('Mobile Recharge'))
@section('sub_title', __('Mobile Recharge Management'))
@section('sub_subtitle', __('Manage mobile top-up providers and recharge history.'))

@section('sub_content')
    @php
        $registeredProviders = $registeredProviders ?? collect();
        $activeProviderCode = (string) $selectedProviderCode;
        $activeProvider = null;

        if ($activeProviderCode !== '') {
            $selectedProvider = $registeredProviders->firstWhere('code', $activeProviderCode);
            $activeProvider = $selectedProvider && $selectedProvider->status ? $selectedProvider : null;
        }

        $activeProvider ??= $registeredProviders->first(fn ($provider) => $provider->status && $provider->is_default);
        $activeProvider ??= $registeredProviders->first(fn ($provider) => $provider->status);

        $totalProviders = $registeredProviders->count();
        $tab = $activeTab ?? null;
        $providersTabActive = $tab === 'providers';
        $dashboardTabActive = ! $providersTabActive;
    @endphp

    <div class="mra-tabs">
        <div class="mra-tabbar" aria-label="{{ __('Mobile recharge management sections') }}">
            <a class="mra-tab {{ $dashboardTabActive ? 'active' : '' }}" href="{{ route('admin.mobile-recharge.index') }}">
                <i class="fa-solid fa-chart-line" aria-hidden="true"></i>
                <span>@lang('Dashboard')</span>
            </a>
            <a class="mra-tab {{ $providersTabActive ? 'active' : '' }}" href="{{ route('admin.mobile-recharge.index', ['tab' => 'providers']) }}">
                <i class="fa-solid fa-plug" aria-hidden="true"></i>
                <span>@lang('Providers')</span>
                @if($totalProviders > 0)
                    <span class="mra-tab__count">{{ $totalProviders }}</span>
                @endif
            </a>
        </div>

        <div class="mra-route-panel">
            @if($dashboardTabActive)
            <div id="mra-dashboard-pane" tabindex="0">
                <div class="mra-dashboard-flow">
                    <div class="mra-kpi-grid">
                        <div class="mra-kpi">
                            <span class="mra-kpi__icon mra-kpi__icon--primary"><x-icon name="mobile-recharge" height="20" width="20"/></span>
                            <div>
                                <div class="mra-kpi__label">@lang('Requests')</div>
                                <div class="mra-kpi__value">{{ number_format((int) $metrics['total']) }}</div>
                            </div>
                        </div>
                        <div class="mra-kpi">
                            <span class="mra-kpi__icon mra-kpi__icon--success"><x-icon name="check" height="20" width="20"/></span>
                            <div>
                                <div class="mra-kpi__label">@lang('Completed')</div>
                                <div class="mra-kpi__value">{{ number_format((int) $metrics['completed']) }}</div>
                            </div>
                        </div>
                        <div class="mra-kpi">
                            <span class="mra-kpi__icon mra-kpi__icon--warning"><x-icon name="spinner" height="20" width="20"/></span>
                            <div>
                                <div class="mra-kpi__label">@lang('Processing')</div>
                                <div class="mra-kpi__value">{{ number_format((int) $metrics['processing']) }}</div>
                            </div>
                        </div>
                        <div class="mra-kpi">
                            <span class="mra-kpi__icon mra-kpi__icon--danger"><x-icon name="failed" height="20" width="20"/></span>
                            <div>
                                <div class="mra-kpi__label">@lang('Failed')</div>
                                <div class="mra-kpi__value">{{ number_format((int) $metrics['failed']) }}</div>
                            </div>
                        </div>
                        <div class="mra-kpi">
                            <span class="mra-kpi__icon mra-kpi__icon--info"><x-icon name="money" height="20" width="20"/></span>
                            <div>
                                <div class="mra-kpi__label">@lang('Volume')</div>
                                <div class="mra-kpi__value">{{ siteCurrency('symbol') }}{{ number_format((float) $metrics['volume'], 2) }}</div>
                            </div>
                        </div>
                    </div>

                    <section class="mra-history-panel">
                        <div class="mra-history-panel__head">
                            <div>
                                <span class="mra-card__eyebrow">@lang('History')</span>
                                <h3 class="mra-card__title">@lang('Recharge History')</h3>
                            </div>
                            <span class="mra-pill mra-pill--secondary">
                                {{ trans_choice('{1} :count request|[2,*] :count requests', $recharges->total(), ['count' => number_format($recharges->total())]) }}
                            </span>
                        </div>

                        <form class="mra-filter__row mra-history-panel__filters" method="GET">
                            <div class="mra-filter__field mra-filter__field--search">
                                <label class="form-label">@lang('Search')</label>
                                <input type="search" class="form-control" name="search" value="{{ $filters['search'] }}" placeholder="{{ __('Phone, customer, reference') }}">
                            </div>
                            <div class="mra-filter__field">
                                <label class="form-label">@lang('Status')</label>
                                <select name="status" class="form-select">
                                    <option value="">@lang('All Statuses')</option>
                                    @foreach($statuses as $status)
                                        <option value="{{ $status->value }}" @selected($filters['status'] === $status->value)>{{ $status->label() }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mra-filter__field">
                                <label class="form-label">@lang('Provider')</label>
                                <select name="provider" class="form-select">
                                    <option value="">@lang('All Providers')</option>
                                    @foreach($registeredProviders as $registered)
                                        <option value="{{ $registered->code }}" @selected($filters['provider'] === $registered->code)>{{ $registered->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mra-filter__actions">
                                <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-1">
                                    <i class="fa-solid fa-filter" aria-hidden="true"></i>
                                    @lang('Filter')
                                </button>
                                <a href="{{ route('admin.mobile-recharge.index') }}" class="btn btn-light" aria-label="{{ __('Reset filters') }}">
                                    <i class="fa-solid fa-rotate-left" aria-hidden="true"></i>
                                </a>
                            </div>
                        </form>

                        <div class="table-responsive">
                            <table class="mra-table">
                                <thead>
                                    <tr>
                                        <th>@lang('ID')</th>
                                        <th>@lang('Customer')</th>
                                        <th>@lang('Phone')</th>
                                        <th>@lang('Debit')</th>
                                        <th>@lang('Provider')</th>
                                        <th>@lang('Status')</th>
                                        <th class="text-end">@lang('Action')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recharges as $recharge)
                                        <tr>
                                            <td>
                                                <div class="fw-semibold">#{{ $recharge->id }}</div>
                                                <div class="mra-muted">{{ $recharge->created_at?->format('d M Y, H:i') }}</div>
                                            </td>
                                            <td>
                                                @if($recharge->user)
                                                    <div class="mra-user">
                                                        <span class="mra-user__name">{{ $recharge->user->name }}</span>
                                                        <span class="mra-user__email">{{ $recharge->user->email }}</span>
                                                    </div>
                                                @else
                                                    <span class="mra-muted">@lang('Deleted user')</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="fw-semibold">{{ $recharge->phone_number }}</div>
                                                <div class="mra-muted">{{ $recharge->operator ?: __('Any operator') }}</div>
                                            </td>
                                            <td class="mra-amount">
                                                <strong>{{ number_format((float) $recharge->total_amount, 2) }} {{ $recharge->currency }}</strong>
                                                <div class="mra-muted">{{ __('Fee :fee', ['fee' => number_format((float) $recharge->fee, 2)]) }}</div>
                                            </td>
                                            <td>
                                                <span class="mra-pill mra-pill--info">{{ ucfirst($recharge->provider) }}</span>
                                            </td>
                                            <td>
                                                <span class="mra-pill mra-pill--{{ $recharge->status->color() }}">{{ $recharge->status->label() }}</span>
                                            </td>
                                            <td class="text-end">
                                                <a href="{{ route('admin.mobile-recharge.show', $recharge) }}" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1">
                                                    <x-icon name="eye" height="14" width="14"/>
                                                    @lang('View')
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7">
                                                <x-admin-not-found
                                                    :title="__('No mobile recharges found')"
                                                    :message="__('Recharge requests will appear here after users submit mobile top-ups.')"
                                                    icon="fa-mobile-screen-button"
                                                />
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if($recharges->hasPages())
                            <div class="mra-table-card__footer">{{ $recharges->links() }}</div>
                        @endif
                    </section>
                </div>
            </div>
            @endif

            @if($providersTabActive)
            <div id="mra-providers-pane" tabindex="0">
                <div class="mra-providers-toolbar">
                    <div>
                        <h3 class="mra-card__title mb-1">@lang('Recharge Providers')</h3>
                        <p class="mra-muted small mb-0">@lang('Manage delivery providers, charges, and routing.')</p>
                    </div>
                    @can('mobile-recharge-manage')
                        <button type="button"
                                class="btn btn-primary d-inline-flex align-items-center gap-1 mra-provider-manage-modal"
                                data-edit-url="{{ route('admin.mobile-recharge.providers.create') }}">
                            <i class="fa-solid fa-plus" aria-hidden="true"></i>
                            @lang('New Provider')
                        </button>
                    @endcan
                </div>

                @if($registeredProviders->isEmpty())
                    <x-admin-not-found
                        :title="__('No mobile recharge providers')"
                        :message="__('Add a provider to start delivering mobile top-ups to your users.')"
                        icon="fa-plug"
                    />
                @else
                    <div class="mra-providers-grid">
                        @foreach($registeredProviders as $provider)
                            @php
                                $logo = $provider->logo ?: 'general/static/plugins/mobile-recharge.svg';
                                $hasPlugin = $provider->plugin !== null;
                                $driverLabel = $driverLabels[$provider->driver] ?? ucfirst($provider->driver);
                                $isActiveProvider = $activeProvider?->id === $provider->id;
                                $chargeLabel = (float) $provider->fee_percent > 0
                                    ? number_format((float) $provider->fee_percent, 2).'%'
                                    : siteCurrency('symbol').number_format((float) $provider->fee_fixed, 2);
                            @endphp
                            <article class="mra-provider-card {{ $isActiveProvider ? 'is-active' : '' }} {{ $provider->status ? '' : 'is-disabled' }}">
                                <div class="mra-provider-card__head">
                                    <div class="mra-provider-card__brand">
                                        <img src="{{ asset($logo) }}" alt="" loading="lazy"
                                             onerror="this.onerror=null;this.src='{{ asset('general/static/plugins/mobile-recharge.svg') }}'"/>
                                    </div>
                                    @if($isActiveProvider)
                                        <div class="mra-provider-card__meta">
                                            <span class="mra-pill mra-pill--primary mra-pill--sm">
                                                <i class="fa-solid fa-star" aria-hidden="true"></i>
                                                @lang('Default')
                                            </span>
                                        </div>
                                    @endif
                                </div>

                                <h3 class="mra-provider-card__title">{{ $provider->name }}</h3>

                                <dl class="mra-provider-card__stats">
                                    <div>
                                        <dt>@lang('Driver')</dt>
                                        <dd>{{ $driverLabel }}</dd>
                                    </div>
                                    <div>
                                        <dt>@lang('Charge')</dt>
                                        <dd>{{ $chargeLabel }}</dd>
                                    </div>
                                    <div>
                                        <dt>@lang('Amount Range')</dt>
                                        <dd>
                                            {{ number_format((float) $provider->min_amount, 2) }}
                                            -
                                            {{ $provider->max_amount !== null ? number_format((float) $provider->max_amount, 2) : __('No cap') }}
                                        </dd>
                                    </div>
                                </dl>

                                @if(! $hasPlugin)
                                    <div class="mra-provider-card__warn">
                                        <i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i>
                                        @lang('Plugin is missing. Re-run the seeder or recreate this provider.')
                                    </div>
                                @endif

                                <div class="mra-provider-card__actions">
                                    @can('mobile-recharge-manage')
                                        <button type="button"
                                                class="btn btn-sm btn-primary mra-provider-manage-modal"
                                                data-edit-url="{{ route('admin.mobile-recharge.providers.edit', $provider) }}">
                                            <x-icon name="manage" height="14" width="14"/>
                                            @lang('Edit Provider')
                                        </button>
                                        @if($provider->plugin)
                                            <button type="button" class="btn btn-sm btn-outline-dark edit-modal"
                                                    data-edit-url="{{ route('admin.settings.plugin.edit', $provider->plugin->id) }}">
                                                <x-icon name="plugin" height="14" width="14"/>
                                                @lang('Credentials')
                                            </button>
                                        @endif
                                    @else
                                        <a href="{{ route('admin.mobile-recharge.providers.edit', $provider) }}" class="btn btn-sm btn-light">
                                            <x-icon name="eye" height="14" width="14"/>
                                            @lang('View')
                                        </a>
                                    @endcan
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
            </div>
            @endif

        </div>
    </div>

    @include('backend.mobile_recharge.providers._manage_modal')
    @include('backend.settings.plugin.partials._manage')
@endsection

@push('scripts')
    @include('backend.settings.plugin.partials._scripts')
@endpush
