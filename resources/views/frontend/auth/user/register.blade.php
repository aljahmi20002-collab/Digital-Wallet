@extends('frontend.layouts.auth')
@section('title', __('User Register'))
@push('styles')
    <link rel="stylesheet" href="{{ asset('frontend/css/agent.css?v=' . config('app.version') . '-' . filemtime(public_path('frontend/css/agent.css'))) }}">
@endpush
@section('auth-content')

    @php
        $myCurrentLocation = getLocation();
        $allCountries      = getCountries();
    @endphp

    <div class="auth-shell is-long">
        <div class="auth-card has-accent is-user is-wide">

            <div class="auth-head">
                <img src="{{ asset(setting('logo')) }}" alt="Logo" class="auth-logo" loading="lazy">
                <h4 class="auth-title">{{ __('Create User Account') }}</h4>
                <p class="auth-subtitle">{{ __('Sign up as a user at') }} {{ setting('site_title') }}</p>
                <span class="auth-role-badge is-user">
                    <i class="fa-duotone fa-user"></i> {{ __('User Account') }}
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

            <form action="{{ route('user.register') }}" method="POST">
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
                            <input type="text" class="form-control" placeholder="{{ __('Phone') }}" name="phone" aria-label="phone" aria-describedby="phone">
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
                </div>

                <button type="submit" class="btn btn-primary btn-submit-auth mt-4">
                    <i class="fa-light fa-user-plus me-1"></i> {{ __('Create Account') }}
                </button>
            </form>

            <p class="text-center mt-4 mb-2">
                {{ __('Already have a user account?') }}
                <a href="{{ route('user.login') }}" class="text-decoration-none text-primary fw-semibold">{{ __('Sign In') }}</a>
            </p>

            @include('frontend.auth.partials._portal_switch', [
                'current' => 'user',
                'page'    => 'register',
                'heading' => __('Other registrations'),
            ])
        </div>
    </div>
@endsection
