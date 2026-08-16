@php
    use App\Enums\VirtualCard\VirtualCardRequestStatus;
    use Illuminate\Support\Str;

    $totalRequests = $cardRequests->count();
    $pendingRequests = $cardRequests->filter(fn ($request) => $request->status === VirtualCardRequestStatus::Pending)->count();
    $issuedRequests = $cardRequests->filter(fn ($request) => $request->status === VirtualCardRequestStatus::Issued)->count();
    $latestRequest = $cardRequests->first();
@endphp
@extends('frontend.layouts.user.index')
@section('title', __('My Virtual Card Requests'))

@push('styles')
    <link rel="stylesheet" href="{{ asset('frontend/css/virtual-card.css?v='.config('app.version')) }}">
@endpush

@section('content')
    <div class="single-form-card">
        <x-user-feature-header
            :title="__('My Virtual Card Requests')"
            :subtitle="__('Track pending card requests and launch new ones quickly.')"
            icon="fas fa-file-signature"
        >
            <a class="btn btn-light-primary btn-sm" href="{{ route('user.virtual-card.index') }}"><i class="fa-solid fa-list"></i>{{ __('My Cards') }}</a>
            <button type="button" class="btn btn-light-success btn-sm" data-bs-toggle="modal" data-bs-target="#requestVirtualCardModal">
                <i class="fa-solid fa-credit-card"></i> {{ __('Request New') }}
            </button>
        </x-user-feature-header>

        <div class="vc-page vc-request-page" data-vc-page>
            <div class="vc-stats vc-stats--compact vc-request-stats">
                <div class="vc-stat">
                    <div class="vc-stat__top">
                        <div class="vc-stat__label">{{ __('Total') }}</div>
                        <div class="vc-stat__icon vc-stat__icon--brand"><i class="fa-solid fa-layer-group"></i></div>
                    </div>
                    <div class="vc-stat__value">{{ $totalRequests }}</div>
                    <div class="vc-stat__sub">{{ __('requests submitted') }}</div>
                </div>
                <div class="vc-stat">
                    <div class="vc-stat__top">
                        <div class="vc-stat__label">{{ __('Pending') }}</div>
                        <div class="vc-stat__icon vc-stat__icon--amber"><i class="fa-regular fa-clock"></i></div>
                    </div>
                    <div class="vc-stat__value">{{ $pendingRequests }}</div>
                    <div class="vc-stat__sub">{{ __('awaiting admin') }}</div>
                </div>
                <div class="vc-stat">
                    <div class="vc-stat__top">
                        <div class="vc-stat__label">{{ __('Issued') }}</div>
                        <div class="vc-stat__icon vc-stat__icon--green"><i class="fa-solid fa-credit-card"></i></div>
                    </div>
                    <div class="vc-stat__value">{{ $issuedRequests }}</div>
                    <div class="vc-stat__sub">{{ __('cards created') }}</div>
                </div>
                <div class="vc-stat">
                    <div class="vc-stat__top">
                        <div class="vc-stat__label">{{ __('Latest') }}</div>
                        <div class="vc-stat__icon vc-stat__icon--violet"><i class="fa-solid fa-bolt"></i></div>
                    </div>
                    <div class="vc-stat__value vc-stat__value--date">
                        {{ $latestRequest?->created_at?->format('M d') ?? '-' }}
                    </div>
                    <div class="vc-stat__sub">{{ $latestRequest?->status?->label() ?? __('no requests') }}</div>
                </div>
            </div>

            <div class="vc-request-panel">
                <div class="vc-request-panel__head">
                    <div>
                        <div class="vc-request-panel__eyebrow">{{ __('Request center') }}</div>
                        <h3 class="vc-request-panel__title">{{ __('Virtual card requests') }}</h3>
                    </div>
                    <span class="vc-request-panel__count">{{ trans_choice(':count request|:count requests', $totalRequests, ['count' => $totalRequests]) }}</span>
                </div>

                <div class="vc-request-table-wrap">
                    <table class="vc-request-table">
                        <thead>
                            <tr>
                                <th scope="col">#{{ __('Request ID') }}</th>
                                <th scope="col">{{ __('Wallet') }}</th>
                                <th scope="col">{{ __('Status') }}</th>
                                <th scope="col">{{ __('Requested') }}</th>
                                <th scope="col">{{ __('Admin Note') }}</th>
                                <th scope="col">{{ __('Card Info') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($cardRequests as $req)
                            @php
                                $statusTone = match ($req->status->badgeColor()) {
                                    'success' => 'green',
                                    'warning' => 'amber',
                                    'danger' => 'red',
                                    'info', 'primary' => 'blue',
                                    default => 'neutral',
                                };
                            @endphp
                            <tr>
                                <td data-label="{{ __('Request ID') }}">
                                    <span class="vc-request-id">#{{ $req->uuid }}</span>
                                </td>
                                <td data-label="{{ __('Wallet') }}">
                                    <span class="vc-request-wallet">{{ $req->wallet->currency->code ?? 'N/A' }}</span>
                                </td>
                                <td data-label="{{ __('Status') }}">
                                    <span class="vc-pill vc-pill--{{ $statusTone }}">
                                        <span class="vc-pill__dot"></span>{{ $req->status->label() }}
                                    </span>
                                </td>
                                <td data-label="{{ __('Requested') }}">
                                    <span class="vc-request-date">{{ $req->created_at->format('M d, Y') }}</span>
                                </td>
                                <td data-label="{{ __('Admin Note') }}">
                                    @if($req->admin_note)
                                        @php $note = $req->admin_note; @endphp
                                        @if(Str::length($note) > 20)
                                            <span class="vc-request-note vc-request-note--alert" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $note }}">
                                                {{ Str::limit($note, 20) }} <i class="fa-solid fa-circle-info ms-1"></i>
                                            </span>
                                        @else
                                            <span class="vc-request-note vc-request-note--alert">{{ $note }}</span>
                                        @endif
                                    @else
                                        <span class="vc-request-empty">{{ __('-') }}</span>
                                    @endif
                                </td>
                                <td data-label="{{ __('Card Info') }}">
                                    @if($req->card)
                                        <span class="vc-request-card-chip">
                                            <i class="fa-regular fa-credit-card"></i>
                                            **** {{ $req->card->last4 }}
                                        </span>
                                        <div class="vc-request-card-expiry">{{ $req->card->expiry_month }}/{{ $req->card->expiry_year }}</div>
                                    @else
                                        <span class="vc-request-empty">{{ __('-') }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr class="vc-request-table__empty-row">
                                <td colspan="6">
                                    <x-user-not-found
                                        :title="__('No virtual card requests found')"
                                        :message="__('Request a new virtual card and track the approval status from this page.')"
                                        :eyebrow="__('Card request center')"
                                        icon="fa-credit-card"
                                    />
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    {{-- Add Card Request Modal --}}
    @include('frontend.user.virtual_card.request.partials._add_card_request_modal')
@endsection
