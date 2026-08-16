@php($availableCurrenciesForWallet = auth()->user()->availableCurrenciesForCreateWallet())

<div class="modal fade wallet-create-modal" id="addWalletModal" tabindex="-1" aria-labelledby="addWalletModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered wallet-create-modal__dialog">
        <div class="modal-content wallet-create-modal__content">
            <div class="wallet-create-modal__header">
                <div class="wallet-create-modal__title">
                    <span class="wallet-create-modal__mark" aria-hidden="true">
                        <i class="fas fa-wallet"></i>
                    </span>
                    <div>
                        <span>{{ __('New Wallet') }}</span>
                        <h6 class="modal-title" id="addWalletModalLabel">{{ __('Create Wallet') }}</h6>
                    </div>
                </div>
                <button type="button" class="btn-close wallet-create-modal__close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
            </div>
            <div class="modal-body wallet-create-modal__body">
                @if($availableCurrenciesForWallet->isNotEmpty())
                    <form action="{{ route('user.wallet.create') }}" method="post" class="wallet-create-modal__form">
                        @csrf
                        <div class="wallet-create-modal__field">
                            <label for="currency_id" class="form-label">{{ __('Currency') }}</label>
                            <div class="wallet-create-modal__select-wrap">
                                <i class="fas fa-coins" aria-hidden="true"></i>
                                <select name="currency_id" id="currency_id" class="form-select" required
                                        data-wallet-currency-select
                                        data-currency-info-url="{{ route('user.wallet.currency-info', ['currency_id' => '__currency_id__']) }}"
                                        data-loading-text="@lang('Loading...')">
                                    <option value="" selected disabled>{{ __('Select Currency') }}</option>
                                    @foreach($availableCurrenciesForWallet as $currency)
                                        <option value="{{ $currency->id }}">{{ $currency->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="currency-preview wallet-create-modal__preview" data-wallet-currency-preview></div>

                        <div class="wallet-create-modal__actions">
                            <button type="button" class="wallet-create-modal__dismiss" data-bs-dismiss="modal">
                                <x-icon name="x" height="18" width="18"/> {{ __('Close') }}
                            </button>
                            <button type="submit" class="wallet-create-modal__submit">
                                <x-icon name="check" height="18" width="18"/> {{ __('Create Wallet') }}
                            </button>
                        </div>
                    </form>
                @else
                    <x-user-not-found
                        class="wallet-create-modal__empty"
                        :title="__('No currency available')"
                        :message="__('You already have wallets for every currency currently available to you.')"
                        icon="fa-wallet"
                    />
                @endif
            </div>
        </div>
    </div>
</div>
