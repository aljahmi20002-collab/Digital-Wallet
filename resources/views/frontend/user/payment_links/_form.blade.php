@php
    $paymentLink         ??= null;
    $merchants           ??= collect();
    $preselectMerchantId ??= null;

    $values = [
        'title'        => old('title', $paymentLink?->title),
        'description'  => old('description', $paymentLink?->description),
        'merchant_id'  => old('merchant_id', $paymentLink?->merchant_id ?? $preselectMerchantId),
        'currency_id'  => old('currency_id', $paymentLink?->currency_id),
        'amount'       => old('amount', $paymentLink?->amount !== null ? rtrim(rtrim(number_format((float) $paymentLink->amount, 2, '.', ''), '0'), '.') : null),
        'min_amount'   => old('min_amount', $paymentLink?->min_amount !== null ? rtrim(rtrim(number_format((float) $paymentLink->min_amount, 2, '.', ''), '0'), '.') : null),
        'max_amount'   => old('max_amount', $paymentLink?->max_amount !== null ? rtrim(rtrim(number_format((float) $paymentLink->max_amount, 2, '.', ''), '0'), '.') : null),
        'expires_at'   => old('expires_at', $paymentLink?->expires_at?->format('Y-m-d\TH:i')),
        'max_payments' => old('max_payments', $paymentLink?->max_payments),
    ];

    $merchantsByIdJson = $merchants
        ->mapWithKeys(function ($m) {
            $merchantCurrencies = $m->supportedCurrencies->isNotEmpty()
                ? $m->supportedCurrencies
                : collect([$m->currency])->filter();
            $primaryCurrency = $m->primaryCurrency() ?? $merchantCurrencies->first();

            return [
                (int) $m->id => [
                    'business_name' => $m->business_name,
                    'currency_id'   => $primaryCurrency?->id,
                    'currency_code' => $primaryCurrency?->code,
                    'currency_name' => $primaryCurrency?->name,
                    'currencies'    => $merchantCurrencies
                        ->map(fn ($currency) => [
                            'id'   => (int) $currency->id,
                            'code' => $currency->code,
                            'name' => $currency->name,
                        ])
                        ->values(),
                    'business_logo' => asset($m->business_logo),
                    'fee'           => (float) $m->fee,
                ],
            ];
        })
        ->toJson();
@endphp

