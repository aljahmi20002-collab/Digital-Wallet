@extends('backend.p2p.layout')

@section('title', __('Marketplace Settings'))

@section('p2p_title')
    {{ __('Marketplace Settings') }}
@endsection

@section('p2p_icon', 'cil-settings')

@php
    $allowedSelected = old('allowed_countries', $allowedSelected ?? []);
    $blockedSelected = old('blocked_countries', $blockedSelected ?? []);
    $makerFee        = old('maker_fee_pct', $settings->maker_fee_pct);
    $takerFee        = old('taker_fee_pct', $settings->taker_fee_pct);
    $orderExpiry     = old('order_expiry_minutes', $settings->order_expiry_minutes);
    $disputeWindow   = old('dispute_window_minutes', $settings->dispute_window_minutes);
    $minAmount       = old('min_amount', $settings->min_amount);
    $maxAmount       = old('max_amount', $settings->max_amount);
    $p2pEnabled      = (bool) ($settings->enabled ?? false);
    $currencyCode    = siteCurrency('code') ?? 'BDT';
    $allowedCount    = count($allowedSelected);
    $blockedCount    = count($blockedSelected);
    $updatedAgo      = $settings->updated_at ? $settings->updated_at->diffForHumans() : __('never');
@endphp

@section('p2p_action')
    <a href="{{ route('admin.p2p.index') }}" class="fb-btn fb-btn--ghost fb-btn--sm">
        <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
        <span>@lang('Back to dashboard')</span>
    </a>
@endsection

