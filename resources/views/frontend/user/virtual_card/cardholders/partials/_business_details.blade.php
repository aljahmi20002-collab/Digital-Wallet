{{--
    Business cardholder (KYB) — provider-universal layout.

    Sections cover everything Stripe Connect, Bitnob business, Marqeta KYB,
    Lithic, Adyen, Galileo, etc. ask for during onboarding:
      1. Legal Identity      — registered name + DBA + entity type
      2. Tax & Registration  — TIN, registration number, MCC, website
      3. Business Contact    — point of contact + phone country code
      4. Registered Address  — incorporated address (AVS-equivalent)
      5. Beneficial Owners   — UBOs ≥ 25% (KYB requirement)
--}}
@php
    $businessOwners = old('beneficial_owners', $business->beneficial_owners ?? []);
    if (! is_array($businessOwners) || count($businessOwners) === 0) {
        $businessOwners = [['name'=>'', 'dob'=>'', 'ownership_pct'=>'', 'country'=>'', 'id_type'=>'', 'id_number'=>'']];
    }
@endphp

<div class="business-fields">

    {{-- 1. Legal Identity --}}
    <section class="vc-form-section">
        <header class="vc-form-section__head">
            <span class="vc-form-section__icon vc-form-section__icon--violet"><i class="fa-solid fa-building"></i></span>
            <div class="vc-form-section__copy">
                <div class="vc-form-section__title">{{ __('Legal Identity') }}</div>
                <div class="vc-form-section__subtitle">
                    {{ __('Match the legal entity exactly as it appears on incorporation documents.') }}
                </div>
            </div>
        </header>

        <div class="row g-3">
            <div class="col-md-6">
                <label for="business_name" class="form-label">@lang('Legal Business Name')</label>
                <input type="text" class="form-control" id="business_name" name="business_name"
                       value="{{ old('business_name', $business->business_name ?? '') }}"
                       placeholder="@lang('Enter legal business name')">
            </div>
            <div class="col-md-6">
                <label for="trading_name" class="form-label">@lang('Trading Name (DBA)') <span class="vc-field__hint">@lang('Optional')</span></label>
                <input type="text" class="form-control" id="trading_name" name="trading_name"
                       value="{{ old('trading_name', $business->trading_name ?? '') }}"
                       placeholder="@lang('Doing-business-as / brand name')">
            </div>
            <div class="col-md-6 col-lg-4">
                <label for="business_type" class="form-label">@lang('Entity Type')</label>
                <select class="form-select" id="business_type" name="business_type">
                    <option value="">@lang('Select entity type')</option>
                    @foreach([
                        'sole_proprietor' => __('Sole Proprietor'),
                        'partnership'     => __('Partnership'),
                        'llc'             => __('LLC'),
                        'private_limited' => __('Private Limited'),
                        'public_limited'  => __('Public Limited'),
                        'corporation'     => __('Corporation'),
                        'non_profit'      => __('Non-profit'),
                        'trust'           => __('Trust'),
                    ] as $value => $label)
                        <option value="{{ $value }}" @selected(old('business_type', $business->business_type ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6 col-lg-4">
                <label for="incorporation_date" class="form-label">@lang('Date of Incorporation')</label>
                <input type="date" class="form-control" id="incorporation_date" name="incorporation_date"
                       value="{{ old('incorporation_date', isset($business->incorporation_date) && $business->incorporation_date ? $business->incorporation_date->format('Y-m-d') : '') }}">
            </div>
            <div class="col-md-12 col-lg-4">
                <label for="incorporation_country" class="form-label">@lang('Country of Incorporation')</label>
                <select class="form-select" id="incorporation_country" name="incorporation_country">
                    <option value="">@lang('Select country')</option>
                    @foreach($allCountries as $country)
                        <option value="{{ $country['code'] }}" @selected(old('incorporation_country', $business->incorporation_country ?? '') == $country['code'])>
                            {{ title($country['name']) }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </section>

    {{-- 2. Tax & Registration --}}
    <section class="vc-form-section">
        <header class="vc-form-section__head">
            <span class="vc-form-section__icon"><i class="fa-solid fa-scale-balanced"></i></span>
            <div class="vc-form-section__copy">
                <div class="vc-form-section__title">{{ __('Tax & Registration') }}</div>
                <div class="vc-form-section__subtitle">
                    {{ __('Registration number, tax id, industry classification and your public website.') }}
                </div>
            </div>
        </header>

        <div class="row g-3">
            <div class="col-md-6">
                <label for="registration_number" class="form-label">@lang('Registration Number')</label>
                <input type="text" class="form-control" id="registration_number" name="registration_number"
                       value="{{ old('registration_number', $business->registration_number ?? '') }}"
                       placeholder="@lang('Enter registration number')">
            </div>
            <div class="col-md-6">
                <label for="tin" class="form-label">@lang('Tax ID / TIN / EIN / VAT')</label>
                <input type="text" class="form-control" id="tin" name="tin"
                       value="{{ old('tin', $business->tin ?? '') }}"
                       placeholder="@lang('Enter tax identification number')">
            </div>
            <div class="col-md-6 col-lg-4">
                <label for="industry" class="form-label">@lang('Industry') <span class="vc-field__hint">@lang('Optional')</span></label>
                <input type="text" class="form-control" id="industry" name="industry"
                       value="{{ old('industry', $business->industry ?? '') }}"
                       placeholder="@lang('e.g. SaaS, Marketing, Retail')">
            </div>
            <div class="col-md-6 col-lg-3">
                <label for="mcc_code" class="form-label">@lang('MCC Code') <span class="vc-field__hint">@lang('Optional')</span></label>
                <input type="text" class="form-control" id="mcc_code" name="mcc_code"
                       value="{{ old('mcc_code', $business->mcc_code ?? '') }}"
                       placeholder="@lang('e.g. 5734')">
            </div>
            <div class="col-md-12 col-lg-5">
                <label for="website_url" class="form-label">@lang('Website') <span class="vc-field__hint">@lang('Optional')</span></label>
                <input type="url" class="form-control" id="website_url" name="website_url"
                       value="{{ old('website_url', $business->website_url ?? '') }}"
                       placeholder="https://example.com">
            </div>
        </div>
    </section>

    {{-- 3. Business Contact --}}
    <section class="vc-form-section">
        <header class="vc-form-section__head">
            <span class="vc-form-section__icon vc-form-section__icon--green"><i class="fa-solid fa-headset"></i></span>
            <div class="vc-form-section__copy">
                <div class="vc-form-section__title">{{ __('Business Contact') }}</div>
                <div class="vc-form-section__subtitle">
                    {{ __('The point of contact a provider can reach for compliance or disputes.') }}
                </div>
            </div>
        </header>

        <div class="row g-3">
            <div class="col-md-6">
                <label for="contact_email" class="form-label">@lang('Contact Email')</label>
                <input type="email" class="form-control" id="contact_email" name="contact_email"
                       value="{{ old('contact_email', $business->contact_email ?? '') }}"
                       placeholder="@lang('Enter contact email')">
            </div>
            <div class="col-md-2">
                <label for="phone_country_code_b" class="form-label">@lang('Code')</label>
                <input type="text" class="form-control" id="phone_country_code_b" name="phone_country_code_b"
                       value="{{ old('phone_country_code_b', $business->phone_country_code ?? '') }}"
                       placeholder="+1">
            </div>
            <div class="col-md-4">
                <label for="contact_phone" class="form-label">@lang('Contact Phone')</label>
                <input type="text" class="form-control" id="contact_phone" name="contact_phone"
                       value="{{ old('contact_phone', $business->contact_phone ?? '') }}"
                       placeholder="@lang('Business phone number')">
            </div>
        </div>
    </section>

    {{-- 4. Registered Address --}}
    <section class="vc-form-section">
        <header class="vc-form-section__head">
            <span class="vc-form-section__icon vc-form-section__icon--amber"><i class="fa-solid fa-location-dot"></i></span>
            <div class="vc-form-section__copy">
                <div class="vc-form-section__title">{{ __('Registered Address') }}</div>
                <div class="vc-form-section__subtitle">
                    {{ __('The street address on file with your registrar / tax authority.') }}
                </div>
            </div>
        </header>

        <div class="row g-3">
            <div class="col-12">
                <label for="address_line1_b" class="form-label">@lang('Address Line 1')</label>
                <input type="text" class="form-control" id="address_line1_b" name="address_line1_b"
                       value="{{ old('address_line1_b', $business->address_line1 ?? '') }}"
                       placeholder="@lang('Business street address')">
            </div>
            <div class="col-12">
                <label for="address_line2_b" class="form-label">@lang('Address Line 2') <span class="vc-field__hint">@lang('Optional')</span></label>
                <input type="text" class="form-control" id="address_line2_b" name="address_line2_b"
                       value="{{ old('address_line2_b', $business->address_line2 ?? '') }}"
                       placeholder="@lang('Suite, floor, etc.')">
            </div>
            <div class="col-md-6 col-lg-3">
                <label for="city_b" class="form-label">@lang('City')</label>
                <input type="text" class="form-control" id="city_b" name="city_b"
                       value="{{ old('city_b', $business->city ?? '') }}"
                       placeholder="@lang('Enter city')">
            </div>
            <div class="col-md-6 col-lg-3">
                <label for="state_b" class="form-label">@lang('State / Province')</label>
                <input type="text" class="form-control" id="state_b" name="state_b"
                       value="{{ old('state_b', $business->state ?? '') }}"
                       placeholder="@lang('Enter state')">
            </div>
            <div class="col-md-6 col-lg-3">
                <label for="postal_code_b" class="form-label">@lang('Postal Code')</label>
                <input type="text" class="form-control" id="postal_code_b" name="postal_code_b"
                       value="{{ old('postal_code_b', $business->postal_code ?? '') }}"
                       placeholder="@lang('ZIP / postal')">
            </div>
            <div class="col-md-6 col-lg-3">
                <label for="country_b" class="form-label">@lang('Country')</label>
                <select class="form-select" id="country_b" name="country_b">
                    <option value="">@lang('Select Country')</option>
                    @foreach($allCountries as $country)
                        <option value="{{ $country['code']}}" @selected(old('country_b', $business->country ?? '') == $country['code'])>
                            {{ title($country['name']) }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </section>

    {{-- 5. Beneficial Owners (UBO) --}}
    <section class="vc-form-section" data-vc-ubo-section>
        <header class="vc-form-section__head">
            <span class="vc-form-section__icon vc-form-section__icon--violet"><i class="fa-solid fa-user-tie"></i></span>
            <div class="vc-form-section__copy">
                <div class="vc-form-section__title">{{ __('Beneficial Owners') }}</div>
                <div class="vc-form-section__subtitle">
                    {{ __('Anyone who owns or controls 25% or more of the entity. Required by KYB regulators worldwide.') }}
                </div>
            </div>
        </header>

        <div class="vc-ubo-list" data-vc-ubo-list>
            @foreach($businessOwners as $i => $owner)
                <div class="vc-ubo-row" data-vc-ubo-row>
                    <div class="row g-3">
                        <div class="col-md-6 col-lg-4">
                            <label class="form-label">@lang('Full Name')</label>
                            <input type="text" class="form-control" name="beneficial_owners[{{ $i }}][name]"
                                   value="{{ $owner['name'] ?? '' }}" placeholder="@lang('Owner full name')">
                        </div>
                        <div class="col-md-6 col-lg-2">
                            <label class="form-label">@lang('DOB')</label>
                            <input type="date" class="form-control" name="beneficial_owners[{{ $i }}][dob]"
                                   value="{{ $owner['dob'] ?? '' }}">
                        </div>
                        <div class="col-md-6 col-lg-2">
                            <label class="form-label">@lang('Ownership %')</label>
                            <input type="text" class="form-control" name="beneficial_owners[{{ $i }}][ownership_pct]"
                                   value="{{ $owner['ownership_pct'] ?? '' }}" placeholder="25">
                        </div>
                        <div class="col-md-6 col-lg-2">
                            <label class="form-label">@lang('Country')</label>
                            <select class="form-select" name="beneficial_owners[{{ $i }}][country]">
                                <option value="">@lang('—')</option>
                                @foreach($allCountries as $country)
                                    <option value="{{ $country['code'] }}" @selected(($owner['country'] ?? '') === $country['code'])>
                                        {{ title($country['name']) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 col-lg-2">
                            <label class="form-label">@lang('ID Type')</label>
                            <select class="form-select" name="beneficial_owners[{{ $i }}][id_type]">
                                <option value="">@lang('—')</option>
                                @foreach([
                                    'passport' => __('Passport'),
                                    'national_id' => __('National ID'),
                                    'drivers_license' => __('Driver\'s License'),
                                ] as $value => $label)
                                    <option value="{{ $value }}" @selected(($owner['id_type'] ?? '') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-12 col-lg-10">
                            <label class="form-label">@lang('ID Number')</label>
                            <input type="text" class="form-control" name="beneficial_owners[{{ $i }}][id_number]"
                                   value="{{ $owner['id_number'] ?? '' }}" placeholder="@lang('Enter ID number')">
                        </div>
                        <div class="col-md-12 col-lg-2 vc-ubo-row__actions">
                            <button type="button" class="btn btn-light-danger w-100" data-vc-ubo-remove>
                                <i class="fa-solid fa-trash"></i> @lang('Remove')
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <button type="button" class="btn btn-light-primary mt-2" data-vc-ubo-add>
            <i class="fa-solid fa-plus"></i> @lang('Add Beneficial Owner')
        </button>
    </section>

</div>
