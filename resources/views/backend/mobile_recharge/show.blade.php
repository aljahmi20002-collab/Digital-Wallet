@extends('backend.mobile_recharge.layout')

@section('title', __('Mobile Recharge Details'))
@section('sub_title', __('Recharge #:id', ['id' => $recharge->id]))
@section('sub_icon', 'eye')
@section('sub_subtitle', __('Provider reference: :ref', ['ref' => $recharge->provider_reference ?: __('Pending')]))

@section('sub_action')
    <a href="{{ route('admin.mobile-recharge.index') }}" class="btn btn-light d-inline-flex align-items-center gap-1">
        <x-icon name="back" height="16" width="16"/>
        @lang('Back to List')
    </a>
@endsection

@section('sub_content')
    @php
        $owner = $recharge->user;
        $initials = $owner
            ? collect(explode(' ', trim((string) $owner->name)))
                ->filter()
                ->take(2)
                ->map(fn (string $part) => mb_substr($part, 0, 1))
                ->implode('')
            : 'U';
        $initials = $initials ?: 'U';
    @endphp

    <div class="mra-show">
        <section class="mra-hero mb-4">
            <div class="mra-hero__main">
                <span class="mra-hero__eyebrow">
                    <x-icon name="mobile-recharge" height="18" width="18"/>
                    @lang('Recharge Control')
                </span>
                <h2 class="mra-hero__title">{{ $recharge->phone_number }}</h2>
                <div class="mra-hero__meta">
                    <span>{{ __('Request #:id', ['id' => $recharge->id]) }}</span>
                    <span>{{ title($recharge->provider) }}</span>
                    <span>{{ $recharge->currency }}</span>
                    <span>{{ $recharge->created_at?->format('d M Y, H:i') }}</span>
                </div>
            </div>

            <div class="mra-hero__aside">
                <span class="mra-pill mra-pill--{{ $recharge->status->color() }} mra-pill--xl">
                    {{ $recharge->status->label() }}
                </span>
                @if($recharge->transaction)
                    <a href="{{ route('admin.transaction', ['trx' => $recharge->transaction->trx_id]) }}" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1">
                        <x-icon name="transaction-2" height="14" width="14"/>
                        @lang('Open Transaction')
                    </a>
                @endif
            </div>
        </section>

        <div class="mra-show-stats mb-4">
            <div class="mra-show-stat">
                <span class="mra-show-stat__icon mra-show-stat__icon--primary">
                    <x-icon name="money" height="20" width="20"/>
                </span>
                <span class="mra-show-stat__label">@lang('Amount')</span>
                <strong class="mra-show-stat__value">{{ number_format((float) $recharge->amount, 2) }} {{ $recharge->currency }}</strong>
            </div>
            <div class="mra-show-stat">
                <span class="mra-show-stat__icon mra-show-stat__icon--info">
                    <x-icon name="fee" height="20" width="20"/>
                </span>
                <span class="mra-show-stat__label">@lang('Fee')</span>
                <strong class="mra-show-stat__value">{{ number_format((float) $recharge->fee, 2) }} {{ $recharge->currency }}</strong>
            </div>
            <div class="mra-show-stat">
                <span class="mra-show-stat__icon mra-show-stat__icon--success">
                    <x-icon name="wallet" height="20" width="20"/>
                </span>
                <span class="mra-show-stat__label">@lang('Total Debit')</span>
                <strong class="mra-show-stat__value">{{ number_format((float) $recharge->total_amount, 2) }} {{ $recharge->currency }}</strong>
            </div>
            <div class="mra-show-stat">
                <span class="mra-show-stat__icon mra-show-stat__icon--warning">
                    <x-icon name="schedule" height="20" width="20"/>
                </span>
                <span class="mra-show-stat__label">@lang('Processed')</span>
                <strong class="mra-show-stat__value">{{ $recharge->processed_at?->format('d M Y, H:i') ?? __('Waiting') }}</strong>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-xl-8">
                <div class="mra-card mb-4">
                    <div class="mra-card__head">
                        <div>
                            <span class="mra-card__eyebrow">@lang('Overview')</span>
                            <h3 class="mra-card__title">@lang('Recharge Details')</h3>
                        </div>
                        <span class="mra-pill mra-pill--{{ $recharge->status->color() }}">{{ $recharge->status->label() }}</span>
                    </div>
                    <div class="mra-card__body">
                        <div class="mra-detail-grid">
                            <div class="mra-detail-item">
                                <span class="mra-detail-item__icon"><x-icon name="phone-verification" height="18" width="18"/></span>
                                <span class="mra-detail-item__label">@lang('Phone Number')</span>
                                <strong class="mra-detail-item__value">{{ $recharge->phone_number }}</strong>
                                <span class="mra-detail-item__meta">{{ $recharge->operator ?: __('Any operator') }}</span>
                            </div>
                            <div class="mra-detail-item">
                                <span class="mra-detail-item__icon"><x-icon name="api" height="18" width="18"/></span>
                                <span class="mra-detail-item__label">@lang('Provider')</span>
                                <strong class="mra-detail-item__value">{{ title($recharge->provider) }}</strong>
                                <span class="mra-detail-item__meta">{{ $recharge->provider_reference ?: __('Reference pending') }}</span>
                            </div>
                            <div class="mra-detail-item">
                                <span class="mra-detail-item__icon"><x-icon name="wallet" height="18" width="18"/></span>
                                <span class="mra-detail-item__label">@lang('Wallet')</span>
                                <strong class="mra-detail-item__value">{{ $recharge->wallet?->currency?->code ?? $recharge->currency }}</strong>
                                <span class="mra-detail-item__meta">{{ $recharge->wallet?->uuid ?? __('Wallet unavailable') }}</span>
                            </div>
                            <div class="mra-detail-item">
                                <span class="mra-detail-item__icon"><x-icon name="failed" height="18" width="18"/></span>
                                <span class="mra-detail-item__label">@lang('Failure Reason')</span>
                                <strong class="mra-detail-item__value">{{ $recharge->failure_reason ?: __('None') }}</strong>
                                <span class="mra-detail-item__meta">{{ __('Provider or system message') }}</span>
                            </div>
                            <div class="mra-detail-item">
                                <span class="mra-detail-item__icon"><x-icon name="calendar" height="18" width="18"/></span>
                                <span class="mra-detail-item__label">@lang('Created')</span>
                                <strong class="mra-detail-item__value">{{ $recharge->created_at?->format('d M Y, H:i') }}</strong>
                                <span class="mra-detail-item__meta">{{ $recharge->created_at?->diffForHumans() }}</span>
                            </div>
                            <div class="mra-detail-item">
                                <span class="mra-detail-item__icon"><x-icon name="transaction-2" height="18" width="18"/></span>
                                <span class="mra-detail-item__label">@lang('Transaction')</span>
                                <strong class="mra-detail-item__value">{{ $recharge->transaction?->trx_id ?? __('No linked transaction') }}</strong>
                                <span class="mra-detail-item__meta">{{ $recharge->transaction?->status?->label() ?? __('Not available') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mra-card">
                    <div class="mra-card__head">
                        <div>
                            <span class="mra-card__eyebrow">@lang('Provider')</span>
                            <h3 class="mra-card__title">@lang('Provider Payload')</h3>
                        </div>
                        <span class="mra-pill mra-pill--secondary">@lang('JSON')</span>
                    </div>
                    <div class="mra-card__body">
                        <pre class="mra-json mb-0">{{ json_encode($recharge->metadata ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="mra-card mb-4">
                    <div class="mra-card__head">
                        <h3 class="mra-card__title">@lang('Customer')</h3>
                        @if($owner)
                            <span class="mra-pill mra-pill--info">{{ '@'.$owner->username }}</span>
                        @endif
                    </div>
                    <div class="mra-card__body">
                        @if($owner)
                            <div class="mra-user-profile">
                                <span class="mra-user-profile__avatar">{{ $initials }}</span>
                                <div class="mra-user-profile__content">
                                    <strong>{{ $owner->name }}</strong>
                                    <span>{{ $owner->email }}</span>
                                </div>
                            </div>

                            <div class="mra-side-list mt-3">
                                <div class="mra-side-list__row">
                                    <span>@lang('Username')</span>
                                    <strong>{{ '@'.$owner->username }}</strong>
                                </div>
                                <div class="mra-side-list__row">
                                    <span>@lang('Phone')</span>
                                    <strong>{{ $owner->phone ?: __('Not set') }}</strong>
                                </div>
                                <div class="mra-side-list__row">
                                    <span>@lang('Joined')</span>
                                    <strong>{{ $owner->created_at?->format('d M Y') ?? __('N/A') }}</strong>
                                </div>
                            </div>

                            <a href="{{ route('admin.user.manage', $owner->username) }}" class="btn btn-outline-primary btn-sm w-100 mt-3 d-inline-flex align-items-center justify-content-center gap-1">
                                <x-icon name="eye" height="14" width="14"/>
                                @lang('Open User')
                            </a>
                        @else
                            <span class="mra-muted">@lang('User has been removed.')</span>
                        @endif
                    </div>
                </div>

                <div class="mra-card">
                    <div class="mra-card__head">
                        <h3 class="mra-card__title">@lang('Transaction')</h3>
                        @if($recharge->transaction)
                            <span class="mra-pill mra-pill--{{ $recharge->transaction->status->color() }}">{{ $recharge->transaction->status->label() }}</span>
                        @endif
                    </div>
                    <div class="mra-card__body">
                        @if($recharge->transaction)
                            <div class="mra-side-list">
                                <div class="mra-side-list__row">
                                    <span>@lang('TRX ID')</span>
                                    <strong>{{ $recharge->transaction->trx_id }}</strong>
                                </div>
                                <div class="mra-side-list__row">
                                    <span>@lang('Reference')</span>
                                    <strong>{{ $recharge->transaction->trx_reference ?: __('Pending') }}</strong>
                                </div>
                                <div class="mra-side-list__row">
                                    <span>@lang('Provider')</span>
                                    <strong>{{ title($recharge->transaction->provider ?? $recharge->provider) }}</strong>
                                </div>
                            </div>
                        @else
                            <span class="mra-muted">@lang('No linked transaction.')</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
