@if($enabled)
    <link rel="stylesheet" href="{{ asset('frontend/css/auth-demo-credentials.css?v='.config('app.version').'-'.filemtime(public_path('frontend/css/auth-demo-credentials.css'))) }}">

    <section
        class="demo-credentials demo-credentials--{{ $portal }}"
        data-demo-credentials
        data-demo-credentials-portal="{{ $portal }}"
        data-demo-credentials-form="{{ $formId ?? '' }}"
        aria-labelledby="demoCredentialsHeading-{{ $portal }}"
    >
        <header class="demo-credentials__head">
            <span class="demo-credentials__badge" aria-hidden="true">
                <i class="fa-duotone fa-flask-vial"></i>
                {{ __('Demo Mode') }}
            </span>
            <p id="demoCredentialsHeading-{{ $portal }}" class="demo-credentials__lead">
                {{ __('Click to autofill, or copy any field. Your own credentials still work.') }}
            </p>
        </header>

        <ul class="demo-credentials__list" role="list">
            @foreach($credentials as $credential)
                <li class="demo-credentials__item"
                    data-demo-credentials-item
                    data-demo-email="{{ $credential->email }}"
                    data-demo-password="{{ $credential->password }}">
                    <div class="demo-credentials__row demo-credentials__row--identity">
                        <div class="demo-credentials__identity">
                            <span class="demo-credentials__name">{{ $credential->displayName }}</span>
                            @if($credential->statusLabel)
                                <span class="demo-credentials__status-chip is-{{ $credential->statusTone ?? 'neutral' }}">
                                    {{ $credential->statusLabel }}
                                </span>
                            @endif
                        </div>
                        <button
                            type="button"
                            class="demo-credentials__primary"
                            data-demo-credentials-fill
                            aria-label="{{ __('Use the :name demo account to sign in', ['name' => $credential->displayName]) }}"
                        >
                            <i class="fa-duotone fa-wand-magic-sparkles" aria-hidden="true"></i>
                            <span>{{ __('Use this') }}</span>
                        </button>
                    </div>

                    <div class="demo-credentials__row demo-credentials__row--fields">
                        <div class="demo-credentials__field">
                            <span class="demo-credentials__field-label">{{ __('Email') }}</span>
                            <span class="demo-credentials__field-value" data-demo-credentials-value="email">
                                {{ $credential->email }}
                            </span>
                            <button
                                type="button"
                                class="demo-credentials__copy-btn"
                                data-demo-credentials-copy="email"
                                data-clipboard-text="{{ $credential->email }}"
                                aria-label="{{ __('Copy email :email', ['email' => $credential->email]) }}"
                            >
                                <i class="fa-regular fa-copy" aria-hidden="true"></i>
                                <span class="visually-hidden">{{ __('Copy email') }}</span>
                            </button>
                        </div>

                        <div class="demo-credentials__field">
                            <span class="demo-credentials__field-label">{{ __('Pass') }}</span>
                            <span class="demo-credentials__field-value demo-credentials__field-value--mono" data-demo-credentials-value="password">
                                {{ $credential->password }}
                            </span>
                            <button
                                type="button"
                                class="demo-credentials__copy-btn"
                                data-demo-credentials-copy="password"
                                data-clipboard-text="{{ $credential->password }}"
                                aria-label="{{ __('Copy password') }}"
                            >
                                <i class="fa-regular fa-copy" aria-hidden="true"></i>
                                <span class="visually-hidden">{{ __('Copy password') }}</span>
                            </button>
                        </div>
                    </div>
                </li>
            @endforeach
        </ul>
    </section>

    <script src="{{ asset('frontend/js/auth-demo-credentials.js?v='.config('app.version').'-'.filemtime(public_path('frontend/js/auth-demo-credentials.js'))) }}" defer></script>
@endif
