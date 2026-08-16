@extends('backend.virtual_card.index')
@section('title', __('Virtual Card Fee Settings'))

@section('virtual_card_header')
    <div class="vc-admin-hero my-3">
        <div>
            <span class="vc-admin-hero__eyebrow">{{ __('Pricing Rules') }}</span>
            <h3>{{ __('Virtual Card Fee Settings') }}</h3>
            <p>{{ __('Configure thresholds, operation fees, min/max ranges, and provider currency rules.') }}</p>
        </div>
        <div class="vc-admin-hero__stats">
            <div>
                <span>{{ __('Rules') }}</span>
                <strong>{{ $feeSettings->total() }}</strong>
            </div>
        </div>
        <a href="#new_fee_setting_modal" data-coreui-toggle="modal" class="btn btn-light vc-admin-hero__btn">
            <i class="fa-solid fa-plus"></i>
            {{ __('Add New') }}
        </a>
    </div>
@endsection

@section('virtual_card_content')
    <div class="card-body vc-admin-board">
        <div class="table-responsive vc-admin-table">
            <table class="table align-middle mb-0">
                <thead>
                <tr class="align-middle text-nowrap">
                    <th>@lang('Provider') | @lang('Currency')</th>
                    <th>@lang('Threshold') | @lang('Fee Amount')</th>
                    <th>@lang('Min') | @lang('Max Amount')</th>
                    <th>@lang('Status')</th>
                    <th class="text-center">@lang('Actions')</th>
                </tr>
                </thead>
                <tbody>
                @forelse($feeSettings as $setting)
                    @php
                        $op = $setting->operation;
                        $siteSymbol = siteCurrency('symbol');
                    @endphp
                    <tr class="align-middle">
                        <td>
                            <div class="fw-semibold">
                                {{ $setting->provider->name ?? '-' }}
                                <span class="vc-admin-chip ms-1">{{ $setting->currency->code ?? '-' }}</span>
                            </div>
                            <div class="text-muted small mt-1">
                                <span class="badge text-white text-bg-{{ $op?->cssClass() }}">
                                    <i class="{{ $op?->icon() }}"></i>
                                    {{ $op?->label() }}
                                </span>
                            </div>
                        </td>
                        <td>
                            <div class="fw-semibold">{{ $setting->approval_threshold ? $siteSymbol.number_format($setting->approval_threshold, 2) : '-' }}</div>
                            <div class="small text-muted">{{ $siteSymbol.number_format($setting->fee_amount, 2) }} + {{ number_format($setting->fee_percent, 2) }}%</div>
                        </td>
                        <td>
                            <div class="fw-semibold">{{ $siteSymbol.number_format($setting->min_amount, 2) }}</div>
                            <div class="small text-muted">{{ $setting->max_amount ? $siteSymbol.number_format($setting->max_amount, 2) : '-' }}</div>
                        </td>
                        <td>
                            <span class="badge bg-{{ $setting->active ? 'success' : 'secondary' }} text-uppercase">
                                {{ $setting->active ? __('Active') : __('Inactive') }}
                            </span>
                        </td>
                        <td class="text-center">
                            <div class="btn-group vc-admin-actions">
                                <a href="javascript:void(0)" data-edit-url="{{ route('admin.virtual-card.fee-settings.edit', $setting) }}" class="btn btn-primary edit-modal">
                                    <x-icon name="edit" height="18" width="18"/> {{ __('Edit') }}
                                </a>
                                <a href="javascript:void(0)" class="btn btn-danger delete text-white" data-url="{{ route('admin.virtual-card.fee-settings.destroy', $setting) }}">
                                    <x-icon name="delete-3" height="18" width="18"/> {{ __('Delete') }}
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">
                            <x-admin-not-found
                                :title="__('No fee settings found')"
                                :message="__('Create fee settings to control virtual card pricing rules.')"
                                icon="fa-credit-card"
                            />
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-end mt-3">
            {{ $feeSettings->withQueryString()->links() }}
        </div>
    </div>

    @include('backend.virtual_card.fee_settings.partials._new_fee_setting_modal')
    @include('backend.virtual_card.fee_settings.partials._edit_fee_setting_modal')
@endsection
