@extends('frontend.layouts.user.index')
@section('title', __('My Vouchers'))
@section('content')

    <div class="single-form-card">
        <x-user-feature-header
            :title="__('My Vouchers')"
            :subtitle="__('Generate, monitor, and redeem vouchers from one workspace.')"
            icon="fas fa-ticket-alt"
        >
            <a class="btn btn-light-success btn-sm" href="{{ route('user.voucher.create') }}">
                <i class="fa-solid fa-plus-circle"></i> {{ __('Generate Voucher') }}
            </a>
            <a class="btn btn-light-primary btn-sm" href="#" data-bs-toggle="modal"
               data-bs-target="#redeemVoucherModal">
                <i class="fa-solid fa-receipt"></i> {{ __('Redeem Voucher') }}
            </a>
        </x-user-feature-header>
        <div class="card-main">
            @if($vouchers->count() > 0)
                <div class="history-table">
                    <div class="table-list">
                        <ul class="list-header">
                            <li>{{ __('Voucher Code') }}</li>
                            <li>{{ __('Amount') }}</li>
                            <li>{{ __('For Wallet') }}</li>
                            <li>{{ __('Status') }}</li>
                            <li>{{ __('Redeemed On') }}</li>
                            <li>{{ __('Created On') }}</li>
                        </ul>
                    </div>
                    <div class="table-list">
                        @foreach($vouchers as $voucher)
                        <ul class="list-content">
                            <li class="">
                                <span class="voucher-code">{{ $voucher->code }}</span>
                                <span class="copy-wrapper">
                                   <i class="fa-solid fa-copy text-primary cursor-pointer copy-icon copyNow"
                                      data-clipboard-text="{{ $voucher->code }}"
                                      title="{{ __('Copy Voucher Code') }}"
                                      data-bs-toggle="tooltip"
                                      data-bs-placement="top"></i>

                                </span>
                            </li>
                            <li>{{ $voucher->currency->symbol . number_format($voucher->amount, 2)  }}</li>
                            <li class="text-uppercase">{{ $voucher->currency->code ?? __('N/A') }}</li>
                            <li>
                                @if($voucher->is_active && is_null($voucher->redeemed_at))
                                    <span class="badge bg-success text-white">{{ __('Available') }}</span>
                                @else
                                    <span class="badge bg-danger text-white">{{ __('Redeemed') }}</span>
                                @endif
                            </li>
                            <li>{{ $voucher->redeemed_at ? $voucher->redeemed_at->format('M d Y') : __('N/A') }}</li>
                            <li>{{ $voucher->created_at->format('M d Y') }}</li>
                        </ul>
                        @endforeach
                    </div>
                </div>
            @else
                <x-user-not-found
                    :title="__('No vouchers found')"
                    :message="__('Generate a voucher to share stored value securely, or redeem a code you received from another user.')"
                    :eyebrow="__('Voucher wallet ready')"
                    icon="fa-ticket-alt"
                    :action-url="route('user.voucher.create')"
                    :action-label="__('Generate Voucher')"
                    action-icon="fa-plus"
                    :secure-label="__('Redeem anytime')"
                    class="voucher-not-found"
                >
                    <x-slot:preview>
                        <div class="payment-link-empty__preview-top">
                            <span></span>
                            <span></span>
                        </div>
                        <div class="payment-link-empty__preview-row">
                            <i class="fas fa-ticket-alt"></i>
                            <div>
                                <strong>{{ __('Generate Voucher') }}</strong>
                                <small>{{ __('Create a secure transferable code') }}</small>
                            </div>
                        </div>
                        <div class="payment-link-empty__preview-row">
                            <i class="fas fa-receipt"></i>
                            <div>
                                <strong>{{ __('Redeem Voucher') }}</strong>
                                <small>{{ __('Apply a received voucher instantly') }}</small>
                            </div>
                        </div>
                        <div class="payment-link-empty__preview-url">
                            {{ __('Code') }}: XXXX-XXXX-XXXX
                        </div>
                    </x-slot:preview>
                </x-user-not-found>
            @endif
        </div>
    </div>

    {{-- redeem voucher modal--}}
    @include('frontend.user.vouchers.partials._redeem_voucher_modal')

@endsection
