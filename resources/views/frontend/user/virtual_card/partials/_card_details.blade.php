@php
    /** @var array $cardDetails */
@endphp
<div class="vc-cd">
    <div class="vc-cd__row">
        <div class="vc-cd__label">{{ __('Cardholder') }}</div>
        <div class="vc-cd__value">{{ $cardDetails['card_holder_name'] ?? '—' }}</div>
    </div>

    <div class="vc-cd__row">
        <div class="vc-cd__label">{{ __('Card Number') }}</div>
        <div class="vc-cd__value mono" style="font-size:15px;letter-spacing:1px;">
            {{ $cardDetails['card_number'] ?? '**** **** **** ****' }}
        </div>
    </div>

    <div class="vc-cd__grid">
        <div>
            <div class="vc-cd__label">{{ __('Expiry') }}</div>
            <div class="vc-cd__value mono">{{ $cardDetails['expiry'] ?? '--/--' }}</div>
        </div>
        <div>
            <div class="vc-cd__label">{{ __('CVV') }}</div>
            <div class="vc-cd__value mono">{{ $cardDetails['cvv'] ?? '***' }}</div>
        </div>
        <div>
            <div class="vc-cd__label">{{ __('Brand') }}</div>
            <div class="vc-cd__value">{{ $cardDetails['card_brand'] ?? '—' }}</div>
        </div>
        <div>
            <div class="vc-cd__label">{{ __('Status') }}</div>
            <div class="vc-cd__value">
                <span class="vc-pill vc-pill--green"><span class="vc-pill__dot"></span>{{ Str::title($cardDetails['card_status'] ?? 'active') }}</span>
            </div>
        </div>
    </div>

    <div class="vc-cd__row">
        <div class="vc-cd__label">{{ __('Card Balance') }}</div>
        <div class="vc-cd__value">{{ '$' . number_format((float) ($cardDetails['balance'] ?? 0), 2) }}</div>
    </div>

    @if(!empty($cardDetails['billing_street']))
        <div class="vc-cd__row">
            <div class="vc-cd__label">{{ __('Billing Address') }}</div>
            <div class="vc-cd__value">
                {{ $cardDetails['billing_street'] }}<br>
                {{ $cardDetails['billing_city'] ?? '' }}{{ !empty($cardDetails['billing_country']) ? ', ' . $cardDetails['billing_country'] : '' }}
                {{ !empty($cardDetails['billing_zip_code']) ? ' · ' . $cardDetails['billing_zip_code'] : '' }}
            </div>
        </div>
    @endif

    @if(!empty($cardDetails['customer_email']))
        <div class="vc-cd__row">
            <div class="vc-cd__label">{{ __('Email') }}</div>
            <div class="vc-cd__value">{{ $cardDetails['customer_email'] }}</div>
        </div>
    @endif
</div>

<style>
    .vc-cd__row { padding: 10px 0; border-bottom: 1px solid var(--vc-line-2); }
    .vc-cd__row:last-child { border-bottom: none; }
    .vc-cd__label { font-size: 11px; font-weight: 700; letter-spacing: .8px; color: var(--vc-muted); text-transform: uppercase; }
    .vc-cd__value { font-size: 14px; font-weight: 600; color: var(--vc-ink); margin-top: 4px; }
    .vc-cd__grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; padding: 10px 0; border-bottom: 1px solid var(--vc-line-2); }
    .vc-cd .mono { font-family: 'JetBrains Mono', ui-monospace, monospace; }
</style>
