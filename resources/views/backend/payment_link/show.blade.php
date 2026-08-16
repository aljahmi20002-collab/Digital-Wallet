@extends('backend.payment_link.layout')

@section('title', __('Payment Link Details'))
@section('sub_title', __('Payment Link #:id', ['id' => $paymentLink->id]))
@section('sub_icon', 'eye')
@section('sub_subtitle', $paymentLink->title)

@section('sub_action')
    <a href="{{ route('admin.payment-links.index') }}"
       class="btn btn-light d-inline-flex align-items-center gap-1">
        <x-icon name="back" height="18" width="18"/>
        @lang('Back to List')
    </a>
@endsection

@section('sub_content')
    @php
        $owner       = $paymentLink->user;
        $merchant    = $paymentLink->merchant;
        $currency    = $paymentLink->currencyCode();
        $isExpired   = $paymentLink->isExpired();
        $isMaxedOut  = $paymentLink->isMaxedOut();
        $statusLabel = $isExpired ? __('Expired') : ($isMaxedOut ? __('Limit Reached') : $paymentLink->status->label());
        $statusColor = $isExpired || $isMaxedOut ? 'warning' : $paymentLink->status->color();
        $isActive    = $paymentLink->status === \App\Enums\PaymentLinkStatus::ACTIVE;
        $publicUrl   = $paymentLink->publicUrl();
    @endphp

    <div class="pla-show">
        {{-- Hero --}}
        <div class="pla-hero mb-4">
            <div class="pla-hero__main">
                <div class="pla-hero__eyebrow">
                    <span class="pla-hero__icon"><x-icon name="payment" height="20" width="20"/></span>
                    @lang('Payment Link Control')
                </div>
                <h2 class="pla-hero__title">{{ $paymentLink->title }}</h2>
                <div class="pla-hero__meta">
                    <span>{{ __('ID #:id', ['id' => $paymentLink->id]) }}</span>
                    @if($owner)
                        <span>{{ $owner->name }}</span>
                    @endif
                    @if($merchant)
                        <span>{{ $merchant->business_name }}</span>
                    @endif
                    <span>{{ $currency }}</span>
                </div>
            </div>

            <div class="pla-hero__aside">
                <span class="pla-pill pla-pill--{{ $statusColor }} pla-pill--xl">
                    {{ $statusLabel }}
                </span>

                @can('payment-link-manage')
                    <div class="pla-action-stack">
                        <form method="POST"
                              action="{{ route('admin.payment-links.toggle-status', $paymentLink) }}"
                              onsubmit="return confirm('{{ __('Toggle the status of this payment link?') }}')">
                            @csrf
                            <button type="submit"
                                    class="btn {{ $isActive ? 'btn-outline-warning' : 'btn-success' }} btn-sm d-inline-flex align-items-center gap-1">
                                <x-icon name="{{ $isActive ? 'close' : 'check' }}" height="14" width="14"/>
                                {{ $isActive ? __('Deactivate') : __('Activate') }}
                            </button>
                        </form>

                        <form method="POST"
                              action="{{ route('admin.payment-links.destroy', $paymentLink) }}"
                              onsubmit="return confirm('{{ __('Delete this payment link permanently?') }}')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger btn-sm d-inline-flex align-items-center gap-1">
                                <x-icon name="delete" height="14" width="14"/>
                                @lang('Delete')
                            </button>
                        </form>
                    </div>
                @endcan
            </div>
        </div>

        {{-- KPI strip --}}
        <div class="pla-show-stats mb-4">
            <div class="pla-show-stat">
                <span class="pla-show-stat__icon pla-show-stat__icon--primary">
                    <x-icon name="wallet" height="20" width="20"/>
                </span>
                <span class="pla-show-stat__label">@lang('Amount')</span>
                <strong class="pla-show-stat__value">
                    @if($paymentLink->isOpenAmount())
                        @lang('Open')
                    @else
                        {{ $currency }} {{ number_format((float) $paymentLink->amount, 2) }}
                    @endif
                </strong>
            </div>
            <div class="pla-show-stat">
                <span class="pla-show-stat__icon pla-show-stat__icon--info">
                    <x-icon name="transaction-2" height="20" width="20"/>
                </span>
                <span class="pla-show-stat__label">@lang('Payments')</span>
                <strong class="pla-show-stat__value">
                    {{ number_format((int) $paymentLink->payments_count) }}
                    @if($paymentLink->max_payments)
                        <small class="pla-muted"> / {{ number_format((int) $paymentLink->max_payments) }}</small>
                    @endif
                </strong>
            </div>
            <div class="pla-show-stat">
                <span class="pla-show-stat__icon pla-show-stat__icon--success">
                    <x-icon name="calendar" height="20" width="20"/>
                </span>
                <span class="pla-show-stat__label">@lang('Created')</span>
                <strong class="pla-show-stat__value">
                    {{ $paymentLink->created_at?->format('d M Y, H:i') }}
                </strong>
            </div>
            <div class="pla-show-stat">
                <span class="pla-show-stat__icon pla-show-stat__icon--warning">
                    <x-icon name="schedule" height="20" width="20"/>
                </span>
                <span class="pla-show-stat__label">@lang('Expires')</span>
                <strong class="pla-show-stat__value">
                    @if($paymentLink->expires_at)
                        {{ $paymentLink->expires_at->format('d M Y, H:i') }}
                    @else
                        <span class="pla-muted">@lang('Never')</span>
                    @endif
                </strong>
            </div>
        </div>

        <div class="row g-4">
            {{-- Left: Detail grid --}}
            <div class="col-xl-8">
                <div class="pla-card mb-4">
                    <div class="pla-card__head">
                        <div>
                            <span class="pla-card__title">@lang('Link Overview')</span>
                            <div class="pla-card__subtitle">@lang('Configuration and lifecycle data')</div>
                        </div>
                        <span class="pla-pill pla-pill--{{ $statusColor }}">{{ $statusLabel }}</span>
                    </div>
                    <div class="pla-card__body">
                        <div class="pla-detail-grid">
                            <div class="pla-detail-item">
                                <span class="pla-detail-item__icon"><x-icon name="payment" height="18" width="18"/></span>
                                <span class="pla-detail-item__label">@lang('Title')</span>
                                <strong class="pla-detail-item__value">{{ $paymentLink->title }}</strong>
                                <span class="pla-detail-item__meta">{{ $paymentLink->description ?: __('No description') }}</span>
                            </div>

                            <div class="pla-detail-item">
                                <span class="pla-detail-item__icon"><x-icon name="clipboard" height="18" width="18"/></span>
                                <span class="pla-detail-item__label">@lang('Token')</span>
                                <strong class="pla-detail-item__value pla-token">{{ $paymentLink->token }}</strong>
                                <span class="pla-detail-item__meta">{{ __('Public URL identifier') }}</span>
                            </div>

                            <div class="pla-detail-item">
                                <span class="pla-detail-item__icon"><x-icon name="money" height="18" width="18"/></span>
                                <span class="pla-detail-item__label">@lang('Amount')</span>
                                <strong class="pla-detail-item__value">
                                    @if($paymentLink->isOpenAmount())
                                        @lang('Open Amount')
                                    @else
                                        {{ $currency }} {{ number_format((float) $paymentLink->amount, 2) }}
                                    @endif
                                </strong>
                                <span class="pla-detail-item__meta">
                                    @if($paymentLink->isOpenAmount())
                                        @if($paymentLink->min_amount !== null)
                                            {{ __('Min :v', ['v' => number_format((float) $paymentLink->min_amount, 2)]) }}
                                        @endif
                                        @if($paymentLink->max_amount !== null)
                                            {{ __('Max :v', ['v' => number_format((float) $paymentLink->max_amount, 2)]) }}
                                        @endif
                                    @else
                                        {{ __('Fixed amount') }}
                                    @endif
                                </span>
                            </div>

                            <div class="pla-detail-item">
                                <span class="pla-detail-item__icon"><x-icon name="fee" height="18" width="18"/></span>
                                <span class="pla-detail-item__label">@lang('Merchant Fee')</span>
                                <strong class="pla-detail-item__value">
                                    @if($paymentLink->merchant_fee !== null)
                                        {{ rtrim(rtrim(number_format((float) $paymentLink->merchant_fee, 4), '0'), '.') }}%
                                    @else
                                        <span class="pla-muted">@lang('No fee')</span>
                                    @endif
                                </strong>
                                <span class="pla-detail-item__meta">{{ __('Snapshotted at link creation') }}</span>
                            </div>

                            <div class="pla-detail-item">
                                <span class="pla-detail-item__icon"><x-icon name="transaction-2" height="18" width="18"/></span>
                                <span class="pla-detail-item__label">@lang('Payments Received')</span>
                                <strong class="pla-detail-item__value">
                                    {{ number_format((int) $paymentLink->payments_count) }}
                                    @if($paymentLink->max_payments)
                                        / {{ number_format((int) $paymentLink->max_payments) }}
                                    @endif
                                </strong>
                                <span class="pla-detail-item__meta">{{ __('Successful payments counter') }}</span>
                            </div>

                            <div class="pla-detail-item">
                                <span class="pla-detail-item__icon"><x-icon name="schedule" height="18" width="18"/></span>
                                <span class="pla-detail-item__label">@lang('Expires At')</span>
                                <strong class="pla-detail-item__value">
                                    {{ $paymentLink->expires_at?->format('d M Y, H:i') ?? __('No expiry') }}
                                </strong>
                                <span class="pla-detail-item__meta">
                                    @if($paymentLink->expires_at && $paymentLink->expires_at->isFuture())
                                        {{ $paymentLink->expires_at->diffForHumans() }}
                                    @endif
                                </span>
                            </div>

                            <div class="pla-detail-item">
                                <span class="pla-detail-item__icon"><x-icon name="wallet-payment" height="18" width="18"/></span>
                                <span class="pla-detail-item__label">@lang('Wallet Reference')</span>
                                <strong class="pla-detail-item__value">
                                    {{ $paymentLink->wallet_reference ?: __('Not available') }}
                                </strong>
                                <span class="pla-detail-item__meta">{{ __('Receiver wallet UUID') }}</span>
                            </div>

                            <div class="pla-detail-item">
                                <span class="pla-detail-item__icon"><x-icon name="calendar" height="18" width="18"/></span>
                                <span class="pla-detail-item__label">@lang('Updated')</span>
                                <strong class="pla-detail-item__value">{{ $paymentLink->updated_at?->format('d M Y, H:i') }}</strong>
                                <span class="pla-detail-item__meta">{{ __('Last modification time') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="pla-card">
                    <div class="pla-card__head">
                        <div>
                            <span class="pla-card__title">@lang('Public URL')</span>
                            <div class="pla-card__subtitle">@lang('Share this URL with payers')</div>
                        </div>
                        <a href="{{ $publicUrl }}" target="_blank" rel="noopener"
                           class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1">
                            <x-icon name="share" height="14" width="14"/>
                            @lang('Open')
                        </a>
                    </div>
                    <div class="pla-card__body">
                        <div class="input-group">
                            <input type="text" class="form-control form-control-sm pla-public-url"
                                   value="{{ $publicUrl }}" readonly>
                            <button type="button"
                                    class="btn btn-sm btn-light"
                                    data-pla-copy="{{ $publicUrl }}">
                                <x-icon name="clipboard" height="14" width="14"/>
                                @lang('Copy')
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right: Side panel --}}
            <div class="col-xl-4">
                <div class="pla-card mb-4">
                    <div class="pla-card__head">
                        <span class="pla-card__title">@lang('Owner')</span>
                        @if($owner)
                            <span class="pla-pill pla-pill--info">{{ '@'.$owner->username }}</span>
                        @endif
                    </div>
                    <div class="pla-card__body">
                        @if($owner)
                            @php
                                $initials = collect(explode(' ', trim((string) $owner->name)))
                                    ->filter()
                                    ->take(2)
                                    ->map(fn (string $part) => mb_substr($part, 0, 1))
                                    ->implode('') ?: 'U';
                            @endphp
                            <div class="pla-user-profile">
                                <div class="pla-user-profile__avatar">{{ $initials }}</div>
                                <div class="pla-user-profile__content">
                                    <strong>{{ $owner->name }}</strong>
                                    <span>{{ $owner->email }}</span>
                                </div>
                            </div>

                            <div class="pla-side-list mt-3">
                                <div class="pla-side-list__row">
                                    <span>@lang('Username')</span>
                                    <strong>{{ '@'.$owner->username }}</strong>
                                </div>
                                <div class="pla-side-list__row">
                                    <span>@lang('Joined')</span>
                                    <strong>{{ $owner->created_at?->format('d M Y') ?? __('N/A') }}</strong>
                                </div>
                                <div class="pla-side-list__row">
                                    <span>@lang('Role')</span>
                                    <strong>{{ $owner->role?->value ?? __('User') }}</strong>
                                </div>
                            </div>

                            <a href="{{ route('admin.user.manage', $owner->username) }}"
                               class="btn btn-outline-primary btn-sm w-100 mt-3 d-inline-flex align-items-center justify-content-center gap-1">
                                <x-icon name="eye" height="14" width="14"/>
                                @lang('View Profile')
                            </a>
                        @else
                            <div class="pla-muted">@lang('User has been removed.')</div>
                        @endif
                    </div>
                </div>

                @if($merchant)
                    <div class="pla-card">
                        <div class="pla-card__head">
                            <span class="pla-card__title">@lang('Merchant Shop')</span>
                            @if($merchant->status)
                                <span class="pla-pill pla-pill--secondary">{{ $merchant->status->label() }}</span>
                            @endif
                        </div>
                        <div class="pla-card__body">
                            <div class="pla-side-list">
                                <div class="pla-side-list__row">
                                    <span>@lang('Business Name')</span>
                                    <strong>{{ $merchant->business_name }}</strong>
                                </div>
                                <div class="pla-side-list__row">
                                    <span>@lang('Email')</span>
                                    <strong>{{ $merchant->business_email }}</strong>
                                </div>
                                <div class="pla-side-list__row">
                                    <span>@lang('Site URL')</span>
                                    <strong>{{ $merchant->site_url }}</strong>
                                </div>
                                <div class="pla-side-list__row">
                                    <span>@lang('Currency')</span>
                                    <strong>{{ $merchant->currency?->code ?? '—' }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="{{ asset('backend/js/payment-link-admin.js?v=' . config('app.version')) }}"></script>
    @endpush
@endsection
