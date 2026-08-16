@extends('frontend.layouts.user.index')
@section('title', __('Merchant API Configuration'))
@push('styles')
    <link rel="stylesheet" href="{{ asset('frontend/css/merchant.css?v=' . config('app.version') . '-' . filemtime(public_path('frontend/css/merchant.css'))) }}">
@endpush

@section('content')
    @php
        $currentMode = $merchant->current_mode ?? \App\Enums\EnvironmentMode::SANDBOX;
        $merchantCurrencyCodes = $merchant->supportedCurrencies->pluck('code')->implode(', ') ?: ($merchant->currency?->code ?? '-');
        $primaryCurrencyCode = $merchant->primaryCurrency()?->code ?? $merchant->currency?->code ?? '-';
        $gatewayCount = $paymentMethods->count();
        $selectedGatewayCount = count($selectedPaymentMethodIds);
        $credentialSetLabel = $currentMode->isSandbox() ? __('Test set') : __('Live set');
        $productionLockedMessage = __('Production unlocks after admin approval. Sandbox remains active for safe integration testing.');
        $environmentMeta = collect(\App\Enums\EnvironmentMode::cases())->mapWithKeys(fn ($env) => [
            $env->value => [
                'label' => $env->label(),
                'icon' => $env->icon(),
                'caption' => $env->isSandbox() ? __('Test calls') : __('Live payments'),
                'keyset' => $env->isSandbox() ? __('Test set') : __('Live set'),
                'description' => $env->description(),
            ],
        ]);
    @endphp

    <div class="card single-form-card merchant-service-card">
        <x-user-feature-header
            :title="__('Merchant API Configuration')"
            :subtitle="__('Keys, gateways, and launch controls.')"
            icon="fas fa-cogs"
        >
            <a href="{{ route('user.merchant.index') }}" class="btn btn-light-merchant btn-sm">
                <i class="fas fa-arrow-left"></i> {{ __('Back to Merchants') }}
            </a>
        </x-user-feature-header>

        <div class="card-main merchant-context">
            <div class="merchant-config-hero">
                <div class="merchant-config-hero__identity">
                    <img src="{{ asset($merchant->business_logo) }}" alt="{{ $merchant->business_name }}" class="merchant-config-hero__logo" loading="lazy">
                    <div class="merchant-config-hero__copy">
                        <span class="merchant-config-hero__eyebrow">
                            <i class="fa-solid fa-key"></i>
                            {{ __('API console') }}
                        </span>
                        <h2>{{ $merchant->business_name }}</h2>
                        <p>{{ __('Manage mode, keys, gateways, and launch checks from one focused workspace.') }}</p>
                    </div>
                </div>

                <div class="merchant-config-hero__stats">
                    <div>
                        <span>{{ __('Status') }}</span>
                        <strong>{{ $merchant->status->label() }}</strong>
                    </div>
                    <div>
                        <span>{{ __('Primary') }}</span>
                        <strong>{{ $primaryCurrencyCode }}</strong>
                    </div>
                    <div>
                        <span>{{ __('Rails') }}</span>
                        <strong>{{ $merchantCurrencyCodes }}</strong>
                    </div>
                </div>
            </div>

            <div class="merchant-config-summary">
                <div>
                    <i class="{{ $currentMode->icon() }}" data-current-mode-icon></i>
                    <span>{{ __('Active Mode') }}</span>
                    <strong data-current-mode-label>{{ $currentMode->label() }}</strong>
                </div>
                <div>
                    <i class="fa-solid fa-key"></i>
                    <span>{{ __('Credential Set') }}</span>
                    <strong data-current-keyset>{{ $credentialSetLabel }}</strong>
                </div>
                <div>
                    <i class="fa-solid fa-credit-card"></i>
                    <span>{{ __('Gateways') }}</span>
                    <strong>{{ $selectedGatewayCount }}/{{ $gatewayCount }}</strong>
                </div>
                <div>
                    <i class="fa-solid fa-coins"></i>
                    <span>{{ __('Currency Rail') }}</span>
                    <strong>{{ $primaryCurrencyCode }}</strong>
                </div>
            </div>

            <div class="merchant-config-grid">
                <section class="merchant-config-panel">
                    <div class="merchant-config-panel__head">
                        <div class="d-flex align-items-center gap-2 min-w-0">
                            <span class="merchant-panel-icon"><i class="fas fa-toggle-on"></i></span>
                            <div class="min-w-0">
                                <h3>{{ __('Environment') }}</h3>
                                <p>{{ __('Switch mode and use the matching credential set.') }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="merchant-environment-switch" role="group" aria-label="{{ __('API Environment') }}">
                        @foreach(\App\Enums\EnvironmentMode::cases() as $env)
                            @php
                                $isProductionLocked = $env->isProduction() && ! $merchant->isApproved();
                                $environmentTitle = $isProductionLocked
                                    ? __('Production requires merchant approval')
                                    : $env->description();
                            @endphp
                            <button type="button"
                                    class="btn merchant-environment-btn environment-btn {{ $currentMode === $env ? 'active' : '' }} {{ $isProductionLocked ? 'is-locked' : '' }}"
                                    data-environment="{{ $env->value }}"
                                    data-locked="{{ $isProductionLocked ? 1 : 0 }}"
                                    data-lock-message="{{ $productionLockedMessage }}"
                                    aria-label="{{ $isProductionLocked ? __('Production locked until admin approval') : $env->label() }}"
                                    aria-pressed="{{ $currentMode === $env ? 'true' : 'false' }}"
                                    title="{{ $environmentTitle }}">
                                <span class="merchant-environment-btn__icon">
                                    <i class="{{ $env->icon() }}"></i>
                                </span>
                                <span class="merchant-environment-btn__copy">
                                    <strong>{{ $env->label() }}</strong>
                                    <small>{{ $env->isSandbox() ? __('Test calls') : __('Live payments') }}</small>
                                </span>
                                @if($isProductionLocked)
                                    <span class="merchant-environment-btn__lock" aria-hidden="true">
                                        <i class="fa-solid fa-lock"></i>
                                    </span>
                                @endif
                            </button>
                        @endforeach
                    </div>

                    <div class="merchant-environment-lock-note {{ $merchant->isApproved() ? 'd-none' : '' }}" data-environment-lock-note>
                        <i class="fa-solid fa-lock"></i>
                        <span data-environment-lock-message>{{ $merchant->isApproved() ? '' : $productionLockedMessage }}</span>
                    </div>

                    <div class="merchant-mode-alert" data-current-mode-alert>
                        <i class="{{ $currentMode->icon() }}" data-mode-icon></i>
                        <div>
                            <strong>{{ __('Mode note') }}</strong>
                            <span data-mode-description>{{ $currentMode->description() }}</span>
                        </div>
                    </div>

                    <div id="credentials-section" class="mt-3">
                        @foreach(\App\Enums\EnvironmentMode::cases() as $env)
                            <div class="credentials-container"
                                 id="{{ $env->value }}-credentials"
                                 style="{{ $currentMode === $env ? '' : 'display: none;' }}">
                                @php
                                    $credentials = [
                                        'merchant_key' => [
                                            'label' => __('Merchant ID'),
                                            'icon' => 'fas fa-id-badge',
                                            'hint' => __('Identifies the shop in every server request.'),
                                            'value' => $env->isSandbox()
                                                ? ($merchant->test_merchant_key ?: __('Generate by switching to sandbox'))
                                                : ($merchant->merchant_key ?: __('Available after approval')),
                                            'copy_title' => $env->isSandbox() ? __('Copy Test Merchant Key') : __('Copy Merchant Key'),
                                        ],
                                        'api_key' => [
                                            'label' => $env->isSandbox() ? __('Test API Key') : __('API Key'),
                                            'icon' => 'fas fa-key',
                                            'hint' => __('Send with API calls from your backend.'),
                                            'value' => $env->isSandbox()
                                                ? ($merchant->test_api_key ?: __('Generate by switching to sandbox'))
                                                : $merchant->api_key,
                                            'copy_title' => $env->isSandbox() ? __('Copy Test API Key') : __('Copy API Key'),
                                        ],
                                        'api_secret' => [
                                            'label' => $env->isSandbox() ? __('Test API Secret Key') : __('API Secret Key'),
                                            'icon' => 'fas fa-shield-alt',
                                            'hint' => __('Keep private and use it only for signing.'),
                                            'value' => $env->isSandbox()
                                                ? ($merchant->test_api_secret ?: __('Generate by switching to sandbox'))
                                                : $merchant->api_secret,
                                            'copy_title' => $env->isSandbox() ? __('Copy Test Secret Key') : __('Copy Secret Key'),
                                        ],
                                    ];
                                @endphp

                                <div class="merchant-config-panel__head">
                                    <div class="d-flex align-items-center gap-2 min-w-0">
                                        <span class="merchant-panel-icon"><i class="{{ $env->icon() }}"></i></span>
                                        <div class="min-w-0">
                                            <h3>{{ __('Credentials') }}</h3>
                                            <p>{{ __('Server-side keys for the selected mode.') }}</p>
                                        </div>
                                    </div>
                                    <span class="merchant-config-pill">
                                        <i class="fa-solid fa-server"></i>
                                        {{ $env->isSandbox() ? __('Test keys') : __('Live keys') }}
                                    </span>
                                </div>

                                <div class="merchant-credential-grid">
                                    @foreach($credentials as $key => $credential)
                                        <div class="merchant-credential-card">
                                            <span class="merchant-credential-card__icon">
                                                <i class="{{ $credential['icon'] }}"></i>
                                            </span>
                                            <div class="min-w-0">
                                                <strong>{{ $credential['label'] }}</strong>
                                                <code data-credential-field="{{ $key }}" data-credential-environment="{{ $env->value }}">{{ $credential['value'] }}</code>
                                                <small>{{ $credential['hint'] }}</small>
                                            </div>
                                            <button type="button"
                                                    class="merchant-copy-btn copyNow"
                                                    data-clipboard-text="{{ $credential['value'] }}"
                                                    data-copy-field="{{ $key }}"
                                                    data-copy-environment="{{ $env->value }}"
                                                    title="{{ $credential['copy_title'] }}"
                                                    data-bs-toggle="tooltip"
                                                    data-bs-placement="top">
                                                <i class="fa-solid fa-copy"></i>
                                            </button>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="merchant-credential-guide">
                                    <div>
                                        <i class="fa-solid fa-server"></i>
                                        <strong>{{ __('Backend only') }}</strong>
                                        <span>{{ __('Create payment sessions from your server, never from browser code.') }}</span>
                                    </div>
                                    <div>
                                        <i class="fa-solid fa-code-branch"></i>
                                        <strong>{{ __('Mode aware') }}</strong>
                                        <span>{{ __('Sandbox and production use separate IDs, keys, and secrets.') }}</span>
                                    </div>
                                    <div>
                                        <i class="fa-solid fa-rotate"></i>
                                        <strong>{{ __('Rotate safely') }}</strong>
                                        <span>{{ __('Replace exposed keys before accepting live payments.') }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>

                <aside class="merchant-config-side">
                    <section class="merchant-gateway-panel merchant-config-panel mb-3">
                        <div class="merchant-config-panel__head">
                            <div class="d-flex align-items-center gap-2 min-w-0">
                                <span class="merchant-panel-icon"><i class="fa-solid fa-credit-card"></i></span>
                                <div class="min-w-0">
                                    <h3>{{ __('Payment Gateway Controls') }}</h3>
                                    <p>{{ __('Currency-safe gateways for this merchant wallet rail.') }}</p>
                                </div>
                            </div>
                            <span class="merchant-config-pill">
                                <i class="fa-solid fa-check-double"></i>
                                {{ $selectedGatewayCount }}/{{ $gatewayCount }}
                            </span>
                        </div>

                        @if($paymentMethods->isNotEmpty())
                            <form action="{{ route('user.merchant.payment-methods.update', $merchant) }}" method="POST" class="merchant-gateway-form">
                                @csrf
                                @method('PUT')

                                <div class="merchant-gateway-statusbar">
                                    <span><i class="fa-solid fa-coins"></i> {{ $primaryCurrencyCode }}</span>
                                    <span><i class="fa-solid fa-wallet"></i> {{ __('Wallet matched') }}</span>
                                    <span><i class="fa-solid fa-shield-halved"></i> {{ __('Currency safe') }}</span>
                                </div>

                                <div class="merchant-gateway-grid {{ $gatewayCount > 4 ? 'merchant-gateway-grid--scroll' : '' }}">
                                    @foreach($paymentMethods as $method)
                                        @php
                                            $gatewayInputId = 'merchant_gateway_'.$method->id;
                                        @endphp
                                        <input
                                            type="checkbox"
                                            class="merchant-gateway-card__input"
                                            id="{{ $gatewayInputId }}"
                                            name="payment_method_ids[]"
                                            value="{{ $method->id }}"
                                            @checked(in_array((int) $method->id, $selectedPaymentMethodIds, true))
                                        >
                                        <label class="merchant-gateway-card" for="{{ $gatewayInputId }}">
                                            <span class="merchant-gateway-card__mark"><i class="fas fa-check"></i></span>
                                            <span class="merchant-gateway-card__logo">
                                                <img src="{{ asset($method->logo_alt) }}" alt="{{ $method->name }}" loading="lazy">
                                            </span>
                                            <span class="merchant-gateway-card__body">
                                                <strong>{{ $method->name }}</strong>
                                                <small>{{ $method->currency }} &middot; {{ $method->method_code }}</small>
                                            </span>
                                        </label>
                                    @endforeach
                                </div>

                                @error('payment_method_ids')
                                    <div class="invalid-feedback d-block mt-2">{{ $message }}</div>
                                @enderror
                                @error('payment_method_ids.*')
                                    <div class="invalid-feedback d-block mt-2">{{ $message }}</div>
                                @enderror

                                <div class="merchant-gateway-save">
                                    <span>{{ __(':count available', ['count' => $gatewayCount]) }}</span>
                                    <button type="submit" class="btn btn-merchant">
                                        <i class="fa-solid fa-sliders"></i> {{ __('Save Gateways') }}
                                    </button>
                                </div>
                            </form>
                        @else
                            <x-user-not-found
                                :title="__('No matching gateways found')"
                                :message="__('Enable an automatic deposit method whose currency matches this merchant and its active wallet.')"
                                icon="fa-credit-card"
                            />
                        @endif
                    </section>

                    <section class="merchant-checklist-card mb-3">
                        <div class="merchant-checklist-card__head">
                            <div class="d-flex align-items-center gap-2 min-w-0">
                                <span class="merchant-panel-icon"><i class="fa-solid fa-list-check"></i></span>
                                <div class="min-w-0">
                                    <h3>{{ __('Readiness') }}</h3>
                                    <p>{{ __('Quick launch controls before live checkout.') }}</p>
                                </div>
                            </div>
                        </div>

                        <ul class="merchant-checklist">
                            <li>
                                <i class="fa-solid fa-flask"></i>
                                <div>
                                    <strong>{{ __('Sandbox tested') }}</strong>
                                    <span>{{ __('Callbacks and redirects verified.') }}</span>
                                </div>
                            </li>
                            <li>
                                <i class="fa-solid fa-coins"></i>
                                <div>
                                    <strong>{{ __('Currency: :currency', ['currency' => $primaryCurrencyCode]) }}</strong>
                                    <span>{{ __('API and payment links match rail.') }}</span>
                                </div>
                            </li>
                            <li>
                                <i class="fa-solid fa-lock"></i>
                                <div>
                                    <strong>{{ __('Secrets protected') }}</strong>
                                    <span>{{ __('Server-side storage only.') }}</span>
                                </div>
                            </li>
                            <li>
                                <i class="fa-solid fa-rocket"></i>
                                <div>
                                    <strong>{{ __('Approval required') }}</strong>
                                    <span>{{ __('Production unlocks after review.') }}</span>
                                </div>
                            </li>
                        </ul>
                    </section>

                    <section class="merchant-integration-card">
                        <div class="merchant-integration-card__head">
                            <div class="d-flex align-items-center gap-2 min-w-0">
                                <span class="merchant-panel-icon"><i class="fa-solid fa-plug"></i></span>
                                <div class="min-w-0">
                                    <h3>{{ __('Actions') }}</h3>
                                    <p>{{ __('Docs, links, and profile setup.') }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="merchant-integration-actions">
                            <a href="{{ route('api-docs.index') }}" class="btn btn-light-merchant">
                                <i class="fas fa-book"></i> {{ __('API Docs') }}
                            </a>
                            <a href="{{ route('user.payment-links.create', ['merchant_id' => $merchant->id]) }}" class="btn btn-merchant">
                                <i class="fas fa-link"></i> {{ __('Payment Link') }}
                            </a>
                            <a href="{{ route('user.merchant.edit', $merchant->id) }}" class="btn btn-outline-merchant">
                                <i class="fas fa-edit"></i> {{ __('Edit Profile') }}
                            </a>
                        </div>
                    </section>
                </aside>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        "use strict";

        document.addEventListener('DOMContentLoaded', function() {
            const environmentMeta = @json($environmentMeta);
            const switchUrl = '{{ route("user.merchant.switch-environment") }}';
            const csrfToken = '{{ csrf_token() }}';
            const merchantId = {{ $merchant->id }};
            const buttons = Array.from(document.querySelectorAll('.environment-btn'));

            buttons.forEach(function(button) {
                button.dataset.originalHtml = button.innerHTML;

                button.addEventListener('click', function() {
                    if (button.disabled) {
                        return;
                    }

                    if (Number(button.dataset.locked) === 1) {
                        showEnvironmentLock(button.dataset.lockMessage || '{{ $productionLockedMessage }}');

                        return;
                    }

                    switchEnvironment(button);
                });
            });

            async function switchEnvironment(button) {
                const environment = button.dataset.environment;
                const previousEnvironment = document.querySelector('.environment-btn.active')?.dataset.environment || '{{ $currentMode->value }}';

                setLoadingEnvironment(button);

                try {
                    const response = await fetch(switchUrl, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: JSON.stringify({
                            merchant_id: merchantId,
                            environment: environment,
                        }),
                    });

                    const payload = await response.json().catch(function() {
                        return {};
                    });

                    if (!response.ok || !payload.success) {
                        throw new Error(payload.message || '{{ __("Failed to switch environment") }}');
                    }

                    const activeEnvironment = payload.current_mode || environment;

                    restoreActiveEnvironment(activeEnvironment);
                    toggleCredentialsDisplay(activeEnvironment);
                    updateCurrentModeAlert(activeEnvironment);
                    updateCredentials(activeEnvironment, payload.credentials || {});
                    clearEnvironmentLock();
                    notifySuccess(payload.message);
                } catch (error) {
                    restoreActiveEnvironment(previousEnvironment);
                    notifyError(error.message || '{{ __("An error occurred while switching environment") }}');
                } finally {
                    restoreEnvironmentButtons();
                }
            }

            function setLoadingEnvironment(activeButton) {
                buttons.forEach(function(item) {
                    item.classList.remove('active');
                    item.disabled = true;
                });

                activeButton.classList.add('active');
                activeButton.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> {{ __("Switching...") }}';
            }

            function toggleCredentialsDisplay(environment) {
                document.querySelectorAll('.credentials-container').forEach(function(container) {
                    container.style.display = container.id === environment + '-credentials' ? '' : 'none';
                });
            }

            function restoreActiveEnvironment(environment) {
                buttons.forEach(function(item) {
                    item.classList.toggle('active', item.dataset.environment === environment);
                    item.setAttribute('aria-pressed', item.dataset.environment === environment ? 'true' : 'false');
                });
            }

            function restoreEnvironmentButtons() {
                buttons.forEach(function(item) {
                    const locked = Number(item.dataset.locked) === 1;

                    item.innerHTML = item.dataset.originalHtml;
                    item.disabled = false;
                    item.classList.toggle('is-locked', locked);
                    item.removeAttribute('aria-disabled');
                });
            }

            function updateCurrentModeAlert(environment) {
                const meta = environmentMeta[environment];
                const alertBox = document.querySelector('[data-current-mode-alert]');

                if (!alertBox || !meta) {
                    return;
                }

                alertBox.querySelector('[data-mode-icon]').className = meta.icon;
                alertBox.querySelector('[data-mode-description]').textContent = meta.description;

                const currentModeLabel = document.querySelector('[data-current-mode-label]');
                const currentModeIcon = document.querySelector('[data-current-mode-icon]');
                const currentKeyset = document.querySelector('[data-current-keyset]');

                if (currentModeIcon) {
                    currentModeIcon.className = meta.icon;
                }

                if (currentModeLabel) {
                    currentModeLabel.textContent = meta.label;
                }

                if (currentKeyset) {
                    currentKeyset.textContent = meta.keyset;
                }
            }

            function updateCredentials(environment, credentials) {
                Object.entries(credentials).forEach(function([field, value]) {
                    const credential = document.querySelector('[data-credential-field="' + field + '"][data-credential-environment="' + environment + '"]');
                    const copyButton = document.querySelector('[data-copy-field="' + field + '"][data-copy-environment="' + environment + '"]');

                    if (credential) {
                        credential.textContent = value;
                    }

                    if (copyButton) {
                        copyButton.dataset.clipboardText = value;
                    }
                });
            }

            function notifySuccess(message) {
                if (typeof notifyEvs === 'function') {
                    notifyEvs('success', message);
                }
            }

            function notifyError(message) {
                if (typeof notifyEvs === 'function') {
                    notifyEvs('error', message);
                }
            }

            function showEnvironmentLock(message) {
                const lockNote = document.querySelector('[data-environment-lock-note]');

                if (lockNote) {
                    lockNote.classList.remove('d-none');
                    lockNote.querySelector('[data-environment-lock-message]').textContent = message;
                }

                notifyError(message);
            }

            function clearEnvironmentLock() {
                const lockNote = document.querySelector('[data-environment-lock-note]');

                if (lockNote && ! lockNote.querySelector('[data-environment-lock-message]').textContent) {
                    lockNote.classList.add('d-none');
                }
            }
        });
    </script>
@endsection
