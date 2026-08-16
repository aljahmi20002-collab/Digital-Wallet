@extends('frontend.layouts.user.index')
@section('title', __('Create Withdraw Account'))
@section('content')
    @php
        $selectedMethod = $withdrawMethods->firstWhere('id', (int) old('method_id'));
    @endphp
    <div class="single-form-card">
        <x-user-feature-header
            :title="__('Create Account')"
            :subtitle="__('Add a verified payout destination for future withdrawals.')"
            icon="fas fa-plus-circle"
        >
            <a class="btn btn-light-primary btn-sm" href="{{ route('user.withdraw.account.index') }}">
                <i class="fa-solid fa-receipt"></i> {{ __('My Accounts') }}
            </a>
        </x-user-feature-header>
        <div class="card-main bg-main">
            @if($withdrawMethods->isEmpty())
                <div class="alert alert-warning mb-0">
                    {{ __('No active withdrawal methods are available right now. Please try again later.') }}
                </div>
            @else
            <form action="{{ route('user.withdraw.account.store') }}"
                  method="POST"
                  enctype="multipart/form-data"
                  data-withdraw-account-form
                  data-fields-url-template="{{ route('user.withdraw.credentials.fields', ['method_id' => '__METHOD_ID__']) }}"
                  data-loading-text="{{ __('Loading credential fields...') }}"
                  data-error-text="{{ __('Unable to load credential fields. Please choose another method or try again.') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="method-select" class="form-label">{{ __('Withdrawal Method') }}</label>
                            <select class="form-select @error('method_id') is-invalid @enderror" id="method-select" name="method_id" required>
                                <option value="" disabled @selected(blank(old('method_id')))>{{ __('Select Withdrawal Method') }}</option>
                                @foreach($withdrawMethods as $method)
                                    <option value="{{ $method->id }}" @selected((int) old('method_id') === (int) $method->id)>
                                        {{ title($method->name) }} - {{ $method->currency }}
                                    </option>
                                @endforeach
                            </select>
                            @error('method_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="account-name" class="form-label">{{ __('Account Name') }}</label>
                            <input type="text"
                                   class="form-control @error('account_name') is-invalid @enderror"
                                   id="accountName"
                                   name="account_name"
                                   value="{{ old('account_name') }}"
                                   placeholder="{{ __('Enter Account Name') }}"
                                   required>
                            @error('account_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>


                {{-- Dynamic Credential Fields --}}
                <div class="row" id="credential-fields">
                    @if($selectedMethod)
                        @include('frontend.user.withdraw.partials.credentials_fields', ['method' => $selectedMethod])
                    @endif
                </div>

                {{-- Submit Button --}}
                <button type="submit" class="btn btn-primary mt-3 w-100">{{ __('Create Account') }}</button>
            </form>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('frontend/js/withdraw-account.js?v=' . config('app.version')) }}"></script>
@endpush
