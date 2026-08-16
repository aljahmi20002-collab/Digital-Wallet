@extends('frontend.layouts.auth')
@section('title', __('Agent Login'))
@push('styles')
    <link rel="stylesheet" href="{{ asset('frontend/css/agent.css?v=' . config('app.version') . '-' . filemtime(public_path('frontend/css/agent.css'))) }}">
@endpush
@section('auth-content')
    <div class="auth-shell agent-context">
        <div class="auth-card has-accent is-agent">

            {{-- Header --}}
            <div class="auth-head">
                <img src="{{ asset(setting('logo')) }}" alt="Logo" class="auth-logo" loading="lazy">
                <h4 class="auth-title is-agent">{{ __('Agent Login') }}</h4>
                <p class="auth-subtitle">{{ __('Sign in to your agent account at') }} {{ setting('site_title') }}</p>
                <span class="auth-role-badge is-agent">
                    <i class="fa-duotone fa-user-tie"></i> {{ __('Agent Account') }}
                </span>
            </div>

            <form id="agentLoginForm" action="{{ route('agent.login') }}" method="post">
                @csrf
                <div class="mb-3">
                    <label for="login" class="form-label fw-semibold">{{ __('E-mail Or Username') }}</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                        <input type="text" name="login" class="form-control" id="login" placeholder="{{ __('E-mail Or Username') }}" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label fw-semibold">{{ __('Password') }}</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" name="password" class="form-control" id="password" placeholder="{{ __('Password') }}" required>
                        <span class="input-group-text bg-transparent cursor-pointer" id="togglePassword">
                            <i class="fas fa-eye"></i>
                        </span>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center my-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="rememberMe">
                        <label class="form-check-label" for="rememberMe">{{ __('Remember Me') }}</label>
                    </div>
                    <a href="{{ route('agent.password.request') }}" class="text-decoration-none text-agent fw-semibold">
                        {{ __('Forgot Password') }}?
                    </a>
                </div>

                @if(config('services.recaptcha.status'))
                    <div class="g-recaptcha mt-3 mb-3" data-sitekey="{{ config('services.recaptcha.key') }}"></div>
                @endif

                <button class="btn btn-agent btn-submit-auth" type="submit">
                    <i class="fa-light fa-right-to-bracket me-1"></i> {{ __('Sign In') }}
                </button>
            </form>

            <p class="text-center mt-4 mb-2">
                {{ __("Don't have an agent account?") }}
                <a href="{{ route('agent.register') }}" class="text-decoration-none text-agent fw-semibold">{{ __('Create One') }}</a>
            </p>

            <x-demo-credentials portal="agent" form-id="agentLoginForm" />

            @include('frontend.auth.partials._portal_switch', [
                'current' => 'agent',
                'page'    => 'login',
                'heading' => __('Switch portal'),
            ])
        </div>
    </div>
@endsection
@push('scripts')
    <script async src="https://www.google.com/recaptcha/api.js"></script>
@endpush
