@extends('frontend.layouts.user.index')
@section('title', __('Add Cardholder'))

@push('styles')
    <link rel="stylesheet" href="{{ asset('frontend/css/virtual-card.css?v='.config('app.version')) }}">
@endpush

@section('content')
    <div class="single-form-card">
        <x-user-feature-header
            :title="__('Add Cardholder')"
            :subtitle="__('Create a compliant cardholder profile for future virtual card issuance.')"
            icon="fas fa-user-plus"
        >
            <a class="btn btn-light-primary btn-sm" href="{{ route('user.virtual-card.cardholders.index') }}">
                <i class="fa-solid fa-list"></i> {{ __('All Cardholders') }}
            </a>
        </x-user-feature-header>

        <div class="vc-page" data-vc-page>
            <form method="POST"
                  action="{{ route('user.virtual-card.cardholders.store') }}"
                  autocomplete="off"
                  enctype="multipart/form-data"
                  class="vc-form">
                @csrf

                {{-- 1. Profile type --}}
                <section class="vc-form-section">
                    <header class="vc-form-section__head">
                        <span class="vc-form-section__icon"><i class="fa-solid fa-id-card"></i></span>
                        <div class="vc-form-section__copy">
                            <div class="vc-form-section__title">{{ __('Profile Type') }}</div>
                            <div class="vc-form-section__subtitle">
                                {{ __('Choose how this cardholder is identified. Switch to Business for company-issued cards.') }}
                            </div>
                        </div>
                    </header>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="card_type" class="form-label">@lang('Cardholder Type')</label>
                            <select class="form-select" id="card_type" name="card_type">
                                <option value="">@lang('Select Cardholder Type')</option>
                                @foreach($cardholderType as $type)
                                    <option value="{{ $type->value }}" {{ old('card_type','personal')==$type->value ? 'selected' : '' }}>{{ $type->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </section>

                {{-- 2. Personal-cardholder block --}}
                <div id="personal-details-block">
                    @include('frontend.user.virtual_card.cardholders.partials._personal_details')
                </div>

                {{-- 3. Business-cardholder block --}}
                <div id="business-details-block" class="d-none">
                    @include('frontend.user.virtual_card.cardholders.partials._business_details')
                </div>

                {{-- 4. Action bar --}}
                <div class="vc-form-actions">
                    <span class="vc-form-actions__hint">
                        {{ __('Cardholder profiles can be reused for any future virtual card.') }}
                    </span>
                    <a href="{{ route('user.virtual-card.cardholders.index') }}" class="btn btn-light-secondary">
                        <i class="fa-solid fa-xmark"></i>
                        @lang('Cancel')
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-check"></i>
                        @lang('Save Cardholder')
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    @include('frontend.user.virtual_card.cardholders.partials._script')
@endpush
