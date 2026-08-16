@extends('frontend.layouts.user.index')
@section('title', __('Payment Links'))
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card single-form-card">
                {{-- Card Header --}}
                <x-user-feature-header
                    :title="__('Payment Links')"
                    :subtitle="__('Create shareable payment links and accept wallet or gateway payments instantly.')"
                    icon="fas fa-link"
                >
                    <a class="btn btn-light-success btn-sm"
                       href="{{ route('user.transaction.index', ['type' => \App\Enums\TrxType::RECEIVE_PAYMENT]) }}">
                        <i class="fas fa-list me-1"></i>{{ __('Payments') }}
                    </a>
                    <a class="btn btn-light-primary btn-sm" href="{{ route('user.payment-links.create') }}">
                        <i class="fas fa-plus-circle me-1"></i>{{ __('New Payment Link') }}
                    </a>
                </x-user-feature-header>

                {{-- Card Body --}}
                <div class="card-body">
                    <div class="payment-link-list mt-3">
                        @forelse($paymentLinks as $paymentLink)
                            @php
                                $isActive    = $paymentLink->isActive();
                                $isExpired   = $paymentLink->isExpired();
                                $isMaxedOut  = $paymentLink->isMaxedOut();
                                $publicUrl   = $paymentLink->publicUrl();
                                $statusText  = $isExpired
                                    ? __('Expired')
                                    : ($isMaxedOut ? __('Limit Reached') : $paymentLink->status->label());
                                $statusColor = $isExpired || $isMaxedOut ? 'danger' : $paymentLink->status->color();
                                $amountText  = $paymentLink->isOpenAmount()
                                    ? __('Open amount')
                                    : number_format((float) $paymentLink->amount, 2).' '.$paymentLink->currency->code;
                                $paymentsText = $paymentLink->payments_count.($paymentLink->max_payments ? ' / '.$paymentLink->max_payments : '');
                                $expiresText  = $paymentLink->expires_at?->format('M d, Y H:i') ?? __('Never');
                            @endphp

                            <article class="payment-link-card payment-link-card--{{ $statusColor }}">
                                {{-- Top Row: Icon + Title + Status --}}
                                <header class="payment-link-card__header">
                                    <div class="payment-link-card__identity">
                                        <span class="payment-link-card__icon" aria-hidden="true">
                                            <i class="fas fa-link"></i>
                                        </span>
                                        <div class="payment-link-card__copy">
                                            <h6 class="payment-link-card__title" title="{{ $paymentLink->title }}">
                                                {{ $paymentLink->title }}
                                            </h6>
                                            <div class="payment-link-card__meta-line">
                                                <span class="payment-link-card__currency">{{ $paymentLink->currency->code }}</span>
                                                <span class="payment-link-card__separator" aria-hidden="true">·</span>
                                                <span class="payment-link-card__amount">{{ $amountText }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="payment-link-card__status">
                                        <span class="badge bg-{{ $statusColor }} payment-link-card__badge">
                                            <i class="fas fa-circle payment-link-card__badge-dot"></i>{{ $statusText }}
                                        </span>
                                    </div>
                                </header>

                                {{-- URL row --}}
                                <div class="payment-link-card__url-row">
                                    <div class="input-group input-group-sm payment-link-url-group" data-payment-link-group>
                                        <span class="input-group-text payment-link-url-prefix">
                                            <i class="fas fa-globe"></i>
                                        </span>
                                        <input type="text"
                                               class="form-control form-control-sm payment-link-url-input"
                                               value="{{ $publicUrl }}"
                                               readonly
                                               aria-label="{{ __('Payment link URL') }}"
                                               data-payment-link-url>
                                        <button type="button"
                                                class="btn btn-outline-primary payment-link-copy-btn"
                                                data-payment-link-copy
                                                data-toast-message="{{ __('Payment link copied to clipboard') }}"
                                                title="{{ __('Copy link') }}"
                                                aria-label="{{ __('Copy link') }}">
                                            <i class="fas fa-copy" data-copy-icon></i>
                                            <span class="d-none d-md-inline ms-1"
                                                  data-copy-label
                                                  data-copied-label="{{ __('Copied') }}">{{ __('Copy') }}</span>
                                        </button>
                                    </div>
                                </div>

                                {{-- Footer: stats + actions (side-by-side on >=768px, stacked on mobile) --}}
                                <div class="payment-link-card__footer">
                                    <div class="payment-link-card__stats">
                                        <div class="payment-link-card__stat">
                                            <span class="payment-link-card__stat-label">{{ __('Payments') }}</span>
                                            <span class="payment-link-card__stat-value">
                                                <i class="fas fa-receipt me-1 text-muted"></i>{{ $paymentsText }}
                                            </span>
                                        </div>
                                        <div class="payment-link-card__stat">
                                            <span class="payment-link-card__stat-label">{{ __('Expires') }}</span>
                                            <span class="payment-link-card__stat-value">
                                                <i class="fas fa-clock me-1 text-muted"></i>{{ $expiresText }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="payment-link-card__actions">
                                        <button type="button"
                                                class="btn btn-sm btn-outline-success payment-link-action"
                                                data-bs-toggle="modal"
                                                data-bs-target="#paymentLinkQrModal{{ $paymentLink->id }}"
                                                title="{{ __('Show QR Code') }}"
                                                aria-label="{{ __('Show QR Code') }}">
                                            <i class="fas fa-qrcode"></i>
                                            <span class="payment-link-action__label">{{ __('QR Code') }}</span>
                                        </button>
                                        <a href="{{ $publicUrl }}"
                                           target="_blank"
                                           rel="noopener"
                                           class="btn btn-sm btn-outline-primary payment-link-action"
                                           title="{{ __('Open') }}"
                                           aria-label="{{ __('Open') }}">
                                            <i class="fas fa-external-link-alt"></i>
                                            <span class="payment-link-action__label">{{ __('Open') }}</span>
                                        </a>
                                        <a href="{{ route('user.payment-links.edit', $paymentLink) }}"
                                           class="btn btn-sm btn-primary payment-link-action"
                                           title="{{ __('Edit') }}"
                                           aria-label="{{ __('Edit') }}">
                                            <i class="fas fa-edit"></i>
                                            <span class="payment-link-action__label">{{ __('Edit') }}</span>
                                        </a>
                                        <form action="{{ route('user.payment-links.toggle', $paymentLink) }}"
                                              method="post"
                                              class="payment-link-action-form">
                                            @csrf
                                            <button type="submit"
                                                    class="btn btn-sm btn-outline-{{ $isActive ? 'warning' : 'success' }} payment-link-action"
                                                    title="{{ $isActive ? __('Deactivate') : __('Activate') }}"
                                                    aria-label="{{ $isActive ? __('Deactivate') : __('Activate') }}">
                                                <i class="fas fa-power-off"></i>
                                                <span class="payment-link-action__label">{{ $isActive ? __('Deactivate') : __('Activate') }}</span>
                                            </button>
                                        </form>
                                        <form action="{{ route('user.payment-links.destroy', $paymentLink) }}"
                                              method="post"
                                              class="payment-link-action-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="btn btn-sm btn-outline-danger payment-link-action"
                                                    data-payment-link-delete
                                                    title="{{ __('Delete') }}"
                                                    aria-label="{{ __('Delete') }}">
                                                <i class="fas fa-trash"></i>
                                                <span class="payment-link-action__label">{{ __('Delete') }}</span>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </article>

                            <div class="modal fade payment-link-qr-modal"
                                 id="paymentLinkQrModal{{ $paymentLink->id }}"
                                 tabindex="-1"
                                 aria-labelledby="paymentLinkQrModalLabel{{ $paymentLink->id }}"
                                 aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content border-0">
                                        <div class="payment-link-qr-modal__hero">
                                            <div class="payment-link-qr-modal__hero-copy">
                                                <span class="payment-link-qr-modal__mark" aria-hidden="true">
                                                    <i class="fas fa-qrcode"></i>
                                                </span>
                                                <div>
                                                    <span class="payment-link-qr-modal__eyebrow">{{ __('Scan to Pay') }}</span>
                                                    <h6 class="modal-title" id="paymentLinkQrModalLabel{{ $paymentLink->id }}">
                                                        {{ __('Payment QR Code') }}
                                                    </h6>
                                                    <small>{{ $paymentLink->title }}</small>
                                                </div>
                                            </div>
                                            <div class="payment-link-qr-modal__hero-actions">
                                                <span class="payment-link-qr-modal__status payment-link-qr-modal__status--{{ $statusColor }}">
                                                    {{ $statusText }}
                                                </span>
                                            </div>
                                            <button type="button"
                                                    class="btn-close payment-link-qr-modal__close"
                                                    data-bs-dismiss="modal"
                                                    aria-label="{{ __('Close') }}"></button>
                                        </div>
                                        <div class="modal-body payment-link-qr-modal__body">
                                            <div class="payment-link-qr-modal__summary">
                                                <div>
                                                    <span>{{ __('Amount') }}</span>
                                                    <strong>{{ $amountText }}</strong>
                                                </div>
                                                <div>
                                                    <span>{{ __('Expires') }}</span>
                                                    <strong>{{ $expiresText }}</strong>
                                                </div>
                                            </div>
                                            <div class="payment-link-qr-modal__scan-card">
                                                <div class="payment-link-qr-modal__code"
                                                     id="paymentLinkQrCode{{ $paymentLink->id }}">
                                                    {!! $paymentLink->qrCodeSvg(232) !!}
                                                </div>
                                                <p class="payment-link-qr-modal__hint">
                                                    {{ __('Scan with a phone camera or DigiKash scanner to open checkout.') }}
                                                </p>
                                            </div>
                                            <div class="input-group input-group-sm payment-link-url-group mx-auto"
                                                 data-payment-link-group>
                                                <input type="text"
                                                       class="form-control form-control-sm payment-link-url-input"
                                                       value="{{ $publicUrl }}"
                                                       readonly
                                                       aria-label="{{ __('Payment link URL') }}"
                                                       data-payment-link-url>
                                                <button type="button"
                                                        class="btn btn-outline-primary payment-link-copy-btn"
                                                        data-payment-link-copy
                                                        data-toast-message="{{ __('Payment link copied to clipboard') }}"
                                                        title="{{ __('Copy link') }}"
                                                        aria-label="{{ __('Copy link') }}">
                                                    <i class="fas fa-copy" data-copy-icon></i>
                                                    <span data-copy-label
                                                          data-copied-label="{{ __('Copied') }}">{{ __('Copy') }}</span>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="modal-footer payment-link-qr-modal__footer">
                                            <a href="{{ $publicUrl }}"
                                               target="_blank"
                                               rel="noopener"
                                               class="btn btn-sm btn-primary payment-link-qr-modal__primary">
                                                <i class="fas fa-external-link-alt me-1"></i>{{ __('Open Checkout') }}
                                            </a>
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-secondary payment-link-qr-modal__secondary"
                                                    data-payment-link-print
                                                    data-print-target="paymentLinkQrCode{{ $paymentLink->id }}"
                                                    data-print-title="{{ $paymentLink->title }}"
                                                    data-print-amount="{{ $amountText }}"
                                                    data-print-url="{{ $publicUrl }}">
                                                <i class="fas fa-print me-1"></i>{{ __('Print QR') }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <x-user-not-found
                                :title="__('No payment links yet')"
                                :message="__('Create a shareable checkout link for wallet or gateway payments in seconds.')"
                                :eyebrow="__('Ready to collect payments')"
                                icon="fa-link"
                                :action-url="route('user.payment-links.create')"
                                :action-label="__('Create Payment Link')"
                                action-icon="fa-plus"
                                :secure-label="__('Secure checkout')"
                            >
                                <x-slot:preview>
                                    <div class="payment-link-empty__preview-top">
                                        <span></span>
                                        <span></span>
                                    </div>
                                    <div class="payment-link-empty__preview-row">
                                        <i class="fas fa-wallet"></i>
                                        <div>
                                            <strong>{{ __('Wallet Payment') }}</strong>
                                            <small>{{ __('Share link and receive instantly') }}</small>
                                        </div>
                                    </div>
                                    <div class="payment-link-empty__preview-row">
                                        <i class="fas fa-credit-card"></i>
                                        <div>
                                            <strong>{{ __('Gateway Ready') }}</strong>
                                            <small>{{ __('Accept flexible checkout flows') }}</small>
                                        </div>
                                    </div>
                                    <div class="payment-link-empty__preview-url">
                                        {{ url('/payment-link') }}/...
                                    </div>
                                </x-slot:preview>
                            </x-user-not-found>
                        @endforelse
                    </div>

                    @if($paymentLinks->hasPages())
                        <div class="mt-4 d-flex justify-content-center">
                            {{ $paymentLinks->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script src="{{ asset('frontend/js/payment-links.js') }}"></script>
@endpush
