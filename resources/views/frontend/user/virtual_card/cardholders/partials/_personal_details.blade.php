{{--
    Personal cardholder details — provider-universal layout.

    Sections map to the data every issuer (Stripe, Bitnob, StroWallet,
    Marqeta, Adyen, Lithic, Galileo, ...) asks for in some shape:
      1. Identity                — legal name, gender, DOB, nationality
      2. Contact                 — email, phone (E.164)
      3. Billing Address         — street/city/state/zip/country (AVS)
      4. Government ID           — passport / national ID / driver's licence
      5. Tax / Fiscal            — tax id + tax country
      6. Employment & AML        — occupation, employer, income, source of funds
      7. Compliance Declarations — PEP / sanctions self-disclosure
--}}
<div class="personal-fields">

    {{-- 1. Identity --}}
    <section class="vc-form-section">
        <header class="vc-form-section__head">
            <span class="vc-form-section__icon vc-form-section__icon--violet"><i class="fa-solid fa-user"></i></span>
            <div class="vc-form-section__copy">
                <div class="vc-form-section__title">{{ __('Identity') }}</div>
                <div class="vc-form-section__subtitle">
                    {{ __('Match the legal name on the cardholder\'s government ID exactly.') }}
                </div>
            </div>
        </header>

        <div class="row g-3">
            <div class="col-6 col-md-2">
                <label for="title" class="form-label">@lang('Title')</label>
                <select class="form-select" id="title" name="title">
                    <option value="">@lang('—')</option>
                    @foreach(['Mr', 'Ms', 'Mrs', 'Mx', 'Dr'] as $titleOption)
                        <option value="{{ $titleOption }}" @selected(old('title', $cardholder->title ?? '') === $titleOption)>{{ $titleOption }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-5 col-lg-4">
                <label for="first_name" class="form-label">@lang('First Name')</label>
                <input type="text" class="form-control" id="first_name" name="first_name"
                       value="{{ old('first_name', $cardholder->first_name ?? '') }}"
                       placeholder="@lang('Enter first name')">
            </div>
            <div class="col-md-5 col-lg-3">
                <label for="middle_name" class="form-label">@lang('Middle Name') <span class="vc-field__hint">@lang('Optional')</span></label>
                <input type="text" class="form-control" id="middle_name" name="middle_name"
                       value="{{ old('middle_name', $cardholder->middle_name ?? '') }}"
                       placeholder="@lang('Enter middle name')">
            </div>
            <div class="col-md-12 col-lg-3">
                <label for="last_name" class="form-label">@lang('Last Name')</label>
                <input type="text" class="form-control" id="last_name" name="last_name"
                       value="{{ old('last_name', $cardholder->last_name ?? '') }}"
                       placeholder="@lang('Enter last name')">
            </div>

            <div class="col-md-6 col-lg-3">
                <label for="gender" class="form-label">@lang('Gender')</label>
                <select class="form-select" id="gender" name="gender">
                    <option value="">@lang('Select Gender')</option>
                    @foreach ($genderOptions as $value => $label)
                        <option value="{{ $value }}" @selected(old('gender', $cardholder->gender->value ?? '') == $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6 col-lg-3">
                <label for="dob" class="form-label">@lang('Date of Birth')</label>
                <input type="date" class="form-control" id="dob" name="dob"
                       value="{{ old('dob', isset($cardholder) && $cardholder?->dob ? $cardholder->dob->format('Y-m-d') : '') }}"
                       placeholder="@lang('YYYY-MM-DD')">
            </div>
            <div class="col-md-6 col-lg-3">
                <label for="nationality" class="form-label">@lang('Nationality')</label>
                <select class="form-select" id="nationality" name="nationality">
                    <option value="">@lang('Select Nationality')</option>
                    @foreach($allCountries as $country)
                        <option value="{{ $country['code'] }}" @selected(old('nationality', $cardholder->nationality ?? '') == $country['code'])>
                            {{ title($country['name']) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6 col-lg-3">
                <label for="place_of_birth" class="form-label">@lang('Place of Birth') <span class="vc-field__hint">@lang('Optional')</span></label>
                <input type="text" class="form-control" id="place_of_birth" name="place_of_birth"
                       value="{{ old('place_of_birth', $cardholder->place_of_birth ?? '') }}"
                       placeholder="@lang('City, country')">
            </div>

            <div class="col-12">
                <label for="relation" class="form-label">@lang('Relation') <span class="vc-field__hint">@lang('Optional')</span></label>
                <input type="text" class="form-control" id="relation" name="relation"
                       value="{{ old('relation', $cardholder->relation ?? '') }}"
                       placeholder="@lang('e.g. Self, Spouse, Authorised user')">
            </div>
        </div>
    </section>

    {{-- 2. Contact --}}
    <section class="vc-form-section">
        <header class="vc-form-section__head">
            <span class="vc-form-section__icon vc-form-section__icon--green"><i class="fa-solid fa-envelope"></i></span>
            <div class="vc-form-section__copy">
                <div class="vc-form-section__title">{{ __('Contact') }}</div>
                <div class="vc-form-section__subtitle">
                    {{ __('Used for OTPs, statements, and provider notifications.') }}
                </div>
            </div>
        </header>

        <div class="row g-3">
            <div class="col-md-6">
                <label for="email" class="form-label">@lang('Email')</label>
                <input type="email" class="form-control" id="email" name="email"
                       value="{{ old('email', $cardholder->email ?? '') }}"
                       placeholder="@lang('name@example.com')">
            </div>
            <div class="col-md-2">
                <label for="phone_country_code" class="form-label">@lang('Code')</label>
                <input type="text" class="form-control" id="phone_country_code" name="phone_country_code"
                       value="{{ old('phone_country_code', $cardholder->phone_country_code ?? '') }}"
                       placeholder="+1">
            </div>
            <div class="col-md-4">
                <label for="mobile" class="form-label">@lang('Mobile')</label>
                <input type="text" class="form-control" id="mobile" name="mobile"
                       value="{{ old('mobile', $cardholder->mobile ?? $cardholder->phone ?? '') }}"
                       placeholder="@lang('555 0100 200')">
            </div>
        </div>
    </section>

    {{-- 3. Billing Address --}}
    <section class="vc-form-section">
        <header class="vc-form-section__head">
            <span class="vc-form-section__icon vc-form-section__icon--amber"><i class="fa-solid fa-location-dot"></i></span>
            <div class="vc-form-section__copy">
                <div class="vc-form-section__title">{{ __('Billing Address') }}</div>
                <div class="vc-form-section__subtitle">
                    {{ __('The full street address tied to the cardholder. Required by most providers for AVS.') }}
                </div>
            </div>
        </header>

        <div class="row g-3">
            <div class="col-12">
                <label for="address_line1" class="form-label">@lang('Address Line 1')</label>
                <input type="text" class="form-control" id="address_line1" name="address_line1"
                       value="{{ old('address_line1', $cardholder->address_line1 ?? '') }}"
                       placeholder="@lang('Street address, P.O. box, company name, c/o')">
            </div>
            <div class="col-12">
                <label for="address_line2" class="form-label">@lang('Address Line 2') <span class="vc-field__hint">@lang('Optional')</span></label>
                <input type="text" class="form-control" id="address_line2" name="address_line2"
                       value="{{ old('address_line2', $cardholder->address_line2 ?? '') }}"
                       placeholder="@lang('Apartment, suite, unit, building, floor, etc.')">
            </div>
            <div class="col-md-6 col-lg-3">
                <label for="city" class="form-label">@lang('City')</label>
                <input type="text" class="form-control" id="city" name="city"
                       value="{{ old('city', $cardholder->city ?? '') }}"
                       placeholder="@lang('Enter city')">
            </div>
            <div class="col-md-6 col-lg-3">
                <label for="state" class="form-label">@lang('State / Province')</label>
                <input type="text" class="form-control" id="state" name="state"
                       value="{{ old('state', $cardholder->state ?? '') }}"
                       placeholder="@lang('Enter state')">
            </div>
            <div class="col-md-6 col-lg-3">
                <label for="postal_code" class="form-label">@lang('Postal Code')</label>
                <input type="text" class="form-control" id="postal_code" name="postal_code"
                       value="{{ old('postal_code', $cardholder->postal_code ?? '') }}"
                       placeholder="@lang('ZIP / postal')">
            </div>
            <div class="col-md-6 col-lg-3">
                <label for="country" class="form-label">@lang('Country')</label>
                <select class="form-select" id="country" name="country">
                    <option value="">@lang('Select Country')</option>
                    @foreach($allCountries as $country)
                        <option value="{{ $country['code'] }}" @selected(old('country', $cardholder->country ?? '') == $country['code'])>
                            {{ title($country['name']) }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </section>

    {{-- 4. Government ID --}}
    @php
        $existingIdDoc = $cardholder->kyc_documents['id_document'] ?? null;
    @endphp
    <section class="vc-form-section">
        <header class="vc-form-section__head">
            <span class="vc-form-section__icon"><i class="fa-solid fa-id-card-clip"></i></span>
            <div class="vc-form-section__copy">
                <div class="vc-form-section__title">{{ __('Government ID') }}</div>
                <div class="vc-form-section__subtitle">
                    {{ __('Single source of truth for identity verification across every provider (Bitnob, StroWallet, Stripe, Marqeta, Adyen, Lithic).') }}
                </div>
            </div>
        </header>

        <div class="row g-3">
            <div class="col-md-6 col-lg-3">
                <label for="id_type" class="form-label">@lang('ID Type')</label>
                <select class="form-select" id="id_type" name="id_type">
                    <option value="">@lang('Select ID Type')</option>
                    @foreach([
                        'passport'         => __('Passport'),
                        'national_id'      => __('National ID'),
                        'drivers_license'  => __('Driver\'s License'),
                        'residence_permit' => __('Residence Permit'),
                        'voter_id'         => __('Voter ID'),
                    ] as $value => $label)
                        <option value="{{ $value }}" @selected(old('id_type', $cardholder->id_type ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6 col-lg-3">
                <label for="id_number" class="form-label">@lang('ID Number')</label>
                <input type="text" class="form-control" id="id_number" name="id_number"
                       value="{{ old('id_number', $cardholder->id_number ?? '') }}"
                       placeholder="@lang('Enter ID number')">
            </div>
            <div class="col-md-6 col-lg-3">
                <label for="id_issue_country" class="form-label">@lang('Issuing Country')</label>
                <select class="form-select" id="id_issue_country" name="id_issue_country">
                    <option value="">@lang('Select country')</option>
                    @foreach($allCountries as $country)
                        <option value="{{ $country['code'] }}" @selected(old('id_issue_country', $cardholder->id_issue_country ?? '') == $country['code'])>
                            {{ title($country['name']) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6 col-lg-3">
                <label for="id_issue_date" class="form-label">@lang('Issue Date') <span class="vc-field__hint">@lang('Optional')</span></label>
                <input type="date" class="form-control" id="id_issue_date" name="id_issue_date"
                       value="{{ old('id_issue_date', isset($cardholder) && $cardholder?->id_issue_date ? $cardholder->id_issue_date->format('Y-m-d') : '') }}">
            </div>
            <div class="col-md-6 col-lg-3">
                <label for="id_expiry" class="form-label">@lang('Expiry Date')</label>
                <input type="date" class="form-control" id="id_expiry" name="id_expiry"
                       value="{{ old('id_expiry', isset($cardholder) && $cardholder?->id_expiry ? $cardholder->id_expiry->format('Y-m-d') : '') }}">
            </div>

            <div class="col-md-12 col-lg-9">
                <label for="id_document" class="form-label">
                    @lang('ID Document')
                    @if($existingIdDoc)
                        <span class="vc-field__hint">@lang('Replace existing file (optional)')</span>
                    @endif
                </label>
                <input type="file" class="form-control" id="id_document" name="id_document"
                       accept="image/jpeg,image/png,image/webp,application/pdf">
                <div class="vc-field__help">
                    {{ __('Clear photo or scan of the ID front (and back if applicable). JPG, PNG, WEBP or PDF, up to 8 MB.') }}
                    @if($existingIdDoc)
                        <a href="{{ asset($existingIdDoc) }}" target="_blank" rel="noopener" class="vc-field__doc-link">
                            <i class="fa-solid fa-file-arrow-down"></i> {{ __('View current document') }}
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </section>

    {{-- 5. Tax / Fiscal --}}
    <section class="vc-form-section">
        <header class="vc-form-section__head">
            <span class="vc-form-section__icon vc-form-section__icon--violet"><i class="fa-solid fa-receipt"></i></span>
            <div class="vc-form-section__copy">
                <div class="vc-form-section__title">{{ __('Tax & Fiscal') }}</div>
                <div class="vc-form-section__subtitle">
                    {{ __('US SSN / ITIN, Indian PAN, EU TIN — required by providers operating in regulated markets.') }}
                </div>
            </div>
        </header>

        <div class="row g-3">
            <div class="col-md-6">
                <label for="tax_id" class="form-label">@lang('Tax ID') <span class="vc-field__hint">@lang('Optional')</span></label>
                <input type="text" class="form-control" id="tax_id" name="tax_id"
                       value="{{ old('tax_id', $cardholder->tax_id ?? '') }}"
                       placeholder="@lang('SSN / ITIN / PAN / TIN')">
            </div>
            <div class="col-md-6">
                <label for="tax_country" class="form-label">@lang('Tax Country')</label>
                <select class="form-select" id="tax_country" name="tax_country">
                    <option value="">@lang('Select country')</option>
                    @foreach($allCountries as $country)
                        <option value="{{ $country['code'] }}" @selected(old('tax_country', $cardholder->tax_country ?? '') == $country['code'])>
                            {{ title($country['name']) }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </section>

    {{-- 6. Employment & AML --}}
    <section class="vc-form-section">
        <header class="vc-form-section__head">
            <span class="vc-form-section__icon vc-form-section__icon--green"><i class="fa-solid fa-briefcase"></i></span>
            <div class="vc-form-section__copy">
                <div class="vc-form-section__title">{{ __('Employment & Source of Funds') }}</div>
                <div class="vc-form-section__subtitle">
                    {{ __('Anti-money-laundering disclosure. Some providers will reject issuance without this.') }}
                </div>
            </div>
        </header>

        <div class="row g-3">
            <div class="col-md-6">
                <label for="occupation" class="form-label">@lang('Occupation')</label>
                <input type="text" class="form-control" id="occupation" name="occupation"
                       value="{{ old('occupation', $cardholder->occupation ?? '') }}"
                       placeholder="@lang('e.g. Software Engineer, Accountant')">
            </div>
            <div class="col-md-6">
                <label for="employer" class="form-label">@lang('Employer') <span class="vc-field__hint">@lang('Optional')</span></label>
                <input type="text" class="form-control" id="employer" name="employer"
                       value="{{ old('employer', $cardholder->employer ?? '') }}"
                       placeholder="@lang('Company name or "Self-employed"')">
            </div>
            <div class="col-md-6">
                <label for="annual_income" class="form-label">@lang('Annual Income (USD)') <span class="vc-field__hint">@lang('Optional')</span></label>
                <input type="text" class="form-control" id="annual_income" name="annual_income"
                       value="{{ old('annual_income', $cardholder->annual_income ?? '') }}"
                       placeholder="@lang('e.g. 60000')">
            </div>
            <div class="col-md-6">
                <label for="source_of_funds" class="form-label">@lang('Source of Funds')</label>
                <select class="form-select" id="source_of_funds" name="source_of_funds">
                    <option value="">@lang('Select')</option>
                    @foreach([
                        'salary'      => __('Salary'),
                        'business'    => __('Business income'),
                        'investment'  => __('Investments / dividends'),
                        'savings'     => __('Personal savings'),
                        'inheritance' => __('Inheritance'),
                        'other'       => __('Other'),
                    ] as $value => $label)
                        <option value="{{ $value }}" @selected(old('source_of_funds', $cardholder->source_of_funds ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </section>

    {{-- 7. Compliance Declarations --}}
    <section class="vc-form-section">
        <header class="vc-form-section__head">
            <span class="vc-form-section__icon vc-form-section__icon--amber"><i class="fa-solid fa-shield-halved"></i></span>
            <div class="vc-form-section__copy">
                <div class="vc-form-section__title">{{ __('Compliance Declarations') }}</div>
                <div class="vc-form-section__subtitle">
                    {{ __('Self-declared. Providers run their own checks; misrepresentation can void the card.') }}
                </div>
            </div>
        </header>

        <div class="row g-3">
            <div class="col-md-6">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="pep_flag" name="pep_flag" value="1"
                           @checked(old('pep_flag', $cardholder->pep_flag ?? false))>
                    <label class="form-check-label" for="pep_flag">
                        @lang('I am a Politically Exposed Person (PEP)')
                    </label>
                </div>
                <div class="vc-field__hint">{{ __('Or am a close associate / family member of one.') }}</div>
            </div>
            <div class="col-md-6">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="sanctions_flag" name="sanctions_flag" value="1"
                           @checked(old('sanctions_flag', $cardholder->sanctions_flag ?? false))>
                    <label class="form-check-label" for="sanctions_flag">
                        @lang('I am subject to international sanctions')
                    </label>
                </div>
                <div class="vc-field__hint">{{ __('Including any list maintained by OFAC, UN, EU, UK, or local authorities.') }}</div>
            </div>
        </div>
    </section>

</div>
