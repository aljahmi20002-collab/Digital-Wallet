<div class="modal fade vc-modal" id="requestVirtualCardModal" tabindex="-1" aria-labelledby="requestVirtualCardModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            @if(!$wallets->isEmpty())
                <form action="{{ route('user.virtual-card.request.store') }}" method="post" autocomplete="off" data-vc-issue-form>
                    @csrf

                    <div class="modal-header">
                        <div class="vc-modal__icon"><i class="fa-solid fa-plus"></i></div>
                        <div class="vc-modal__head-text">
                            <div class="vc-modal__eyebrow">{{ __('Issue New Card') }}</div>
                            <div class="vc-modal__title" id="requestVirtualCardModalLabel">{{ __('Create a virtual card') }}</div>
                            <div class="vc-modal__subtitle">
                                {{ __('Submit a request — admins approve and the chosen provider issues the card.') }}
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    {{-- Stepper --}}
                    <div class="vc-stepper" data-vc-issue-stepper>
                        <div class="vc-stepper__item is-current" data-step="1">
                            <span class="vc-stepper__dot">1</span>
                            <span class="vc-stepper__label">{{ __('Cardholder & Network') }}</span>
                        </div>
                        <div class="vc-stepper__line"></div>
                        <div class="vc-stepper__item" data-step="2">
                            <span class="vc-stepper__dot">2</span>
                            <span class="vc-stepper__label">{{ __('Wallet & Initial Load') }}</span>
                        </div>
                        <div class="vc-stepper__line"></div>
                        <div class="vc-stepper__item" data-step="3">
                            <span class="vc-stepper__dot">3</span>
                            <span class="vc-stepper__label">{{ __('Review & Confirm') }}</span>
                        </div>
                    </div>

                    <div class="modal-body">

                        {{-- STEP 1 — Cardholder + Network --}}
                        <div data-vc-issue-step="1">
                            <div class="vc-field">
                                <div class="vc-field__label"><span>{{ __('Select Cardholder') }}</span></div>
                                <select name="cardholder_id" id="cardholder_id" class="vc-select" required aria-required="true">
                                    <option value="" selected disabled>{{ __('Choose Cardholder') }}</option>
                                    @foreach($cardholders as $cardholder)
                                        <option value="{{ $cardholder->id }}">
                                            @if($cardholder->card_type->isBusiness() && $cardholder->business)
                                                {{ $cardholder->business->business_name }} ({{ $cardholder->business->contact_email }})
                                            @else
                                                {{ $cardholder->full_name }} ({{ $cardholder->email }})
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                <div class="vc-field__help">
                                    {{ __('Select an approved cardholder profile to request a card.') }}
                                </div>
                            </div>

                            <div class="vc-field">
                                <div class="vc-field__label"><span>{{ __('Network') }}</span></div>
                                <div class="vc-chips" data-vc-network-chips>
                                    @foreach(\App\Enums\VirtualCard\VirtualCardNetwork::cases() as $type)
                                        <button type="button" class="vc-chips__item" data-vc-network="{{ $type->value }}">
                                            {{ $type->label() }}
                                        </button>
                                    @endforeach
                                </div>
                                <input type="hidden" name="network" id="network" required>
                            </div>
                        </div>

                        {{-- STEP 2 — Wallet + Initial Load + MiniCard preview --}}
                        <div data-vc-issue-step="2" class="vc-step--hidden">
                            <div class="vc-issue-grid">
                                <div>
                                    <div class="vc-field">
                                        <div class="vc-field__label"><span>{{ __('Select Wallet') }}</span></div>
                                        <div class="vc-field__select-wrap">
                                            <select name="wallet_id" id="wallet_id" class="vc-select" required aria-required="true" disabled>
                                                <option value="" selected disabled>{{ __('Select network first…') }}</option>
                                            </select>
                                            <span id="wallet-loading" class="vc-field__select-spinner d-none">
                                                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                            </span>
                                        </div>
                                        <div class="vc-field__help">
                                            {{ __('Selected wallet currency becomes the virtual card currency.') }}
                                        </div>
                                    </div>

                                    <div class="vc-field">
                                        <div class="vc-field__label">
                                            <span>{{ __('Initial Load Amount') }} <span class="text-danger">*</span></span>
                                            <span class="vc-field__hint">{{ __('Required') }}</span>
                                        </div>
                                        <div class="vc-input-group">
                                            <input type="text"
                                                   class="vc-input"
                                                   id="initial_load_amount"
                                                   name="initial_load_amount"
                                                   oninput="this.value = validateDouble(this.value)"
                                                   placeholder="0.00"
                                                   required
                                                   value="{{ old('initial_load_amount') }}">
                                            <span class="vc-input-group__suffix" id="initial-load-currency">{{ siteCurrency() }}</span>
                                        </div>
                                        <div class="vc-field__help">
                                            {{ __('Funds the new card. Every provider needs a non-zero starting balance.') }}
                                        </div>
                                    </div>

                                    {{-- Card design picker — drives the live preview's data-theme --}}
                                    <div class="vc-field">
                                        <div class="vc-field__label"><span>{{ __('Card Design') }}</span></div>
                                        <div class="vc-design-picker" data-vc-design-picker role="radiogroup" aria-label="{{ __('Card design') }}">
                                            @foreach([
                                                'midnight' => __('Midnight'),
                                                'ocean'    => __('Ocean'),
                                                'graphite' => __('Graphite'),
                                                'emerald'  => __('Emerald'),
                                                'violet'   => __('Violet'),
                                            ] as $themeKey => $themeLabel)
                                                <button type="button"
                                                        class="vc-design-swatch {{ ($loop->first ? 'is-active' : '') }}"
                                                        data-vc-design="{{ $themeKey }}"
                                                        data-theme="{{ $themeKey }}"
                                                        aria-label="{{ $themeLabel }}"
                                                        aria-pressed="{{ $loop->first ? 'true' : 'false' }}"
                                                        title="{{ $themeLabel }}"></button>
                                            @endforeach
                                        </div>
                                        <input type="hidden" name="theme" id="theme_input" value="midnight">
                                    </div>
                                </div>

                                <div>
                                    <div class="vc-issue-eyebrow">{{ __('Live Preview') }}</div>
                                    <div class="vc-mini-preview" data-theme="midnight" data-vc-design-target>
                                        <div class="vc-mini-preview__inner">
                                            <div class="vc-mini-preview__head">
                                                <div>
                                                    <div class="vc-mini-preview__brand-name">Digikash</div>
                                                    <div class="vc-mini-preview__sub">
                                                        VIRTUAL · <span data-vc-preview-network>VISA</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div>
                                                <div class="vc-mini-preview__pan">•••• •••• •••• ••••</div>
                                                <div class="vc-mini-preview__row">
                                                    <div class="vc-mini-preview__holder">{{ __('NEW CARD') }}</div>
                                                    <div class="vc-mini-preview__exp">04 / 30</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="vc-issue-note">
                                        {{ __('The provider issues this card after admin approval. You can change limits and controls later.') }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- STEP 3 — Review + fee preview --}}
                        <div data-vc-issue-step="3" class="vc-step--hidden">
                            <div class="vc-issue-section-title">
                                {{ __('Card Issuing Fee') }}
                            </div>
                            <div class="vc-summary">
                                <div class="vc-summary__row">
                                    <span class="label">{{ __('Fixed Issuance Fee') }}</span>
                                    <span class="value" id="fee-fixed-text">—</span>
                                </div>
                                <div class="vc-summary__row">
                                    <span class="label">{{ __('Surcharge on Initial Load') }}</span>
                                    <span class="value" id="fee-surcharge-text">—</span>
                                </div>
                                <div class="vc-summary__divider"></div>
                                <div class="vc-summary__total">
                                    <span class="label">{{ __('Est. Total Issuance Cost') }}</span>
                                    <span class="value" id="fee-total-text">—</span>
                                </div>
                            </div>

                            <div class="vc-issue-info">
                                <i class="fa-solid fa-circle-info"></i>
                                <span>
                                    @if($reqData['min_issue_fee'] == $reqData['max_issue_fee'])
                                        {{ __('One-time fee :fee. Some providers add a small percentage on the initial load amount.', ['fee' => siteCurrency('symbol') . number_format($reqData['min_issue_fee'], 2)]) }}
                                    @else
                                        {{ __('One-time fee from :min to :max depending on provider. Some providers add a small percentage on the initial load amount.', [
                                            'min' => siteCurrency('symbol') . number_format($reqData['min_issue_fee'],2),
                                            'max' => siteCurrency('symbol') . number_format($reqData['max_issue_fee'],2),
                                        ]) }}
                                    @endif
                                </span>
                            </div>

                            <ul class="vc-issue-list">
                                <li>{{ __('Admins review the request and notify you when ready.') }}</li>
                                <li>{{ __('Each card is linked to the selected wallet and cardholder.') }}</li>
                                <li>{{ __('Some providers require a minimum balance to issue.') }}</li>
                            </ul>
                        </div>

                    </div>

                    <div class="modal-footer">
                        <button type="button" class="vc-btn vc-btn--ghost" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="button" class="vc-btn vc-btn--secondary d-none" data-vc-issue-back>{{ __('Back') }}</button>
                        <button type="button" class="vc-btn vc-btn--primary" data-vc-issue-next>{{ __('Continue') }}</button>
                        <button type="submit" class="vc-btn vc-btn--primary d-none" data-vc-issue-submit>
                            <i class="fa-solid fa-check"></i> {{ __('Submit Request') }}
                        </button>
                    </div>
                </form>
            @else
                <div class="modal-header">
                    <div class="vc-modal__icon"><i class="fa-solid fa-plus"></i></div>
                    <div class="vc-modal__head-text">
                        <div class="vc-modal__title">{{ __('Request New Virtual Card') }}</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger mb-0">
                        <p class="mb-0">{{ __("You don't have any wallet to request a virtual card.") }}</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
    <script>
        "use strict";
        $(function () {

            let defaultCurrency = "{{ siteCurrency() }}";
            const FEE_CFG = {
                minFixed: Number({{ (float)($reqData['min_issue_fee']     ?? 0) }}),
                maxFixed: Number({{ (float)($reqData['max_issue_fee']     ?? 0) }}),
                minPct:   Number({{ (float)($reqData['min_issue_fee_pct'] ?? 0) }}),
                maxPct:   Number({{ (float)($reqData['max_issue_fee_pct'] ?? 0) }})
            };
            let currentCurrencyCode = defaultCurrency;

            function parseAmount(val){
                if (typeof val !== 'string') return 0;
                val = val.replace(/[^0-9.]/g, '');
                const num = parseFloat(val);
                return isNaN(num) ? 0 : Math.max(0, num);
            }
            const fmt = (n) => n.toFixed(2);
            function asRange(minVal, maxVal){
                if (minVal === maxVal) return fmt(minVal) + ' ' + currentCurrencyCode;
                return fmt(minVal) + '–' + fmt(maxVal) + ' ' + currentCurrencyCode;
            }
            function updateFeePreview(){
                const amt = parseAmount($('#initial_load_amount').val());
                const fixedMin = Math.max(0, FEE_CFG.minFixed || 0);
                const fixedMax = Math.max(fixedMin, FEE_CFG.maxFixed || 0);
                const pctMin   = Math.max(0, FEE_CFG.minPct || 0);
                const pctMax   = Math.max(pctMin, FEE_CFG.maxPct || 0);
                const surMin   = amt * (pctMin/100);
                const surMax   = amt * (pctMax/100);
                $('#fee-fixed-text').text(asRange(fixedMin, fixedMax));
                $('#fee-surcharge-text').text(asRange(surMin, surMax));
                $('#fee-total-text').text(asRange(fixedMin + surMin, fixedMax + surMax));
            }

            // Network chips → write into hidden field + drive preview
            $('[data-vc-network-chips]').on('click', '[data-vc-network]', function () {
                $('[data-vc-network-chips] [data-vc-network]').removeClass('is-active');
                $(this).addClass('is-active');
                const value = $(this).data('vc-network');
                $('#network').val(value).trigger('change');
                $('[data-vc-preview-network]').text(String(value).toUpperCase());
            });

            // Card design swatches → swap the preview's theme + write hidden field
            $('[data-vc-design-picker]').on('click', '[data-vc-design]', function () {
                const $picker = $('[data-vc-design-picker]');
                $picker.find('[data-vc-design]').removeClass('is-active').attr('aria-pressed', 'false');
                $(this).addClass('is-active').attr('aria-pressed', 'true');
                const theme = $(this).data('vc-design');
                $('#theme_input').val(theme);
                $('[data-vc-design-target]').attr('data-theme', theme);
            });

            // Existing wallet AJAX
            $('#network').on('change', function () {
                var network = $(this).val();
                $('#wallet_id').prop('disabled', true).html('<option>Loading…</option>');
                $('#wallet-loading').removeClass('d-none');
                if (network) {
                    $.get('{{ route("user.virtual-card.request.eligible-wallets") }}', {network: network}, function (wallets) {
                        if (wallets.length) {
                            let html = `<option value="" selected disabled>{{ __('Choose a wallet') }}</option>`;
                            wallets.forEach(function (wallet) {
                                html += `<option value="${wallet.id}" data-code="${wallet.code}" data-symbol="${wallet.symbol}">${wallet.text}</option>`;
                            });
                            $('#wallet_id').html(html).prop('disabled', false);
                        } else {
                            $('#wallet_id').html('<option value="">{{ __("No eligible wallets found for this network.") }}</option>').prop('disabled', true);
                        }
                        $('#wallet-loading').addClass('d-none');
                        currentCurrencyCode = defaultCurrency;
                        $('#initial-load-currency').text(defaultCurrency);
                        updateFeePreview();
                    }).fail(function () {
                        $('#wallet_id').html('<option value="">{{ __("Error loading wallets.") }}</option>').prop('disabled', true);
                        $('#wallet-loading').addClass('d-none');
                        updateFeePreview();
                    });
                }
            });

            $('#wallet_id').on('change', function () {
                const $opt   = $(this).find(':selected');
                const code   = $opt.data('code') || '';
                currentCurrencyCode = code || defaultCurrency;
                $('#initial-load-currency').text(code || defaultCurrency);
                updateFeePreview();
            });

            $('#initial_load_amount').on('input', updateFeePreview);
            updateFeePreview();

            // ---------- Stepper navigation ----------
            const $modal = $('#requestVirtualCardModal');
            let step = 1;

            function gotoStep(n) {
                step = n;
                $modal.find('[data-vc-issue-step]').addClass('vc-step--hidden');
                $modal.find('[data-vc-issue-step="' + n + '"]').removeClass('vc-step--hidden');

                $modal.find('[data-vc-issue-stepper] [data-step]').each(function () {
                    const idx = Number($(this).data('step'));
                    $(this).removeClass('is-current is-done');
                    if (idx < n) $(this).addClass('is-done');
                    else if (idx === n) $(this).addClass('is-current');
                });

                $modal.find('[data-vc-issue-back]').toggleClass('d-none', n <= 1);
                $modal.find('[data-vc-issue-next]').toggleClass('d-none', n >= 3);
                $modal.find('[data-vc-issue-submit]').toggleClass('d-none', n !== 3);
            }

            function step1Valid() {
                return $('#cardholder_id').val() && $('#network').val();
            }
            function step2Valid() {
                return $('#wallet_id').val() && !$('#wallet_id').prop('disabled');
            }

            $modal.on('click', '[data-vc-issue-next]', function () {
                if (step === 1 && !step1Valid()) {
                    notifyEvs && notifyEvs('error', '{{ __("Please pick a cardholder and network.") }}');
                    return;
                }
                if (step === 2 && !step2Valid()) {
                    notifyEvs && notifyEvs('error', '{{ __("Please pick a wallet.") }}');
                    return;
                }
                gotoStep(Math.min(3, step + 1));
            });
            $modal.on('click', '[data-vc-issue-back]', function () { gotoStep(Math.max(1, step - 1)); });

            // Reset on open
            $modal.on('show.bs.modal', function () { gotoStep(1); });
        });
    </script>
@endpush
