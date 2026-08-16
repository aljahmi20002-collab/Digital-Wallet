@extends('frontend.layouts.user.index')
@section('title', __('Create Payment Link'))
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card single-form-card">
                <x-user-feature-header
                    :title="__('Create Payment Link')"
                    :subtitle="__('Generate a shareable payment link your customers can pay from any device.')"
                    icon="fas fa-link"
                >
                    <a href="{{ route('user.payment-links.index') }}" class="btn btn-light-primary btn-sm">
                        <i class="fas fa-arrow-left"></i> {{ __('Back to Payment Links') }}
                    </a>
                </x-user-feature-header>

                <div class="card-main">
                    <div class="text-muted small fw-500 border-left-5 rounded p-2 mb-3 bg-light d-flex align-items-center" role="alert">
                        {{ __('Payment links work for any role - users, merchants and agents - and credit your wallet automatically when paid.') }}
                    </div>

                    <form action="{{ route('user.payment-links.store') }}" method="POST">
                        @csrf

                        @include('frontend.user.payment_links._form', [
                            'paymentLink'         => null,
                            'merchants'           => $merchants ?? collect(),
                            'preselectMerchantId' => $preselectMerchantId ?? null,
                        ])

                        <button type="submit" class="btn btn-primary mt-4">
                            <i class="fas fa-check"></i> {{ __('Create Payment Link') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
