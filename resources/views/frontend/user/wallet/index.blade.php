@php use App\Constants\CurrencyRole; @endphp
@extends('frontend.layouts.user.index')
@section('title', __('My Wallets'))
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card single-form-card">
                <x-user-feature-header
                    :title="__('My Wallets')"
                    :subtitle="__('Review balances, supported roles, and recent wallet activity.')"
                    icon="fas fa-wallet"
                    class="wallet-page-header"
                >
                    <a href="{{ route('user.settings.wallet-pin') }}"
                       class="btn btn-light-primary btn-sm d-flex align-items-center wallet-page-action wallet-page-action--pin">
                        <i class="fas fa-key me-1" aria-hidden="true"></i>
                        {{ __('Wallet PIN') }}
                    </a>
                    <button type="button" class="btn btn-light-primary btn-sm d-flex align-items-center wallet-page-action" data-bs-toggle="modal"
                            data-bs-target="#addWalletModal">
                        <x-icon name="wallet-1" height="20" width="20" class="me-1"/>
                        {{ __('Create Wallet') }}
                    </button>
                </x-user-feature-header>
                <div class="card-body">
                    <div class="wallet-grid">
                        @forelse($wallets as $wallet)
                            @php
                                $amountColor = $wallet->latestTransaction?->amount_flow->color($wallet->latestTransaction->status);
                                $amountSign = $wallet->latestTransaction?->amount_flow->sign($wallet->latestTransaction->status);
                                $receiveMoneyUrl = $wallet->receiveMoneyUrl();
                                $formattedWalletId = implode(' ', str_split($wallet->uuid, 3));
                                $isDefaultWallet = $wallet->currency->code === siteCurrency();
                            @endphp

                            <article class="wallet-card">
                                <div class="wallet-header">
                                    <div class="wallet-currency-info">
                                        <span class="wallet-currency-img-wrap">
                                            <img src="{{ asset($wallet->currency->flag) }}" class="wallet-currency-img"
                                                 alt="{{ $wallet->currency->code }}" loading="lazy">
                                        </span>
                                        <div class="wallet-card__identity">
                                            <div class="wallet-card__title-row">
                                                <h6>{{ $wallet->currency->code }}</h6>
                                                @if($isDefaultWallet)
                                                    <span class="wallet-default-pill">
                                                        <i class="fas fa-check-circle" aria-hidden="true"></i>
                                                        {{ __('Default') }}
                                                    </span>
                                                @endif
                                            </div>
                                            <p class="wallet-id-chip">
                                                <span>{{ __('Wallet ID') }}</span>
                                                <strong>{{ $formattedWalletId }}</strong>
                                                <button type="button"
                                                        class="wallet-id-copy copyNow"
                                                        data-clipboard-text="{{ $wallet->uuid }}"
                                                        data-bs-toggle="tooltip"
                                                        title="{{ __('Copy Wallet ID') }}"
                                                        aria-label="{{ __('Copy Wallet ID') }}">
                                                    <i class="fas fa-copy" aria-hidden="true"></i>
                                                </button>
                                            </p>
                                        </div>
                                    </div>

                                    <div class="wallet-card__balance-panel">
                                        <span>{{ __('Balance') }}</span>
                                        <h5 class="wallet-balance mb-0">{{ $wallet->currency->symbol }}{{ number_format($wallet->balance, 2) }}</h5>
                                        <p>
                                            1 {{ siteCurrency() }}
                                            = {{ $wallet->currency->exchange_rate }} {{ $wallet->currency->code }}
                                        </p>
                                    </div>
                                </div>

                                <div class="wallet-card__support-row">
                                    <div class="wallet-roles" aria-label="{{ __('Supported wallet roles') }}">
                                        @foreach($wallet->currency->activeRoles as $role)
                                            <div class="wallet-role-icon bg-{{ CurrencyRole::getBadgesColor($role->role_name) }}"
                                                 title="{{ strtoupper($role->role_name) }}" data-bs-toggle="tooltip">
                                                <x-icon name="{{ str_replace('_', '-', $role->role_name) }}" height="18"
                                                        width="18"/>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="wallet-footer">
                                    <div class="wallet-transaction-info">
                                        @if($wallet->latestTransaction)
                                            <p class="small mb-0">
                                                <span class="text-muted">{{ __('Recent:') }}</span>
                                                <span class="fw-bold {{ $amountColor }}">
                                                    {{ $amountSign.getSymbol($wallet->currency->code).number_format($wallet->latestTransaction->amount, 2) }}
                                                </span>
                                                <span class="text-muted">{{ __('via') }}</span>
                                                <span class="fw-bold text-{{ $wallet->latestTransaction->trx_type->badgeColor() }}">
                                                    {{ $wallet->latestTransaction->trx_type->label() }}
                                                </span>
                                            </p>
                                        @else
                                            <p class="small mb-0 text-muted">{{ __('No recent activity.') }}</p>
                                        @endif
                                    </div>

                                    <div class="wallet-actions">
                                        @if($wallet->status && $wallet->is_sender)
                                            <button type="button"
                                                    class="wallet-btn-icon"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#walletReceiveQrModal{{ $wallet->id }}"
                                                    title="{{ __('Receive QR') }}"
                                                    aria-label="{{ __('Receive QR') }}">
                                                <i class="fas fa-qrcode"></i>
                                            </button>
                                        @endif
                                        <a href="{{ route('user.deposit.create', ['uuid' => $wallet->uuid]) }}" class="wallet-btn-icon" title="{{ __('Deposit') }}"
                                           data-bs-toggle="tooltip">
                                            <x-icon name="deposit" height="20" width="20"/>
                                        </a>
                                        @if($wallet->is_withdraw)
                                            <a href="{{ route('user.withdraw.create', ['uuid' => $wallet->uuid]) }}" class="wallet-btn-icon" title="{{ __('Withdraw') }}"
                                               data-bs-toggle="tooltip">
                                                <x-icon name="withdraw" height="20" width="20"/>
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </article>

                            @if($wallet->status && $wallet->is_sender)
                                <div class="modal fade wallet-receive-qr-modal"
                                     id="walletReceiveQrModal{{ $wallet->id }}"
                                     tabindex="-1"
                                     aria-labelledby="walletReceiveQrModalLabel{{ $wallet->id }}"
                                     aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered wallet-receive-qr-modal__dialog">
                                        <div class="modal-content border-0">
                                            <div class="wallet-receive-qr-modal__hero">
                                                <div class="wallet-receive-qr-modal__hero-copy">
                                                    <span class="wallet-receive-qr-modal__mark" aria-hidden="true">
                                                        <i class="fas fa-qrcode"></i>
                                                    </span>
                                                    <div>
                                                        <span class="wallet-receive-qr-modal__eyebrow">{{ __('Receive QR') }}</span>
                                                        <h6 class="modal-title" id="walletReceiveQrModalLabel{{ $wallet->id }}">
                                                            {{ __('My QR Code') }}
                                                        </h6>
                                                        <small>{{ __('Receive to your :wallet wallet', ['wallet' => $wallet->currency->code]) }}</small>
                                                    </div>
                                                </div>
                                                <button type="button"
                                                        class="btn-close wallet-receive-qr-modal__close"
                                                        data-bs-dismiss="modal"
                                                        aria-label="{{ __('Close') }}"></button>
                                            </div>

                                            <div class="modal-body wallet-receive-qr-modal__body">
                                                <div class="wallet-receive-qr-modal__summary">
                                                    <div>
                                                        <span>{{ __('Wallet ID') }}</span>
                                                        <strong>{{ $wallet->uuid }}</strong>
                                                    </div>
                                                    <div>
                                                        <span>{{ __('Receive Currency') }}</span>
                                                        <strong>{{ $wallet->currency->code }}</strong>
                                                    </div>
                                                </div>

                                                <div class="wallet-receive-qr-modal__scan-card">
                                                    <div class="wallet-receive-qr-modal__code"
                                                         id="walletReceiveQrCode{{ $wallet->id }}">
                                                        {!! $wallet->receiveMoneyQrCodeSvg(196) !!}
                                                    </div>
                                                    <p class="wallet-receive-qr-modal__hint">
                                                        {{ __('Share this QR or link with the payer.') }}
                                                    </p>
                                                </div>

                                                <div class="input-group input-group-sm wallet-receive-qr-modal__url">
                                                    <input type="text"
                                                           class="form-control form-control-sm"
                                                           value="{{ $receiveMoneyUrl }}"
                                                           readonly
                                                           aria-label="{{ __('Receive QR link') }}">
                                                </div>
                                            </div>

                                            <div class="modal-footer wallet-receive-qr-modal__footer">
                                                <button type="button"
                                                        class="btn btn-sm btn-primary wallet-receive-qr-modal__primary"
                                                        data-wallet-qr-share
                                                        data-wallet-qr-link="{{ $receiveMoneyUrl }}"
                                                        data-wallet-qr-title="{{ __('DigiKash :currency Receive QR', ['currency' => $wallet->currency->code]) }}"
                                                        data-wallet-qr-text="{{ __('Use this DigiKash QR link to send money to my :currency wallet.', ['currency' => $wallet->currency->code]) }}"
                                                        data-toast-message="{{ __('Receive link ready to share') }}">
                                                    <i class="fas fa-share-alt me-1"></i>{{ __('Share QR') }}
                                                </button>
                                                <button type="button"
                                                        class="btn btn-sm btn-outline-primary wallet-receive-qr-modal__secondary"
                                                        data-wallet-qr-copy
                                                        data-wallet-qr-link="{{ $receiveMoneyUrl }}"
                                                        data-toast-message="{{ __('Receive link copied') }}">
                                                    <i class="fas fa-copy me-1"></i>{{ __('Copy Link') }}
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @empty
                            <div class="wallet-grid__empty">
                                <x-user-not-found
                                    :title="__('No wallets yet')"
                                    :message="__('Create your first wallet to manage balances, deposits, withdrawals, and receive QR links.')"
                                    icon="fa-wallet"
                                />
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
