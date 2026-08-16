@extends('frontend.layouts.user.index')
@section('title', __('My Trade Orders'))
@section('content')
{{-- Trade Orders List: active/history cards, resume banner, and quick review modal launcher --}}
<div class="single-form-card p2p-ui">
    <x-user-feature-header
        :title="__('My Trade Orders')"
        :subtitle="__('Review open activity, resume recent trades, and jump into order details quickly.')"
        icon="fas fa-exchange-alt"
    >
        <a href="{{ route('user.p2p.offers.index') }}" class="btn btn-light-primary btn-sm p2p-btn-xs">
            <i class="fas fa-store"></i> @lang('Marketplace')
        </a>
    </x-user-feature-header>
    <div class="card-main p2p-card-main">
    {{-- Resume shortcut for the last active trade order --}}
    <div id="p2pResumeBanner" class="p2p-resume-banner d-none" role="status" aria-live="polite">
        <div class="p2p-resume-left">
            <span class="p2p-resume-icon-wrap">
                <i class="fas fa-clock-rotate-left p2p-resume-icon"></i>
            </span>
            <div class="p2p-resume-content">
                <div class="p2p-resume-title-row">
                    <span class="p2p-resume-title">@lang('Resume last trade')</span>
                    <span id="p2pResumeStatus" class="p2p-resume-status d-none"></span>
                </div>
                <div class="p2p-resume-order-row">
                    <strong id="p2pResumeOrderText">@lang('Order') #</strong>
                    <span id="p2pResumeUpdated" class="p2p-resume-updated d-none"></span>
                </div>
                <div id="p2pResumeMeta" class="p2p-resume-meta"></div>
            </div>
        </div>
        <a id="p2pResumeLink" href="#" class="btn btn-light-primary btn-sm p2p-btn-xs p2p-resume-link">
            <i class="fas fa-external-link-alt me-1"></i> @lang('Open Trade')
            <i class="fas fa-chevron-right ms-1"></i>
        </a>
    </div>

    <div class="p2p-offers-panel">
        <div class="p2p-offers-panel__head">
            <h6 class="mb-0">@lang('Trade Orders')</h6>
        </div>
        {{-- Trade order cards --}}
        <div class="p2p-offers-panel__body">
            @forelse($orders as $o)
                @php
                    $currency = $o->wallet->currency->code;
                    $decimals = (int) setting('site_decimal', 2);
                    $actorId = (int) auth()->id();
                    $hasFeedback = $o->relationLoaded('feedbacks')
                        ? $o->feedbacks->where('user_id', $actorId)->isNotEmpty()
                        : false;
                    $canRate = $o->status->value === 'COMPLETED' && $actorId === (int) $o->taker_id && ! $hasFeedback;
                @endphp
                <div class="p2p-data-card">
                    <div class="p2p-data-card__left">
                        <div class="p2p-data-card__title">#{{ $o->id }} &middot; {{ $currency }}</div>
                        <div class="p2p-data-card__sub">
                            <span class="{{ $o->offer->side->badgeClass() }}">{{ $o->offer->side->label() }}</span>
                            <span class="{{ $o->status->badgeClass() }}">{{ $o->status->label() }}</span>
                            <span><strong>@lang('Price'):</strong> {{ number_format((float) $o->price, $decimals) }}</span>
                            <span><strong>@lang('Amount'):</strong> {{ number_format((float) $o->amount, $decimals) }}</span>
                        </div>
                    </div>
                    <div class="p2p-data-card__right">
                        <a class="btn btn-light-primary btn-sm p2p-btn-xs" href="{{ route('user.p2p.orders.show', $o) }}">
                            <i class="fas fa-eye me-1"></i> @lang('View')
                        </a>

                        @if($canRate)
                            <button type="button"
                                    class="btn btn-light-warning btn-sm p2p-btn-xs ms-2 p2p-rate-order-btn"
                                    data-bs-toggle="modal"
                                    data-bs-target="#p2pFeedbackModal"
                                    data-feedback-url="{{ route('user.p2p.orders.feedback', $o) }}"
                                    data-order="#{{ $o->id }}"
                            >
                                <i class="fas fa-star"></i> @lang('Review')
                            </button>
                        @elseif($o->status->value === 'COMPLETED' && $hasFeedback)
                            @php
                                $myFb = $o->feedbacks->where('user_id', $actorId)->first();
                            @endphp
                            @if($myFb)
                                <span class="badge bg-success ms-2">{{ (int) $myFb->rating }}/5</span>
                            @endif
                        @endif
                    </div>
                </div>
            @empty
                <x-user-not-found
                    :title="__('No trade orders yet')"
                    :message="__('Trade orders will appear here after you start buying or selling from the marketplace.')"
                    :eyebrow="__('P2P order desk')"
                    icon="fa-exchange-alt"
                    :action-url="route('user.p2p.offers.index')"
                    :action-label="__('Marketplace')"
                    action-icon="fa-store"
                />
            @endforelse
        </div>
    </div>

    <div class="modal fade p2p-ui-modal" id="p2pFeedbackModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Review Trade') <span class="text-muted" id="p2pFeedbackOrderLabel"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="" id="p2pFeedbackForm">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">@lang('Rating')</label>
                            <input type="hidden" name="rating" id="p2pFeedbackRating" value="5">
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
                            <textarea name="comment" class="form-control form-control-sm" rows="3" maxlength="500">{{ old('comment') }}</textarea>
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

    {{ $orders->links() }}
    </div>
</div>

@push('scripts')
    @include('frontend.user.p2p.trade_orders.partials._trade_orders_scripts')
@endpush
@endsection