<div class="row g-3">
    @if($merchants->isNotEmpty())
        <div class="col-12">
            <div class="mb-2 single-input-inner style-border">
                <label for="merchant_id" class="form-label">
                    {{ __('Use a Merchant Shop') }}
                    <i data-bs-toggle="tooltip" data-bs-placement="top"
                       title="{{ __('Optional. Select one of your approved merchant shops to brand the link and choose one of its supported currencies.') }}"
                       class="fa-solid fa-circle-info ms-1"></i>
                </label>
                <select class="form-select"
                        id="merchant_id"
                        name="merchant_id"
                        data-payment-link-merchant
                        data-merchants="{{ $merchantsByIdJson }}">
                    <option value="">{{ __('Personal / General Payment Link') }}</option>
                    @foreach($merchants as $merchant)
                        @php
                            $merchantCurrencyCodes = $merchant->supportedCurrencies->isNotEmpty()
                                ? $merchant->supportedCurrencies->pluck('code')->implode(', ')
                                : ($merchant->currency?->code ?? '-');
                        @endphp
                        <option value="{{ $merchant->id }}"
                                @selected((int) $values['merchant_id'] === (int) $merchant->id)>
                            {{ __('Merchant Shop: :name (:currencies)', [
                                'name'     => $merchant->business_name,
                                'currencies' => $merchantCurrencyCodes,
                            ]) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="alert alert-light border d-none align-items-center gap-3 p-2 mb-0"
                 id="paymentLinkMerchantPreview"
                 data-payment-link-merchant-preview>
                <img src="" alt="" class="rounded payment-link-merchant-preview-logo" data-merchant-preview-logo loading="lazy">
                <div>
                    <div class="fw-bold" data-merchant-preview-name></div>
                    <small class="text-muted">
                        {{ __('Currencies:') }} <span data-merchant-preview-currency></span>
                        @php($feeLabel = __('Fee'))
                        &middot; {{ $feeLabel }}: <span data-merchant-preview-fee></span>%
                    </small>
                </div>
            </div>
        </div>
    @endif

    <div class="col-md-8">
        <div class="mb-2 single-input-inner style-border">
            <label for="title" class="form-label">{{ __('Title') }}</label>
            <input type="text"
                   class="form-control @error('title') is-invalid @enderror"
                   id="title"
                   name="title"
                   value="{{ $values['title'] }}"
                   required
                   placeholder="{{ __('e.g. Invoice #1042') }}">
            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>

    <div class="col-md-4">
        <div class="mb-2 single-input-inner style-border">
            <label for="currency_id" class="form-label">
                {{ __('Currency') }}
                <i data-bs-toggle="tooltip" data-bs-placement="top"
                   title="{{ __('Currency comes from your active wallet list. When a merchant shop is selected, choose one of that shop\'s supported currencies.') }}"
                   class="fa-solid fa-circle-info ms-1"></i>
            </label>
            <select class="form-select @error('currency_id') is-invalid @enderror"
                    id="currency_id"
                    name="currency_id"
                    data-payment-link-currency>
                <option value="" disabled {{ $values['currency_id'] ? '' : 'selected' }}>{{ __('Select Currency') }}</option>
                @foreach($currencies as $currency)
                    <option value="{{ $currency->id }}" @selected((int) $values['currency_id'] === (int) $currency->id)>
                        {{ $currency->name }} ({{ $currency->code }})
                    </option>
                @endforeach
            </select>
            @error('currency_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>

    <div class="col-12">
        <div class="mb-2">
            <label for="description" class="form-label">{{ __('Description') }}</label>
            <textarea class="form-control h-25 @error('description') is-invalid @enderror"
                      id="description"
                      name="description"
                      placeholder="{{ __('Optional details payers see at checkout') }}">{{ $values['description'] }}</textarea>
            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>

    <div class="col-12">
        <div class="text-muted small fw-500 border-left-5 rounded p-2 mb-2 bg-light">
            {{ __('Set a fixed amount, OR leave it blank to accept any amount within an optional min / max range.') }}
        </div>
    </div>

    <div class="col-md-4">
        <div class="mb-2 single-input-inner style-border">
            <label for="amount" class="form-label">{{ __('Fixed Amount') }}</label>
            <input type="number"
                   step="0.01"
                   min="0.01"
                   class="form-control @error('amount') is-invalid @enderror"
                   id="amount"
                   name="amount"
                   value="{{ $values['amount'] }}"
                   placeholder="{{ __('e.g. 99.00') }}">
            @error('amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>

    <div class="col-md-4">
        <div class="mb-2 single-input-inner style-border">
            <label for="min_amount" class="form-label">{{ __('Min Amount') }}</label>
            <input type="number"
                   step="0.01"
                   min="0.01"
                   class="form-control @error('min_amount') is-invalid @enderror"
                   id="min_amount"
                   name="min_amount"
                   value="{{ $values['min_amount'] }}"
                   placeholder="{{ __('Optional') }}">
            @error('min_amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>

    <div class="col-md-4">
        <div class="mb-2 single-input-inner style-border">
            <label for="max_amount" class="form-label">{{ __('Max Amount') }}</label>
            <input type="number"
                   step="0.01"
                   min="0.01"
                   class="form-control @error('max_amount') is-invalid @enderror"
                   id="max_amount"
                   name="max_amount"
                   value="{{ $values['max_amount'] }}"
                   placeholder="{{ __('Optional') }}">
            @error('max_amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>

    <div class="col-md-6">
        <div class="mb-2 single-input-inner style-border">
            <label for="expires_at" class="form-label">{{ __('Expires At') }}</label>
            <input type="datetime-local"
                   class="form-control @error('expires_at') is-invalid @enderror"
                   id="expires_at"
                   name="expires_at"
                   value="{{ $values['expires_at'] }}">
            @error('expires_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>

    <div class="col-md-6">
        <div class="mb-2 single-input-inner style-border">
            <label for="max_payments" class="form-label">{{ __('Max Payments') }}</label>
            <input type="number"
                   step="1"
                   min="1"
                   class="form-control @error('max_payments') is-invalid @enderror"
                   id="max_payments"
                   name="max_payments"
                   value="{{ $values['max_payments'] }}"
                   placeholder="{{ __('Unlimited if blank') }}">
            @error('max_payments')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
</div>
