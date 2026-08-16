@extends('frontend.layouts.user.index')
@section('title', __('My QR Code'))
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card single-form-card wallet-qr-page">
                <x-user-feature-header
                    :title="__('My QR Code')"
                    :subtitle="__('Share your receive QR or scan another DigiKash QR to pay and send.')"
                    icon="fas fa-qrcode"
                >
                    <button type="button" class="btn btn-light-primary btn-sm d-flex align-items-center" data-dk-qr-scanner-open>
                        <i class="fas fa-camera me-1"></i>
                        {{ __('Scan QR') }}
                    </button>
                    <a href="{{ route('user.wallet.index') }}" class="btn btn-outline-primary btn-sm d-flex align-items-center">
                        <i class="fas fa-wallet me-1"></i>
                        {{ __('My Wallets') }}
                    </a>
                </x-user-feature-header>

                <div class="card-body wallet-qr-page__body">
                    @if($wallets->isNotEmpty())
                        <div class="wallet-qr-page__intro">
                            <div class="wallet-qr-page__intro-mark" aria-hidden="true">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <div>
                                <span>{{ __('Receive with confidence') }}</span>
                                <p>{{ __('Share a QR or link with the payer. Your own receive QR does not open a self-send action here.') }}</p>
                            </div>
                        </div>

                        <div class="wallet-qr-grid">
                            @foreach($wallets as $wallet)
                                @php
                                    $receiveMoneyUrl = $wallet->receiveMoneyUrl();
                                    $shareTitle = __('DigiKash :currency Receive QR', ['currency' => $wallet->currency->code]);
                                    $shareText = __('Use this DigiKash QR link to send money to my :currency wallet.', ['currency' => $wallet->currency->code]);
                                @endphp

                                <article class="wallet-qr-card" data-wallet-qr-card>
                                    <div class="wallet-qr-card__scan">
                                        <div class="wallet-qr-card__code" data-wallet-qr-svg>
                                            {!! $wallet->receiveMoneyQrCodeSvg(260) !!}
                                        </div>
                                        <span class="wallet-qr-card__scan-label">
                                            <i class="fas fa-lock"></i>
                                            {{ __('Receive only') }}
                                        </span>
                                    </div>

                                    <div class="wallet-qr-card__content">
                                        <div class="wallet-qr-card__top">
                                            <div class="wallet-qr-card__currency">
                                                <span class="wallet-qr-card__flag">
                                                    <img src="{{ asset($wallet->currency->flag) }}" alt="{{ $wallet->currency->code }}" loading="lazy">
                                                </span>
                                                <div>
                                                    <h6>{{ $wallet->currency->code }}</h6>
                                                    <p>{{ __('Wallet ID') }} {{ implode(' ', str_split($wallet->uuid, 3)) }}</p>
                                                </div>
                                            </div>
                                            <div class="wallet-qr-card__balance">
                                                <span>{{ __('Balance') }}</span>
                                                <strong>{{ $wallet->currency->symbol }}{{ number_format($wallet->balance, 2) }}</strong>
                                            </div>
                                        </div>

                                        <div class="wallet-qr-card__meta">
                                            <div>
                                                <span>{{ __('Currency') }}</span>
                                                <strong>{{ $wallet->currency->name }}</strong>
                                            </div>
                                            <div>
                                                <span>{{ __('Rate') }}</span>
                                                <strong>1 {{ siteCurrency() }} = {{ $wallet->currency->exchange_rate }} {{ $wallet->currency->code }}</strong>
                                            </div>
                                        </div>

                                        <div class="wallet-qr-card__link-box">
                                            <div>
                                                <span>{{ __('Receive link') }}</span>
                                                <input type="text"
                                                       value="{{ $receiveMoneyUrl }}"
                                                       data-wallet-qr-link-input
                                                       readonly
                                                       aria-label="{{ __('Receive QR link') }}">
                                            </div>
                                            <button type="button"
                                                    data-wallet-qr-copy
                                                    data-wallet-qr-link="{{ $receiveMoneyUrl }}"
                                                    data-wallet-qr-success-label="{{ __('Copied') }}"
                                                    data-toast-message="{{ __('Receive link copied') }}"
                                                    aria-label="{{ __('Copy receive link') }}">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                        </div>

                                        <div class="wallet-qr-card__actions">
                                            <button type="button"
                                                    class="wallet-qr-card__btn wallet-qr-card__btn--primary"
                                                    data-wallet-qr-share
                                                    data-wallet-qr-link="{{ $receiveMoneyUrl }}"
                                                    data-wallet-qr-title="{{ $shareTitle }}"
                                                    data-wallet-qr-text="{{ $shareText }}"
                                                    data-wallet-qr-success-label="{{ __('Shared') }}"
                                                    data-toast-message="{{ __('Receive link ready to share') }}">
                                                <i class="fas fa-share-alt"></i>
                                                <span data-wallet-qr-action-label>{{ __('Share QR') }}</span>
                                            </button>
                                            <button type="button"
                                                    class="wallet-qr-card__btn wallet-qr-card__btn--secondary"
                                                    data-wallet-qr-copy
                                                    data-wallet-qr-link="{{ $receiveMoneyUrl }}"
                                                    data-wallet-qr-success-label="{{ __('Copied') }}"
                                                    data-toast-message="{{ __('Receive link copied') }}">
                                                <i class="fas fa-copy"></i>
                                                <span data-wallet-qr-action-label>{{ __('Copy Link') }}</span>
                                            </button>
                                            <button type="button"
                                                    class="wallet-qr-card__btn wallet-qr-card__btn--ghost"
                                                    data-wallet-qr-download
                                                    data-wallet-qr-filename="digikash-{{ strtolower($wallet->currency->code) }}-wallet-qr.svg"
                                                    data-wallet-qr-success-label="{{ __('Saved') }}"
                                                    data-toast-message="{{ __('QR code downloaded') }}">
                                                <i class="fas fa-download"></i>
                                                <span data-wallet-qr-action-label>{{ __('Download QR') }}</span>
                                            </button>
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @else
                        <x-user-not-found
                            :title="__('No receiving wallet found')"
                            :message="__('Create or activate a sender-enabled wallet before sharing your QR code.')"
                            icon="fa-qrcode"
                            :action-url="route('user.wallet.index')"
                            :action-label="__('Go to Wallets')"
                            action-icon="fa-wallet"
                        />
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
