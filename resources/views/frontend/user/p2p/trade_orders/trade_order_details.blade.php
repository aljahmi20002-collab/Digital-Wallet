@extends('frontend.layouts.user.index')
@section('title', __('Trade Order #').$order->id)
@section('content')
{{-- Trade Order Details: participant state, order overview, action buttons, dispute handling, and review flow --}}
@php
    $side = $order->offer->side; // enum App\Enums\P2P\OrderSide
    $actorId  = (int) auth()->id();
    $sellerId = (int) ($side === \App\Enums\P2P\OrderSide::SELL ? $order->maker_id : $order->taker_id);
    $buyerId  = (int) ($side === \App\Enums\P2P\OrderSide::SELL ? $order->taker_id : $order->maker_id);
    $role = $actorId === $sellerId ? 'seller' : ($actorId === $buyerId ? 'buyer' : 'guest');
    $isExpired = $order->status->value === 'PENDING' && $order->expires_at && now()->greaterThan($order->expires_at);

    $hasFeedback = $order->relationLoaded('feedbacks')
        ? $order->feedbacks->where('user_id', $actorId)->isNotEmpty()
        : false;
    $canRate = $order->status->value === 'COMPLETED' && $actorId === (int) $order->taker_id && ! $hasFeedback;
    $myFb = $hasFeedback ? $order->feedbacks->where('user_id', $actorId)->first() : null;
    $payerSnapshot = is_array($order->payer_payment_account_snapshot) ? $order->payer_payment_account_snapshot : [];
    $receiverSnapshot = is_array($order->receiver_payment_account_snapshot) ? $order->receiver_payment_account_snapshot : [];
    $payerDetails = collect($payerSnapshot['details'] ?? [])->filter(function ($detail) {
        return is_array($detail) && trim((string) ($detail['value'] ?? '')) !== '';
    })->values()->all();
    $receiverDetails = collect($receiverSnapshot['details'] ?? [])->filter(function ($detail) {
        return is_array($detail) && trim((string) ($detail['value'] ?? '')) !== '';
    })->values()->all();
    $selectedPaymentMethodName = $order->paymentMethod?->name ?? ($payerSnapshot['payment_method_name'] ?? $receiverSnapshot['payment_method_name'] ?? __('Not available'));
