{{-- Admin payment link list filter form. Posts as GET to the same route so
     query string remains pageable + reset-able. --}}
<div class="pla-filter mb-4">
    <form method="GET" action="{{ route('admin.payment-links.index') }}">
        <div class="pla-filter__row">
            <div class="pla-filter__field pla-filter__field--user">
                <label class="form-label" for="pla-search">@lang('Search')</label>
                <input type="text" id="pla-search" name="search" class="form-control form-control-sm"
                       placeholder="{{ __('Title, token, owner name/email') }}"
                       value="{{ $filters['search'] ?? '' }}">
            </div>

            <div class="pla-filter__field">
                <label class="form-label" for="pla-status">@lang('Status')</label>
                <select id="pla-status" name="status" class="form-select form-select-sm">
                    <option value="">@lang('All')</option>
                    @foreach($statuses as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['status'] ?? null) === $value)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="pla-filter__field">
                <label class="form-label" for="pla-merchant">@lang('Merchant Shop')</label>
                <select id="pla-merchant" name="merchant_id" class="form-select form-select-sm">
                    <option value="">@lang('All Shops')</option>
                    @foreach($merchants as $merchant)
                        <option value="{{ $merchant->id }}"
                            @selected((int) ($filters['merchant_id'] ?? 0) === (int) $merchant->id)>
                            {{ $merchant->business_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="pla-filter__field">
                <label class="form-label" for="pla-currency">@lang('Currency')</label>
                <select id="pla-currency" name="currency_id" class="form-select form-select-sm">
                    <option value="">@lang('All')</option>
                    @foreach($currencies as $currency)
                        <option value="{{ $currency->id }}"
                            @selected((int) ($filters['currency_id'] ?? 0) === (int) $currency->id)>
                            {{ $currency->code }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="pla-filter__field">
                <label class="form-label" for="pla-date-from">@lang('From')</label>
                <input type="date" id="pla-date-from" name="date_from" class="form-control form-control-sm"
                       value="{{ $filters['date_from'] ?? '' }}">
            </div>

            <div class="pla-filter__field">
                <label class="form-label" for="pla-date-to">@lang('To')</label>
                <input type="date" id="pla-date-to" name="date_to" class="form-control form-control-sm"
                       value="{{ $filters['date_to'] ?? '' }}">
            </div>

            <div class="pla-filter__field pla-filter__field--check">
                <div class="form-check pla-filter__check">
                    <input type="checkbox" name="has_payments" value="1"
                           id="pla-has-payments" class="form-check-input"
                           @checked(! empty($filters['has_payments']))>
                    <label class="form-check-label" for="pla-has-payments">
                        @lang('Has payments')
                    </label>
                </div>
            </div>

            <div class="pla-filter__actions">
                <button type="submit" class="btn btn-sm btn-primary d-inline-flex align-items-center gap-1">
                    <x-icon name="filter" height="14" width="14"/>
                    @lang('Filter')
                </button>
                @if(collect($filters)->filter(fn ($v) => $v !== null && $v !== '' && $v !== false)->isNotEmpty())
                    <a href="{{ route('admin.payment-links.index') }}"
                       class="btn btn-sm btn-light">@lang('Reset')</a>
                @endif
            </div>
        </div>
    </form>
</div>
