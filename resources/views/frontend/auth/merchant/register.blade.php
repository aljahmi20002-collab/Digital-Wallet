@extends('frontend.layouts.auth')
@section('title', __('Merchant Register'))
@push('styles')
    <link rel="stylesheet" href="{{ asset('frontend/css/agent.css?v=' . config('app.version') . '-' . filemtime(public_path('frontend/css/agent.css'))) }}">
@endpush
@section('auth-content')

    @php
        $myCurrentLocation = getLocation();
        $allCountries      = getCountries();
    @endphp

    <div class="auth-shell is-long">
        <div class="auth-card has-accent is-merchant is-wide">

            <div class="auth-head">
                <img src="{{ asset(setting('logo')) }}" alt="Logo" class="auth-logo" loading="lazy">
                <h4 class="auth-title">{{ __('Create Merchant Account') }}</h4>
                <p class="auth-subtitle">{{ __('Sign up as a merchant at') }} {{ setting('site_title') }}</p>
                <span class="auth-role-badge is-merchant">
                    <i class="fa-duotone fa-store"></i> {{ __('Merchant Account') }}
                </span>
            </div>

            @if ($errors->any())
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    @foreach ($errors->all() as $error)
                        <strong>{{ $error }}</strong><br>
                    @endforeach
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="alert alert-info mb-3">
                <i class="fas fa-lightbulb me-2"></i>
                {{ __('Merchant benefits: Accept payments, generate QR codes, access API, reduced fees.') }}
            </div>

            <form action="{{ route('merchant.register') }}" method="POST">
                @csrf

                <h6 class="auth-section-heading">
                    <i class="fas fa-user me-1"></i>{{ __('Personal Information') }}
                </h6>

                <div class="row g-3">
                    <div class="col-md-6 col-12">
                        <label for="first_name" class="form-label">{{ __('First Name') }}</label>
                        <input type="text" name="first_name" class="form-control" id="first_name" placeholder="{{ __('First Name') }}" required value="{{ old('first_name') }}">
                    </div>
                    <div class="col-md-6 col-12">
                        <label for="last_name" class="form-label">{{ __('Last Name') }}</label>
                        <input type="text" name="last_name" class="form-control" id="last_name" placeholder="{{ __('Last Name') }}" required value="{{ old('last_name') }}">
                    </div>
                    <div class="col-md-6 col-12">
                        <label for="username" class="form-label">{{ __('Username') }}</label>
                        <input type="text" name="username" class="form-control" id="username" placeholder="{{ __('Choose a username') }}" required value="{{ old('username') }}">
                    </div>
                    <div class="col-md-6 col-12">
                        <label for="email" class="form-label">{{ __('Email Address') }}</label>
                        <input type="email" name="email" class="form-control" id="email" placeholder="{{ __('Email Address') }}" required value="{{ old('email') }}">
                    </div>

                    <div class="col-md-6 col-12">
                        <label class="form-label" for="country">{{ __('Country') }}</label>
                        <select class="form-select" id="countrySelect" name="country" required>
                            <option selected disabled value="">{{ __('Select Country') }}</option>
                            @foreach($allCountries as $country)
                                <option value="{{ strtoupper((string) $country['code']) }}" data-dial-code="{{ $country['dial_code'] ?? '' }}" @selected(old('country', $myCurrentLocation['country_code']) == strtoupper((string) $country['code']))>
                                    {{ getCountryDisplayLabel((string) $country['code']) ?? title((string) $country['name']) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 col-12">
                        <label class="form-label" for="phone">{{ __('Phone Number') }}</label>
                        <div class="input-group">
                            <span class="input-group-text" id="phone">{{ $myCurrentLocation['dial_code'] }}</span>
                            <input type="text" class="form-control" placeholder="{{ __('Phone') }}" name="phone" aria-label="phone" aria-describedby="phone" value="{{ old('phone') }}">
                        </div>
                    </div>

                    <div class="col-md-6 col-12">
                        <label for="password" class="form-label">{{ __('Password') }}</label>
                        <input type="password" name="password" class="form-control" id="password" placeholder="{{ __('Password') }}" required>
                    </div>
                    <div class="col-md-6 col-12">
                        <label for="password_confirmation" class="form-label">{{ __('Confirm Password') }}</label>
                        <input type="password" name="password_confirmation" class="form-control" id="password_confirmation" placeholder="{{ __('Confirm Password') }}" required>
                    </div>

                    {{-- Business Information --}}
                    <div class="col-12 mt-2">
                        <h6 class="auth-section-heading">
                            <i class="fas fa-store me-1"></i>{{ __('Business Information') }}
                        </h6>
                    </div>

                    <div class="col-12">
                        <label for="business_name" class="form-label">{{ __('Business Name') }}</label>
                        <input type="text" name="business_name" class="form-control" id="business_name" placeholder="{{ __('Your business name') }}" required value="{{ old('business_name') }}">
                    </div>
                    <div class="col-12">
                        <label for="business_address" class="form-label">{{ __('Business Address') }}</label>
                        <input type="text" name="business_address" class="form-control" id="business_address" placeholder="{{ __('Business address') }}" required value="{{ old('business_address') }}">
                    </div>
                </div>

                <button type="submit" class="btn btn-success btn-submit-auth mt-4">
                    <i class="fa-light fa-user-plus me-1"></i> {{ __('Create Account') }}
                </button>
            </form>

            <p class="text-center mt-4 mb-2">
                {{ __('Already have a merchant account?') }}
                <a href="{{ route('merchant.login') }}" class="text-decoration-none text-success fw-semibold">{{ __('Sign In') }}</a>
            </p>

            @include('frontend.auth.partials._portal_switch', [
                'current' => 'merchant',
                'page'    => 'register',
                'heading' => __('Other registrations'),
            ])
        </div>
    </div>
@endsection