@endphp
<div class="single-form-card p2p-ui">
    <x-user-feature-header
        :title="__('Trade Order #').$order->id"
        :subtitle="__('Track participant actions, payment snapshots, and dispute status from one screen.')"
        icon="fas fa-file-alt"
    >
        <a href="{{ route('user.p2p.orders.index') }}" class="btn btn-light-primary btn-sm p2p-btn-xs">
            <i class="fas fa-arrow-left"></i> @lang('All Orders')
        </a>
    </x-user-feature-header>
    <div class="card-main p2p-card-main">
    <div class="d-flex flex-wrap gap-2 mb-3">
        <span id="p2pStatusBadge" class="{{ ($isExpired ? \App\Enums\P2P\OrderStatus::EXPIRED : $order->status)->badgeClass() }}">{{ ($isExpired ? \App\Enums\P2P\OrderStatus::EXPIRED : $order->status)->label() }}</span>
    </div>

    {{-- Order Overview + Participant Actions --}}
    <div class="row g-3">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li><strong>@lang('Side'):</strong> {{ $order->offer->side->label() }}</li>
                        <li><strong>@lang('Price'):</strong> {{ $order->price }}</li>
                        <li><strong>@lang('Amount'):</strong> {{ $order->amount }} {{ $order->wallet->currency->code }}</li>
                        <li><strong>@lang('Total'):</strong> {{ $order->total }} {{ $order->wallet->currency->code }}</li>
                        <li><strong>@lang('Expires In'):</strong> {{ $isExpired ? __('Expired') : optional($order->expires_at)->diffForHumans() }}</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="mb-3">
                        <div class="mb-1"><strong>@lang('Selected Payment Method')</strong></div>
                        <span class="badge bg-light text-dark border">{{ $selectedPaymentMethodName }}</span>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <div class="border rounded p-2 bg-light h-100">
                                <div class="fw-semibold mb-2">@lang('Payer Account')</div>
                                @if(!empty($payerSnapshot))
                                    <div class="small mb-1"><strong>@lang('Method'):</strong> {{ $payerSnapshot['payment_method_name'] ?? '-' }}</div>
                                    <div class="small mb-1"><strong>@lang('Account'):</strong> {{ $payerSnapshot['account_label'] ?? $payerSnapshot['display_name'] ?? '-' }}</div>
                                    @foreach($payerDetails as $detail)
                                        <div class="small mb-1"><strong>{{ $detail['label'] ?? '-' }}:</strong> {{ $detail['value'] ?? '-' }}</div>
                                    @endforeach
                                    @if(!empty($payerSnapshot['method_instructions']))
                                        <div class="small text-muted mt-2">{!! nl2br(e($payerSnapshot['method_instructions'])) !!}</div>
                                    @endif
                                @else
                                    <span class="text-muted small">@lang('No payer account snapshot available.')</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded p-2 bg-light h-100">
                                <div class="fw-semibold mb-2">@lang('Receiver Account')</div>
                                @if(!empty($receiverSnapshot))
                                    <div class="small mb-1"><strong>@lang('Method'):</strong> {{ $receiverSnapshot['payment_method_name'] ?? '-' }}</div>
                                    <div class="small mb-1"><strong>@lang('Account'):</strong> {{ $receiverSnapshot['account_label'] ?? $receiverSnapshot['display_name'] ?? '-' }}</div>
                                    @foreach($receiverDetails as $detail)
                                        <div class="small mb-1"><strong>{{ $detail['label'] ?? '-' }}:</strong> {{ $detail['value'] ?? '-' }}</div>
                                    @endforeach
                                    @if(!empty($receiverSnapshot['method_instructions']))
                                        <div class="small text-muted mt-2">{!! nl2br(e($receiverSnapshot['method_instructions'])) !!}</div>
                                    @endif
                                @else
                                    <span class="text-muted small">@lang('No receiver account snapshot available.')</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="mb-1"><strong>@lang('Offer Payment Methods')</strong></div>
                        @forelse($order->offer->paymentMethods as $pm)
                            <div class="d-inline-block me-2 mb-2">
                                <a class="badge bg-light text-dark border text-decoration-none" data-bs-toggle="collapse" href="#pmInst{{ $pm->id }}" role="button" aria-expanded="false" aria-controls="pmInst{{ $pm->id }}">
                                    {{ $pm->name }}
                                </a>
                                <div class="collapse" id="pmInst{{ $pm->id }}">
                                    <div class="border rounded p-2 bg-white mt-2" style="min-width: 260px;">
                                        @if(!empty($pm->instructions))
                                            {!! nl2br(e($pm->instructions)) !!}
                                        @else
                                            <span class="text-muted">@lang('No instructions provided.')</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <span class="text-muted">@lang('No payment methods')</span>
                        @endforelse
                    </div>

                    @if(!empty($order->offer->terms_text))
                        <div class="mb-3">
                            <div class="mb-1"><strong>@lang('Terms')</strong></div>
                            <div class="border rounded p-2 bg-light">{!! nl2br(e($order->offer->terms_text)) !!}</div>
                        </div>
                    @endif

                    <div class="d-flex gap-2">
                        <div id="p2pActionPaid" class="d-inline">
                            @if($order->status->value === 'PENDING' && !$isExpired && $actorId === (int) ($side === \App\Enums\P2P\OrderSide::SELL ? $order->taker_id : $order->maker_id))
                                <form method="POST" action="{{ route('user.p2p.orders.paid', $order) }}">
                                    @csrf
                                    <button class="btn btn-light-success btn-sm p2p-btn-xs" type="submit">
                                        <i class="fas fa-check-circle me-1"></i> @lang('I have paid')
                                    </button>
                                </form>
                            @endif
                        </div>
                        <div id="p2pActionRelease" class="d-inline">
                            @if($order->status->value === 'PAID' && $actorId === (int) ($side === \App\Enums\P2P\OrderSide::SELL ? $order->maker_id : $order->taker_id))
                                <form method="POST" action="{{ route('user.p2p.orders.release', $order) }}">
                                    @csrf
                                    <button class="btn btn-light-success btn-sm p2p-btn-xs" type="submit">
                                        <i class="fas fa-unlock-alt me-1"></i> @lang('Release Escrow')
                                    </button>
                                </form>
                            @endif
                        </div>
                        <div id="p2pActionCancel" class="d-inline">
                            @if(in_array($order->status->value, ['PENDING']) && !$isExpired && in_array($actorId, [(int) $order->maker_id, (int) $order->taker_id], true))
                                <form method="POST" action="{{ route('user.p2p.orders.cancel', $order) }}" onsubmit="return confirm(@json(__('Are you sure to cancel?')))" >
                                    @csrf
                                    <button class="btn btn-light-primary btn-sm p2p-btn-xs" type="submit">
                                        <i class="fas fa-times-circle me-1"></i> @lang('Cancel')
                                    </button>
                                </form>
                            @endif
                        </div>

                        @if($canRate)
                            <div class="d-inline">
                                <button type="button" class="btn btn-light-warning btn-sm p2p-btn-xs" data-bs-toggle="modal" data-bs-target="#p2pFeedbackModal">
                                    <i class="fas fa-star"></i> @lang('Review')
                                </button>
                            </div>
                        @elseif($order->status->value === 'COMPLETED' && $myFb)
                            <div class="d-inline">
                                <span class="badge bg-success">{{ (int) $myFb->rating }}/5</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(in_array($order->status->value, ['PENDING','PAID']) && in_array($actorId, [(int) $order->maker_id, (int) $order->taker_id], true))
        <div class="card mt-3">
            <div class="card-header">
                <strong>@lang('Open Trade Dispute')</strong>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('user.p2p.orders.dispute', $order) }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">@lang('Reason')</label>
                        <textarea name="reason" class="form-control " rows="3" required></textarea>
                    </div>
                    <button class="btn btn-light-primary btn-sm p2p-btn-xs" type="submit">
                        <i class="fas fa-gavel me-1"></i> @lang('Open Trade Dispute')
                    </button>
                </form>
            </div>
        </div>
    @endif
    </div>
