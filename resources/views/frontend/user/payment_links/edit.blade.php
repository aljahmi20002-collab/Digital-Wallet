@extends('frontend.layouts.user.index')
@section('title', __('Edit Payment Link'))
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card single-form-card">
                <x-user-feature-header
                    :title="__('Edit Payment Link')"
                    :subtitle="__('Update the details payers see at checkout.')"
                    icon="fas fa-link"
                >
                    <a href="{{ route('user.payment-links.index') }}" class="btn btn-light-primary btn-sm">
                        <i class="fas fa-arrow-left"></i> {{ __('Back to Payment Links') }}
                    </a>
                </x-user-feature-header>

                <div class="card-main">
                    <form action="{{ route('user.payment-links.update', $paymentLink) }}" method="POST">
                        @csrf
                        @method('PUT')

                        @include('frontend.user.payment_links._form', [
                            'paymentLink' => $paymentLink,
                            'merchants'   => $merchants ?? collect(),
                        ])

                        <button type="submit" class="btn btn-primary mt-4">
                            <i class="fas fa-check"></i> {{ __('Save Changes') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