@section('p2p_content')
    <div class="p2p-refresh">

        {{-- ── Status hero (read-only) ── --}}
        <section class="settings-status">
            <div>
                <span class="p2p-market-eyebrow">@lang('Marketplace status')</span>
                <div class="settings-status__title">
                    <span>{{ $p2pEnabled ? __('Open · Accepting trades') : __('Paused') }}</span>
                    <span class="p2p-market-chip {{ $p2pEnabled ? 'p2p-market-chip--success' : 'p2p-market-chip--danger' }}">
                        <span class="p2p-market-chip__dot"></span>
                        {{ $p2pEnabled ? __('Live') : __('Offline') }}
                    </span>
                </div>
                <p class="settings-status__sub">
                    @lang('Trading availability is controlled by the platform feature flag, not from this page. To toggle, use the')
                    <code>p2p_marketplace</code> @lang('feature in admin → Feature management.')
                </p>
            </div>
            <div class="settings-status__meta">
                <span><i class="fa-solid fa-clock-rotate-left me-1" aria-hidden="true"></i> @lang('Last updated') {{ $updatedAgo }}</span>
                <span><i class="fa-solid fa-coins me-1" aria-hidden="true"></i> @lang('Default currency') <b>{{ $currencyCode }}</b></span>
            </div>
        </section>

        {{-- ── Validation summary ── --}}
        @if($errors->any())
            <div class="settings-errors" role="alert">
                <span class="settings-errors__icon"><i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i></span>
                <div>
                    <h6>@lang(':n field(s) need attention', ['n' => $errors->count()])</h6>
                    <ul>
                        @foreach($errors->all() as $msg)
                            <li>{{ $msg }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.p2p.settings.update') }}" id="p2p-settings-form" data-settings-form>
            @csrf
            @method('PUT')

            <div class="settings-grid">

                {{-- ── Fees ── --}}
                <section class="settings-panel">
                    <div class="settings-panel__head">
                        <span class="settings-panel__icon settings-panel__icon--primary">
                            <i class="fa-solid fa-percent" aria-hidden="true"></i>
                        </span>
                        <div class="settings-panel__title">
                            <h6>@lang('Fees')</h6>
                            <span>@lang('Platform commission charged to each side of a trade')</span>
                        </div>
                    </div>

                    <div class="settings-fields">
                        <div class="settings-field">
                            <label for="maker_fee_pct">
                                @lang('Maker fee') <span class="req" aria-hidden="true">*</span>
                            </label>
                            <div class="settings-input-wrap">
                                <input type="number" step="0.0001" min="0" max="100" name="maker_fee_pct" id="maker_fee_pct"
                                       data-settings-fee="maker"
                                       class="@error('maker_fee_pct') is-invalid @enderror"
                                       value="{{ $makerFee }}"
                                       inputmode="decimal" required>
                                <span class="settings-input-wrap__suffix">%</span>
                            </div>
                            <span class="hint">@lang('Charged to the trader who created the offer (the seller).')</span>
                            @error('maker_fee_pct')<span class="error">{{ $message }}</span>@enderror
                        </div>

                        <div class="settings-field">
                            <label for="taker_fee_pct">
                                @lang('Taker fee') <span class="req" aria-hidden="true">*</span>
                            </label>
                            <div class="settings-input-wrap">
                                <input type="number" step="0.0001" min="0" max="100" name="taker_fee_pct" id="taker_fee_pct"
                                       data-settings-fee="taker"
                                       class="@error('taker_fee_pct') is-invalid @enderror"
                                       value="{{ $takerFee }}"
                                       inputmode="decimal" required>
                                <span class="settings-input-wrap__suffix">%</span>
                            </div>
                            <span class="hint">@lang('Charged to the trader who accepts the offer (the buyer).')</span>
                            @error('taker_fee_pct')<span class="error">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    <div class="fee-calc" data-fee-calc>
                        <span class="fee-calc__icon"><i class="fa-solid fa-calculator" aria-hidden="true"></i></span>
                        <span class="fee-calc__body">
                            @lang('On a trade of')
                            <span class="fb-mono">{{ $currencyCode }}</span>
                            <input type="number" min="1" step="1" value="10000" class="fee-calc__base"
                                   data-fee-calc-base aria-label="{{ __('Sample trade amount') }}">
                            <br>
                            @lang('Maker earns')
                            <b data-fee-calc-maker>0.00</b>
                            <span class="fb-mono">{{ $currencyCode }}</span>
                            · @lang('Taker pays')
                            <b data-fee-calc-taker>0.00</b>
                            <span class="fb-mono">{{ $currencyCode }}</span>
                            · @lang('Total platform revenue')
                            <b data-fee-calc-total>0.00</b>
                            <span class="fb-mono">{{ $currencyCode }}</span>
                        </span>
                    </div>
                </section>

                {{-- ── Timing ── --}}
                <section class="settings-panel">
                    <div class="settings-panel__head">
                        <span class="settings-panel__icon settings-panel__icon--warning">
                            <i class="fa-solid fa-clock" aria-hidden="true"></i>
                        </span>
                        <div class="settings-panel__title">
                            <h6>@lang('Time windows')</h6>
                            <span>@lang('How long traders have to pay and to dispute')</span>
                        </div>
                    </div>

                    <div class="settings-fields">
                        <div class="settings-field">
                            <label for="order_expiry_minutes">
                                @lang('Order expiry') <span class="req" aria-hidden="true">*</span>
                            </label>
                            <div class="settings-input-wrap">
                                <input type="number" min="5" max="2880" name="order_expiry_minutes" id="order_expiry_minutes"
                                       data-settings-expiry
                                       class="@error('order_expiry_minutes') is-invalid @enderror"
                                       value="{{ $orderExpiry }}"
                                       inputmode="numeric" required>
                                <span class="settings-input-wrap__suffix">@lang('min')</span>
                            </div>
                            <span class="hint">@lang('Buyer payment deadline · 5 to 2,880 minutes (48h)')</span>
                            @error('order_expiry_minutes')<span class="error">{{ $message }}</span>@enderror
                        </div>

                        <div class="settings-field">
                            <label for="dispute_window_minutes">
                                @lang('Dispute window') <span class="req" aria-hidden="true">*</span>
                            </label>
                            <div class="settings-input-wrap">
                                <input type="number" min="10" max="4320" name="dispute_window_minutes" id="dispute_window_minutes"
                                       data-settings-dispute
                                       class="@error('dispute_window_minutes') is-invalid @enderror"
                                       value="{{ $disputeWindow }}"
                                       inputmode="numeric" required>
                                <span class="settings-input-wrap__suffix">@lang('min')</span>
                            </div>
                            <span class="hint">@lang('Time to raise a dispute after payment · 10 to 4,320 min (72h)')</span>
                            @error('dispute_window_minutes')<span class="error">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    <div class="timing-preview" data-timing-preview>
                        <i class="fa-solid fa-route" aria-hidden="true"></i>
                        <span>@lang('Settlement timeline')</span>
                        <span class="timing-preview__bar" aria-hidden="true">
                            <span class="seg-pay"   data-timing-seg-pay     style="width: 25%;"></span>
                            <span class="seg-dispute" data-timing-seg-dispute style="left: 25%; width: 75%;"></span>
                        </span>
                        <span class="timing-preview__total">
                            <span data-timing-total>{{ (int) $orderExpiry + (int) $disputeWindow }}</span> @lang('min')
                        </span>
                    </div>
                </section>

                {{-- ── Limits ── --}}
                <section class="settings-panel">
                    <div class="settings-panel__head">
                        <span class="settings-panel__icon settings-panel__icon--success">
                            <i class="fa-solid fa-layer-group" aria-hidden="true"></i>
                        </span>
                        <div class="settings-panel__title">
                            <h6>@lang('Trade limits')</h6>
                            <span>@lang('Default min / max trade amount per offer')</span>
                        </div>
                    </div>

                    <div class="settings-fields">
                        <div class="settings-field">
                            <label for="min_amount">@lang('Minimum amount')</label>
                            <div class="settings-input-wrap">
                                <input type="number" step="0.00000001" min="0" name="min_amount" id="min_amount"
                                       class="@error('min_amount') is-invalid @enderror"
                                       value="{{ $minAmount }}"
                                       inputmode="decimal"
                                       placeholder="0.00">
                                <span class="settings-input-wrap__suffix">{{ $currencyCode }}</span>
                            </div>
                            <span class="hint">@lang('Smallest amount a trader can buy or sell in one order.')</span>
                            @error('min_amount')<span class="error">{{ $message }}</span>@enderror
                        </div>

                        <div class="settings-field">
                            <label for="max_amount">@lang('Maximum amount')</label>
                            <div class="settings-input-wrap">
                                <input type="number" step="0.00000001" min="0" name="max_amount" id="max_amount"
                                       class="@error('max_amount') is-invalid @enderror"
                                       value="{{ $maxAmount }}"
                                       inputmode="decimal"
                                       placeholder="{{ __('No cap') }}">
                                <span class="settings-input-wrap__suffix">{{ $currencyCode }}</span>
                            </div>
                            <span class="hint">@lang('Leave blank for no upper limit. Must exceed minimum.')</span>
                            @error('max_amount')<span class="error">{{ $message }}</span>@enderror
                        </div>
                    </div>
                </section>

                {{-- ── Geography ── --}}
                <section class="settings-panel settings-panel--wide">
                    <div class="settings-panel__head">
                        <span class="settings-panel__icon settings-panel__icon--info">
                            <i class="fa-solid fa-earth-americas" aria-hidden="true"></i>
                        </span>
                        <div class="settings-panel__title">
                            <h6>@lang('Country availability')</h6>
                            <span>@lang('Allow specific countries, block specific countries, or leave both empty to allow everyone')</span>
                        </div>
                    </div>

                    <div class="settings-fields">
                        {{-- Allowed countries --}}
                        <div class="settings-field">
                            <label for="allowed_countries">
                                <i class="fa-solid fa-circle-check" style="color: var(--color-success);" aria-hidden="true"></i>
                                @lang('Allowed countries')
                                <span class="fb-pill fb-pill--success ms-1" data-country-count="allowed">{{ $allowedCount }}</span>
                            </label>
                            <div class="country-picker" data-country-picker="allowed">
                                <div class="country-picker__chips" data-empty="{{ __('No countries selected · all are allowed') }}" data-country-chips="allowed"></div>
                                <div class="country-picker__search">
                                    <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                                    <input type="search" data-country-search="allowed" placeholder="{{ __('Search countries…') }}" aria-label="{{ __('Search allowed countries') }}">
                                </div>
                                <select name="allowed_countries[]" id="allowed_countries"
                                        class="country-picker__native @error('allowed_countries') is-invalid @enderror"
                                        multiple
                                        data-country-select="allowed"
                                        tabindex="-1"
                                        aria-hidden="true">
                                    @foreach($countryOptions as $country)
                                        <option value="{{ $country['code'] }}" {{ in_array($country['code'], $allowedSelected, true) ? 'selected' : '' }}>
                                            {{ $country['name'] }} ({{ $country['code'] }})
                                        </option>
                                    @endforeach
                                </select>
                                <ul class="country-picker__options" role="listbox" aria-multiselectable="true" aria-label="{{ __('Allowed countries') }}" data-country-options="allowed">
                                    @foreach($countryOptions as $country)
                                        @php($isSelected = in_array($country['code'], $allowedSelected, true))
                                        <li class="country-option {{ $isSelected ? 'is-selected' : '' }}"
                                            role="option"
                                            tabindex="0"
                                            aria-selected="{{ $isSelected ? 'true' : 'false' }}"
                                            data-country-value="{{ $country['code'] }}"
                                            data-country-search-text="{{ strtolower($country['name'].' '.$country['code']) }}">
                                            <span class="country-option__check"><i class="fa-solid fa-check" aria-hidden="true"></i></span>
                                            <span class="country-option__label">{{ $country['name'] }}</span>
                                            <span class="country-option__code">{{ $country['code'] }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                                <div class="country-picker__empty is-hidden" data-country-empty="allowed">
                                    @lang('No countries match your search')
                                </div>
                            </div>
                            @error('allowed_countries')<span class="error">{{ $message }}</span>@enderror
                            @error('allowed_countries.*')<span class="error">{{ $message }}</span>@enderror
                        </div>

                        {{-- Blocked countries --}}
                        <div class="settings-field">
                            <label for="blocked_countries">
                                <i class="fa-solid fa-ban" style="color: var(--color-danger);" aria-hidden="true"></i>
                                @lang('Blocked countries')
                                <span class="fb-pill fb-pill--danger ms-1" data-country-count="blocked">{{ $blockedCount }}</span>
                            </label>
                            <div class="country-picker" data-country-picker="blocked">
                                <div class="country-picker__chips" data-empty="{{ __('No countries blocked') }}" data-country-chips="blocked"></div>
                                <div class="country-picker__search">
                                    <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                                    <input type="search" data-country-search="blocked" placeholder="{{ __('Search countries…') }}" aria-label="{{ __('Search blocked countries') }}">
                                </div>
                                <select name="blocked_countries[]" id="blocked_countries"
                                        class="country-picker__native @error('blocked_countries') is-invalid @enderror"
                                        multiple
                                        data-country-select="blocked"
                                        tabindex="-1"
                                        aria-hidden="true">
                                    @foreach($countryOptions as $country)
                                        <option value="{{ $country['code'] }}" {{ in_array($country['code'], $blockedSelected, true) ? 'selected' : '' }}>
                                            {{ $country['name'] }} ({{ $country['code'] }})
                                        </option>
                                    @endforeach
                                </select>
                                <ul class="country-picker__options" role="listbox" aria-multiselectable="true" aria-label="{{ __('Blocked countries') }}" data-country-options="blocked">
                                    @foreach($countryOptions as $country)
                                        @php($isSelected = in_array($country['code'], $blockedSelected, true))
                                        <li class="country-option {{ $isSelected ? 'is-selected' : '' }}"
                                            role="option"
                                            tabindex="0"
                                            aria-selected="{{ $isSelected ? 'true' : 'false' }}"
                                            data-country-value="{{ $country['code'] }}"
                                            data-country-search-text="{{ strtolower($country['name'].' '.$country['code']) }}">
                                            <span class="country-option__check"><i class="fa-solid fa-check" aria-hidden="true"></i></span>
                                            <span class="country-option__label">{{ $country['name'] }}</span>
                                            <span class="country-option__code">{{ $country['code'] }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                                <div class="country-picker__empty is-hidden" data-country-empty="blocked">
                                    @lang('No countries match your search')
                                </div>
                            </div>
                            @error('blocked_countries')<span class="error">{{ $message }}</span>@enderror
                            @error('blocked_countries.*')<span class="error">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    <div class="geo-notice">
                        <i class="fa-solid fa-shield-halved" aria-hidden="true"></i>
                        <span>
                            <b>@lang('Compliance tip')</b>: @lang('A country cannot appear in both lists. If both are empty, the marketplace is open to all countries by default.')
                        </span>
                    </div>
                </section>
            </div>

            {{-- ── Sticky save bar ── --}}
            <div class="settings-savebar" data-settings-savebar data-pristine="true">
                <span class="settings-savebar__hint">
                    <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
                    <span>@lang('Changes take effect immediately for new orders. Existing trades follow their original rules.')</span>
                    <span class="settings-savebar__unsaved">@lang('Unsaved changes')</span>
                </span>
                <button type="button" class="fb-btn fb-btn--ghost fb-btn--sm" data-savebar-reset>
                    <i class="fa-solid fa-arrow-rotate-left" aria-hidden="true"></i>
                    <span>@lang('Discard')</span>
                </button>
                <a href="{{ route('admin.p2p.index') }}" class="fb-btn fb-btn--ghost fb-btn--sm">@lang('Cancel')</a>
                <button type="submit" class="fb-btn fb-btn--primary fb-btn--sm">
                    <i class="fa-solid fa-check" aria-hidden="true"></i>
                    <span>@lang('Save changes')</span>
                </button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            var form = document.getElementById('p2p-settings-form');
            if (!form) return;

            // ── Fee calculator ──────────────────────────────────────────────
            var calc        = form.querySelector('[data-fee-calc]');
            var baseInput   = calc && calc.querySelector('[data-fee-calc-base]');
            var makerOut    = calc && calc.querySelector('[data-fee-calc-maker]');
            var takerOut    = calc && calc.querySelector('[data-fee-calc-taker]');
            var totalOut    = calc && calc.querySelector('[data-fee-calc-total]');
            var makerInput  = form.querySelector('[data-settings-fee="maker"]');
            var takerInput  = form.querySelector('[data-settings-fee="taker"]');

            function fmt(n) {
                if (!isFinite(n)) return '0.00';
                return n.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }

            function recalcFees() {
                if (!calc) return;
                var base  = parseFloat(baseInput.value) || 0;
                var maker = parseFloat(makerInput.value) || 0;
                var taker = parseFloat(takerInput.value) || 0;
                var makerCut = base * (maker / 100);
                var takerCut = base * (taker / 100);
                makerOut.textContent = fmt(makerCut);
                takerOut.textContent = fmt(takerCut);
                totalOut.textContent = fmt(makerCut + takerCut);
            }

            if (calc) {
                [baseInput, makerInput, takerInput].forEach(function (el) {
                    el && el.addEventListener('input', recalcFees);
                });
                recalcFees();
            }

            // ── Timing preview ──────────────────────────────────────────────
            var expiryInput  = form.querySelector('[data-settings-expiry]');
            var disputeInput = form.querySelector('[data-settings-dispute]');
            var segPay       = form.querySelector('[data-timing-seg-pay]');
            var segDispute   = form.querySelector('[data-timing-seg-dispute]');
            var totalEl      = form.querySelector('[data-timing-total]');

            function recalcTiming() {
                var pay     = Math.max(0, parseInt(expiryInput.value, 10) || 0);
                var dispute = Math.max(0, parseInt(disputeInput.value, 10) || 0);
                var total   = pay + dispute;
                if (totalEl) totalEl.textContent = total;
                if (total > 0 && segPay && segDispute) {
                    var payPct = (pay / total) * 100;
                    segPay.style.width = payPct + '%';
                    segDispute.style.left = payPct + '%';
                    segDispute.style.width = (100 - payPct) + '%';
                }
            }

            if (expiryInput && disputeInput) {
                expiryInput.addEventListener('input', recalcTiming);
                disputeInput.addEventListener('input', recalcTiming);
                recalcTiming();
            }

            // ── Country pickers (chips + search + click-to-toggle list) ───
            ['allowed', 'blocked'].forEach(function (key) {
                var select  = form.querySelector('[data-country-select="' + key + '"]');
                var chips   = form.querySelector('[data-country-chips="' + key + '"]');
                var search  = form.querySelector('[data-country-search="' + key + '"]');
                var count   = form.querySelector('[data-country-count="' + key + '"]');
                var optList = form.querySelector('[data-country-options="' + key + '"]');
                var emptyEl = form.querySelector('[data-country-empty="' + key + '"]');

                if (!select || !optList) return;

                var optionByValue = {};
                Array.prototype.forEach.call(select.options, function (opt) {
                    optionByValue[opt.value] = opt;
                });

                function refreshChips() {
                    if (!chips) return;
                    chips.innerHTML = '';
                    Array.prototype.forEach.call(select.selectedOptions, function (opt) {
                        var chip = document.createElement('span');
                        chip.className = 'country-chip';
                        chip.innerHTML = '<span>' + opt.value + '</span>';
                        var btn = document.createElement('button');
                        btn.type = 'button';
                        btn.className = 'country-chip__remove';
                        btn.setAttribute('aria-label', @json(__('Remove')) + ' ' + opt.textContent.trim());
                        btn.innerHTML = '<i class="fa-solid fa-xmark" aria-hidden="true"></i>';
                        btn.addEventListener('click', function (e) {
                            e.preventDefault();
                            e.stopPropagation();
                            setSelected(opt.value, false);
                        });
                        chip.appendChild(btn);
                        chips.appendChild(chip);
                    });
                    if (count) count.textContent = select.selectedOptions.length;
                }

                function setSelected(value, isSelected) {
                    var opt = optionByValue[value];
                    if (!opt) return;
                    opt.selected = isSelected;
                    var li = optList.querySelector('[data-country-value="' + value + '"]');
                    if (li) {
                        li.classList.toggle('is-selected', isSelected);
                        li.setAttribute('aria-selected', isSelected ? 'true' : 'false');
                    }
                    select.dispatchEvent(new Event('change', { bubbles: true }));
                }

                function toggleValue(value) {
                    var opt = optionByValue[value];
                    if (!opt) return;
                    setSelected(value, !opt.selected);
                }

                optList.addEventListener('click', function (e) {
                    var li = e.target.closest('[data-country-value]');
                    if (!li) return;
                    toggleValue(li.getAttribute('data-country-value'));
                });

                optList.addEventListener('keydown', function (e) {
                    if (e.key !== 'Enter' && e.key !== ' ') return;
                    var li = e.target.closest('[data-country-value]');
                    if (!li) return;
                    e.preventDefault();
                    toggleValue(li.getAttribute('data-country-value'));
                });

                if (search) {
                    search.addEventListener('input', function () {
                        var q = this.value.trim().toLowerCase();
                        var visibleCount = 0;
                        Array.prototype.forEach.call(optList.querySelectorAll('[data-country-value]'), function (li) {
                            var match = q === '' || (li.getAttribute('data-country-search-text') || '').indexOf(q) !== -1;
                            li.classList.toggle('is-hidden', !match);
                            if (match) visibleCount++;
                        });
                        if (emptyEl) emptyEl.classList.toggle('is-hidden', visibleCount > 0);
                    });
                }

                select.addEventListener('change', refreshChips);
                refreshChips();
            });

            // ── Unsaved-changes detection ──────────────────────────────────
            var savebar  = form.querySelector('[data-settings-savebar]');
            var resetBtn = form.querySelector('[data-savebar-reset]');

            function serializeForm() {
                var fd = new FormData(form);
                var pairs = [];
                fd.forEach(function (v, k) { pairs.push(k + '=' + v); });
                return pairs.sort().join('&');
            }

            var initial = serializeForm();

            function updatePristine() {
                if (!savebar) return;
                savebar.setAttribute('data-pristine', serializeForm() === initial ? 'true' : 'false');
            }

            form.addEventListener('input', updatePristine);
            form.addEventListener('change', updatePristine);

            if (resetBtn) {
                resetBtn.addEventListener('click', function () {
                    form.reset();
                    // Re-trigger select option restore + chip refresh
                    ['allowed', 'blocked'].forEach(function (key) {
                        var select = form.querySelector('[data-country-select="' + key + '"]');
                        if (select) select.dispatchEvent(new Event('change', { bubbles: true }));
                    });
                    recalcFees();
                    recalcTiming();
                    updatePristine();
                });
            }

            // Warn on navigate-away with unsaved changes
            window.addEventListener('beforeunload', function (e) {
                if (savebar && savebar.getAttribute('data-pristine') === 'false') {
                    e.preventDefault();
                    e.returnValue = '';
                }
            });

            // Mark pristine after successful submit so navigation away doesn't warn
            form.addEventListener('submit', function () {
                initial = serializeForm();
                updatePristine();
            });
        })();
    </script>
@endpush
