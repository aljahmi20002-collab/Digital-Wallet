@extends('backend.p2p.layout')

@section('title', __('Promotion Plans'))

@section('p2p_title')
    {{ __('Promotion Plans') }}
@endsection

@section('p2p_icon', 'apps')

@section('p2p_action')
    @can('p2p-manage')
        <a href="{{ route('admin.p2p.promotion-packages.create') }}" class="fb-btn fb-btn--primary fb-btn--sm">
            <x-icon name="add" height="14" width="14"/>
            @lang('Add Plan')
        </a>
    @endcan
@endsection

@php
    $decimals      = (int) setting('site_decimal', 2);
    $packagesTotal = method_exists($packages, 'count') ? (int) $packages->count() : count($packages);
    $activePlans   = collect($packages)->where('status', true)->count();
    $featuredId    = optional(collect($packages)->firstWhere(fn ($p) => strtoupper((string) ($p->visibility ?? '')) === 'FEATURED' && $p->status))->id;
@endphp

@section('p2p_content')
    <div class="fb-page fb-console">
        <section class="fb-card pa-table-card">
            <div class="fb-card__head">
                <div>
                    <span class="fb-hero__eyebrow">{{ __('Plan Catalog') }}</span>
                    <h5>{{ __('Promotion plans') }}</h5>
                </div>
                <div class="fb-card__meta">
                    <span class="fb-pill fb-pill--neutral">{{ __('Plans') }} <b>{{ number_format($packagesTotal) }}</b></span>
                    <span class="fb-pill fb-pill--success">{{ __('Active') }} <b>{{ number_format($activePlans) }}</b></span>
                </div>
            </div>

            <div class="fb-table table-responsive">
                <table class="pa-table">
                    <thead>
                        <tr>
                            @can('p2p-manage')
                                <th class="text-center fb-table__sort-col">&nbsp;</th>
                            @endcan
                            <th>@lang('Plan')</th>
                            <th>@lang('Billing')</th>
                            <th class="text-end">@lang('Price')</th>
                            <th>@lang('Duration')</th>
                            <th>@lang('Visibility')</th>
                            <th>@lang('Status')</th>
                            @can('p2p-manage')
                                <th class="text-end">@lang('Action')</th>
                            @endcan
                        </tr>
                    </thead>
                    <tbody id="p2p-package-sortable">
                    @forelse($packages as $package)
                        @php
                            $billingType = strtoupper(trim((string) ($package->billing_type ?? 'FIXED')));
                            $billingText = match ($billingType) {
                                'DAILY_PRICE' => __('Daily'),
                                'PER_TRADE_FEE' => __('Per Trade'),
                                default => __('Fixed'),
                            };
                            $durationMinutes = (int) $package->duration_minutes;
                            $durationText = $durationMinutes >= 60
                                ? (rtrim(rtrim(number_format($durationMinutes / 60, 2, '.', ''), '0'), '.') . ' ' . __('hours'))
                                : ($durationMinutes . ' ' . __('min'));
                            $visibility = strtoupper((string) ($package->visibility ?? 'PUBLIC'));
                            $isFeatured = $package->id === $featuredId;
                        @endphp
                        <tr data-id="{{ $package->id }}" class="{{ $isFeatured ? 'fb-row-featured' : '' }}">
                            @can('p2p-manage')
                                <td class="text-center fb-text-faint">
                                    <i class="fa-solid fa-grip-vertical drag-handle" title="@lang('Drag to sort')" data-coreui-toggle="tooltip"></i>
                                </td>
                            @endcan
                            <td>
                                <div class="fb-user__name">
                                    {{ $package->name }}
                                    @if($isFeatured)
                                        <span class="fb-featured-tag">@lang('Featured')</span>
                                    @endif
                                </div>
                                <div class="fb-user__meta">#{{ $package->id }}</div>
                            </td>
                            <td>
                                <span class="fb-pill fb-pill--neutral pa-pill pa-pill--neutral">{{ $billingText }}</span>
                            </td>
                            <td class="text-end fb-num">{{ number_format((float) $package->effectiveBasePrice(), $decimals) }}</td>
                            <td class="fb-mono fb-text-muted">{{ $durationText }}</td>
                            <td>
                                <span class="fb-pill {{ $visibility === 'FEATURED' ? 'fb-pill--primary' : 'fb-pill--neutral' }} pa-pill pa-pill--neutral">{{ $visibility }}</span>
                            </td>
                            <td>
                                <span class="fb-pill {{ $package->status ? 'fb-pill--success' : 'fb-pill--neutral' }} pa-pill {{ $package->status ? 'pa-pill--success' : 'pa-pill--neutral' }}">
                                    {{ $package->status ? __('Active') : __('Inactive') }}
                                </span>
                            </td>
                            @can('p2p-manage')
                                <td class="text-end">
                                    <div class="fb-btn-group">
                                        <a href="{{ route('admin.p2p.promotion-packages.edit', $package) }}" class="fb-btn fb-btn--ghost fb-btn--sm">
                                            <x-icon name="manage" height="13" width="13"/>
                                            {{ __('Edit') }}
                                        </a>
                                        <a href="javascript:void(0)" class="fb-btn fb-btn--danger fb-btn--sm fb-btn--icon delete"
                                           data-url="{{ route('admin.p2p.promotion-packages.destroy', $package) }}"
                                           aria-label="{{ __('Delete plan') }}">
                                            <x-icon name="delete-3" height="13" width="13"/>
                                        </a>
                                    </div>
                                </td>
                            @endcan
                        </tr>
                    @empty
                        <tr>
                            <td colspan="@can('p2p-manage')8 @else 6 @endcan">
                                <x-admin-not-found
                                    :title="__('No plans found')"
                                    :message="__('Create your first promotion plan to start monetizing trade-ad placements.')"
                                    icon="fa-bullhorn"
                                />
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection

@can('p2p-manage')
@push('scripts')
    @include('backend.p2p.promotion_plans.partials._plans_list_scripts')
@endpush
@endcan
