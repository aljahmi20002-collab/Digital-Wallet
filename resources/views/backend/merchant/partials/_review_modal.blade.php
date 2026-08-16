<div class="modal fade" id="review-{{ $merchant->id }}" tabindex="-1" aria-labelledby="MerchantDetailsLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
        <div class="modal-content text-break" style="overflow-wrap:anywhere; word-break: break-word;">
            {{-- Modal Header --}}
            <div class="modal-header bg-primary text-white align-items-center">
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <img src="{{ asset($merchant->business_logo) }}" alt="{{ $merchant->business_name }}" class="rounded-1 border border-light" style="width:32px;height:32px;object-fit:cover;" loading="lazy">
                    <div class="d-flex flex-column" style="min-width:0;">
                        <h5 class="modal-title fw-bold mb-0 text-break" id="MerchantDetailsLabel">{{ $merchant->business_name }}</h5>
                        <small class="text-white-50 text-break">{{ $merchant->business_email }}</small>
                    </div>
                    <span class="badge bg-{{ $merchant->status->color() }} ms-auto text-uppercase">{{ $merchant->status->label() }}</span>
                </div>
                <button type="button" class="btn-close btn-close-white" data-coreui-dismiss="modal" aria-label="Close"></button>
            </div>

            {{-- Merchant Action Form --}}
            <form action="{{ route('admin.merchant.request-action') }}" method="post">
                @csrf
                <input type="hidden" name="merchant_id" value="{{ $merchant->id }}">

                {{-- Modal Body --}}
                <div class="modal-body text-break" style="overflow-wrap:anywhere; word-break: break-word; max-height: 70vh; overflow-y: auto;">
                    {{-- Contextual Status Alert --}}
                    @if($merchant->status === \App\Enums\MerchantStatus::APPROVED)
                        <div class="alert alert-success mb-3" role="alert" style="overflow-wrap:anywhere; word-break: break-word;">
                            <div class="d-flex align-items-start gap-2">
                                <i class="fa-solid fa-circle-check mt-1 flex-shrink-0"></i>
                                <div class="text-wrap">{{ __('This merchant is enabled and live.') }}</div>
                            </div>
                        </div>
                    @elseif($merchant->status === \App\Enums\MerchantStatus::DISABLED)
                        <div class="alert alert-warning mb-3" role="alert" style="overflow-wrap:anywhere; word-break: break-word;">
                            <div class="d-flex align-items-start gap-2">
                                <i class="fa-solid fa-triangle-exclamation mt-1 flex-shrink-0"></i>
                                <div class="text-wrap">{{ __('This merchant is currently disabled.') }}</div>
                            </div>
                        </div>
                    @elseif($merchant->status === \App\Enums\MerchantStatus::PENDING)
                        <div class="alert alert-info mb-3" role="alert" style="overflow-wrap:anywhere; word-break: break-word;">
                            <div class="d-flex align-items-start gap-2">
                                <i class="fa-solid fa-hourglass-half mt-1 flex-shrink-0"></i>
                                <div class="text-wrap">{{ __('This request is pending review. Approving will enable merchant operations.') }}</div>
                            </div>
                        </div>
                    @elseif($merchant->status === \App\Enums\MerchantStatus::REJECTED)
                        <div class="alert alert-danger mb-3" role="alert" style="overflow-wrap:anywhere; word-break: break-word;">
                            <div class="d-flex align-items-start gap-2">
                                <i class="fa-solid fa-circle-xmark mt-1 flex-shrink-0"></i>
                                <div class="text-wrap">{{ __('This merchant request has been rejected.') }}</div>
                            </div>
                        </div>
                    @endif

                    {{-- Quick Summary --}}
                    <div class="row g-2 mb-3">
                        <div class="col-12 col-md-auto">
                            <span class="badge bg-light text-dark border text-wrap d-inline-block" style="white-space: normal; overflow-wrap:anywhere; word-break: break-word; max-width: 100%;">{{ __('ID') }}: {{ $merchant->merchant_key }}</span>
                        </div>
                        <div class="col-12 col-md-auto">
                            <a href="{{ route('admin.user.manage', $merchant->user->username) }}" class="badge bg-light text-primary border text-decoration-none text-wrap d-inline-block" style="white-space: normal; max-width: 100%; overflow-wrap:anywhere; word-break: break-word;" target="_blank" rel="noopener" title="{{ __('View user details') }}">
                                <i class="fa-solid fa-user me-1"></i>{{ $merchant->user->name }}
                            </a>
                        </div>
                        <div class="col-12 col-md-auto" style="max-width: 100%;">
                            <a href="{{ safeUrl($merchant->site_url) }}" target="_blank" rel="noopener" class="badge bg-light text-primary border text-decoration-none text-wrap d-inline-block text-start" style="white-space: normal; max-width: 100%; overflow-wrap:anywhere; word-break: break-word;">
                                <i class="fa-solid fa-link me-1"></i>{{ $merchant->site_url }}
                            </a>
                        </div>
                        <div class="col-auto">
                            <span class="badge bg-light text-dark border">
                                <i class="fa-solid fa-coins me-1"></i>{{ $merchant->supportedCurrencies->pluck('code')->implode(', ') ?: $merchant->currency->code }}
                            </span>
                        </div>
                        <div class="col-auto">
                            @if($merchant->status === \App\Enums\MerchantStatus::PENDING)
                                <span class="badge bg-info text-white border">
                                    <i class="fa-solid fa-clipboard-list me-1"></i>{{ __('Merchant Request') }}
                                </span>
                            @endif
                        </div>
                        <div class="col-auto">
                            @if($merchant->isSandboxMode())
                                <span class="badge bg-warning text-dark border">{{ __('Mode') }}: {{ __('Sandbox') }}</span>
                            @elseif($merchant->isProductionMode())
                                <span class="badge bg-success text-white border">{{ __('Mode') }}: {{ __('Production') }}</span>
                            @endif
                        </div>
                    </div>
                    {{-- Requested Merchant Section --}}
                    <div class="card border shadow-sm mb-3">
                        <div class="card-body p-3">
                            <h6 class="fw-bold text-primary mb-3">
                                <i class="fa-solid fa-briefcase me-2"></i>{{ __('Requested Merchant') }}
                            </h6>
                            @php($desc = trim((string)($merchant->business_description ?? '')))
                            @php($descId = 'merchant-desc-' . $merchant->id)
                            <div class="bg-light rounded-2 border p-3 border-start border-3 border-primary">
                                <div class="d-flex align-items-start">
                                    <i class="fa-solid fa-quote-left text-secondary me-2 mt-1"></i>
                                    <div class="flex-grow-1">
                                        <div class="fw-semibold text-dark mb-1">{{ __('Business Description') }}</div>
                                        @if($desc === '')
                                            <div class="text-secondary fst-italic">{{ __('No description provided') }}</div>
                                        @elseif(mb_strlen($desc) > 180)
                                            <div class="text-muted text-wrap" id="{{ $descId }}-short">
                                                {{ \Illuminate\Support\Str::limit($desc, 180) }}
                                                <a class="ms-1" data-bs-toggle="collapse" href="#{{ $descId }}-more" role="button" aria-expanded="false" aria-controls="{{ $descId }}-more">{{ __('Show more') }}</a>
                                            </div>
                                            <div class="collapse mt-2" id="{{ $descId }}-more">
                                                <div class="text-muted text-wrap">{!! nl2br(e($desc)) !!}</div>
                                                <a class="d-inline-block mt-1" data-bs-toggle="collapse" href="#{{ $descId }}-more" role="button" aria-expanded="true" aria-controls="{{ $descId }}-more">{{ __('Show less') }}</a>
                                            </div>
                                        @else
                                            <div class="text-muted text-wrap">{!! nl2br(e($desc)) !!}</div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex flex-wrap align-items-center gap-2 mt-3 pt-2 border-top small text-muted">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="fa-solid fa-clock flex-shrink-0"></i>
                                    <span>{{ __('Request Date') }}: {{ $merchant->created_at?->format('Y-m-d H:i') }}</span>
                                </div>
                                <span class="badge bg-light text-dark border ms-md-auto">{{ $merchant->created_at?->diffForHumans() }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Fee Configuration Section --}}
                    @if($merchant->status !== \App\Enums\MerchantStatus::REJECTED)
                        <div class="card border shadow-sm">
                            <div class="card-body p-3">
                                <label class="form-label fw-bold" for="merchant_fee">{{ __('Set Merchant Transaction Fee') }}</label>
                                <div class="input-group w-100">
                                    <input type="text" oninput="this.value = validateDouble(this.value)"
                                           class="form-control @error('fee') is-invalid @enderror" id="merchant_fee" name="fee" aria-label="Merchant fee in percentage" aria-describedby="feeHelp" placeholder="0.00" inputmode="decimal" autocomplete="off"
                                           value="{{ old('fee', $merchant->fee) }}" title="{{ __('Percentage of each successful transaction charged to this merchant') }}">
                                    <span class="input-group-text">%</span>
                                </div>
                                <div id="feeHelp" class="form-text mt-1 text-break" style="white-space: normal; overflow-wrap:anywhere; word-break: break-word;">
                                    {{ __('This fee applies to successful transactions for this merchant.') }}
                                </div>
                                @error('fee')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    @endif

                    {{-- Operational Note --}}
                    @if($merchant->status === \App\Enums\MerchantStatus::APPROVED)
                        <div class="text-muted small mt-2"><i class="fa-solid fa-circle-info me-1"></i>{{ __('You can disable this merchant to pause payments and API access.') }}</div>
                    @elseif($merchant->status === \App\Enums\MerchantStatus::DISABLED)
                        <div class="text-muted small mt-2"><i class="fa-solid fa-circle-info me-1"></i>{{ __('Enable the merchant to resume payments and API access.') }}</div>
                    @endif
                </div>

                {{-- Modal Footer --}}
                <div class="modal-footer bg-light p-3">
                    <div class="d-flex w-100 gap-2">
                        @if($merchant->status === \App\Enums\MerchantStatus::PENDING)
                            {{-- Approve & Reject when pending --}}
                            <button type="submit" value="approve" name="action" class="btn btn-success text-white flex-fill d-flex align-items-center justify-content-center" title="{{ __('Approve this merchant and enable operations') }}">
                                <i class="fa-solid fa-check me-2"></i> {{ __('Approve') }}
                            </button>
                            <button type="submit" value="reject" name="action" class="btn btn-danger text-white flex-fill d-flex align-items-center justify-content-center" title="{{ __('Reject this merchant request') }}">
                                <i class="fa-solid fa-times me-2"></i> {{ __('Reject') }}
                            </button>
                        @elseif($merchant->status === \App\Enums\MerchantStatus::APPROVED)
                            {{-- Update fee or Disable when approved --}}
                            <button type="submit" value="approve" name="action" class="btn btn-primary flex-fill d-flex align-items-center justify-content-center" title="{{ __('Save the updated fee') }}">
                                <i class="fa-solid fa-rotate me-2"></i> {{ __('Update Fee') }}
                            </button>
                            <button type="submit" value="disable" name="action" class="btn btn-outline-danger flex-fill d-flex align-items-center justify-content-center" title="{{ __('Disable this merchant to pause payments and API access') }}">
                                <i class="fa-solid fa-ban me-2"></i> {{ __('Disable Merchant') }}
                            </button>
                        @elseif($merchant->status === \App\Enums\MerchantStatus::DISABLED)
                            {{-- Enable back or Update fee when disabled --}}
                            <button type="submit" value="enable" name="action" class="btn btn-success flex-fill d-flex align-items-center justify-content-center text-white" title="{{ __('Enable this merchant to resume payments and API access') }}">
                                <i class="fa-solid fa-power-off me-2"></i> {{ __('Enable Merchant') }}
                            </button>
                            <button type="submit" value="approve" name="action" class="btn btn-outline-primary flex-fill d-flex align-items-center justify-content-center" title="{{ __('Save the updated fee') }}">
                                <i class="fa-solid fa-rotate me-2"></i> {{ __('Update Fee') }}
                            </button>
                        @else
                            {{-- Rejected: no actions --}}
                            <div class="text-muted small w-100 text-center">{{ __('This merchant is rejected. No further actions available.') }}</div>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