</div>

{{-- Info modal (Binance-style) shown once on creation for buyer/seller --}}
<div class="modal fade p2p-ui-modal" id="p2pInfoModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">@lang('Important Information')</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        @if($role === 'buyer')
            <ol class="mb-0">
                <li>@lang('Pay the seller using the listed payment method(s).')</li>
                <li>@lang('After you transfer the funds, click "I have paid" before the order expires.')</li>
                <li>@lang('Do not cancel if you have already paid. Contact the seller in case of issues.')</li>
            </ol>
        @elseif($role === 'seller')
            <ol class="mb-0">
                <li>@lang('Wait for the buyer to mark as paid and verify the payment in your account.')</li>
                <li>@lang('Release escrow only after confirming payment receipt.')</li>
                <li>@lang('If payment is not received before expiry, you may cancel to refund escrow.')</li>
            </ol>
        @else
            <p class="mb-0">@lang('You are viewing this order as a guest.')</p>
        @endif
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light-primary btn-sm p2p-btn-xs" data-bs-dismiss="modal">
            <i class="fas fa-check me-1"></i> @lang('Got it')
        </button>
      </div>
    </div>
  </div>
 </div>

{{-- Review Modal --}}
<div class="modal fade p2p-ui-modal" id="p2pFeedbackModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">@lang('Review Trade') <span class="text-muted">#{{ $order->id }}</span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="POST" action="{{ route('user.p2p.orders.feedback', $order) }}" id="p2pFeedbackForm">
        @csrf
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">@lang('Rating')</label>
            <input type="hidden" name="rating" id="p2pFeedbackRating" value="{{ old('rating', 5) }}">
            <div class="d-flex align-items-center gap-2" id="p2pFeedbackStars">
              @for($i = 1; $i <= 5; $i++)
                <button type="button" class="btn btn-link p-0 p2p-feedback-star" data-value="{{ $i }}" aria-label="@lang('Review') {{ $i }}">
                  <i class="fas fa-star"></i>
                </button>
              @endfor
            </div>
            @error('rating')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
          </div>

          <div class="mb-0">
            <label class="form-label">@lang('Comment') (@lang('optional'))</label>
            <textarea name="comment" class="form-control " rows="3" maxlength="500">{{ old('comment') }}</textarea>
            @error('comment')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">@lang('Cancel')</button>
          <button type="submit" class="btn btn-warning btn-sm">@lang('Submit')</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
{{-- Trade Order Script: local state persistence, onboarding modal, status polling, and review modal helpers --}}
@push('scripts')
    @include('frontend.user.p2p.trade_orders.partials._trade_order_details_scripts')
@endpush
