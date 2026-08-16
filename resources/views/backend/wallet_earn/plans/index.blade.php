@extends('backend.wallet_earn.layout')

@section('title', __('Wallet Earn Plans'))
@section('wallet_earn_title', __('Wallet Earn Plans'))
@section('wallet_earn_icon', 'apps')
@section('wallet_earn_subtitle', __('Create and manage plan limits, reward rates, durations, and approval behavior.'))

@section('wallet_earn_action')
    @can('wallet-earn-manage')
        <a href="{{ route('admin.wallet-earn.plans.create') }}" class="btn btn-primary d-flex align-items-center px-4">
            <x-icon name="add" height="20" width="20" class="me-2"/>
            @lang('Add Plan')
        </a>
    @endcan
@endsection

@section('wallet_earn_content')
    <div class="we-table-card">
        <div class="table-responsive">
            <table class="we-table">
                <thead>
                    <tr>
                        @can('wallet-earn-manage')
                            <th class="we-table__drag-col text-center">&nbsp;</th>
                        @endcan
                        <th>@lang('Plan')</th>
                        <th>@lang('Currency')</th>
                        <th>@lang('Plan Badge')</th>
                        <th>@lang('Limit')</th>
                        <th>@lang('Profit')</th>
                        <th>@lang('Duration')</th>
                        <th>@lang('Payout')</th>
                        <th>@lang('Status')</th>
                        @can('wallet-earn-manage')
                            <th class="text-end">@lang('Action')</th>
                        @endcan
                    </tr>
                </thead>
                <tbody
                    id="wallet-earn-plan-sortable"
                    data-position-url="{{ route('admin.wallet-earn.plans.position-update') }}"
                    data-csrf-token="{{ csrf_token() }}"
                    data-success-message="{{ __('Plan order updated successfully.') }}"
                    data-error-message="{{ __('Unable to update plan order right now.') }}"
                >
                    @forelse($plans as $plan)
                        <tr data-id="{{ $plan->id }}">
                            @can('wallet-earn-manage')
                                <td class="text-center text-muted">
                                    <i class="fa-solid fa-grip-vertical drag-handle" title="@lang('Drag to sort')" data-coreui-toggle="tooltip"></i>
                                </td>
                            @endcan
                            <td>
                                <div class="fw-semibold">{{ $plan->name }}</div>
                                <div class="we-muted">#{{ $plan->id }} &middot; {{ $plan->stakes_count }} @lang('stakes')</div>
                            </td>
                            <td>
                                <span class="we-pill we-pill--primary">
                                    {{ $plan->currency_id ? $plan->currency->code : __('All Currencies') }}
                                </span>
                            </td>
                            <td>
                                @if($plan->isHighlighted())
                                    <div class="d-flex flex-column gap-1">
                                        <span class="we-pill we-pill--warning">{{ $plan->is_featured ? __('Featured Plan') : __('Badge Active') }}</span>
                                        <span class="we-muted">{{ $plan->planBadgeLabel() }}</span>
                                    </div>
                                @else
                                    <span class="we-muted">@lang('Normal')</span>
                                @endif
                            </td>
                            <td>{{ $plan->amountRangeLabel() }}</td>
                            <td>
                                <span class="fw-semibold">{{ number_format((float) $plan->profit_rate, 4) }}</span>
                                <span class="we-muted">{{ $plan->profit_type->label() }}</span>
                            </td>
                            <td>{{ $plan->durationLabel() }}</td>
                            <td>{{ $plan->payout_frequency->label() }}</td>
                            <td>
                                <span class="we-pill {{ $plan->status ? 'we-pill--success' : 'we-pill--danger' }}">
                                    {{ $plan->status ? __('Active') : __('Inactive') }}
                                </span>
                            </td>
                            @can('wallet-earn-manage')
                                <td class="text-end">
                                    <div class="d-inline-flex gap-2">
                                        <a href="{{ route('admin.wallet-earn.plans.edit', $plan) }}" class="btn btn-outline-primary btn-sm d-inline-flex align-items-center">
                                            <x-icon name="manage" height="14" width="14" class="me-1"/>
                                            @lang('Edit')
                                        </a>
                                        <a href="javascript:void(0)" class="btn btn-outline-danger btn-sm delete d-inline-flex align-items-center"
                                           data-url="{{ route('admin.wallet-earn.plans.destroy', $plan) }}">
                                            <x-icon name="delete-3" height="14" width="14" class="me-1"/>
                                            @lang('Delete')
                                        </a>
                                    </div>
                                </td>
                            @endcan
                        </tr>
                        @empty
                        <tr>
                            <td colspan="@can('wallet-earn-manage')10 @else 8 @endcan">
                                <x-admin-not-found
                                    :title="__('No Wallet Earn plans found')"
                                    :message="__('Create earn plans to let users stake wallet balances.')"
                                    icon="fa-chart-line"
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@can('wallet-earn-manage')
    @push('scripts')
        <script src="{{ asset('backend/js/wallet-earn-admin.js?v=' . config('app.version')) }}"></script>
    @endpush
@endcan
