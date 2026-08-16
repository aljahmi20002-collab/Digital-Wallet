@php
    $formId = $formId ?? 'agent_operation';
    $requiresCustomerOtp = $requiresCustomerOtp ?? false;
    $agentOptions = collect($agents);
    $singleAgent = $agentOptions->count() === 1 ? $agentOptions->first() : null;
    $walletOptions = collect($wallets);

    if ($singleAgent) {
        $singleAgent->loadMissing('supportedCurrencies');
        $supportedCurrencyIds = $singleAgent->supportedCurrencyIds();
        $walletOptions = $walletOptions
            ->whereIn('currency_id', $supportedCurrencyIds)
            ->values();
    }
@endphp

<div class="row g-3">
    <div class="col-12">
        @if($singleAgent)
            <label class="form-label" for="{{ $formId }}_agent_id">{{ __('Agent Account') }}</label>
            <input type="hidden" id="{{ $formId }}_agent_id" name="agent_id" value="{{ $singleAgent->id }}">
            <div class="agent-field-summary">
                <span class="agent-field-summary__icon"><x-icon name="sidebar-agent" width="18" height="18" class="agent-field-summary__svg"/></span>
                <div>
                    <strong>{{ $singleAgent->agent_name }}</strong>
                    <span>
                        {{ $singleAgent->agent_code }}
                        &middot;
                        {{ $singleAgent->supportedCurrencies->pluck('code')->implode(', ') ?: $singleAgent->currency?->code }}
                        &middot;
                        {{ number_format((float) $singleAgent->commission, 2) }}%
                    </span>
                </div>
            </div>
        @else
            <label class="form-label" for="{{ $formId }}_agent_id">{{ __('Agent Account') }}</label>
            <select class="form-select @error('agent_id') is-invalid @enderror" id="{{ $formId }}_agent_id" name="agent_id" required>
                <option value="" disabled selected>{{ __('Select agent account') }}</option>
            @foreach($agentOptions as $agent)
                <option value="{{ $agent->id }}" @selected(old('agent_id') == $agent->id)>
                    {{ $agent->agent_name }} &middot; {{ $agent->supportedCurrencies->pluck('code')->implode(', ') ?: $agent->currency?->code }} &middot; {{ number_format((float) $agent->commission, 2) }}%
                </option>
            @endforeach
            </select>
            @error('agent_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        @endif
    </div>

    <div class="col-12">
        <label class="form-label" for="{{ $formId }}_wallet_id">{{ __('Agent Wallet') }}</label>
        <select class="form-select @error('wallet_id') is-invalid @enderror" id="{{ $formId }}_wallet_id" name="wallet_id" required @disabled($walletOptions->isEmpty())>
            <option value="" disabled selected>{{ __('Select currency wallet') }}</option>
            @foreach($walletOptions as $wallet)
                <option value="{{ $wallet->id }}" @selected(old('wallet_id') == $wallet->id)>
                    {{ $wallet->currency?->code }} &middot; {{ $wallet->uuid }} &middot; {{ number_format((float) $wallet->balance, (int) setting('site_decimal', 2)) }}
                </option>
            @endforeach
        </select>
        @error('wallet_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror

        @if($walletOptions->isEmpty())
            <div class="agent-form-alert mt-2">
                <i class="fa-solid fa-wallet"></i>
                <span>{{ __('No active wallet matches this agent account currencies.') }}</span>
            </div>
        @endif
    </div>

    <div class="col-12">
        <label class="form-label" for="{{ $formId }}_customer">{{ __('Customer Email or Wallet ID') }}</label>
        <input
            type="text"
            class="form-control @error('customer') is-invalid @enderror"
            id="{{ $formId }}_customer"
            name="customer"
            value="{{ old('customer') }}"
            placeholder="{{ __('customer@example.com or wallet ID') }}"
            autocomplete="off"
            required
        >
        @error('customer')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12 col-md-6">
        <label class="form-label" for="{{ $formId }}_amount">{{ __('Amount') }}</label>
        <input
            type="text"
            class="form-control @error('amount') is-invalid @enderror"
            id="{{ $formId }}_amount"
            name="amount"
            value="{{ old('amount') }}"
            inputmode="decimal"
            oninput="this.value = validateDouble(this.value)"
            required
        >
        @error('amount')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12 col-md-6">
        <label class="form-label" for="{{ $formId }}_note">{{ __('Counter Note') }}</label>
        <input
            type="text"
            class="form-control @error('note') is-invalid @enderror"
            id="{{ $formId }}_note"
            name="note"
            value="{{ old('note') }}"
            maxlength="255"
        >
        @error('note')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    @if($requiresCustomerOtp)
        <div class="col-12">
            <label class="form-label" for="{{ $formId }}_customer_otp">{{ __('Customer OTP') }}</label>
            <input
                type="text"
                class="form-control @error('customer_otp') is-invalid @enderror"
                id="{{ $formId }}_customer_otp"
                name="customer_otp"
                value="{{ old('customer_otp') }}"
                inputmode="numeric"
                maxlength="6"
                autocomplete="one-time-code"
                placeholder="{{ __('Enter customer OTP') }}"
                required
            >
            <small class="agent-field-help">{{ __('Send an OTP to the customer first, then enter that code here to complete cash-out.') }}</small>
            @error('customer_otp')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    @endif
</div>
