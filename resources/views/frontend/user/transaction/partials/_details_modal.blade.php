@php use App\Enums\TrxStatus; @endphp
@php use App\Enums\TrxType; @endphp
@php($icon = $transaction->trx_type->icon())
@php($amountColor = $transaction->amount_flow->color($transaction->status))
@php($amountSign = $transaction->amount_flow->sign($transaction->status))
@php($provider = $transaction->provider ?: __('Wallet engine'))
@php($remarks = filled($transaction->remarks) ? $transaction->remarks : __('No notes attached to this receipt.'))

<div class="modal fade ud-trx-modal" id="transactionModal{{ $transaction->id }}" tabindex="-1"
     aria-labelledby="transactionModalLabel{{ $transaction->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable ud-trx-modal__dialog">
        <div class="modal-content ud-trx-modal__content">
            <button type="button" class="btn-close ud-trx-modal__close" data-bs-dismiss="modal"
                    aria-label="{{ __('Close') }}"></button>

            <div class="modal-body ud-trx-modal__body">
                <span class="ud-trx-modal__grabber" aria-hidden="true"></span>

                <section class="ud-trx-modal__hero">
                    <div class="ud-trx-modal__hero-top">
                        <div class="ud-trx-modal__hero-brand">
                            <span class="ud-trx-modal__eyebrow">{{ __('Wallet Transaction Receipt') }}</span>
                        </div>

                        <div class="ud-trx-modal__hero-badges">
                            <span class="ud-trx-chip ud-trx-chip--type {{ $transactionTypeClass }}">
                                {{ strtoupper($transaction->trx_type->label()) }}
                            </span>
                            <span class="ud-trx-chip ud-trx-chip--status bg-{{ $transaction->status->color() }}">
                                {{ strtoupper($transaction->status->value) }}
                            </span>
                        </div>
                    </div>

                    <div class="ud-trx-modal__hero-layout">
                        <div class="ud-trx-modal__identity">
                            <span class="ud-trx-modal__hero-icon {{ $transactionTypeClass }}">
                                <x-icon name="{{ $icon }}" height="26" width="26"/>
                            </span>

                            <div class="ud-trx-modal__identity-copy">
                                <h5 class="ud-trx-modal__title" id="transactionModalLabel{{ $transaction->id }}">
                                    {{ __('Transaction Receipt') }}
                                </h5>
                                <p class="ud-trx-modal__subtitle">{{ $transaction->description }}</p>
                            </div>
                        </div>

                        <aside class="ud-trx-modal__amount-panel">
                            <span class="ud-trx-modal__amount-label">{{ __('Settled movement') }}</span>
                            <span class="ud-trx-modal__amount {{ $amountColor }}">
                                {{ $amountSign.number_format($transaction->amount, 2) }} {{ $transaction->currency }}
                            </span>
                            <span class="ud-trx-modal__amount-note">
                                {{ __('Processed on') }} {{ $transaction->created_at->format('d M Y, h:i A') }}
                            </span>
                        </aside>
                    </div>
                </section>

                <div class="ud-trx-modal__ledger">
                    <section class="ud-trx-modal__panel">
                        <div class="ud-trx-modal__panel-head">
                            <div>
                                <span class="ud-trx-modal__panel-eyebrow">{{ __('Reference') }}</span>
                                <h6 class="ud-trx-modal__panel-title">{{ __('Receipt Map') }}</h6>
                            </div>
                        </div>

                        <div class="ud-trx-modal__detail-grid">
                            <article class="ud-trx-modal__detail-card">
                                <span class="ud-trx-modal__detail-label">{{ __('Transaction ID') }}</span>
                                <span class="ud-trx-modal__detail-value ud-trx-modal__detail-value--mono">{{ strtoupper($transaction->trx_id) }}</span>
                            </article>

                            <article class="ud-trx-modal__detail-card">
                                <span class="ud-trx-modal__detail-label">{{ __('Processed At') }}</span>
                                <span class="ud-trx-modal__detail-value">{{ $transaction->created_at->format('d M Y, h:i A') }}</span>
                            </article>

                            <article class="ud-trx-modal__detail-card">
                                <span class="ud-trx-modal__detail-label">{{ __('Provider') }}</span>
                                <span class="ud-trx-modal__detail-value">{{ $provider }}</span>
                            </article>

                            <article class="ud-trx-modal__detail-card">
                                <span class="ud-trx-modal__detail-label">{{ __('Settlement Currency') }}</span>
                                <span class="ud-trx-modal__detail-value">{{ $transaction->payable_currency }}</span>
                            </article>
                        </div>
                    </section>

                    <section class="ud-trx-modal__panel ud-trx-modal__panel--accent">
                        <div class="ud-trx-modal__panel-head">
                            <div>
                                <span class="ud-trx-modal__panel-eyebrow">{{ __('Settlement') }}</span>
                                <h6 class="ud-trx-modal__panel-title">{{ __('Balance Breakdown') }}</h6>
                            </div>
                        </div>

                        <div class="ud-trx-modal__money-list">
                            <div class="ud-trx-modal__money-row">
                                <span>{{ __('Fee') }}</span>
                                <strong>{{ number_format($transaction->fee, 2) }} {{ $transaction->currency }}</strong>
                            </div>
                            <div class="ud-trx-modal__money-row">
                                <span>{{ __('Net Amount') }}</span>
                                <strong>{{ number_format($transaction->net_amount, 2) }} {{ $transaction->payable_currency }}</strong>
                            </div>
                            <div class="ud-trx-modal__money-row ud-trx-modal__money-row--strong">
                                <span>{{ __('Payable Amount') }}</span>
                                <strong>{{ number_format($transaction->payable_amount, 2) }} {{ $transaction->payable_currency }}</strong>
                            </div>
                        </div>
                    </section>

                    <section class="ud-trx-modal__panel ud-trx-modal__panel--note">
                        <div class="ud-trx-modal__panel-head">
                            <div>
                                <span class="ud-trx-modal__panel-eyebrow">{{ __('Internal memo') }}</span>
                                <h6 class="ud-trx-modal__panel-title">{{ __('Receipt Note') }}</h6>
                            </div>
                        </div>

                        <p class="ud-trx-modal__note-copy">{{ $remarks }}</p>
                    </section>

                    @if($transaction->trx_type == TrxType::REQUEST_MONEY && $transaction->status === TrxStatus::PENDING && $transaction->trx_reference !== null)
                        <form method="POST" action="{{ route('user.transaction.action') }}" class="ud-trx-modal__action-panel">
                            @csrf
                            <input type="hidden" name="trx_id" value="{{ $transaction->trx_id }}">
                            <input type="hidden" name="action" value="">

                            <div class="ud-trx-modal__panel-head">
                                <div>
                                    <span class="ud-trx-modal__panel-eyebrow">{{ __('Pending approval') }}</span>
                                    <h6 class="ud-trx-modal__panel-title">{{ __('Request Control') }}</h6>
                                </div>
                            </div>

                            <p class="ud-trx-modal__action-text">{{ __('Approve or reject this request with an optional private remark for your own audit trail.') }}</p>

                            <label class="ud-trx-modal__detail-label" for="remarks{{ $transaction->id }}">{{ __('Remarks (Optional)') }}</label>
                            <textarea name="remarks" id="remarks{{ $transaction->id }}" rows="3"
                                      class="form-control ud-trx-modal__textarea"></textarea>

                            <div class="ud-trx-modal__action-buttons">
                                <button type="button" class="btn btn-light-success ud-trx-modal__action-btn"
                                        onclick="this.form.querySelector('input[name=action]').value='approve'; this.form.submit();">
                                    {{ __('Approve') }}
                                </button>
                                <button type="button" class="btn btn-light-danger ud-trx-modal__action-btn"
                                        onclick="this.form.querySelector('input[name=action]').value='reject'; this.form.submit();">
                                    {{ __('Reject') }}
                                </button>
                            </div>
                        </form>
                    @endif
                </div>

                <div class="ud-trx-modal__footer">
                    <a href="{{ route('user.transaction.download-pdf', $transaction->trx_id) }}"
                       class="btn btn-light-primary ud-trx-modal__download">
                        <i class="fa-solid fa-file-lines"></i> {{ __('Download PDF') }}
                    </a>
                    <button type="button" class="btn btn-outline-secondary ud-trx-modal__dismiss"
                            data-bs-dismiss="modal">
                        {{ __('Close') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
