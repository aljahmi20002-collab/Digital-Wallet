@php use App\Constants\CurrencyRole; @endphp
<div class="wallet-currency-preview-card">
    <div class="wallet-currency-preview-card__row">
        <span class="wallet-currency-preview-card__label">
            <i class="fas fa-coins" aria-hidden="true"></i>
            {{ __('Type') }}
        </span>
        <strong>{{ strtoupper((string) $currency->type) }}</strong>
    </div>

    <div class="wallet-currency-preview-card__row">
        <span class="wallet-currency-preview-card__label">
            <i class="fas fa-code" aria-hidden="true"></i>
            {{ __('Code') }}
        </span>
        <strong>{{ $currency->code }}</strong>
    </div>

    <div class="wallet-currency-preview-card__row">
        <span class="wallet-currency-preview-card__label">
            <i class="fas fa-exchange-alt" aria-hidden="true"></i>
            {{ __('Rate') }}
            @if($currency->rate_live === true)
                <span class="wallet-currency-preview-card__live">{{ __('Live') }}</span>
            @endif
        </span>
        <strong>1 {{ siteCurrency() }} = {{ $currency->exchange_rate }} {{ $currency->code }}</strong>
    </div>

    <div class="wallet-currency-preview-card__row wallet-currency-preview-card__row--roles">
        <span class="wallet-currency-preview-card__label">
            <i class="fas fa-user-tag" aria-hidden="true"></i>
            {{ __('Role') }}
        </span>
        <span class="wallet-currency-preview-card__roles">
            @foreach($currency->activeRoles as $role)
                <span class="wallet-currency-preview-card__role bg-{{ CurrencyRole::getBadgesColor($role->role_name) }}">
                    {{ str($role->role_name)->replace('_', ' ')->headline() }}
                </span>
            @endforeach
        </span>
    </div>
</div>
