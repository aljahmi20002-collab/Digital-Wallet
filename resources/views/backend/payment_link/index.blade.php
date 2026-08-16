@extends('backend.payment_link.layout')

@section('title', __('Payment Links'))
@section('sub_title', __('Payment Links'))
@section('sub_icon', 'payment')
@section('sub_subtitle', __('Monitor, filter and moderate payment links created by users, merchants and agents.'))

@section('sub_content')

    {{-- Metrics --}}
    <div class="pla-kpi-grid mb-4">
        <div class="pla-kpi">
            <span class="pla-kpi__icon pla-kpi__icon--primary">
                <x-icon name="payment" height="20" width="20"/>
            </span>
            <div>
                <div class="pla-kpi__label">@lang('Total Links')</div>
                <div class="pla-kpi__value">{{ number_format((int) ($metrics['total'] ?? 0)) }}</div>
            </div>
        </div>
        <div class="pla-kpi">
            <span class="pla-kpi__icon pla-kpi__icon--success">
                <x-icon name="check" height="20" width="20"/>
            </span>
            <div>
                <div class="pla-kpi__label">@lang('Active')</div>
                <div class="pla-kpi__value">{{ number_format((int) ($metrics['active'] ?? 0)) }}</div>
            </div>
        </div>
        <div class="pla-kpi">
            <span class="pla-kpi__icon pla-kpi__icon--warning">
                <x-icon name="close" height="20" width="20"/>
            </span>
            <div>
                <div class="pla-kpi__label">@lang('Inactive')</div>
                <div class="pla-kpi__value">{{ number_format((int) ($metrics['inactive'] ?? 0)) }}</div>
            </div>
        </div>
        <div class="pla-kpi">
            <span class="pla-kpi__icon pla-kpi__icon--info">
                <x-icon name="transaction-2" height="20" width="20"/>
            </span>
            <div>
                <div class="pla-kpi__label">@lang('Successful Payments')</div>
                <div class="pla-kpi__value">{{ number_format((int) ($metrics['payments'] ?? 0)) }}</div>
            </div>
        </div>
    </div>

    @include('backend.payment_link.partials._filter')

    {{-- Table --}}
    <div class="pla-table-card">
        <div class="table-responsive">
            <table class="pla-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>@lang('Title')</th>
                        <th>@lang('Owner')</th>
                        <th>@lang('Merchant')</th>
                        <th>@lang('Amount')</th>
                        <th>@lang('Payments')</th>
                        <th>@lang('Status')</th>
                        <th>@lang('Created')</th>
                        <th class="text-end">@lang('Actions')</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($paymentLinks as $link)
                        @php
                            $owner    = $link->user;
                            $isExpired = $link->isExpired();
                            $isMaxed   = $link->isMaxedOut();
                            $statusKey = $isExpired ? 'expired' : ($isMaxed ? 'maxed' : $link->status->value);
                            $statusLbl = $isExpired ? __('Expired') : ($isMaxed ? __('Limit Reached') : $link->status->label());
                            $statusCol = $isExpired || $isMaxed ? 'warning' : $link->status->color();
                        @endphp
                        <tr>
                            <td class="pla-muted">{{ $link->id }}</td>
                            <td>
                                <div class="fw-semibold pla-link-title">{{ $link->title }}</div>
                                <div class="pla-muted pla-token">{{ $link->token }}</div>
                            </td>
                            <td>
                                @if($owner)
                                    <div class="pla-user">
                                        <span class="pla-user__name">{{ $owner->name }}</span>
                                        <span class="pla-user__email">{{ $owner->email }}</span>
                                    </div>
                                @else
                                    <span class="pla-muted">@lang('Deleted user')</span>
                                @endif
                            </td>
                            <td>
                                @if($link->merchant)
                                    <span class="pla-pill pla-pill--info">{{ $link->merchant->business_name }}</span>
                                @else
                                    <span class="pla-muted">—</span>
                                @endif
                            </td>
                            <td class="pla-amount">
                                @if($link->isOpenAmount())
                                    <span class="pla-pill pla-pill--secondary">@lang('Open')</span>
                                    <div class="pla-muted pla-amount-meta">
                                        @if($link->min_amount !== null) {{ __('Min :amt', ['amt' => number_format((float) $link->min_amount, 2)]) }} @endif
                                        @if($link->max_amount !== null) {{ __('Max :amt', ['amt' => number_format((float) $link->max_amount, 2)]) }} @endif
                                    </div>
                                @else
                                    <strong>{{ $link->currencyCode() }} {{ number_format((float) $link->amount, 2) }}</strong>
                                @endif
                            </td>
                            <td class="pla-amount">
                                <strong>{{ number_format((int) $link->payments_count) }}</strong>
                                @if($link->max_payments)
                                    <span class="pla-muted"> / {{ number_format((int) $link->max_payments) }}</span>
                                @endif
                            </td>
                            <td>
                                <span class="pla-pill pla-pill--{{ $statusCol }}">{{ $statusLbl }}</span>
                            </td>
                            <td class="pla-muted pla-nowrap">
                                {{ $link->created_at?->format('d M Y') }}
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.payment-links.show', $link) }}"
                                   class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1">
                                    <x-icon name="eye" height="14" width="14"/>
                                    @lang('View')
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9">
                                <x-admin-not-found
                                    :title="__('No payment links found')"
                                    :message="__('No payment links match the current filters. Clear filters or wait for users to create new links.')"
                                    icon="fa-link"
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($paymentLinks->hasPages())
            <div class="pla-table-card__footer">{{ $paymentLinks->links() }}</div>
        @endif
    </div>

@endsection
