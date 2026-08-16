@extends('frontend.layouts.user.index')
@section('title', __('Edit Cardholder'))

@push('styles')
    <link rel="stylesheet" href="{{ asset('frontend/css/virtual-card.css?v='.config('app.version')) }}">
@endpush

@section('content')
    <div class="single-form-card">
        <x-user-feature-header
            :title="__('Edit Cardholder')"
            :subtitle="__('Update approved cardholder information without losing compliance context.')"
            icon="fas fa-user-pen"
        >
            <a class="btn btn-light-primary btn-sm" href="{{ route('user.virtual-card.cardholders.index') }}">
                <i class="fa-solid fa-list"></i> {{ __('All Cardholders') }}
            </a>
        </x-user-feature-header>

        <div class="vc-page" data-vc-page>
            <form method="POST"
                  action="{{ route('user.virtual-card.cardholders.update', $cardholder) }}"
                  autocomplete="off"
                  enctype="multipart/form-data"
                  class="vc-form">
                @csrf
                @method('PUT')

                {{-- 1. Profile type (read-only on edit — type cannot change) --}}
                <section class="vc-form-section">
                    <header class="vc-form-section__head">
                        <span class="vc-form-section__icon"><i class="fa-solid fa-id-card"></i></span>
                        <div class="vc-form-section__copy">
                            <div class="vc-form-section__title">{{ __('Profile Type') }}</div>
                            <div class="vc-form-section__subtitle">
                                {{ __('Cardholder type is locked once a profile exists.') }}
                            </div>
                        </div>
                    </header>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="card_type" class="form-label">@lang('Cardholder Type')</label>
                            <input type="text" class="form-control" value="{{ $cardholder->card_type->label() }}" disabled>
                            <input type="hidden" name="card_type" value="{{ $cardholder->card_type->value }}">
                        </div>
                    </div>
                </section>

                {{-- 2. Personal / Business details --}}
                @if($cardholder->card_type === \App\Enums\VirtualCard\CardholderType::PERSONAL)
                    @include('frontend.user.virtual_card.cardholders.partials._personal_details', ['cardholder' => $cardholder])
                @elseif($cardholder->card_type === \App\Enums\VirtualCard\CardholderType::BUSINESS)
                    @include('frontend.user.virtual_card.cardholders.partials._business_details', ['business' => $cardholder->business])
                @endif

                {{-- 3. Action bar --}}
                <div class="vc-form-actions">
                    <span class="vc-form-actions__hint">
                        {{ __('Changes apply immediately. Pending KYC reviews will keep their current state.') }}
                    </span>
                    <a href="{{ route('user.virtual-card.cardholders.index') }}" class="btn btn-light-secondary">
                        <i class="fa-solid fa-xmark"></i>
                        @lang('Cancel')
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-check"></i>
                        @lang('Update Cardholder')
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    @include('frontend.user.virtual_card.cardholders.partials._script')
@endpush
