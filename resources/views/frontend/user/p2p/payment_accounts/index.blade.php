@extends('frontend.layouts.user.index')
@section('title', __('P2P Payment Accounts'))
@section('content')
    @use('Illuminate\Support\Str')

    @php
        $availableMethods = collect($methods ?? [])->values();
        $savedAccounts = collect($accounts ?? [])->values();
        $request = request();
        $oldFormType = (string) old('form_type', '');

        $methodOptions = $availableMethods->map(function ($method) {
            $countryLabel = filled($method->country)
                ? (getCountryDisplayLabel((string) $method->country, false) ?? $method->country)
                : null;
            $label = (string) $method->name;

            if ($countryLabel) {
                $label .= ' ('.$countryLabel.')';
            }

            return [
                'id' => (int) $method->id,
                'label' => $label,
            ];
        })->values();

        $methodsPayload = $availableMethods->map(function ($method) {
            $logoUrl = filled($method->logo)
                ? asset('storage/'.ltrim((string) $method->logo, '/'))
                : null;

            return [
                'id' => (int) $method->id,
                'name' => (string) $method->name,
                'country' => (string) ($method->country ?? ''),
                'logo_url' => $logoUrl,
                'instructions' => (string) ($method->instructions ?? ''),
                'fields' => $method->normalizedFields(),
            ];
        })->all();

        $accountsPayload = $savedAccounts->map(function ($account) {
            $snapshot = $account->toTradeSnapshot();
            $snapshot['label'] = (string) ($account->label ?? $account->effective_label ?? '');

            return $snapshot;
        })->all();

        $createOldAccountPayload = $oldFormType === 'create_account'
            ? [
                'payment_method_id' => old('payment_method_id'),
                'label' => old('label'),
                'field_values' => old('field_values', []),
            ]
            : [
                'payment_method_id' => null,
                'label' => null,
                'field_values' => [],
            ];

        $createQueryAccountPayload = [
            'payment_method_id' => $request->input('payment_method_id'),
            'label' => null,
            'field_values' => [],
        ];

        $createInitialAccountPayload = filled($createOldAccountPayload['payment_method_id'])
            ? $createOldAccountPayload
            : $createQueryAccountPayload;

        $updateOldAccountPayload = $oldFormType === 'update_account'
            ? [
                'account_id' => old('account_id'),
                'payment_method_id' => old('payment_method_id'),
                'label' => old('label'),
                'field_values' => old('field_values', []),
            ]
            : null;

        $accountCards = $savedAccounts->map(function ($account) {
            $paymentMethod = $account->paymentMethod;
            $paymentMethodName = (string) ($paymentMethod?->name ?? __('Payment Method'));
            $accountDisplayName = (string) ($account->effective_label ?: $paymentMethodName);
            $displayValue = trim((string) ($account->display_value ?? ''));
            $filteredDetails = collect($account->renderedDetails($paymentMethod))
                ->reject(function ($detail) use ($displayValue) {
                    return $displayValue !== ''
                        && trim((string) ($detail['value'] ?? '')) === $displayValue;
                })
                ->values();

            return [
                'account' => $account,
                'payment_method_name' => $paymentMethodName,
                'account_display_name' => $accountDisplayName,
                'shows_account_display_name' => strcasecmp(trim($accountDisplayName), trim($paymentMethodName)) !== 0,
                'payment_method_logo_url' => filled($paymentMethod?->logo)
                    ? asset('storage/'.ltrim((string) $paymentMethod->logo, '/'))
                    : null,
                'payment_method_initial' => Str::upper(Str::substr($paymentMethodName, 0, 1)),
                'details_preview' => $filteredDetails->take(2)->all(),
                'saved_details_count' => $filteredDetails->count(),
                'country_label' => filled($paymentMethod?->country)
                    ? (getCountryDisplayLabel((string) $paymentMethod->country) ?? $paymentMethod->country)
                    : null,
            ];
        })->values();

        $configuredMethodIds = $accountCards
            ->pluck('account.payment_method_id')
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values()
            ->all();

        $missingMethods = $availableMethods->reject(function ($method) use ($configuredMethodIds) {
            return in_array((int) $method->id, $configuredMethodIds, true);
        })->values();

        $availableMethodCards = $missingMethods->take(6)->map(function ($method) {
            $methodName = (string) $method->name;

            return [
                'id' => (int) $method->id,
                'name' => $methodName,
                'country_label' => filled($method->country)
                    ? (getCountryDisplayLabel((string) $method->country) ?? $method->country)
                    : __('Global'),
                'logo_url' => filled($method->logo)
                    ? asset('storage/'.ltrim((string) $method->logo, '/'))
                    : null,
                'initial' => Str::upper(Str::substr($methodName, 0, 1)),
                'create_url' => route('user.p2p.payment-accounts.index', ['create' => 1, 'payment_method_id' => $method->id]),
            ];
        })->values();

        $regionSupportedMethods = $availableMethods->filter(function ($method) use ($userCountryCode) {
            return blank($method->country) || (string) $method->country === (string) $userCountryCode;
        })->count();

        $summaryCards = [
            [
                'label' => __('Saved Accounts'),
                'value' => $accountCards->count(),
                'suffix' => __('accounts'),
                'icon' => 'fa-wallet',
                'tone' => 'primary',
            ],
            [
                'label' => __('Methods Ready'),
                'value' => count($configuredMethodIds),
                'suffix' => __('ready'),
                'icon' => 'fa-circle-check',
                'tone' => 'success',
            ],
            [
                'label' => __('Methods Left'),
                'value' => $missingMethods->count(),
                'suffix' => __('left'),
                'icon' => 'fa-layer-group',
                'tone' => 'info',
            ],
            [
                'label' => __('Region Ready'),
                'value' => $regionSupportedMethods,
                'suffix' => __('regions'),
                'icon' => 'fa-globe',
                'tone' => 'warning',
            ],
        ];

        $queuedMethodsCount = max($missingMethods->count() - 6, 0);
        $formattedQueuedMethodsCount = number_format($queuedMethodsCount);
        $queuedMethodsSummary = trans_choice(
            ':count more method to set up.|:count more methods to set up.',
            $queuedMethodsCount,
            ['count' => $formattedQueuedMethodsCount]
        );

        $paymentAccountPageState = [
            'methods' => $methodsPayload,
            'accounts' => $accountsPayload,
            'create' => [
                'initialPayload' => $createInitialAccountPayload,
                'shouldOpen' => $oldFormType === 'create_account'
                    || $request->boolean('create')
                    || $request->filled('payment_method_id'),
            ],
            'update' => [
                'shouldOpen' => $oldFormType === 'update_account',
                'accountId' => data_get($updateOldAccountPayload, 'account_id'),
                'payload' => $updateOldAccountPayload,
                'urlTemplate' => route('user.p2p.payment-accounts.update', ['paymentAccount' => '__ACCOUNT__']),
            ],
            'lang' => [
                'methodInstructions' => __('Method Instructions'),
                'noMethodInstructions' => __('No extra method instructions provided.'),
                'selectOption' => __('Select option'),
                'noDynamicFields' => __('No dynamic fields configured for this method yet.'),
                'accountLabelExample' => __('Example: Personal :method', ['method' => ':method']),
                'accountLabelGeneric' => __('Example: Personal account'),
                'deleteModalTitle' => __('Delete Payment Account'),
                'deleteModalBody' => __('Are you sure you want to delete this payment account? This action cannot be undone.'),
                'deleteModalLabel' => __('Account'),
                'deleteModalConfirm' => __('Yes, Delete'),
            ],
        ];
    @endphp

    <div class="single-form-card p2p-ui p2p-accounts">
        <x-user-feature-header
            :title="__('P2P Payment Accounts')"
            :subtitle="__('Manage your payment methods for trading')"
            icon="fas fa-wallet"
        >
            <button type="button" class="btn btn-light btn-sm p2p-btn-xs" data-bs-toggle="modal" data-bs-target="#p2pPaymentAccountCreateModal">
                <i class="fas fa-plus"></i> @lang('Add Account')
            </button>
        </x-user-feature-header>

        <div class="card-main p2p-card-main">
            <div class="p2p-my-ads-summary">
                @foreach($summaryCards as $card)
                    <div class="p2p-summary-card p2p-summary-card--{{ $card['tone'] }}">
                        <span class="p2p-summary-card__icon"><i class="fas {{ $card['icon'] }}"></i></span>
                        <div class="p2p-summary-card__content">
                            <span class="p2p-summary-card__label">{{ $card['label'] }}</span>
                            <div class="p2p-summary-card__metric">
                                <strong class="p2p-summary-card__value">{{ number_format($card['value']) }}</strong>
                                <span class="p2p-summary-card__suffix">{{ $card['suffix'] }}</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="row g-4">
                <div class="col-12 col-xl-8">
                    @include('frontend.user.p2p.payment_accounts.partials._saved_accounts_panel', [
                        'accountCards' => $accountCards,
                    ])
                </div>

                <div class="col-12 col-xl-4">
                    @include('frontend.user.p2p.payment_accounts.partials._available_methods_panel', [
                        'availableMethodCards' => $availableMethodCards,
                        'queuedMethodsCount' => $queuedMethodsCount,
                        'formattedQueuedMethodsCount' => $formattedQueuedMethodsCount,
                        'queuedMethodsSummary' => $queuedMethodsSummary,
                    ])
                </div>
            </div>
        </div>
    </div>

    @include('frontend.user.p2p.payment_accounts.partials._payment_account_modal', [
        'modalId' => 'p2pPaymentAccountCreateModal',
        'title' => __('Add P2P Payment Account'),
        'formAction' => route('user.p2p.payment-accounts.store'),
        'formId' => 'p2pPaymentAccountCreateForm',
        'formType' => 'create_account',
        'httpMethod' => null,
        'accountIdInputId' => null,
        'accountIdValue' => null,
        'methodSelectId' => 'p2pCreatePaymentMethod',
        'labelInputId' => 'p2pCreateAccountLabel',
        'infoId' => 'p2pCreateMethodInfo',
        'fieldsWrapId' => 'p2pCreateDynamicFields',
        'submitLabel' => __('Save Account'),
        'methodOptions' => $methodOptions,
        'selectedPaymentMethodId' => data_get($createInitialAccountPayload, 'payment_method_id'),
        'labelValue' => data_get($createInitialAccountPayload, 'label'),
    ])

    @include('frontend.user.p2p.payment_accounts.partials._payment_account_modal', [
        'modalId' => 'p2pPaymentAccountEditModal',
        'title' => __('Edit P2P Payment Account'),
        'formAction' => '#',
        'formId' => 'p2pPaymentAccountEditForm',
        'formType' => 'update_account',
        'httpMethod' => 'PUT',
        'accountIdInputId' => 'p2pEditAccountId',
        'accountIdValue' => data_get($updateOldAccountPayload, 'account_id'),
        'methodSelectId' => 'p2pEditPaymentMethod',
        'labelInputId' => 'p2pEditAccountLabel',
        'infoId' => 'p2pEditMethodInfo',
        'fieldsWrapId' => 'p2pEditDynamicFields',
        'submitLabel' => __('Update Account'),
        'methodOptions' => $methodOptions,
        'selectedPaymentMethodId' => data_get($updateOldAccountPayload, 'payment_method_id'),
        'labelValue' => data_get($updateOldAccountPayload, 'label'),
    ])

    <div class="modal fade p2p-ui-modal" id="p2pPaymentAccountDeleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Delete Payment Account')</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="@lang('Close')"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-2">@lang('Are you sure you want to delete this payment account? This action cannot be undone.')</p>
                    <div class="rounded border bg-light px-3 py-2">
                        <span class="fw-semibold">@lang('Account'):</span>
                        <span id="p2pDeleteAccountLabel">-</span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">@lang('Cancel')</button>
                    <button type="button" class="btn btn-danger btn-sm" id="p2pPaymentAccountDeleteConfirm">
                        <i class="fas fa-trash-alt me-1"></i> @lang('Yes, Delete')
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @include('frontend.user.p2p.payment_accounts.partials._payment_accounts_scripts', [
        'paymentAccountPageState' => $paymentAccountPageState,
    ])
@endpush
